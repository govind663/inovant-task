<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditTrail;
use Illuminate\Support\Facades\Storage;

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
     * Hidden fields (optional cleanup)
     */
    protected $hidden = [
        'image_path',
    ];

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

    // Full image URL for API response (safe + flexible)
    public function getImageUrlAttribute()
    {
        return $this->image_path
            ? Storage::url($this->image_path)
            : null;
    }
}