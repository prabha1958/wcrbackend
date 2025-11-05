<?php

namespace App\Http\Requests;

use App\Models\Member;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins will reach this request thanks to middleware, but keep check if desired:
        $user = $this->user();
        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    public function rules(): array
    {
        $memberId = $this->route('member')?->id;

        return [
            'family_name'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'first_name'          => ['sometimes', 'nullable', 'string', 'max:255'],
            'last_name'           => ['sometimes', 'nullable', 'string', 'max:255'],
            'date_of_birth'       => ['sometimes', 'nullable', 'date'],
            'wedding_date'        => ['sometimes', 'nullable', 'date'],

            // NEW fields
            'spouse_name'         => ['sometimes', 'nullable', 'string', 'max:255'],
            'gender'              => ['sometimes', 'nullable', 'in:male,female,other'],
            'status_flag'         => ['sometimes', 'boolean'],

            // contact fields (example)
            'email'               => ['sometimes', 'nullable', 'email', 'max:255'],
            'mobile_number'       => ['sometimes', 'nullable', 'string', 'max:50'],

            // file/photo if present in your app
            'profile_photo'       => ['sometimes', 'nullable', 'image', 'max:5120'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('status_flag')) {
            $val = $this->input('status_flag');
            // normalize typical boolean representations
            $this->merge([
                'status_flag' => in_array($val, [true, 'true', 1, '1', 'on', 'yes'], true) ? 1 : 0,
            ]);
        }
    }
}
