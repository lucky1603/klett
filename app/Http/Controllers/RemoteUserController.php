<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\RemoteUsersExport;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use PhpParser\Node\Expr\Cast\Array_;

class RemoteUserController extends Controller
{
    public function index() {
        return view('remoteusers.index');
    }

    /**
     * Get access token.
     */
    public function connectKeyCloak() {

        return Http::asForm()->post(env('KEYCLOAK_TOKEN_URL'), [
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
            ->get(env('KEYCLOAK_REALM_URL')."groups");
    }

    /**
     * Get the group id by the given name.
     */
    public function getGroupIdByName($groupName) {
        $response = $this->connectKeyCloak();
        $token = $response->json('access_token');

        $response = Http::withToken($token)
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
                    ->delete(env('KEYCLOAK_API_USERS_URL').$userId."/groups/".$oldGroupId);

            } else {
                $sameGroups = true;
            }
        }

        if(!$sameGroups) {
            // If the group was changed, set the new group.
            Http::withToken($token)
                ->put(env('KEYCLOAK_API_USERS_URL').$userId."/groups/".$groupId);
        }

        return $userId;

    }

    public function getData(Request $request) {
        $token = $request->post('token');
        return Http::withToken($token)->get(ENV("KEYCLOAK_API_USERS_URL"));
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

        // if($data['username'] != '') {
        //     if(str_contains($requestUrl, "?")) {
        //         $requestUrl .= "?";
        //     } else {
        //         $requestUrl .= "&&";
        //     }

        //     $requestUrl .= "username=".$data['username'];
        // }

        // if($data['email'] != '') {
        //     if(str_contains($requestUrl, "?")) {
        //         $requestUrl .= "?";
        //     } else {
        //         $requestUrl .= "&&";
        //     }

        //     $requestUrl .= "email=".$data['email'];
        // }

        return Http::withToken($token)->get($requestUrl);

    }

    public function create() {
        return view('remoteusers.create');
    }

    public function store(Request $request) {
        $data = $request->post();

        $response = Http::withToken($data['token'])
            ->asJson()
            ->post(env("KEYCLOAK_API_USERS_URL"), [
                "username" => $data['username'],
                "firstName" => $data['firstName'],
                "lastName" => $data['lastName'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" ? true : false,
                "attributes" => [
                    "subjects" => isset($data['subjects']) ? serialize($data['subjects']) : null,
                    "professions" => isset($data['professions']) ? serialize($data['professions']) : null,
                    "township" => isset($data['township']) ?  $data['township'] : null,
                    "institution_type" => isset($data['institutionType']) ? $data['institutionType'] : null,
                    "institution" => isset($data['institution']) ? $data['institution'] : null
                ],
        ]);

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

        $response = $this->setUserGroup($setGroupRequest);

        // Send password reset link.
        if($data['updatePassword'] == 'true') {
            return Http::withToken($data['token'])->withBody('["UPDATE_PASSWORD"]', 'application/json')
                ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
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
            ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
    }

    public function userData(Request $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data['token'];
        $response = Http::withToken($token)
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

        return [
            'id' => $retObject->id,
            'username' => $retObject->username,
            'firstName' => $retObject->firstName ?? '',
            'lastName' => $retObject->lastName ?? '',
            'email' => $retObject->email,
            'institution' => isset($retObject->attributes) ? $retObject->attributes->institution[0] : null,
            'institutionType' => isset($retObject->attributes) ? $retObject->attributes->institution_type[0] : null,
            'township' => isset($retObject->attributes->township)  ? $retObject->attributes->township[0] : null,
            'subjects' => isset($retObject->attributes->subjects) ? unserialize($retObject->attributes->subjects[0]) : [],
            'professions' => isset($retObject->attributes->professions) ? unserialize($retObject->attributes->professions[0]) : [],
            'enabled' => $retObject->enabled,
            'isTeacher' => $isTeacher
        ];
    }

    public function update(Request $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data["token"];
        Http::withToken($token)
            ->asJson()
            ->put(env("KEYCLOAK_API_USERS_URL").$userId,[
                "username" => $data['username'],
                "firstName" => $data['firstName'],
                "lastName" => $data['lastName'],
                "email" => $data["email"],
                "enabled" => $data['enabled'] == "true" ? true : false,
                "attributes" => [
                    "subjects" => isset($data['subjects']) ? serialize($data['subjects']) : null,
                    "professions" => isset($data['professions']) ? serialize($data['professions']) : null,
                    "township" => isset($data['township']) ?  $data['township'] : null,
                    "institution_type" => isset($data['institutionType']) ? $data['institutionType'] : null,
                    "institution" => isset($data['institution']) ? $data['institution'] : null
                ],
        ]);



        // Get current user group.
        $getGroupRequest = new Request([], [
            'userId' => $userId,
            'token' => $token
        ], [], [], [], [], null);

        var_dump($getGroupRequest->post());
        $oldGroup = $this->userGroup($getGroupRequest);
        var_dump($oldGroup);


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

        $response = $this->setUserGroup($setGroupRequest);

        if($data['updatePassword'] == "true") {
            Http::withToken($data['token'])->withBody('["UPDATE_PASSWORD"]', 'application/json')
                ->put(env("KEYCLOAK_API_USERS_URL").$userId."/execute-actions-email");
        }

        return $response;
    }

    public function delete(Request $request) {
        $data = $request->post();

        $userId = $data['userId'];
        $token = $data['token'];

        return Http::withToken($token)
            ->delete(env("KEYCLOAK_API_USERS_URL").$userId);
    }

    public function export() {
        return Excel::download(new RemoteUsersExport, 'remoteusers.xlsx');
    }

}
