<?php

namespace App\Http\Controllers;

use App\Mail\TestMail;
use App\Models\SendEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendEmailController extends Controller
{
    public function listAll() {
        return SendEmail::all();
    }

    public function list() {
        $paginator = SendEmail::where('sent', 0)->paginate(10);
        $currentPage = $paginator->currentPage();
        $perPage = $paginator->perPage();
        $count = $paginator->total();

        $returnData = $paginator->load("email_type")
            ->map(function($sendEmail) {
                return [
                    "username" => $sendEmail->username,
                    "email" => $sendEmail->email,
                    "emailType" => $sendEmail->email_type->description,
                    "userId" => $sendEmail->user_id,
                    "sent" => $sendEmail->sent
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

    /**
     * Sends the test email and sets the flag to true.
     */
    public function sendTestMail($userId, $userEmail) {
        $user = SendEmail::where('user_id', $userId)->firstOrFail();
        $user->sent = true;
        $user->updated_at = now();
        $user->save();

        return Mail::to($userEmail)->send(new TestMail($userId));
    }

    public function setSent($userId) {
        $user = SendEmail::where('user_id', $userId)->firstOrFail();
        $user->sent = true;
        $user->updated_at = now();
        $user->save();
    }

    public function deleteAll() {
        return DB::table('send_emails')->delete();
    }
}
