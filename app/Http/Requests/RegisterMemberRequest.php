<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterMemberRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Combine email
        if ($this->has('email_prefix') && $this->has('email_domain')) {
            $this->merge([
                'email' => $this->input('email_prefix') . '@' . $this->input('email_domain'),
            ]);
        }

        // Combine mobile (if array)
        if ($this->has('mobile') && is_array($this->input('mobile'))) {
             $this->merge([
                'mobile_str' => implode('-', $this->input('mobile')),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $regType = $this->input('register_type', 'general');

        $rules = [
            'agree_terms' => 'required',
            'agree_privacy' => 'required',
            'agree_third_party' => 'required'
        ];

        if ($regType == 'general') {
            // Username: Alpha-numeric only (a-z, A-Z, 0-9)
            $rules['username'] = [
                'required',
                'min:4',
                'max:20',
                'unique:users,username',
                'regex:/^[a-zA-Z0-9]+$/'
            ];
            $rules['email'] = 'required|email|max:150|unique:users';
            // Password: Upper + Lower + Special + Min 6
            $rules['password'] = [
                'required',
                'confirmed',
                'min:6',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*[!@#$%^&*]).+$/'
            ];
        } else {
            // Social
            $rules['email'] = 'required|email|max:150|unique:users'; 
            $rules['name'] = 'required|string|max:100';
            $rules['mobile'] = 'required|numeric|digits_between:10,11';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'agree_terms.required' => '이용약관에 동의해야 합니다.',
            'agree_privacy.required' => '개인정보 수집 및 이용에 동의해야 합니다.',
            'agree_third_party.required' => '제3자 정보제공에 동의해야 합니다.',
            'password.confirmed' => '비밀번호가 일치하지 않습니다.',
            'password.regex' => '비밀번호는 영문 대문자, 소문자, 특수문자(!@#$%^&*)를 포함해야 합니다.',
            'email.unique' => '이미 등록된 이메일입니다.',
            'username.unique' => '이미 사용중인 아이디입니다.',
            'username.required' => '아이디를 입력해주세요.',
            'username.regex' => '아이디는 영문 대소문자와 숫자만 사용 가능합니다.'
        ];
    }
}
