<?php

namespace App\Http\Controllers;

use App\Mail\NoCRMInfo;
use App\Mail\RequestEdit;
use App\Models\UserImport;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ScheduledEdit;
use App\Exports\RemoteUsersExport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Expr\Cast\Array_;
use App\Http\Requests\AdminCreateUserRequest;
use App\Http\Requests\CreateRemoteUserRequest;

class RemoteUserController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    public function index() {
        return view('remoteusers.index');
    }

    /**
     * Get access token.
     */
    public function connectKeyCloak() {

        return Http::asForm()
        // ->withOptions(['verify' => false])
        ->post(env('KEYCLOAK_TOKEN_URL'), [
            "client_id" => "admin-cli",
            "username" => "admin",
            "password" => "BiloKoji12@",
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

    public function getData(Request $request) {
        $token = $request->post('token');
        $first = $request->post('first');
        $max = $request->post('max');
        return Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->get(ENV("KEYCLOAK_API_USERS_URL")."?briefRepresentation=true&first=".$first."&max=".$max);
    }

    public function getCount(Request $request) {
        $token = $request->post('token');
        return Http::withToken($token)
            ->get(ENV("KEYCLOAK_API_USERS_URL")."count");
    }

    public function filterCount(Request $request) {
        $token = $request->post('token');
        $data = $request->post();
        $requestUrl = ENV("KEYCLOAK_API_USERS_URL")."count";
        if($data['firstName'] != '') {
            $requestUrl .= "?firstName=".$data['firstName'];
        }

        if($data['lastName'] != '') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $requestUrl .= "lastName=".$data['lastName'];
        }

        if($data['userStatus'] != 0) {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $val = $data['userStatus'] == 1 ? "false" : "true";
            $requestUrl .= "enabled=".$val;
        }

        return Http::withToken($token)
            ->get($requestUrl);
    }

    public function filterUsers(Request $request) {
        $data = $request->post();

        // Get access token.
        $token = '';
        if(isset($data['token']) && $data['token'] != '') {
            $token = $data['token'];
        } else {
            $response = $this->connectKeyCloak();
            $token = $response->json('access_token');
        }

        $requestUrl = env('KEYCLOAK_API_USERS_URL');
        if($data['firstName'] != '') {
            $requestUrl .= "?firstName=".$data['firstName'];
        }

        if($data['lastName'] != '') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $requestUrl .= "lastName=".$data['lastName'];
        }

        if($data['userStatus'] != 0) {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $val = $data['userStatus'] == 1 ? "false" : "true";
            $requestUrl .= "enabled=".$val;
        }

        if(!str_contains($requestUrl, "?")) {
            $requestUrl .= "?";
        } else {
            $requestUrl .= "&&";
        }
        $requestUrl .= "briefRepresentation=true&&first=".$data['first']."&&max=".$data['max'];

        return Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->get($requestUrl);

    }

    public function create() {
        return view('remoteusers.create');
    }

    public function store(CreateRemoteUserRequest $request) {
        $data = $request->post();

        $inCRM = true;

        if($data['isTeacher'] == "true") {
            // Check CRM
            $inCRM = false;
            $value = $this->checkUser($data['email']);
            if(is_array($value) && count($value) > 0) {
                $inCRM = true;

                // TODO: Call positive CRM
                $contactId = $value['contactid'];
                $this->ackCRMPositive($data, $contactId);

            } else {
                // TODO: Call negative CRM
                $this->ackCRMNegative($data);

                // send email
                Mail::to($data['email'])->send(new NoCRMInfo($data));
            }
        }

        $response = Http::withToken($data['token'])
            ->asJson()
            // ->withOptions(['verify' => false])
            ->post(env("KEYCLOAK_API_USERS_URL"), [
                "username" => $data['korisnickoIme'],
                "firstName" => $data['ime'],
                "lastName" => $data['prezime'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" && $inCRM ? true : false,
                "attributes" => [
                    "subjects" => isset($data['subjects']) ? serialize($data['subjects']) : null,
                    // "professions" => isset($data['professions']) ? serialize($data['professions']) : null,
                    "township" => isset($data['township']) ?  $data['township'] : null,
                    "institution_type" => isset($data['institutionType']) ? $data['institutionType'] : null,
                    "institution" => isset($data['skola']) ? $data['skola'] : null,
                    "billing_first_name" => $data['ime'],
                    "billing_last_name" => $data['prezime'],
                    "billing_address_1" => $data['adresa'],
                    'billing_city' => $data['mesto'],
                    "billing_postcode" => $data['postanskiBroj'],
                    "billing_phone" => $data['telefon1'],
                    "testomat" => $data['testomat'] == "true" ? 1 : 0,
                    "pedagoska_sveska" => $data["pedagoska_sveska"] == "true" ? 1 : 0
                ],
        ]);

        /* test
        var_dump($response->ok());
        var_dump($response->failed());
        var_dump($response->status());
        */

        if($response->status() == 201 /* Created */) {
            $items = explode("/", $response->header("Location"));
            $userId = $items[count($items) - 1];

            if($data['isTeacher'] == "true") {
                $groupId = $this->getGroupIdByName("Teacher");
            } else {
                $groupId = $this->getGroupIdByName('Subscriber');
            }

            $setGroupRequest = new Request([],[
                'groupId' => $groupId,
                'userId' => $userId,
                'token' => $data['token']
            ], [], [], [], [], null);

            $this->setUserGroup($setGroupRequest);

            // Send password reset link.
            if($data['updatePassword'] == 'true') {
                Http::withToken($data['token'])->withBody('["UPDATE_PASSWORD"]', 'application/json')
                    ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
            }

            return [
                'status' => $response->status(),
                'message' => "Success!!!"
            ];
        }


        // $response->status() = 209 - Failed.
        return [
            'status' => $response->status(),
            'message' => $response->json('errorMessage')
        ];
    }


    public function adminStore(AdminCreateUserRequest $request) {
        $data = $request->post();

        $inCRM = true;

        if($data['rola'] == "bab78444-87f6-45e9-86fc-fd1b1d5b6530" /* Teacher */) {
            // Check CRM
            $inCRM = false;
            $value = $this->checkUser($data['email']);
            if(is_array($value) && count($value) > 0) {
                $inCRM = true;

                // TODO: Call positive CRM
                $contactId = $value['contactid'];
                $this->ackCRMPositive($data, $contactId);

            } else {
                // TODO: Call negative CRM
                $this->ackCRMNegative($data);

                // send email
                Mail::to($data['email'])->send(new NoCRMInfo($data));
            }
        }

        $response = Http::withToken($data['token'])
            ->asJson()
            // ->withOptions(['verify' => false])
            ->post(env("KEYCLOAK_API_USERS_URL"), [
                "username" => $data['korisnickoIme'],
                "firstName" => $data['ime'],
                "lastName" => $data['prezime'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" && $inCRM ? true : false,
                "attributes" => [
                    "subjects" => isset($data['subjects']) ? serialize($data['subjects']) : null,
                    // "professions" => isset($data['professions']) ? serialize($data['professions']) : null,
                    "township" => isset($data['township']) ?  $data['township'] : null,
                    "institution_type" => isset($data['institutionType']) ? $data['institutionType'] : null,
                    "institution" => isset($data['skola']) ? $data['skola'] : null,
                    "billing_first_name" => $data['ime'],
                    "billing_last_name" => $data['prezime'],
                    "billing_address_1" => $data['adresa'],
                    'billing_city' => $data['mesto'],
                    "billing_postcode" => $data['postanskiBroj'],
                    "billing_phone" => $data['telefon1'],
                    "testomat" => $data['testomat'] == "true" ? 1 : 0,
                    "pedagoska_sveska" => $data["pedagoska_sveska"] == "true" ? 1 : 0
                ],
        ]);

        /* test
        var_dump($response->ok());
        var_dump($response->failed());
        var_dump($response->status());
        */

        if($response->status() == 201 /* Created */) {
            $items = explode("/", $response->header("Location"));
            $userId = $items[count($items) - 1];

            $groupId = $data['rola'];

            $setGroupRequest = new Request([],[
                'groupId' => $groupId,
                'userId' => $userId,
                'token' => $data['token']
            ], [], [], [], [], null);

            $this->setUserGroup($setGroupRequest);

            // Send password reset link.
            if($data['updatePassword'] == 'true') {
                Http::withToken($data['token'])->withBody('["UPDATE_PASSWORD"]', 'application/json')
                    ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
            }

            return [
                'status' => $response->status(),
                'message' => "Success!!!"
            ];
        }


        // $response->status() = 209 - Failed.
        return [
            'status' => $response->status(),
            'message' => $response->json('errorMessage')
        ];
    }

    public function sendUpdatePasswordNotice($userId) {
        $response = Http::asForm()
            // ->withOptions(['verify' => false])
            ->post(env("KEYCLOAK_TOKEN_URL"), [
                "client_id" => "admin-cli",
                "username" => "admin",
                "password" => "BiloKoji12@",
                "grant_type" => "password"
            ]);

        $token = $response->json('access_token');

        return Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->withBody('["UPDATE_PASSWORD"]', 'application/json')
            ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
    }

    public function userData(Request $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data['token'];
        $response = Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->get(env("KEYCLOAK_API_USERS_URL").$userId);
        $retObject = json_decode($response->body());


        $userGroupRequest = new Request([], [
            'userId' => $userId,
            'token' => $token
        ], [], [], [], [], null);

        $groupResponse = $this->userGroup($userGroupRequest);
        $isTeacher = false;
        if($groupResponse["name"] == "Teacher") {
            $isTeacher = true;
        }

        $rola = $groupResponse["id"] ?? null;

        return [
            'id' => $retObject->id,
            'korisnickoIme' => $retObject->username,
            'ime' => $retObject->firstName ?? '',
            'prezime' => $retObject->lastName ?? '',
            'email' => $retObject->email ?? '',
            'skola' => isset($retObject->attributes) ? $retObject->attributes->institution[0] : null,
            'institutionType' => isset($retObject->attributes) ? $retObject->attributes->institution_type[0] : null,
            'township' => isset($retObject->attributes->township)  ? $retObject->attributes->township[0] : null,
            'subjects' => isset($retObject->attributes->subjects) ? unserialize($retObject->attributes->subjects[0]) : [],
            'professions' => isset($retObject->attributes->professions) ? unserialize($retObject->attributes->professions[0]) : [],
            'enabled' => $retObject->enabled,
            'isTeacher' => $isTeacher,
            'adresa' => isset($retObject->attributes) && isset($retObject->attributes->billing_address_1) ?  $retObject->attributes->billing_address_1[0] : '',
            'mesto' => isset($retObject->attributes) && isset($retObject->attributes->billing_city) ?  $retObject->attributes->billing_city[0] : '',
            'postanskiBroj' => isset($retObject->attributes) && isset($retObject->attributes->billing_postcode) ?  $retObject->attributes->billing_postcode[0] : '',
            'drzava' => isset($retObject->attributes) && isset($retObject->attributes->billing_country) ?  $retObject->attributes->billing_country[0] : '',
            'telefon1' => isset($retObject->attributes) && isset($retObject->attributes->billing_phone) ? $retObject->attributes->billing_phone[0] : '',
            'telefon2' => isset($retObject->attributes) && isset($retObject->attributes->billing_phone) ? $retObject->attributes->billing_phone[0] : '',
            'rola' => $rola,
            'testomat' => isset($retObject->attributes) && isset($retObject->attributes->testomat) ? ($retObject->attributes->testomat[0] == 1 ? true : false) : false,
            'pedagoska_sveska' => isset($retObject->attributes) && isset($retObject->attributes->pedagoska_sveska) ? ($retObject->attributes->pedagoska_sveska[0] == 1 ? true : false) : false,
            'klf_korisnik' => isset($retObject->attributes) && isset($retObject->attributes->klf_korisnik) ? ($retObject->attributes->klf_korisnik[0] == 1 ? true : false) : false,
        ];
    }

    public function update(CreateRemoteUserRequest $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data["token"];
        $response = Http::withToken($token)
            ->asJson()
            // ->withOptions(['verify' => false])
            ->put(env("KEYCLOAK_API_USERS_URL").$userId,[
                "username" => $data['korisnickoIme'],
                "firstName" => $data['ime'],
                "lastName" => $data['prezime'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" ? true : false,
                "attributes" => [
                    "subjects" => isset($data['subjects']) ? serialize($data['subjects']) : null,
                    "professions" => isset($data['professions']) ? serialize($data['professions']) : null,
                    "township" => isset($data['township']) ?  $data['township'] : null,
                    "institution_type" => isset($data['institutionType']) ? $data['institutionType'] : null,
                    "institution" => isset($data['skola']) ? $data['skola'] : null,
                    "billing_first_name" => $data['ime'],
                    "billing_last_name" => $data['prezime'],
                    "billing_address_1" => $data['adresa'],
                    'billing_city' => $data['mesto'],
                    "billing_postcode" => $data['postanskiBroj'],
                    "testomat" => $data['testomat'] == "true" ? 1 : 0,
                    "pedagoska_sveska" => $data["pedagoska_sveska"] == "true" ? 1 : 0
                ],
        ]);

        if($response->status() == 204) {

            // Get current user group.
            $getGroupRequest = new Request([], [
                'userId' => $userId,
                'token' => $token
            ], [], [], [], [], null);

            $oldGroup = $this->userGroup($getGroupRequest);

            if($data['isTeacher'] == "true") {
                $groupId = $this->getGroupIdByName("Teacher");
            } else {
                $groupId = $this->getGroupIdByName('Subscriber');
            }

            $setGroupRequest = new Request([],[
                'groupId' => $groupId,
                'userId' => $userId,
                'token' => $data['token'],
                'oldGroupId' => $oldGroup["id"]
            ], [], [], [], [], null);

            $this->setUserGroup($setGroupRequest);

            if($data['updatePassword'] == "true") {
                Http::withToken($data['token'])
                    ->withBody('["UPDATE_PASSWORD"]', 'application/json')
                    // ->withOptions(['verify' => false])
                    ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
            }

            return [
                "status" => $response->status(),
                "message" => "Success!!!"
            ];

        }

        return [
            "status" => $response->status(),
            "message" => $response->json("errorMessage")
        ];

    }

    public function adminUpdate(AdminCreateUserRequest $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data["token"];
        $response = Http::withToken($token)
            ->asJson()
            // ->withOptions(['verify' => false])
            ->put(env("KEYCLOAK_API_USERS_URL").$userId,[
                "username" => $data['korisnickoIme'],
                "firstName" => $data['ime'],
                "lastName" => $data['prezime'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" ? true : false,
                "attributes" => [
                    "subjects" => isset($data['subjects']) ? serialize($data['subjects']) : null,
                    "professions" => isset($data['professions']) ? serialize($data['professions']) : null,
                    "township" => isset($data['township']) ?  $data['township'] : null,
                    "institution_type" => isset($data['institutionType']) ? $data['institutionType'] : null,
                    "institution" => isset($data['skola']) ? $data['skola'] : null,
                    "billing_first_name" => $data['ime'],
                    "billing_last_name" => $data['prezime'],
                    "billing_address_1" => $data['adresa'],
                    'billing_city' => $data['mesto'],
                    "billing_postcode" => $data['postanskiBroj'],
                    "billing_phone" => $data['telefon1'],
                    "testomat" => $data['testomat'] == "true" ? 1 : 0,
                    "pedagoska_sveska" => $data["pedagoska_sveska"] == "true" ? 1 : 0
                ],
        ]);

        if($response->status() == 204) {

            // Get current user group.
            $getGroupRequest = new Request([], [
                'userId' => $userId,
                'token' => $token
            ], [], [], [], [], null);

            $oldGroup = $this->userGroup($getGroupRequest);
            $groupId = $data['rola'];

            $setGroupRequest = new Request([],[
                'groupId' => $groupId,
                'userId' => $userId,
                'token' => $data['token'],
                'oldGroupId' => $oldGroup["id"]
            ], [], [], [], [], null);

            $this->setUserGroup($setGroupRequest);

            if($data['updatePassword'] == "true") {
                Http::withToken($data['token'])
                    ->withBody('["UPDATE_PASSWORD"]', 'application/json')
                    // ->withOptions(['verify' => false])
                    ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
            }

            return [
                "status" => $response->status(),
                "message" => "Success!!!"
            ];

        }

        return [
            "status" => $response->status(),
            "message" => $response->json("errorMessage")
        ];
    }

    public function delete(Request $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data['token'];

        return Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->delete(env("KEYCLOAK_API_USERS_URL").$userId);
    }

    public function deleteAll() {
        $response = $this->connectKeyCloak();
        $token = $response->json('access_token');

        $response = Http::withToken($token)->get(env('KEYCLOAK_API_USERS_URL'));
        $users = $response->json();
        foreach($users as $user) {
            $userId = $user['id'];

            $request = new Request([],[
                'token' => $token,
                'userId' => $userId,
            ], [], [], [], [], null);

            $r1 = $this->delete($request);
            echo $userId." deleted! Request status is ".$r1->status()."\n";
        }

        return [
            'tokenResponseStatus' => $response->status(),
            'deleteResponseStatus' => $r1->status(),
            'message' => "Success!"
        ];
    }

    public function export() {
        return Excel::download(new RemoteUsersExport, 'remoteusers.xlsx');
    }

    public function testMail($emailAddress) {
        var_dump($emailAddress);
        $email = [];
        $email[] = $emailAddress;
        Mail::to($email[0])
            ->send(new NoCRMInfo(['firstName' => 'Sinisa', 'lastName' => 'Ristic']));

        return 0;
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

    public function ackCRMPositive($data, $userId) {
        $response = $this->connectCRM();
        $token = $response->json('access_token');

        $requestUrl = env('CRM_URL').'/api/data/v9.2/ext_webupits';
        return Http::withToken($token)
            ->post($requestUrl, [
                'ext_ime' => $data['ime'],
                'ext_prezime' => $data['prezime'],
                'ext_emailadresa' => $data['email'],
                'ext_kontakttelefon' => $data['telefon1'],
                'ext_Tipustanove@odata.bind' => "ext_tipposlovnogkontaktas(".$data['institutionType'].")",
                'ext_Opstinaustanove@odata.bind' => "/ext_opstinas(".$data['township'].")",
                'ext_Nazivustanove@odata.bind' => "/accounts(".$data['skola'].")",
                'ext_Predmet@odata.bind' => "/ext_predmets(".$data['subjects'][0].")",
                'ext_Imekontakta@odata.bind' => '/contacts('.$userId.")",
                "ext_verified" => true
            ]);
    }

    public function ackCRMNegative($data) {
        $response = $this->connectCRM();
        $token = $response->json('access_token');

        $requestUrl = env('CRM_URL').'/api/data/v9.2/ext_webupits';
        return Http::withToken($token)
            ->post($requestUrl, [
                'ext_ime' => $data['ime'],
                'ext_prezime' => $data['prezime'],
                'ext_emailadresa' => $data['email'],
                'ext_kontakttelefon' => $data['telefon1'],
                'ext_Tipustanove@odata.bind' => "ext_tipposlovnogkontaktas(".$data['institutionType'].")",
                'ext_Opstinaustanove@odata.bind' => "/ext_opstinas(".$data['township'].")",
                'ext_Nazivustanove@odata.bind' => "/accounts(".$data['skola'].")",
                'ext_Predmet@odata.bind' => "/ext_predmets(".$data['subjects'][0].")",
            ]);
    }

    public function importUser(Request $request) {
        $data = $request->post();
        return $this->import($data);

        // Send ack-email
        // TODO later
    }

    public function importAllUsers() {
        for($i = 0; $i < 100; $i++) {
            $this->importFirstUser();
        }
    }

    public function importFirstUser() {
        $user = UserImport::where('imported', 0)->first();
        $data = [
            "userId" => $user->id,
            'email' => $user->email,
            'firstName' => $user->ime,
            'lastName' => $user->prezime,
            'username' => $user->username,
            'isTeacher' => $user->is_teacher == 1 ? "true" : "false"
        ];

        return $this->import($data);
    }

    private function import($data) {

        $user = [
            'email' => $data['email'],
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'username' => $data['username'],
            'isTeacher' => $data['isTeacher'],
        ];

        // Get user data from CRM
        $users = $this->checkUser($data['email']);
        if(count($users) > 0) {
            $crmUserData = $users[0];

            $user['enabled'] = false;
            $user['firstName'] = $crmUserData['firstname'];
            $user['lastName'] = $crmUserData['lastname'];
            $user['billing_address_1'] = $crmUserData['address1_line1'];
            $user['billing_postcode'] = $crmUserData['ext_postanskibroj'];
            $user['township'] =  $crmUserData['_ext_opstina_value'];
            $user['billing_city'] = $crmUserData['_ext_grad_value'];
            $user['billing_phone'] = $crmUserData['mobilephone'];
            $user['crm_id'] = $crmUserData['contactid'];

            if(count($crmUserData['ext_Predmetprofila_Nastavnik_Contact']) > 0) {
                $crmPredmeti = $crmUserData['ext_Predmetprofila_Nastavnik_Contact'];
                $predmeti = [];
                $korisnici = [];
                foreach($crmPredmeti as $crmPredmet) {
                    $predmeti[] = $crmPredmet['_ext_predmet_value'];

                    if(isset($crmPredmet['ext_korisnik']) && !array_key_exists('klf_korisnik', $user)) {
                        $user['klf_korisnik'] = $crmPredmet['ext_korisnik'];
                    }

                    if(isset($crmPredmet['_ext_skola_value']) && !array_key_exists('institution', $user)) {
                        $user['institution'] = $crmPredmet['_ext_skola_value'];
                    }
                }

                $user['predmeti'] = serialize($predmeti);
            }
        }

        // Add it to KeyCloak
        if(!array_key_exists('token',$data)) {
            $response = $this->connectKeyCloak();
            $token = $response->json('access_token');
        } else {
            $token = $data['token'];
        }

        $response = Http::withToken($token)
            ->asJson()
            // ->withOptions(['verify' => false])
            ->post(env("KEYCLOAK_API_USERS_URL"), [
                "username" => $user['username'],
                "firstName" => $user['firstName'],
                "lastName" => $user['lastName'],
                "email" => $user["email"],
                "enabled" => $user['enabled'] ?? false,
                "attributes" => [
                    "subjects" => $user['predmeti'] ?? null,
                    "subject_users" => $user['korisnici'] ?? null,
                    // "professions" => isset($data['professions']) ? serialize($data['professions']) : null,
                    "township" => $user['township'] ??  null,
                    "institution_type" => isset($data['institutionType']) ? $data['institutionType'] : 0,
                    "institution" => $user['institution'] ?? null,
                    "billing_first_name" => $user['firstName'] ?? '',
                    "billing_last_name" => $user['lastName'] ?? '',
                    "billing_address_1" => $user['billing_address_1'] ?? '',
                    'billing_city' => $user['billing_city'] ?? '',
                    "billing_postcode" => $user['billing_postcode'] ?? '',
                    "billing_phone" => $user['billing_phone'] ?? '',
                    "klf_korisnik" => array_key_exists('klf_korisnik', $user) && $user['klf_korisnik'] == "true" ? 1 : 0 ,
                    "testomat" => array_key_exists('testomat', $user) && $user['testomat'] == "true" ? 1 : 0 ,
                    "pedagoska_sveska" => array_key_exists('pedagoska_sveska', $user) && $user['pedagoska_sveska'] == "true" ? 1 : 0 ,
                ],
        ]);


        if($response->status() == 201 /* Created */) {
            $items = explode("/", $response->header("Location"));
            $userId = $items[count($items) - 1];

            if($data['isTeacher'] == "true") {
                $groupId = $this->getGroupIdByName("Teacher");
            } else {
                $groupId = $this->getGroupIdByName('Subscriber');
            }

            $setGroupRequest = new Request([],[
                'groupId' => $groupId,
                'userId' => $userId,
                'token' => $token
            ], [], [], [], [], null);

            $this->setUserGroup($setGroupRequest);

            // // Send password reset link.
            // if($data['updatePassword'] == 'true') {
            //     Http::withToken($data['token'])->withBody('["UPDATE_PASSWORD"]', 'application/json')
            //         ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
            // }



            // Update the user in the user imports list as imported.
            $userImport = UserImport::find($data['userId']);
            $userImport->imported = 1;
            $userImport->save();

            $total = UserImport::count();
            $imported = UserImport::where('imported', true)->count();

            // if($data['sendEmail'] == "true") {

            //     // Send data varification link.
            //     $scheduledEdit = ScheduledEdit::create([
            //         "user_id" => $userId,
            //         "token" => Str::random(60)
            //     ]);

            //     Mail::to($user['email'])->send(new RequestEdit($user['firstName']." ".$user['lastName'], $scheduledEdit->token));

            //     // // Send password reset link.
            //     // if($data['updatePassword'] == 'true') {
            //     //     Http::withToken($data['token'])->withBody('["UPDATE_PASSWORD"]', 'application/json')
            //     //         ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
            //     // }
            // }

            return [
                'status' => $response->status(),
                'message' => "Success!!!",
                'total' => $total,
                'imported' => $imported
            ];
        }
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


