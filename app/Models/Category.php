<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Category extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $table = 'categories';

    // Disable auto-incrementing
    public $incrementing = false;

    // Specify the key type
    protected $keyType = 'string';

    // Specify the fillable fields
    protected $fillable = [

        'category_name',
        'category_type',
        'owner_id',

    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'category_id', 'id');
    }

    public $timestamps = true;

     // Relationship to Merchant (owner)
    public function owner(): BelongsTo
    {
        return $this->belongsTo(Merchant::class, 'owner_id', 'id');
    }

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
