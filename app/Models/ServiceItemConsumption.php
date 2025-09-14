<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceItemConsumption extends Model
{
    protected $table = 'service_item_consumptions';
    protected $fillable = ['service_id','item_id','qty_per_service'];
}
