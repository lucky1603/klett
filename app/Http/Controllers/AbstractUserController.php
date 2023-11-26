<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduledEdit;
use Illuminate\Support\Facades\Http;


class AbstractUserController extends Controller
{
    /**
     * Get access token.
     */
    public function connectKeyCloak() {

        return Http::asForm()
        // ->withOptions(['verify' => false])
        ->post(env('KEYCLOAK_TOKEN_URL'), [
            "client_id" => "admin-cli",
            "username" => "admin",
            "password" => env('KEYCLOAK_AUTH_PASSWORD'),
            "grant_type" => "password"
        ]);
    }

    /**
     * Get all the user groups in realm.
     */
    public function getRealmGroups() {
        $response = $this->connectKeyCloak();
        $token = $response->json('access_token');

        return Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->get(env('KEYCLOAK_REALM_URL')."groups");
    }

    /**
     * Get the group id by the given name.
     */
    public function getGroupIdByName($groupName) {
        $response = $this->connectKeyCloak();
        $token = $response->json('access_token');

        $response = Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->get(env('KEYCLOAK_REALM_URL')."groups");

        $groups = json_decode($response->body());

        $teacher = [
            'name' => "Teacher"
        ];

        $subscriber = [
            'name' => 'Subscriber'
        ];

        foreach($groups as $group) {
            if($group->name == $groupName)
            {
                return $group->id;
            }
        }

        return null;
    }

    /**
     * Get the user groups the user belongs to.
     */
    public function userGroups(Request $request) {
        $data = $request->post();
        $token = $data['token'];
        $userId = $data['userId'];

        return Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->get(env('KEYCLOAK_API_USERS_URL').$userId."/groups");
    }

    /**
     * Get user group textual representation
     */
    public function userGroup(Request $request) : Array {
        $data = $request->post();
        $token = $data['token'];
        $userId = $data['userId'];

        $response = Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->get(env('KEYCLOAK_API_USERS_URL').$userId."/groups");

        $groups = json_decode($response->body());

        $groupId = '';
        $groupName = '';
        if(is_array($groups) && count($groups) > 0) {
            $groupId = $groups[0]->id;
            $groupName = $groups[0]->name;
        }

        return [
            "id" => $groupId,
            "name" => $groupName
        ];
    }

    public function setUserGroup(Request $request) {
        $data = $request->post();

        $groupId = $data['groupId'];
        $userId = $data['userId'];
        $token = $data['token'];
        $oldGroupId = $data['oldGroupId'] ?? null;

        // Get token if not provided.
        if($token == null) {
            $response = $this->connectKeyCloak();
            $token = $response->json('access_token');
        }

        $sameGroups = false;

        if($oldGroupId != null) {
            // If the old group exists and is not the same as the new one, delete it.
            if($oldGroupId != $groupId) {
                // TODO delete the group with the old group id.
                Http::withToken($token)
                    // ->withOptions(['verify' => false])
                    ->delete(env('KEYCLOAK_API_USERS_URL').$userId."/groups/".$oldGroupId);

            } else {
                $sameGroups = true;
            }
        }

        if(!$sameGroups) {
            // If the group was changed, set the new group.
            Http::withToken($token)
                // ->withOptions(['verify' => false])
                ->put(env('KEYCLOAK_API_USERS_URL').$userId."/groups/".$groupId);
        }

        return $userId;

    }



    public function connectCRM() {
        return Http::asForm()
            ->post('https://login.microsoftonline.com/570b0e1b-60ff-4adf-8b73-5a3dab04aa93/oauth2/v2.0/token', [
            "grant_type" => 'Client_Credentials',
            "client_id" => '2f9027fe-9597-46bc-818b-d7af10d52016',
            'client_secret' => '5tJ8Q~3ZSQgSb1aGN8e2rv7opFqUdkhKgmOwbbWH',
            'scope' => 'https://klf.crm4.dynamics.com/.default',
        ]);
    }

