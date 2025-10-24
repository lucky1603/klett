<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;

class Recaptcha implements Rule
{
    protected ?array $data = null;
    protected ?array $error = null;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $secret = config('services.recaptcha.secret_key');

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify',[
            'secret' => $secret,
            'response' => $value,
            'remoteip' => request()->ip(),
        ]);

        if(!$response->ok()) {
            $this->error = 'Neuspesna komunikacija sa reCAPTCHA servisom';
            return false;
        }

        $this->data = $response->json();

        // v3
        if(isset($this->data['score']) && $this->data['score'] < 0.5) {
            $this->error = 'Detektovana sumnjiva aktivnost (reCAPTCHA score).';
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->error;
    }
}
