<?php

namespace App\Http\Controllers;

use App\Exports\ScheduledChangesExport;
use App\Models\ChangeUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ChangeUserController extends Controller
{
    public function listAll() {
        return ChangeUser::all();
    }

    public function list() {
        $paginator = ChangeUser::where('changed', 0)->paginate(10);
        $currentPage = $paginator->currentPage();
        $perPage = $paginator->perPage();
        $count = $paginator->total();

        $returnData = $paginator
            ->map(function($changeUser) {
                return [
                    "userId" => $changeUser->user_id,
                    "username" => $changeUser->username,
                    "email" => $changeUser->email,
                    "firstName" => $changeUser->firstName,
                    "lastName" => $changeUser->lastName,
                    "source" => $changeUser->source,
                    "role" => $changeUser->role,
                    "klfKorisnik" => $changeUser->klf_korisnik,
                    "pedagoskaSveska" => $changeUser->pedagoska_sveska,
                    "testomat" => $changeUser->testomat,
                    "changed" => $changeUser->changed
                ];
            });

        return [
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'count' => $count,
            'rows' => $returnData
        ];
    }

    public function store(Request $request) {
        $data = $request->post();
        if(!isset($data['user_id']) || in_array($data['user_id'],['null', 'undefined'])) {
            return null;
        }

        $changeUser = ChangeUser::where('user_id', $data['userId'])->first();
        if($changeUser == null) {
            $changeUser = ChangeUser::create([
                'user_id' => $data['userId'],
                'username' => $data['username'],
                'email' => $data['email'],
                'firstName' => $data['firstName'],
                'lastName' => $data['lastName'],
                'source' => $data['source'],
                'role' => $data['role'],
                'klf_korisnik' => $data['klfKorisnik'] == '0' ? false : true,
                'pedagoska_sveska' => $data['pedagoskaSveska'] == '0' ? false : true,
                'testomat' => $data['testomat'] == '0' ? false : true,
                'created_at' => $data['created']
            ]);
        }
        

        return $changeUser->id;
    }

    public function setChanged($userId) {
        $user = ChangeUser::where('user_id', $userId)->firstOrFail();
        $user->changed = true;
        $user->updated_at = now();
        $user->save();
    }

    public function getCountAll() {
        return ChangeUser::all()->count();
    }

    public function getCountDone() {
        return ChangeUser::where('changed', 1)->count();
    }

    public function deleteAll() {
        return DB::table('change_users')->delete();
    }

    public function export() {
        return Excel::download(new ScheduledChangesExport('Izvoz korisnika za sinhronizaciju', ChangeUser::all()), 'schedule.xlsx');
    }
}
