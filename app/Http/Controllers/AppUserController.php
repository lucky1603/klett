<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Models\AppUser;
use Illuminate\Http\Request;

class AppUserController extends Controller
{

    public function index() {
        return view('appusers.index');
    }

    public function getAppUsers() {
        return AppUser::all()
            ->load("school", "subjects", "professional_statuses", "country")
            ->map(function($appUser) {
                return [
                    "id" => $appUser->id,
                    "ime" => $appUser->ime,
                    "prezime" => $appUser->prezime,
                    "email" => $appUser->email,
                    "country" => $appUser->country->name,
                    "adresa" => $appUser->adresa,
                    "pb" => $appUser->pb,
                    "mesto" => $appUser->mesto,
                    "tel1" => $appUser->tel1,
                    "tel2" => $appUser->tel2,
                    "isTeacher" => $appUser->is_teacher == 1 ? 'DA' : 'NE',
                    "createdAt" => $appUser->created_at,
                    "updatedAt" => $appUser->updated_at,
                ];
            });
    }

    public function store(CreateUserRequest $request) {
        return $request->post();
    }
}
