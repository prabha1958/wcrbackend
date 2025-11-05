<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Member;

class CreateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Admin middleware already protects route, but double-check here.
        $user = $this->user();
        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'family_name'         => ['required', 'string', 'max:255'],
            'first_name'          => ['required', 'string', 'max:255'],
            'middle_name'         => ['nullable', 'string', 'max:255'],
            'last_name'           => ['nullable', 'string', 'max:255'],
            'date_of_birth'       => ['required', 'date'],
            'area_no'       => ['required', 'string', 'max:2'],
            'email'               => [
                'required',
                'email',
                'max:255',
                Rule::unique('members', 'email'),
            ],
            'mobile_number'       => [
                'required',
                'string',
                'min:10',
                'max:10',
                Rule::unique('members', 'mobile_number'),
            ],
            'residential_address' => ['required', 'string'],
            'occupation'          => ['nullable', 'string', 'max:255'],
            'status'              => ['required', Rule::in(['in_service', 'retired', 'other'])],
            'profile_photo'       => ['nullable', 'file', 'image', 'max:2048'],
            'membership_fee'      => ['nullable', 'numeric', 'min:0'],

        ];
    }
}
