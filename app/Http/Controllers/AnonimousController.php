<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\NoCRMInfo;
use App\Mail\RequestEdit;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\ScheduledEdit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ChangePasswordRequest;
use App\Http\Requests\CreateRemoteUserRequest;

class AnonimousController extends AbstractUserController
{
    public function verify($token) {
        $user = User::where('remember_token', $token)->firstOrFail();
        if($user != null) {
            $user->setAttribute('email_verified_at', now());
            $user->save();

            return view('auth.passwords.change', ['token' => $token]);

            // return view('auth.changepassword')->with(
            //     ['token' => $token, 'email' => $user->getAttribute('email')]
            // );
        }

        return view('anonimous.wrongpage');
    }

    public function password(ChangePasswordRequest $request) {
        $data = $request->post();

        $token = $data['token'];
        $user = User::where('remember_token', $token)->firstOrFail();
        $user->password = Hash::make($data['password']);
        $user->setRememberToken(null);
        $user->save();

        Auth::login($user);

        return redirect(route('remoteusers'));
    }

    public function apply() {
        return view('appusers.register');
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
            "password" => env('KEYCLOAK_AUTH_PASSWORD'),
            "grant_type" => "password"
        ]);
    }

    public function refreshCaptcha() {
        return captcha_img('klett');
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

    public function requestEditProfile($username) {
        // Get user from database
        $response = $this->connectKeyCloak();
        $accessToken = $response->json('access_token');
        $userResponse = Http::withToken($accessToken)
            ->get(env('KEYCLOAK_API_USERS_URL').'?briefRepresentation=true&username='.$username);

        $users = $userResponse->json();
        $email = null;
        if(count($users) > 0) {
            // Do something
            $userId = $users[0]['id'];
            $email = $users[0]['email'];
            $scheduledEdit = ScheduledEdit::create([
                'user_id' => $userId,
                'token' => Str::random(60),
                'validated' => false,
            ]);

            if($email != null) {
                $name = $users[0]['firstName'].' '.$users[0]['lastName'];
                Mail::to($email)->send(new RequestEdit($name, $scheduledEdit->token));
            }

            return view('anonimous.editconf');
        }

        return view('anonimous.editerr');
    }

    public function testRequestEdit($username) {
        return view('anonimous.testrequestedit', ['username' => $username]);
    }


    /// create & update
    public function store(CreateRemoteUserRequest $request) {
        $data = $request->post();

        // Communicate with CRM
        $inCRM = false;
        $isUser = false;
        $crmContactId = null;
        if($data['isTeacher'] == "true") {
            // Check CRM
            $value = $this->checkUser($data['email']);
            if(is_array($value) && count($value) > 0) {
                $inCRM = true;

                // TODO: Call positive CRM
                $crmContactId = $value[0]['contactid'];
                $predmetiProfila = $value[0]['ext_Predmetprofila_Nastavnik_Contact'];
                if(is_array($predmetiProfila) && count($predmetiProfila) > 0) {
                    foreach($predmetiProfila as $predmetProfila) {
                        // Kada je podešeno na tačno, ne može više ići nazad.
                        // Ovo je važno jer korisnik može imati više predmetnih
                        // profila, a samo na jednom da je označen kao KLF korisnik.
                        // I taj jedan put je dovoljan.
                        if(!$isUser) {
                            $isUser = $predmetProfila['ext_korisnik'];
                        }
                    }
                }

                // $isUser = $value[0]['ext_Predmetprofila_Nastavnik_Contact'][0]['ext_korisnik'];
            }
        }
        // End of communication with CRM

        $response = Http::withToken($data['token'])
            ->asJson()
            // ->withOptions(['verify' => false])
            ->post(env("KEYCLOAK_API_USERS_URL"), [
                "username" => $data['korisnickoIme'],
                "firstName" => $data['ime'],
                "lastName" => $data['prezime'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" && (($inCRM == true && $data['isTeacher'] == 'true') || $data['isTeacher'] == 'false')  ? true : false,
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
                    "testomat" => $inCRM && $isUser ? 1 : 0,
                    "pedagoska_sveska" => $inCRM && $isUser ? 1 : 0,
                    "klf_korisnik" => $isUser ? 1 : 0,
                    "source" => $data['source'],
                    "role" => $data['isTeacher'] == "true" ? "Teacher" : "Student"
                ],
        ]);

        // $inCRM = false;
        if($response->status() == 201 /* Created */) {
            $items = explode("/", $response->header("Location"));
            $userId = $items[count($items) - 1];

            // Communicate with CRM
            if($data['isTeacher'] == "true") {
                // Check CRM
                if($crmContactId != null) {
                    $this->ackCRMPositive($data, $crmContactId, $userId);

                } else {
                    // TODO: Call negative CRM
                    $this->ackCRMNegative($data, $userId);
                    // send email
                    Mail::to($data['email'])->send(new NoCRMInfo($data));
                }
            }
            // End of communication with CRM

            if($data['isTeacher'] == "true") {
                $groupId = $this->getGroupIdByName("Teacher");
            } else {
                $groupId = $this->getGroupIdByName('Student');
            }

            $setGroupRequest = new Request([],[
                'groupId' => $groupId,
                'userId' => $userId,
                'token' => $data['token']
            ], [], [], [], [], null);

            $this->setUserGroup($setGroupRequest);

            if(($data['isTeacher'] == "true" && $inCRM == true) || $data['isTeacher'] == "false") {
                // Send password reset link.
                if($data['updatePassword'] == 'true') {
                    Http::withToken($data['token'])->withBody('["UPDATE_PASSWORD"]', 'application/json')
                        ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
                }
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
                    "billing_phone" => $data['telefon1'],
                    "testomat" => $data['testomat'] == "true" ? 1 : 0,
                    "pedagoska_sveska" => $data["pedagoska_sveska"] == "true" ? 1 : 0,
                    "source" => $data["Source"] ?? 'Klett',
                    "role" => $data['isTeacher'] == "true" ? "Teacher" : "Student"
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
                $groupId = $this->getGroupIdByName('Student');
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

            ScheduledEdit::where('user_id', $userId)->update([
                'validated' => true
            ]);

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
}
