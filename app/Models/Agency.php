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

    /*
    Scope
    */

    // Scope to filter by agency name
    public function scopeFilterByName($query, $name)
    {
        return $query->when($name, function ($query, $name) {
            $query->where('agency_name', 'like', '%' . $name . '%');
        });
    }

    // Scope to filter by email
    public function scopeFilterByEmail($query, $email)
    {
        return $query->when($email, function ($query, $email) {
            $query->where('email', 'like', '%' . $email . '%');
        });
    }

    // Scope to filter by phone number
    public function scopeFilterByPhone($query, $phone)
    {
        return $query->when($phone, function ($query, $phone) {
            $query->where('phone', 'like', '%' . $phone . '%');
        });
    }
}
