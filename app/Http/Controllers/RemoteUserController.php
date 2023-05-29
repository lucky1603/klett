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
}
