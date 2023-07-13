<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRemoteUserRequest extends FormRequest
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
            'firstName' => 'required',
            'lastName' => 'required',
            'username' => 'required',
            'password' => 'required',
            'email' => 'email|required',
            'address' => 'required',
            'pb' => 'required',
            'city' => 'required',
            'tel1' => 'required',
        ];
    }

    public function withValidator($validator) {
        $validator->after(function($validator) {
            $data = $this->post();

            if($data['isTeacher'] == "true" && $data['institution'] == "0") {
                $validator->errors()->add('institution', 'Morate odabrati školu!');
            }

            if($data['isTeacher'] == "true" && !isset($data['subjects'])) {
                $validator->errors()->add('subjects', 'Morate odabrati bar jedan predmet!');
            }
        });
    }
}
