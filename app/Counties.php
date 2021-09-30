<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Counties extends Model
{
	protected $connection = 'main';
	protected $table = 'counties';
	protected $fillable = ['country_id', 'name','code'];
	protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

	public function country()
	{
		return $this->belongsTo(Countries::class);
	}
}
