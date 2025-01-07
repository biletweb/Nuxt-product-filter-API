<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    public function values()
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_attributes')->withPivot('value_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_attributes');
    }
}
