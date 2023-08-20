<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function list() {
        $roles = Role::all();

        $roleData = [];
        foreach($roles as $role) {
            $roleData[] = [
                'value' => $role->id,
                'text' => $role->label,
            ];
        }

        return $roleData;

    }
}
