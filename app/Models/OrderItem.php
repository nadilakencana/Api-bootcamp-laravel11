<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;
    protected $table = 'order_items';
    protected $guarded = [];

    public function order(){
        return $this->belongsTo(Orders::class, 'id_order', 'id');
    }

    public function Product(){
        return $this->belongsTo(Product::class, 'id_product', 'id');
    }

    public function ProductLog(){
        return $this->hasMany(ProductLog::class, 'id_order_item', 'id');
    }
}
