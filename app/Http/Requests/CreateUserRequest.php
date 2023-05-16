<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use PDO;

class CreateUserRequest extends FormRequest
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
            'ime' => "required",
            'prezime' => 'required',
            'email' => 'email|required',
            'repeated_email' => 'email|required',
            'password' => 'required',
            'repeated_password' => 'required',
            'adresa' => 'required',
            'pb' => 'required',
            'mesto' => 'required',
            'tel1' => 'required',
        ];
    }

    public function withValidator($validator) {
        $validator->after(function($validator) {
            $data = $this->post();
            if($data['password'] != $data['repeated_password']) {
                $validator->errors()->add('repeated_password','Lozinke se moraju poklapati!');
            }

            if($data['email'] != $data['repeated_email']) {
                $validator->errors()->add('repeated_email', 'Imejl adrese se moraju poklapati!');
            }

            if($data['country'] == 0) {
                $validator->errors()->add('country', 'Morate odabrati državu!');
            }

            if($data['isTeacher'] == "true" && $data['school'] == "0") {
                $validator->errors()->add('school', 'Morate odabrati školu!');
            }

            if($data['isTeacher'] == "true" && !isset($data['subjects'])) {
                $validator->errors()->add('subjects', 'Morate odabrati bar jedan predmet!');
            }

            if($data['isTeacher'] == "true" && !isset($data['professionalStatuses'])) {
                $validator->errors()->add('professionalStatuses', 'Morate odabrati bar jedan status!');
            }

        });
    }
}
