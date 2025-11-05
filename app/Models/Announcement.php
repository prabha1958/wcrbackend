<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = [
        'date',
        'title',
        'description',
        'published',
    ];

    protected $casts = [
        'date' => 'date',
        'published' => 'boolean',
    ];
}
