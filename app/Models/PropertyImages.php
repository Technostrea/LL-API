<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PropertyImages extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_id',
        'image_url',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }
}
