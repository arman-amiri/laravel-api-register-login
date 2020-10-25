<?php
/**
 * Created by PhpStorm.
 * User: COMPUTER SHAHR
 * Date: 8/30/2020
 * Time: 2:20 PM
 */

namespace App\Http\Controllers\Api\Front\Account;


// use App\Rules\ValidPassword;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Rules\ValidPassword;
use Carbon\Carbon;
use http\Env\Response;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;


class RegisterController extends Controller
{


	public function register(Request $r)
	{
	$r->validate([
		'name'            => 'nullable|max:100|string',
		'family'          => 'nullable|max:100|string',
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
		$user->code_create_at = Carbon::now()->addMinutes(30);
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

		if ($user->code_create_at < Carbon::now())
		{
			return ['status' => 'false', 'error' => 'تاریخ انقضای کد تمام شده.درخاست ارسال مجدد کد کنید '];
		}

		$user->verified      = 'Y';
		$user->verifiedAt    = Carbon::now();
		$user->code          = null;
		$user->code_create_at = null;
		$user->save();
		$token = auth()->guard('api')->login($user);

		return [
			'status' => 'OK',
			'token'  => $token,
			'user'   => $user,
		];

	}
}