<?php

namespace App\Http\Controllers;

use App\Models\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SendEmailController extends Controller
{
    public function makeTable(Request $request) {

    }

    public function list() {
        $paginator = SendEmail::paginate(10);
        // $data = $paginator->get();
        $currentPage = $paginator->currentPage();
        $perPage = $paginator->perPage();
        $count = $paginator->total();

        $returnData = $paginator->load("email_type")
            ->map(function($sendEmail) {
                return [
                    "username" => $sendEmail->username,
                    "email" => $sendEmail->email,
                    "emailType" => $sendEmail->email_type->description,
                    "userId" => $sendEmail->user_id
                ];
            });

        return [
            'perPage' => $perPage,
            'currentPage' => $currentPage,
            'count' => $count,
            'rows' => $returnData
        ];
    }

    public function store(Request $request) {
        $data = $request->post();

        $sendMail = SendEmail::create([
            "username" => $data['username'],
            "email" => $data['email'],
            "email_type_id" => $data['emailType'],
            "sent" => false,
            "user_id" => $data['userId'],
            "created_at" => now(),
            "updated_at" => now()
        ]);

        return $sendMail->id;
    }

    public function deleteAll() {
        return DB::table('send_emails')->delete();
    }
}
