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

    public function edit($shoolId) {
        return view('schools.edit', ['id' => $shoolId]);
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

    public function update(Request $request) {
        $request->validate([
            'municipality' => 'integer|gt:0',
            'institution_type' => 'integer|gt:0',
            'name' => 'required'
        ]);

        $data = $request->post();

        $school = School::find($data['id']);

        $school->municipality_id = $data['municipality'];
        $school->institution_type_id = $data['institution_type'];
        $school->name = $data['name'];

        $school->save();

        return $request->ip();
    }

    public function getSchools() {
        return School::all()->load('institution_type', 'municipality')
        ->map(function($school) {
            return [
                "id" => $school->id,
                "institution_type" => $school->institution_type->name,
                "municipality" => $school->municipality->name,
                "school" => $school->name
            ];
        });
    }

    public function getSchool($schoolId) {
        $school = School::find($schoolId);
        if($school != null) {
            return [
                "id" => $school->id,
                "municipality_id" => $school->municipality_id,
                "institution_type_id" => $school->institution_type_id,
                "name" => $school->name
            ];
        }

        return null;
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

    public function filterSchools(Request $request) {
        $data = $request->post();
        $municipalityId = $data['municipalityId'];
        $institutionTypeId = $data['institutionTypeId'];
        $schools = School::where(['institution_type_id' => $institutionTypeId, 'municipality_id' => $municipalityId])->get();

        return $schools->map(function($school) {
            return [
                "id" => $school->id,
                "name" => $school->name
            ];
        });
    }
}
