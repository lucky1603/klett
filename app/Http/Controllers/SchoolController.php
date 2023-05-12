<?php

namespace App\Http\Controllers;

use App\Models\InstitutionType;
use App\Models\Municipality;
use App\Models\School;
use Illuminate\Http\Request;

class SchoolController extends Controller
{
    public function index() {
        return view('schools.index', ['schools' => School::all()]);
    }

    public function create() {
        return view('schools.create');
    }

    public function store(Request $request) {
        $request->validate([
            'municipality' => 'integer|gt:0',
            'institution_type' => 'integer|gt:0',
            'name' => 'required'
        ]);

        $data = $request->post();

        School::create([
            'municipality_id' => $data['municipality'],
            'institution_type_id' => $data['institution_type'],
            'name' => $data['name']
        ]);

        return $data;
    }

    public function getSchools() {
        return School::all()->load('institution_type', 'municipality')
        ->map(function($school) {
            return [
                "type" => $school->institution_type->name,
                "municipality" => $school->municipality->name,
                "school" => $school->name
            ];
        });
    }

    public function getMunicipalities() {
        return Municipality::all()->map(function($municipality) {
            return [
                "id" => $municipality->id,
                "name" => $municipality->name
            ];
        });
    }

    public function getInstitutionTypes() {
        return InstitutionType::all()->map(function($institutionType) {
            return [
                "id" => $institutionType->id,
                "name" => $institutionType->name
            ];
        });
    }
}
