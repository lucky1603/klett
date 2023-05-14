<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    // TODO index, when needed.

    /**
     * Gets data for async calls.
     */
    public function data() {
        return Subject::all()->map(function($subject) {
            return [
                "id" => $subject->id,
                "name" => $subject->name
            ];
        });
    }
}
