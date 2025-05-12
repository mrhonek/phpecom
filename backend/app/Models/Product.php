<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'price',
        'image_url',
        'image_filename',
        'image_path',
        'image_alt',
        'image_thumbnail',
        'stock',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'stock' => 'integer',
    ];

    /**
     * Append custom attributes to the model.
     *
     * @var array
     */
    protected $appends = [
        'full_image_url',
        'thumbnail_url',
    ];

    /**
     * Get the cart items for the product.
     */
    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    /**
     * Get the order items for the product.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the full image URL.
     */
    public function getFullImageUrlAttribute()
    {
        if ($this->image_url && filter_var($this->image_url, FILTER_VALIDATE_URL)) {
            return $this->image_url;
        }
        
        if ($this->image_path && $this->image_filename) {
            return url($this->image_path . '/' . $this->image_filename);
        }
        
        return null;
    }
    
    /**
     * Get the thumbnail URL.
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->image_path && $this->image_thumbnail) {
            return url($this->image_path . '/' . $this->image_thumbnail);
        }
        
        // If no thumbnail exists, return the full image or null
        return $this->getFullImageUrlAttribute();
    }
} 