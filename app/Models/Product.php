<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
     protected $table = 'products';
    protected $guarded = [];

    public function Category(){
        return $this->blongsTo(Category::class, 'id_catgory' , 'id');
    }

    public function OrderItem(){
        return $this->hasMany(OrderItem::class, 'id_product','id');
    }

    public function ProductLog(){
        return $this->hasMany(ProductLog::class, 'id_product', 'id');
    }
}
