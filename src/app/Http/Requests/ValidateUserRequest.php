<?php

namespace LaravelEnso\Core\app\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class ValidateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $emailUnique = Rule::unique('people', 'email');
        $uidUnique = Rule::unique('people', 'uid');
        if ($this->method() === 'PATCH') {
            $emailUnique = Rule::unique('users', 'email');

            $emailUnique = $emailUnique->ignore($this->route('user')->id);
            $uidUnique = $uidUnique->ignore($this->route('user')->person_id);
        }
        // $emailUnique = Rule::unique('users', 'email');
 
        // $emailUnique = ($this->method() === 'PATCH')
        //     ? $emailUnique->ignore($this->route('user')->id)
        //     : $emailUnique;

        return [
            'title' => 'integer|nullable',
            'name' => 'required|max:50',
            'appellative' => 'string|max:12|nullable',
            'uid' => ['string', 'nullable', $uidUnique],
            'email' => ['email', 'nullable', $emailUnique],
            'phone' => 'max:30|nullable',
            'birthday' => 'date|nullable',
            'gender' => 'integer|nullable',
            'obs' => 'string|nullable',
            'person_id' => 'exists:people,id',
            'group_id' => 'required|exists:user_groups,id',
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|min:6|confirmed',
            'is_active' => 'boolean',
            'csr_id'=>'required',
        ];
    }
}
