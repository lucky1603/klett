<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Mail\UserCreated;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\CreateUserRequest;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {
        return view('users.index');
    }

    public function filter(Request $request) {
        $data = $request->post();

        $filter = [];
        if(isset($data['name']) && $data['name'] != null && $data['name'] != 'null') {
            $filter[] = ['name', 'like', $data['name'].'%'];
        }

        if(isset($data['email']) && $data['email'] != null && $data['email'] != 'null') {
            $filter[] = ['email', 'like', $data['email'].'%'];
        }


        if(count($filter) > 0) {
            $users = User::where($filter)->get()->load('roles');
        } else {
            $users = User::all()->load('roles');
        }

        $userData = [];
        foreach($users as $user) {
            $userData[] = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()->label
            ];
        }

        return $userData;

    }

    public function store(CreateUserRequest $request) {
        $data = $request->post();

        // Dodaj korisnika.
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make(Str::random(10))
        ]);

        // Podesi remember token.
        $user->setRememberToken(Str::random(60));
        $user->save();

        // Dodeli rolu.
        $user->assignRole(Role::find($data['role']));

        // Posalji email.
        $email = $data['email'];
        Mail::to($email)->send(new UserCreated($user));

        return redirect(route('users'));

    }

    public function updatePassword(Request $request) {
        $data = $request->post();

        $token = $data['token'];
        $user = User::where('remember_token', $token)->firstOrFail();
        $user->password = Hash::make($data['password']);
        $user->setRememberToken(null);
        $user->save();

        Auth::login($user);

        return redirect(route('remoteusers'));
    }

    public function getUserData(Request $request) {
        $data = $request->post();
        $token = $data['token'] ?? null;

        if($token != null) {
            $user = User::where('remember_token', $token)->firstOrFail();
            return [
                'name' => $user->name,
                'email' => $user->email,
            ];
        }

        $id = $data['id'] ?? null;

        if($id != null) {
            $user = User::find($id)->load('roles');
            return [
                'id' => $user->name,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->roles->first()->id,
            ];
        }

    }

    public function delete(Request $request) {
        $id = $request->post('id');
        $user = User::find($id);
        $user->delete();
        return redirect(route('users'));
    }

}
