<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditTrail;

class ProductImage extends Model
{
    use SoftDeletes, AuditTrail;

    /**
     * Mass Assignable Fields
     */
    protected $fillable = [
        'product_id',
        'image_path',
    ];

    /**
     * Append custom attributes
     */
    protected $appends = ['image_url'];

    /**
     * Relationships
     */

    // Each image belongs to a product
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Accessors
     */

    // Full image URL for API response
    public function getImageUrlAttribute()
    {
        return asset('storage/' . $this->image_path);
    }
}