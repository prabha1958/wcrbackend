<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenFellowshipRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Only admins can create/update/delete
        $user = $this->user();
        return $user && method_exists($user, 'isAdmin') && $user->isAdmin();
    }

    public function rules(): array
    {
        return [
            'date_of_event' => ['required', 'date'],
            'members_present' => ['required', 'array'],
            'members_present.*' => ['string', 'max:255'], // each name or ID
            'sermon_by' => ['required', 'string', 'max:255'],
            'event_photos' => ['sometimes', 'array', 'max:4'], // limit 4 images
            'event_photos.*' => ['file', 'image', 'max:5120'], // 5 MB each
        ];
    }
}
