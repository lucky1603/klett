<?php

namespace App\Http\Controllers;

use App\Exports\AppUserExport;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Mail\FetchUsernames;
use App\Models\AppUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;

class AppUserController extends Controller
{

    public function register() {
        return view('appusers.register');
    }

    public function retrieveUsernames() {
        return view('appusers.retrieveusernames');
    }

    public function sendUsernamesPerMail(Request $request) {
        $data = $request->post();   
        $email = $data['email'];

        $users = User::where('email', '=', $email)->get();
        if($users->count() == 0) {
            $message = "Sorry, we could not find any user with the email address you provided.";
        } else {
            $message = "Please find attached a list of user names that match the email address you provided.";
        }

        $usernames = $users->map(function ($user) {
            return $user->name;
        });


        return Mail::to($email)
            ->send(new FetchUsernames($email, $message, $usernames));        
    }
}
