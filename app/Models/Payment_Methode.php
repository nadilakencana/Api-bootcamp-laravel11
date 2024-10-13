<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment_Methode extends Model
{
    use HasFactory;
    protected $table = 'payment_method';
    protected $guarded = [];

    public function Order(){
        return $this->hasMany(Orders::class, 'id_payment', 'id');
    }
}
