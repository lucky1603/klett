<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FetchUsernames extends Mailable
{
    use Queueable, SerializesModels;

    private $email;
    private $usernames;
    private $message;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($email, $message, $usernames)
    {
        $this->email = $email;
        $this->message = $message;
        $this->usernames = $usernames;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.fetchusernames', ['email' => $this->email,'poruka' => $this->message, 'usernames' => $this->usernames])   
            ->replyTo("tehnicka.podrska@klett.rs")         
            ->subject("Korisnička imena");
    }
}
