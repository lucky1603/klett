<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AnonimousController extends Controller
{
    public function verify($token) {
        $user = User::where('remember_token', $token)->firstOrFail();
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

    public function password(Request $request) {
        $data = $request->post();

        $token = $data['token'];
        $user = User::where('remember_token', $token)->firstOrFail();
        $user->password = Hash::make($data['password']);
        $user->setRememberToken(null);
        $user->save();

        Auth::login($user);

        return redirect(route('remoteusers'));
    }
}
