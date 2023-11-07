<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\RequestEdit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ScheduledEdit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ChangePasswordRequest;

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

    public function password(ChangePasswordRequest $request) {
        $data = $request->post();

        $token = $data['token'];
        $user = User::where('remember_token', $token)->firstOrFail();
        $user->password = Hash::make($data['password']);
        $user->setRememberToken(null);
        $user->save();

        Auth::login($user);

        return redirect(route('remoteusers'));
    }

    public function apply() {
        return view('appusers.register');
    }

        /**
     * Get access token.
     */
    public function connectKeyCloak() {

        return Http::asForm()
        // ->withOptions(['verify' => false])
        ->post(env('KEYCLOAK_TOKEN_URL'), [
            "client_id" => "admin-cli",
            "username" => "admin",
            "password" => env('KEYCLOAK_AUTH_PASSWORD'),
            "grant_type" => "password"
        ]);
    }

    public function refreshCaptcha() {
        return captcha_img('klett');
    }

    public function requestEditProfile($email) {
        // Get user from database
        $response = $this->connectKeyCloak();
        $accessToken = $response->json('access_token');
        $userResponse = Http::withToken($accessToken)
            ->get(env('KEYCLOAK_API_USERS_URL').'?briefRepresentation=true&email='.$email);

        $users = $userResponse->json();
        if(count($users) > 0) {
            // Do something
            $userId = $users[0]['id'];
            $scheduledEdit = ScheduledEdit::create([
                'user_id' => $userId,
                'token' => Str::random(60),
                'validated' => false,
            ]);

            $name = $users[0]['firstName'].' '.$users[0]['lastName'];

            Mail::to($email)->send(new RequestEdit($name, $scheduledEdit->token));
            return view('anonimous.editconf');
        }

        return view('anonimous.editerr');

        // $scheduledEdit = ScheduledEdit::create([

        // ]);


    }

    public function testRequestEdit($email) {
        return view('anonimous.testrequestedit', ['email' => $email]);
    }
}
