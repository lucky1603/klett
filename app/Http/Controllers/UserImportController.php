<?php

namespace App\Http\Controllers;

use App\Models\UserImport;
use Illuminate\Http\Request;

class UserImportController extends Controller
{
    public function index() {
        return view('userimports.index');
    }

    public function data() {
        // Vrati ne-importovane korisnike.
        return UserImport::where('imported', false)->get();
    }


}
