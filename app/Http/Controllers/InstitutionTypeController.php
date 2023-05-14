<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InstitutionType;

class InstitutionTypeController extends Controller
{
    // TODO index, when needed.

    /**
     * Gets the data for async calls.
     */
    public function data() {
        return InstitutionType::all()->map(function($institutionType) {
            return [
                "id" => $institutionType->id,
                "name" => $institutionType->name
            ];
        });
    }
}
