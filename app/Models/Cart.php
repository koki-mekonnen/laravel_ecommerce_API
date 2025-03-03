<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Cart extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'carts';
    protected $keyType = 'string';
    public $timestamps = true;

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
        'cart_id',
        'product_id',
        'user_id',
        'amount',
        'totalprice',
        'status',
    ];

    protected $casts = [
        'product_size'  => 'array',
        'product_color' => 'array',
        'product_image' => 'array',
        'status'        => 'string',
    ];

    protected $attributes = [
        'status' => 'active',
    ];

    // Boot method to auto-generate UUID
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->id = (string) Str::uuid();
        });
    }

    // Relationships
    public function owner()
    {
        return $this->belongsTo(Merchant::class, 'owner_id', 'id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id', 'id');
    }


    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_products', 'cart_id', 'product_id')
                    ->withPivot('quantity', 'price', 'total_price')
                    ->withTimestamps();
    }



    // Accessors
    public function getSubtotalAttribute()
    {
        return $this->products->sum(function ($product) {
            return $product->pivot->quantity * $product->pivot->price;
        });
    }

    public function getTaxAttribute()
    {
        $taxRate = 0.1; // Example: 10% tax rate
        return $this->subtotal * $taxRate;
    }

    public function getGrandTotalAttribute()
{
    return max(0, $this->subtotal + $this->tax - $this->discount);
}


    public function getDiscountAttribute()
    {
        if ($this->coupon) {
            return $this->coupon->discount_type === 'percentage'
                ? ($this->subtotal * $this->coupon->discount_value / 100)
                : $this->coupon->discount_value;
        }
        return 0;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAbandoned($query)
    {
        return $query->where('status', 'abandoned');
    }
}