    public function checkUser($userEmail) {
        $response = $this->connectCRM();
        $token = $response->json('access_token');

        $select = "contactid,ext_cmslogin,emailaddress1,firstname,lastname,address1_line1,ext_postanskibroj,_ext_opstina_value,_ext_grad_value,_ext_drzava_value,mobilephone,telephone1,_ext_funkcijatip_value";
        $expand = "ext_Predmetprofila_Nastavnik_Contact(\$select=ext_klfprocenat,ext_korisnik,ext_poslednjipreracunkorisnika,_ext_predmet_value,ext_razred,_ext_skola_value)";
        $filter = "(emailaddress1 eq '".$userEmail."' and (parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a754452c-b664-ec11-8f8f-6045bd888602 or parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a654452c-b664-ec11-8f8f-6045bd888602 or parentcustomerid_account/_ext_tipposlovnogkontakta_value eq a954452c-b664-ec11-8f8f-6045bd888602))";

        $requestUrl = "https://klf.crm4.dynamics.com/api/data/v9.2/contacts";
        $requestUrl .= "?\$select=".$select;
        $requestUrl .= "&\$expand=".$expand;
        $requestUrl .= "&\$filter=".$filter;

        $response = Http::withToken($token)
            ->get($requestUrl);
        return $response->json("value");
    }

    public function ackCRMPositive($data, $crmContactId, $keycloakUserId) {
        $response = $this->connectCRM();
        $token = $response->json('access_token');

        $requestUrl = env('CRM_URL').'/api/data/v9.2/ext_webupits';
        return Http::withToken($token)
            ->post($requestUrl, [
                'ext_ime' => $data['ime'],
                'ext_prezime' => $data['prezime'],
                'ext_emailadresa' => $data['email'],
                'ext_kontakttelefon' => $data['telefon1'],
                'ext_Tipustanove@odata.bind' => "/ext_tipposlovnogkontaktas(".$data['institutionType'].")",
                'ext_Opstinaustanove@odata.bind' => "/ext_opstinas(".$data['township'].")",
                'ext_Nazivustanove@odata.bind' => "/accounts(".$data['skola'].")",
                'ext_Predmet@odata.bind' => "/ext_predmets(".$data['subjects'][0].")",
                'ext_Imekontakta@odata.bind' => '/contacts('.$crmContactId.")",
                "ext_verified" => true,
                "ext_keycloakidkorisnika" => $keycloakUserId
            ]);
    }

    public function ackCRMNegative($data, $keycloakUserId) {
        $response = $this->connectCRM();
        $token = $response->json('access_token');

        $requestUrl = env('CRM_URL').'/api/data/v9.2/ext_webupits';
        return Http::withToken($token)
            ->post($requestUrl, [
                'ext_ime' => $data['ime'],
                'ext_prezime' => $data['prezime'],
                'ext_emailadresa' => $data['email'],
                'ext_kontakttelefon' => $data['telefon1'],
                'ext_Tipustanove@odata.bind' => "/ext_tipposlovnogkontaktas(".$data['institutionType'].")",
                'ext_Opstinaustanove@odata.bind' => "/ext_opstinas(".$data['township'].")",
                'ext_Nazivustanove@odata.bind' => "/accounts(".$data['skola'].")",
                'ext_Predmet@odata.bind' => "/ext_predmets(".$data['subjects'][0].")",
                "ext_keycloakidkorisnika" => $keycloakUserId
            ]);
    }

    public function editScheduled($token)
    {
        $scheduledEdit = ScheduledEdit::where(
            [
                'token' => $token,
                'validated' => false
            ]
        )->firstOrFail();

        if($scheduledEdit != null) {
            $userId = $scheduledEdit->user_id;
            $response = $this->connectKeyCloak();
            $accessToken = $response->json('access_token');
            $response = Http::withToken($accessToken)
                ->get(env("KEYCLOAK_API_USERS_URL").$userId);
            $user = $response->json();

            return view('appusers.update', ['user' => $user['id']]);
        }

        return view('appusers.expired');
    }

}
