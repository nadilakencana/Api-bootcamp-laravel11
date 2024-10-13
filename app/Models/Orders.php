<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orders extends Model
{
    use HasFactory;
    protected $table = 'orders';
    protected $guarded = [];

    public function payment(){
        return $this->blongsTo(Payment_Methode::class, 'id_payment', 'id');
    }

    public function User(){
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function OrderItem(){
        return $this->hasMany(OrderItem::class, 'id_order', 'id');
    }

    public function ProductLog(){
        return $this->hasMany(ProductLog::class, 'id_order', 'id');
    }
}
