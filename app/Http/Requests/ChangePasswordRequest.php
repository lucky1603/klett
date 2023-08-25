<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'password' => 'required|regex:/([A-Za-z]+[0-9]+){8,}/',
            'repeatPassword' => 'required'
        ];
    }

    public function withValidator($validator) {
        $validator->after(function($validator) {
            $data = $this->post();

            if($data['password'] != $data['repeatPassword']) {
                $validator->errors()->add('repeatPassword', 'Lozinke se ne poklapaju!');
            }
        });
    }
}
