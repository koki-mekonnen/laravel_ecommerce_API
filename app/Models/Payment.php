<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
    use HasFactory;

    // Define the table name (optional, in case it doesn't follow Laravel's convention)
    protected $table = 'payments';

    // Specify the primary key type
    protected $keyType = 'string';

    // Define the fillable attributes for mass assignment
    protected $fillable = [
            'amount',
            'reason',
            'merchantId',
            'signedToken',
            'successRedirectUrl',
            'failureRedirectUrl',
            'notifyUrl',
            'cancelRedirectUrl',
    ];


    /**
     * Relationship: Payment belongs to an Owner (Merchant).
     */
    public function owner()
    {
        return $this->belongsTo(Merchant::class, 'owner_id', 'id');
    }

    /**
     * Relationship: Payment belongs to a User (via user_id).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

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

