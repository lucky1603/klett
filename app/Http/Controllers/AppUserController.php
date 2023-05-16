<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateUserRequest;
use App\Models\AppUser;
use Illuminate\Http\Request;

class AppUserController extends Controller
{

    public function index(Request $request) {
        $ip = $request->ip();
        return view('appusers.index', ["ip" => $ip]);
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
        $data = $request->post();

        $appUser = AppUser::create([
            'ime' => $data['ime'],
            'prezime' => $data['prezime'],
            'email' => $data['email'],
            'adresa' => $data['adresa'],
            'pb' => $data['pb'],
            'mesto' => $data['mesto'],
            'country_id' => $data['country'],
            'tel1' => $data['tel1'],
            'tel2' => $data['tel2'],
            'is_teacher' => $data['isTeacher'] == "true" ? true : false,
            'password' => $data['password']
        ]);

        if($appUser->is_teacher == true) {
            $appUser->subjects()->sync($data['subjects']);
            $appUser->professional_statuses()->sync($data['professionalStatuses']);
        }

        return $appUser->id;
    }
}
