<?php

namespace App\Infrastructure\Eloquent;

use App\Infrastructure\Eloquent\EloquentUsage;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class EloquentUser extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // Relationships
    public function usages(): HasMany
    {
        return $this->hasMany(EloquentUsage::class);
    }

    public function inquiries(): HasMany
    {
        return $this->hasMany(EloquentInquiry::class);
    }

    // Scopes
    protected function scopeSearchByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }

    protected function scopeSearchById($query, int $id)
    {
        return $query->where('id', $id);
    }
}