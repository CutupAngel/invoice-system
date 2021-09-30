<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order_Settings extends Model
{
	protected $connection = 'site';
	protected $table = 'order_settings';
	protected $fillable = ['order_id', 'setting_name', 'setting_value'];
	protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];
}
