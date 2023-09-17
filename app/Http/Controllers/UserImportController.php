<?php

namespace App\Http\Controllers;

use App\Models\UserImport;
use Illuminate\Http\Request;

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


}
