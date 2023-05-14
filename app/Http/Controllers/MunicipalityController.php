<?php

namespace App\Http\Controllers;

use App\Models\Municipality;
use Illuminate\Http\Request;

class MunicipalityController extends Controller
{
    // TODO index, when needed.

    /**
     * Get data, for async calls.
     */
    public function data() {
        return Municipality::all()->map(function($municipality) {
            return [
                "id" => $municipality->id,
                "name" => $municipality->name
            ];
        });
    }
}
