<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\AuditTrail;

class Product extends Model
{
    use SoftDeletes, AuditTrail;

    /**
     * Mass Assignable Fields
     */
    protected $fillable = [
        'name',
        'price',
        'user_id',
    ];

    /**
     * Auto Eager Loading (avoid N+1 issue)
     */
    protected $with = ['images'];

    /**
     * Append custom attributes in response
     */
    protected $appends = ['images_count'];

    /**
     * Relationships
     */

    // One product belongs to a user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // One product has multiple images
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    /**
     * Accessors
     */

    // Get total images count
    public function getImagesCountAttribute()
    {
        return $this->images()->count();
    }
}