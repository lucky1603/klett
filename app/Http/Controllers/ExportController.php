<?php

namespace App\Http\Controllers;

use App\Exports\RemoteUsersExport;
use App\Exports\SelectedUsersExport;
use App\Models\Export;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ExportController extends Controller
{
    public function list() {
        return Export::all();
    }

    public function store(Request $request) {
        $data = $request->all();

        $export = Export::create([
            "username"=> $data["username"],
            "email"=> $data["email"],
            "firstName"=> $data["firstName"],
            "lastName"=> $data["lastName"],
        ]);

        return [
            'code' => 200,
            'message'=> 'Success',
        ];
    }

    public function export() {
        $exports = Export::all()->map(function($export) {
            return [
                'id'=> $export->id,
                'username' => $export->username,
                'email'=> $export->email,
                'firstName'=> $export->firstName,
                'lastName' => $export->lastName
            ];
        });

        // var_dump($exports->toArray());
        Excel::download(new SelectedUsersExport($exports), 'userexport.xlsx');
        return [
            'code'=> 200,
            'message'=> 'Success',
        ];
    }

    public function delete($id) {
        $export = Export::findOrFail($id);
        $export->delete();
        return [
            'code'=> 200,
            'message'=> 'Success',
        ];
    }

    public function deleteAll() {
        DB::table('exports')->delete();
        return [
            'code'=> 200,
            'message'=> 'Success',
        ];
    }
}
