<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;

class CountryController extends Controller
{
    // TODO index, when needed.

    /**
     * Gets data for async calls.
     */
    public function data() {
        return Country::all()->map(function($country) {
            return [
                "id" => $country->id,
                "code" => $country->code,
                "name" => $country->name
            ];
        });
    }
}
