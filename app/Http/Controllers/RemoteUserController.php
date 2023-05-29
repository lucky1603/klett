<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class RemoteUserController extends Controller
{
    public function index() {
        return view('remoteusers.index');
    }

    public function connectKeyCloak() {

        return Http::asForm()->post("https://wp6test.prosmart.rs:8443/realms/master/protocol/openid-connect/token", [
            "client_id" => "admin-cli",
            "username" => "admin",
            "password" => "BiloKoji12@",
            "grant_type" => "password"
        ]);
    }

    public function getData(Request $request) {
        $token = $request->post('token');
        return Http::withToken($token)->get("https://wp6test.prosmart.rs:8443/admin/realms/Klett/users");
    }

    public function create() {
        return view('remoteusers.create');
    }

    public function store(Request $request) {
        $data = $request->post();

        $requiredActions = [];
        if(isset($data['verifyEmail'])) {
            $requiredActions[] = "VERIFY_EMAIL";
        }

        if(isset($data['updatePassword'])) {
            $requiredActions[] = "UPDATE_PASSWORD";
        }

        return Http::withToken($data['token'])
            ->asJson()
            ->post("https://wp6test.prosmart.rs:8443/admin/realms/Klett/users", [
                "username" => $data['username'],
                "firstName" => $data['firstName'],
                "lastName" => $data['lastName'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" ? true : false,
                "requiredActions" => $requiredActions
        ]);

    }

    public function userData(Request $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data['token'];
        return Http::withToken($token)
            ->get("https://wp6test.prosmart.rs:8443/admin/realms/Klett/users/".$userId);
    }

    public function update(Request $request) {
        $data = $request->post();

        $requiredActions = [];
        if(isset($data['verifyEmail'])) {
            $requiredActions[] = "VERIFY_EMAIL";
        }

        if(isset($data['updatePassword'])) {
            $requiredActions[] = "UPDATE_PASSWORD";
        }

        $userId = $data['userId'];
        $token = $data["token"];
        return Http::withToken($token)
            ->asJson()
            ->put("https://wp6test.prosmart.rs:8443/admin/realms/Klett/users/".$userId,[
                "username" => $data['username'],
                "firstName" => $data['firstName'],
                "lastName" => $data['lastName'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" ? true : false,
                "requiredActions" => $requiredActions
        ]);
    }
}
