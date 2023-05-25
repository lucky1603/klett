<?php

namespace App\Http\Controllers;

use App\Exports\AppUserExport;
use App\Models\AppUser;
use Illuminate\Http\Request;
use App\Http\Requests\CreateUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Maatwebsite\Excel\Facades\Excel;

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

    public function getAppUser(Request $request) {

        $userId = $request->post('id');
        $user = AppUser::find($userId)->load('school', 'subjects', 'professional_statuses', 'country');

        if(isset($user->school)) {
            $school = [
                'id' => $user->school->id,
                'name' => $user->school->name,
                'municipality' => $user->school->municipality_id,
                'institutionType' => $user->school->institution_type_id
            ];
        }

        if(isset($user->subjects)) {
            $subjects = $user->subjects->map(function($subject) {
                return [
                    "id" => $subject->id,
                    "name" => $subject->name,
                ];
            });
        }


        if(isset($user->professional_statuses)) {
            $professionalStatuses = $user->professional_statuses->map(function($status) {
                return [
                    "id" => $status->id,
                    "name" => $status->name,
                ];
            });
        }

        if(isset($user->country)) {
            $country = [
                "id" => $user->country->id,
                "code" => $user->country->code,
                "name" => $user->country->name,
            ];
        }

        return [
            "id" => $user->id,
            "ime" => $user->ime,
            "prezime" => $user->prezime,
            "adresa" => $user->adresa,
            "pb" => $user->pb,
            "mesto" => $user->mesto,
            "country" => $country ?? null,
            "email" => $user->email,
            "tel1" => $user->tel1,
            "tel2" => $user->tel2,
            "isTeacher" => $user->is_teacher == 1 ? true : false,
            "school" => $school ?? null,
            "subjects" => $subjects ?? [],
            "professionalStatuses" => $professionalStatuses ?? []
        ];

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
            'password' => $data['password'],
        ]);

        if($appUser->is_teacher == true) {
            $appUser->school()->associate($data['school']);
            $appUser->subjects()->sync($data['subjects']);
            $appUser->professional_statuses()->sync($data['professionalStatuses']);
            $appUser->save();
        }

        return $appUser->id;
    }

    public function update(UpdateUserRequest $request) {
        $data = $request->post();
        $id = $data['id'];
        $user = AppUser::find($id)->load('school', 'country', 'subjects', 'professional_statuses');

        $user->ime = $data['ime'];
        $user->prezime = $data['prezime'];
        $user->email = $data['email'];
        $user->adresa = $data['adresa'];
        $user->pb = $data['pb'];
        $user->mesto = $data['mesto'];
        $user->tel1 = $data['tel1'];
        $user->tel2 = $data['tel2'];
        $user->country()->associate($data['country']);
        $user->is_teacher = $data['isTeacher'] == "true" ? true : false;
        $user->school()->associate($data['school']);
        $user->subjects()->sync($data['subjects']);
        $user->professional_statuses()->sync($data['professionalStatuses']);

        $user->save();

        return 0;
    }

    public function register() {
        return view('appusers.register');
    }

    public function export() {
        return Excel::download(new AppUserExport, 'appusers.xlsx');
    }
}
