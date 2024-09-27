<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Pipeline\Pipeline;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory,
        Notifiable,
        HasApiTokens,
        HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'number_phone',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public static function filtered()
    {
        return app(Pipeline::class)
            ->send(self::query())
            ->through([
                \App\Http\Filters\NameFilter::class,
                \App\Http\Filters\EmailFilter::class,
            ])
            ->thenReturn();
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(Property::class, 'property_favorites');
    }

    public function agencies(): HasMany
    {
        return $this->hasMany(Agency::class);
    }

}
