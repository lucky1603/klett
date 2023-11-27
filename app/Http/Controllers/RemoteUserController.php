<?php

namespace App\Http\Controllers;

use App\Mail\NoCRMInfo;
use App\Models\UserImport;
use Illuminate\Http\Request;
use App\Exports\RemoteUsersExport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Requests\AdminCreateUserRequest;
use App\Http\Requests\AdminUpdateUserRequest;

class RemoteUserController extends AbstractUserController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index() {
        return view('remoteusers.index');
    }

    public function getData(Request $request) {
        $token = $request->post('token');
        $first = $request->post('first');
        $max = $request->post('max');
        return Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->get(ENV("KEYCLOAK_API_USERS_URL")."?briefRepresentation=false&first=".$first."&max=".$max);
    }

    public function getCount(Request $request) {
        $token = $request->post('token');
        return Http::withToken($token)
            ->get(ENV("KEYCLOAK_API_USERS_URL")."count");
    }

    public function filterCount(Request $request) {
        $token = $request->post('token');
        if($token == null) {
            $response = $this->connectKeyCloak();
            $token = $response->json('access_token');
        }

        $response = $this->connectKeyCloak();
        $token = $response->json('access_token');

        $data = $request->post();
        $requestUrl = ENV("KEYCLOAK_API_USERS_URL")."count";
        if($data['firstName'] != '') {
            $requestUrl .= "?firstName=".$data['firstName'];
        }

        $response = $this->getRealmGroups();
        $groups = $response->json();
        $roles = [];

        foreach($groups as $group) {
            if(in_array($group['name'], ["Administrator", "Teacher", "Student"])) {
                $roles[$group['id']] = $group['name'];
            }
        }

        if($data['lastName'] != '') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $requestUrl .= "lastName=".$data['lastName'];
        }

        if($data['username'] != '') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $requestUrl .= "username=".$data['username'];
        }

        if($data['email'] != '') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $requestUrl .= "email=".$data['email'];
        }

        if($data['status'] != '0') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            if($data['status'] == '1') {
                $requestUrl .= "enabled=true";
            } else {
                $requestUrl .= "enabled=false";
            }

        }

        if($data['role'] != '0' || $data['source'] != 'null') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $requestUrl .= "q=";

            if($data['role'] != '0') {
                $requestUrl .= "role:".$roles[$data['role']];
            }

            if($data['source'] != 'null') {
                if($data['role'] != '0') {
                    $requestUrl .= ' ';
                }

                $requestUrl .= "source:".$data['source'];
            }

            // $requestUrl .= "q=role:".$roles[$data['role']];
        }

        return Http::withToken($token)
            ->get($requestUrl);
    }

    public function filterUsers(Request $request) {
        $data = $request->post();

        $response = $this->getRealmGroups();
        $groups = $response->json();
        $roles = [];

        foreach($groups as $group) {
            if(in_array($group['name'], ["Administrator", "Teacher", "Student"])) {
                $roles[$group['id']] = $group['name'];
            }
        }

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

        if($data['email'] != '') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $requestUrl .= "email=".$data['email'];
        }

        if($data['username'] != '') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $requestUrl .= "username=".$data['username'];
        }

        if($data['status'] != '0') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            if($data['status'] == '1') {
                $requestUrl .= "enabled=true";
            } else {
                $requestUrl .= "enabled=false";
            }

        }

        if($data['role'] != '0' || $data['source'] != 'null') {
            if(!str_contains($requestUrl, "?")) {
                $requestUrl .= "?";
            } else {
                $requestUrl .= "&&";
            }

            $requestUrl .= "q=";

            if($data['role'] != '0') {
                $requestUrl .= "role:".$roles[$data['role']];
            }

            if($data['source'] != 'null') {
                if($data['role'] != '0') {
                    $requestUrl .= ' ';
                }

                $requestUrl .= "source:".$data['source'];
            }

            // $requestUrl .= "q=role:".$roles[$data['role']];
        }

        if(!str_contains($requestUrl, "?")) {
            $requestUrl .= "?";
        } else {
            $requestUrl .= "&&";
        }
        $requestUrl .= "briefRepresentation=false&&first=".$data['first']."&&max=".$data['max'];

        // var_dump($requestUrl);

        return Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->get($requestUrl);

    }

    public function create() {
        return view('remoteusers.create');
    }

    public function adminStore(AdminCreateUserRequest $request) {
        $data = $request->post();

        $inCRM = false;
        $crmContactId = null;

        $response = $this->getRealmGroups();
        $groups = $response->json();
        $roles = [];

        foreach($groups as $group) {
            if(in_array($group['name'], ["Administrator", "Teacher", "Student"])) {
                $roles[$group['id']] = $group['name'];
            }
        }

        if($data['rola'] == array_search('Teacher', $roles) /* Teacher */) {
            // Check CRM
            $value = $this->checkUser($data['email']);
            if(is_array($value) && count($value) > 0) {
                $inCRM = true;

                // TODO: Call positive CRM
                $crmContactId = $value['contactid'];
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
                    "pedagoska_sveska" => $data["pedagoska_sveska"] == "true" ? 1 : 0,
                    "klf_korisnik" => $data["klf_korisnik"] == "true" ? 1 : 0,
                    "source" => $data['source'],
                    "role" => $roles[$data['rola']],
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

            if($data['rola'] == array_search('Teacher', $roles) /* Teacher */) {
                if($inCRM) {
                    $this->ackCRMPositive($data, $crmContactId, $userId);
                } else {
                    // TODO: Call negative CRM
                    $this->ackCRMNegative($data, $userId);
                    // send email
                    Mail::to($data['email'])->send(new NoCRMInfo($data));
                }
            }

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
                "password" => env('KEYCLOAK_AUTH_PASSWORD'),
                "grant_type" => "password"
            ]);

        $token = $response->json('access_token');

        return Http::withToken($token)
            // ->withOptions(['verify' => false])
            ->withBody('["UPDATE_PASSWORD"]', 'application/json')
            ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
    }

    public function userData(Request $request) {
        $userId = $request->post('userId');

        $response = $this->connectKeyCloak();
        $token = $response->json('access_token');

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
            'skola' => isset($retObject->attributes) ? ( $retObject->attributes->institution[0] ?? null) : null,
            'institutionType' => isset($retObject->attributes) ? ( isset($retObject->attributes->institution_type) ? $retObject->attributes->institution_type[0] : null) : null,
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

    public function adminUpdate(AdminUpdateUserRequest $request) {
        $data = $request->post();

        $response = $this->getRealmGroups();
        $groups = $response->json();
        $roles = [];

        foreach($groups as $group) {
            if(in_array($group['name'], ["Administrator", "Teacher", "Student"])) {
                $roles[$group['id']] = $group['name'];
            }
        }

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
                    "pedagoska_sveska" => $data["pedagoska_sveska"] == "true" ? 1 : 0,
                    "klf_korisnik" => $data["klf_korisnik"] == "true" ? 1 : 0,
                    "source" => $data['source'],
                    "role" => $roles[$data['rola']],
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

        $response = Http::withToken($token)
            ->get(env('KEYCLOAK_API_USERS_URL')."?briefRepresentation=true");

        $userIds = collect($response->json())->map(function($user) {
            return $user['id'];
        });

        foreach($userIds as $userId) {

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

    public function getUnimportedUserIds() {
        return UserImport::where('imported', false)->get()->map(function($user) {
            return $user->id;
        });
    }

    public function importUserById($userId) {
        // $user = UserImport::find($userId);

        $user = DB::table('user_imports')->where('id', $userId)->first();

        $data = [
            "userId" => $user->id,
            'email' => $user->email,
            'firstName' => $user->ime,
            'lastName' => $user->prezime,
            'username' => $user->username,
            'source' => $user->source,
            'rola' => $user->rola,
            'password' => $user->password,
        ];

        return $this->import($data);
    }

    public function importFirstUser() {
        $user = UserImport::where('imported', 0)->first();
        $data = [
            "userId" => $user->id,
            'email' => $user->email,
            'firstName' => $user->ime,
            'lastName' => $user->prezime,
            'username' => $user->username,
            'source' => $user->source,
            'rola' => $user->rola,
            'password' => $user->password,
        ];

        return $this->import($data);
    }

    private function import($data) {
        $user = [
            'email' => $data['email'],
            'firstName' => $data['firstName'],
            'lastName' => $data['lastName'],
            'username' => $data['username'],
            'source' => $data['source'],
            'rola' => $data['rola'],
            'password' => $data['password'],
            'enabled' => true
        ];

        // Get user data from CRM
        $users = $this->checkUser($data['email']);
        if(count($users) > 0) {
            $crmUserData = $users[0];

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
                foreach($crmPredmeti as $crmPredmet) {
                    if(!in_array($crmPredmet['_ext_predmet_value'], $predmeti)) {
                        $predmeti[] = $crmPredmet['_ext_predmet_value'];
                    }

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
                    "testomat" => array_key_exists('klf_korisnik', $user) && $user['klf_korisnik'] == "true" ? 1 : 0 ,
                    "pedagoska_sveska" => array_key_exists('klf_korisnik', $user) && $user['klf_korisnik'] == "true" ? 1 : 0 ,
                    'source' => $user['source'],
                    "role" => $user['rola']
                ],
                "credentials" => $data['password'] != null ? [
                    [
                        "type" => "password",
                        "value" => $data['password'],
                        "temporary" => false,
                    ]
                ] : []
        ]);


        if($response->status() == 201 /* Created */) {
            $items = explode("/", $response->header("Location"));
            $userId = $items[count($items) - 1];
            $groupId = $this->getGroupIdByName($user['rola']);

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

}


