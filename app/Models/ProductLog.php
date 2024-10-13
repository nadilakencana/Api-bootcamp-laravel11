<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductLog extends Model
{
    use HasFactory;
    protected $table = 'product_logs';
    protected $guarded = [];

    public function Product(){
        return $this->belongsTo(Product::class, 'id_product', 'id');
    }
    public function OrderItem(){
        return $this->belongsTo(OrderItem::class, 'id_order_item', 'id');
    }
    public function Order(){
        return $this->belongsTo(Orders::class, 'id_order', 'id');
    }
}
