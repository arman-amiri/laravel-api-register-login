<?php
/**
 * Created by PhpStorm.
 * User: COMPUTER SHAHR
 * Date: 8/30/2020
 * Time: 2:20 PM
 */

namespace Modules\Auth\Http\Controllers;


// use App\Http\Controllers\Controller;
use App\Rules\ValidPassword;
use Modules\Users\Entities\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Nwidart\Modules\Routing\Controller;


class ResetPasswordController extends Controller
{
	public function emailReset(Request $r)
	{
		$r->validate([
			'email' => 'required|max:250|email|string|exists:users,email',
		]);

		$code = random_int(1202121, 9098945);

		$user                = User::where('email', $r->input('email'));
		$user->code          = $code;
		$user->codeCreatedAt = Carbon::now()->addMinutes(30);
		$user->save();

		$data = ['code' => $code];

		Mail::send(['html' => 'email.resetPassword'], $data, function(Message $message) use ($r)
		{
			$message->to($r->input('email'), $r->input('name'))
				->from('armanlegand1396@gmail.com', 'arman-amiri')
				->subject('reset-password');
		});

		return [
			'status' => 'OK',
			'user'   => $user,
		];
	}



	public function confirmEmailReset(Request $r)
	{
		$r->validate([
			'userId' => 'required|exists:users,id',
			'code'   => 'required|integer|exists:users,code',
		]);

		$user = User::where('id', $r->input('userId'))
			->where('code', $r->input('code'))->first();

		if (!$user)
		{
			return ['status' => 'false', 'error' => 'نتیجه ای یافت نشد'];
		}

		if ($user->code !== $r->input('code'))
		{
			return ['status' => 'false', 'error' => 'اطلاعات وارد شده مطابقت ندارد'];
		}

		if ($user->codeExpiration < Carbon::now())
		{
			return ['status' => 'false', 'error' => 'تاریخ انقضای کد تمام شده.درخاست ارسال مجدد کد کنید'];
		}

		$user->verified      = 'Y';
		$user->verifiedAt    = Carbon::now();
		$user->code          = null;
		$user->codeCreatedAt = null;
		$user->save();
		$token = auth()->guard('api')->login($user);

		return [
			'status' => 'OK',
			'token'  => $token,
			'user'   => $user,
		];

	}



	public function newPassword(Request $r)
	{
		$r->validate([
			'userId'          => 'required|exists:users,id',
			'email'           => 'required|exists:users,email',
			'password'        => ['required', 'string', new ValidPassword()],
			'passwordConfirm' => 'required_with:password|same:password',
		]);

		$user = User::where('email', $r->email)
			->where('id', $r->id)->first();

		if (!$user)
		{
			return ['status' => 'false', 'error' => 'مشکلی رخ داده است . دوباره تلاش کنید'];
		}


		$user->password = Hash::make($r->input('password'));

		$user->verified      = 'Y';
		$user->verifiedAt    = Carbon::now();
		$user->code          = null;
		$user->codeCreatedAt = null;
		$user->save();

		$token = auth()->guard('api')->login($user);

		return [
			'status' => 'OK',
			'token'  => $token,
			'user'   => $user,
		];

	}
}
