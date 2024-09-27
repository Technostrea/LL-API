<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agency extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'agency_name',
        'phone',
        'email',
        'agency_license',
        'address',
        'logo',
        'description',
        'website',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
