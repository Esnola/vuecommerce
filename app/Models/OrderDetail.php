<?php

namespace App\Models;

use Database\Factories\OrderDetailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    /** @use HasFactory<OrderDetailFactory> */
    use HasFactory;
}
