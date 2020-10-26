<?php
/**
 * Created by PhpStorm.
 * User: COMPUTER SHAHR
 * Date: 10/24/2020
 * Time: 9:46 PM
 */

namespace App\Repositories;


use App\Models\User;


class UserRepository
{
	// در صورت ادامه همکاری . استفاده از دیزاین پترن
	public function findByEmail($email)
	{
		return User::query()->where('email', $email)->first();
	}
}