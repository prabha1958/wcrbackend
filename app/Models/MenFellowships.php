<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class MenFellowship extends Model
{
    protected $fillable = [
        'date_of_event',
        'members_present',
        'sermon_by',
        'event_photos',
    ];

    protected $casts = [
        'date_of_event' => 'date',
        'members_present' => 'array',
        'event_photos' => 'array',
    ];

    protected $appends = ['event_photo_urls'];

    /**
     * Generate full URLs for event photos (safe fallback).
     */
    public function getEventPhotoUrlsAttribute(): array
    {
        $photos = $this->event_photos ?? [];
        return collect($photos)->map(fn($p) => $this->makePublicUrl($p))->filter()->values()->all();
    }

    protected function makePublicUrl(?string $path): ?string
    {
        if (! $path) return null;

        try {
            if (method_exists(Storage::class, 'url')) {
                return Storage::url($path);
            }
        } catch (\Throwable $e) {
        }

        return asset('storage/' . ltrim($path, '/'));
    }
}
