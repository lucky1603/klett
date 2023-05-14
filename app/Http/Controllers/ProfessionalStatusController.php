<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProfessionalStatus;

class ProfessionalStatusController extends Controller
{
    //TODO index when needed.

    /**
     * Async call.
     */
    public function data() {
        return ProfessionalStatus::all()->map(function($professionalStatus) {
            return [
                "id" => $professionalStatus->id,
                "name" => $professionalStatus->name
            ];
        });
    }
}
