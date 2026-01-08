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
        'picture',
        'exp_date'
    ];

    protected $casts = [
        'date' => 'date',
        'exp_date' => 'date',
        'published' => 'boolean',
    ];
}
