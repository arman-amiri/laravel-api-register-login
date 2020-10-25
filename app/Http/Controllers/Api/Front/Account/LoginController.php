<?php
/**
 * Created by PhpStorm.
 * User: COMPUTER SHAHR
 * Date: 8/30/2020
 * Time: 2:20 PM
 */

// namespace App\Http\Controllers\Api;
namespace Modules\Auth\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Rules\ValidPassword;
use Modules\Users\Entities\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;


class LoginController extends Controller
{
	public function login(Request $r)
	{

		$r->validate([
			'email'    => 'required|max:250|email|string|exists:users,email',
			'password' => ['required', 'string', new ValidPassword()],
		]);

		$email    = $r->input('email');
		$password = $r->input('password');

		$user = User::where('email', $email)
			->first();

		if (!$user)
		{
			return ['status' => 'false', 'error' => 'نتیجه ای یافت نشد'];
		}

		if (Auth::attempt(['email' => $email, 'password' => $password]))
		{
			$token = auth()->guard('api')->login($user);

			return [
				'status' => 'OK',
				'token'  => $token,
				'user'   => $user,
			];
		}
		else
		{
			return ['status' => 'false', 'error' => 'اطلاعات وارد شده تطابق ندارد'];
		}

	}



	public function logout()
	{
		auth()->logout();

		return ['status' => 'OK'];
	}
}