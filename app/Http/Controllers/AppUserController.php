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

    public function register() {
        return view('appusers.register');
    }

}
