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
     * Get the full image URL attribute.
     *
     * @return string|null
     */
    public function getFullImageUrlAttribute()
    {
        if ($this->image_path && $this->image_filename) {
            $localPath = public_path($this->image_path . '/' . $this->image_filename);
            
            // Check if the file exists locally
            if (file_exists($localPath)) {
                return url($this->image_path . '/' . $this->image_filename);
            }
            
            // If file doesn't exist, use placeholder service
            $name = urlencode($this->name);
            $id = $this->id ?: 1;
            return "https://source.unsplash.com/300x200/?product," . $name;
        }
        
        // Return original image_url as fallback if no uploaded image
        return $this->image_url;
    }
    
    /**
     * Get the thumbnail URL attribute.
     *
     * @return string|null
     */
    public function getThumbnailUrlAttribute()
    {
        if ($this->image_path && $this->image_thumbnail) {
            $localPath = public_path($this->image_path . '/' . $this->image_thumbnail);
            
            // Check if the file exists locally
            if (file_exists($localPath)) {
                return url($this->image_path . '/' . $this->image_thumbnail);
            }
            
            // If thumbnail file doesn't exist, use placeholder service
            $name = urlencode($this->name);
            $id = $this->id ?: 1;
            return "https://source.unsplash.com/150x100/?product," . $name;
        }
        
        // Return full image if no thumbnail
        return $this->full_image_url;
    }
} 