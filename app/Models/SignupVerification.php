<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SignupVerification extends Model
{
    protected $fillable = [
        'email',
        'pin_hash',
        'expires_at',
        'verified_at',
        'attempts',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'verified_at' => 'datetime',
        ];
    }
}
