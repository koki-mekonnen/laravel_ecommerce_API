<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Merchant extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'merchants';

    // Disable auto-incrementing
    public $incrementing = false;

    // Specify the key type
    protected $keyType = 'string';

    // Specify the fillable fields
    protected $fillable = [
        'firstname',
        'lastname',
        'phone',
        'email',
        'password',
        'license',
        'tinnumber',
        'role',
    ];


    

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public $timestamps = true;

    // Automatically generate UUIDs for new records
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [
            'role' => $this->role,
        ];
    }
}
