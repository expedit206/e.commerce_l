<?php

namespace App\Models;

use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'grand_total',
        'payment_method',
        'payment_status',
        'status',
        'currency',
        'shipping_amount',
        'shipping_method',
        'notes'
       ]; 

       public function user()
       {
           return $this->belongsto(User::class);
       }
       

       public function items()
       {
           return $this->hasMany(OrderItem::class);
       }

       public function address()
       {
           return $this->hasOne(Address::class);
       }
       
       
}
