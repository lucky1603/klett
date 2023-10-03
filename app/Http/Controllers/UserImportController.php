<?php

namespace App\Http\Controllers;

use App\Imports\EUcionicaImport;
use file;
use App\Models\UserImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class UserImportController extends Controller
{
    /**
     * Shows the list of user imports.
     */
    public function index() {
        return view('userimports.index');
    }

    /**
     * Gets the user import data for the table control.
     */
    public function data() {

        $paginator = UserImport::where('imported', false)->paginate();
        $currentPage = $paginator->currentPage();
        $perPage = $paginator->perPage();
        $count = $paginator->total();

        $rows = $paginator->map(function($user) {
            return [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'ime' => $user->ime,
                'prezime' => $user->prezime,
                'isTeacher' => $user->is_teacher == 0 ?  'NE' : 'DA',
                'source' => $user->source
            ];
        });

        return [
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'count' => $count,
            'rows' => $rows
        ];

    }

    /**
     * Updates user entry as imported.
     *
     * @userid - Id of the user in the user_imports table.
     */
    public function updateImported($userId) {
        $user = UserImport::find($userId);
        $user->imported = true;
        $user->save();
        return 0;
    }

    public function counts() {
        $total = UserImport::count();
        $imported = UserImport::where('imported', true)->count();

        $remains = $total - $imported;

        return [
            'total' => $total,
            'imported' => $imported,
            'remains' => $remains
        ];
    }

    public function reset() {
            return UserImport::where('imported', true)
                ->update([
                    'imported' => false
                ]);
    }

    public function setImport(Request $request)  {
        $data = $request->post();

        if($data['append'] == 'false') {
            // Delete existing imports.
            DB::table('user_imports')->delete();
        }

        Excel::import(new EUcionicaImport, $data['file'], 'public');

        return true;
    }

    public function files() {
        return collect(Storage::disk('public')->listContents())->map(function($file) {
            return $file['basename'];
        });


    }


}
