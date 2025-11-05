<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // For update, you might want to make some fields sometimes|required — keep it flexible.
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

    /**
     * Prepare the data for validation.
     * Ensure boolean string values like "true" / "false" get converted so boolean rule works.
     */
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
