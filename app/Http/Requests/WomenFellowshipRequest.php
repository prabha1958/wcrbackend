<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class WomenFellowshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins allowed to create/update/delete. Replace with your admin check.
        $user = $this->user();
        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'date_of_event' => ['required', 'date'],
            'members_present' => ['required', 'array'],
            'members_present.*' => ['string', 'max:255'], // or 'integer|exists:members,id' for IDs
            'sermon_by' => ['required', 'string', 'max:255'],
            'event_photos' => ['sometimes', 'array', 'max:4'],
            'event_photos.*' => ['file', 'image', 'max:5120'], // 5MB each
        ];
    }
}
