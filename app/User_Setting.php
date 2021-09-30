<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Setting extends Model
{
	protected $connection = 'site';
	protected $table = 'user_settings';
	protected $fillable = ['user_id', 'name', 'value'];
	protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];

	public function user()
	{
		return $this->belongsTo('App\User');
	}
}
