<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\RemoteUsersExport;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;

class RemoteUserController extends Controller
{
    public function index() {
        return view('remoteusers.index');
    }

    public function connectKeyCloak() {

        return Http::asForm()->post(env('KEYCLOAK_TOKEN_URL'), [
            "client_id" => "admin-cli",
            "username" => "admin",
            "password" => "BiloKoji12@",
            "grant_type" => "password"
        ]);
    }

    public function getData(Request $request) {
        $token = $request->post('token');
        return Http::withToken($token)->get(ENV("KEYCLOAK_API_URL"));
    }

    public function create() {
        return view('remoteusers.create');
    }

    public function store(Request $request) {
        $data = $request->post();

        $response = Http::withToken($data['token'])
            ->asJson()
            ->post(env("KEYCLOAK_API_URL"), [
                "username" => $data['username'],
                "firstName" => $data['firstName'],
                "lastName" => $data['lastName'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" ? true : false,
                "attributes" => [
                    "subjects" => serialize($data['subjects']),
                    "professions" => serialize($data['professions']),
                    "township" => $data['township'],
                    "institution_type" => $data['institutionType'],
                    "institution" => $data['institution']
                ]
        ]);

        if($data['updatePassword'] == 'true') {
            $items = explode("/", $response->header("Location"));
            $userId = $items[count($items) - 1];

            return Http::withToken($data['token'])->withBody('["UPDATE_PASSWORD"]', 'application/json')
                ->put(env("KEYCLOAK_API_URL").$userId."/execute-actions-email");
        }

        return $response;
    }

    public function sendUpdatePasswordNotice($userId) {
        $response = Http::asForm()->post(env("KEYCLOAK_TOKEN_URL"), [
            "client_id" => "admin-cli",
            "username" => "admin",
            "password" => "BiloKoji12@",
            "grant_type" => "password"
        ]);

        $token = $response->json('access_token');

        return Http::withToken($token)->withBody('["UPDATE_PASSWORD"]', 'application/json')
            ->put(env("KEYCLOAK_API_URL").$userId."/execute-actions-email");
    }

    public function userData(Request $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data['token'];
        $response = Http::withToken($token)
            ->get(env("KEYCLOAK_API_URL").$userId);
        $retObject = json_decode($response->body());

        return [
            'id' => $retObject->id,
            'username' => $retObject->username,
            'firstName' => $retObject->firstName,
            'lastName' => $retObject->lastName,
            'email' => $retObject->email,
            'institution' => isset($retObject->attributes) ? $retObject->attributes->institution[0] : null,
            'institutionType' => isset($retObject->attributes) ? $retObject->attributes->institution_type[0] : null,
            'township' => isset($retObject->attributes) ? $retObject->attributes->township[0] : null,
            'subjects' => isset($retObject->attributes) ? unserialize($retObject->attributes->subjects[0]) : null,
            'professions' => isset($retObject->attributes) ? unserialize($retObject->attributes->professions[0]) : null,
            'enabled' => $retObject->enabled
        ];
    }

    public function update(Request $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data["token"];
        $response =  Http::withToken($token)
            ->asJson()
            ->put(env("KEYCLOAK_API_URL").$userId,[
                "username" => $data['username'],
                "firstName" => $data['firstName'],
                "lastName" => $data['lastName'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" ? true : false,
        ]);

        if($data['updatePassword'] == "true") {
            return Http::withToken($data['token'])->withBody('["UPDATE_PASSWORD"]', 'application/json')
                ->put(env("KEYCLOAK_API_URL").$userId."/execute-actions-email");
        }
    }

    public function delete(Request $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data['token'];

        return Http::withToken($token)
            ->delete(env("KEYCLOAK_API_URL").$userId);
    }

    public function export() {
        return Excel::download(new RemoteUsersExport, 'remoteusers.xlsx');
    }

}
