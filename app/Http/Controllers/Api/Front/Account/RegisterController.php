<?php
/**
 * Created by PhpStorm.
 * User: COMPUTER SHAHR
 * Date: 8/30/2020
 * Time: 2:20 PM
 */

namespace Modules\Auth\Http\Controllers;


use App\Rules\ValidPassword;
use Modules\Users\Entities\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Nwidart\Modules\Routing\Controller;


class RegisterController extends Controller
{


	public function register(Request $r)
	{
		$r->validate([
			'name'            => 'nullable|max:50|string',
			'family'          => 'nullable|max:50|string',
			'email'           => 'required|max:250|email|string|unique:users',
			'password'        => ['required', 'string', new ValidPassword()],
			'passwordConfirm' => 'required_with:password|same:password',
		]);

		$code = random_int(12121, 98989);

		$user                = new User();
		$user->name          = $r->input('name');
		$user->family        = $r->input('family');
		$user->email         = $r->input('email');
		$user->password      = Hash::make($r->input('password'));
		$user->code          = $code;
		$user->codeCreatedAt = Carbon::now()->addMinutes(30);
		$user->save();

		$data = ['code' => $code];

		Mail::send(['html' => 'email.verified'], $data, function(Message $message) use ($r)
		{
			$message->to($r->input('email'), $r->input('name'))
				->from('armanlegand1396@gmail.com', 'arman-amiri')
				->subject('verified-account');
		});

		return [
			'status' => 'OK',
			'user'   => $user,
		];
	}



	public function confirmRegister(Request $r)
	{
		$r->validate([
			'email' => 'required|exists:users,email',
			'code'  => 'required|integer|exists:users,code',
		]);

		$user = User::where('email', $r->input('email'))
			->where('code', $r->input('code'))->first();

		if (!$user)
		{
			return ['status' => 'false', 'error' => 'نتیجه ای یافت نشد'];
		}

		if ($user->code != $r->input('code'))
		{
			return ['status' => 'false', 'error' => 'اطلاعات وارد شده مطابقت ندارد'];
		}

		if ($user->codeCreatedAt < Carbon::now())
		{
			return ['status' => 'false', 'error' => 'تاریخ انقضای کد تمام شده.درخاست ارسال مجدد کد کنید '];
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
}