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
            'ime' => 'required',
            'prezime' => 'required',
            'korisnickoIme' => 'required',
            'email' => 'email|required',
            'telefon1' => 'required',
        ];
    }

    public function withValidator($validator) {
        $validator->after(function($validator) {
            $data = $this->post();

            if($data['isTeacher'] == "true" && $data['skola'] == "0") {
                $validator->errors()->add('skola', 'Morate odabrati školu!');
            }

            if($data['isTeacher'] == "true" && !isset($data['predmeti'])) {
                $validator->errors()->add('predmeti', 'Morate odabrati bar jedan predmet!');
            }
        });
    }
}
