<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
       protected $fillable = ['order_id', 'total_amount'];
    public $incrementing = true;
    public static function generateOrderId()
{
    $lastOrder = self::latest()->first();
    $number = $lastOrder ? intval(substr($lastOrder->order_id, 4)) + 1 : 1;
    return 'ORD-' . str_pad($number, 3, '0', STR_PAD_LEFT);
}
}
