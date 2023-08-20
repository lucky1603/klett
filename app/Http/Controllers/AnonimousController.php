<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AnonimousController extends Controller
{
    public function verify($token) {
        $user = User::where('remember_token', $token)->firstOrFail();
        $email = $user->email;
        if($user != null) {
            $user->setAttribute('email_verified_at', now());
            $user->save();

            return view('auth.passwords.change', ['token' => $token]);

            // return view('auth.changepassword')->with(
            //     ['token' => $token, 'email' => $user->getAttribute('email')]
            // );
        }

        return view('anonimous.wrongpage');
    }
}
