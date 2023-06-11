<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CRMController extends Controller
{
    public function connectCRM() {
        return Http::asForm()
            ->post('https://login.microsoftonline.com/570b0e1b-60ff-4adf-8b73-5a3dab04aa93/oauth2/v2.0/token', [
            "grant_type" => 'Client_Credentials',
            "client_id" => '2f9027fe-9597-46bc-818b-d7af10d52016',
            'client_secret' => '5tJ8Q~3ZSQgSb1aGN8e2rv7opFqUdkhKgmOwbbWH',
            'scope' => 'https://klettdev.crm4.dynamics.com/.default',
        ]);
    }

    public function checkUser($userEmail) {
        $response = $this->connectCRM();
        $token = $response->json('access_token');

        $requestUrl = 'https://klettdev.crm4.dynamics.com/api/data/v9.2/contacts';
        $requestUrl .= "?\$select=contactid&\$filter=(emailaddress1 eq '".$userEmail."'";
        $requestUrl .= "and (parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a754452c-b664-ec11-8f8f-6045bd888602";
        $requestUrl .= " or parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a654452c-b664-ec11-8f8f-6045bd888602 or parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a954452c-b664-ec11-8f8f-6045bd888602))";

        return Http::withToken($token)->get($requestUrl);
    }
}
