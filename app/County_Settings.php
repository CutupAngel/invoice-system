<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class County_Settings extends Model
{
	protected $connection = 'main';
	protected $table = 'county_settings';
	protected $fillable = ['county_id', 'user_id','setting_name','setting_value'];
	protected $hidden = ['created_at', 'updated_at', 'deleted_at'];
}
