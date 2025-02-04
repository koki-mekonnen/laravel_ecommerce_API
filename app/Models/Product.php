<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

    // Define the table name (optional, in case it doesn't follow Laravel's convention)
    protected $table = 'products';

    // Specify the primary key type
    protected $keyType = 'string';

    // Define the fillable attributes for mass assignment
    protected $fillable = [

        'product_name',
        'product_description',
        'product_price',
        'discount',
        'product_size',
        'product_color',
        'product_image',
        'product_brand',
        'product_quantity',
        'owner_id',
        'category_id',
        'category_name',
        'category_type',
    ];

    // Define the casts for JSON fields
    protected $casts = [
        'product_size' => 'array',
        'product_color' => 'array',
        'product_image' => 'array',
    ];

    /**
     * Relationship: Product belongs to an Owner (Merchant).
     */
    public function owner()
    {
        return $this->belongsTo(Merchant::class, 'owner_id', 'id');
    }



    /**
     * Relationship: Product belongs to a Category (via category_id).
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
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
