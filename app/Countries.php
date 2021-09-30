<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Countries extends Model
{
	protected $connection = 'main';
	protected $table = 'countries';
	protected $fillable = ['name', 'iso2','iso3','isoNUM','address_format','postcode_required'];
	protected $hidden = ['created_at', 'updated_at', 'deleted_at'];

	public function counties()
	{
		return $this->hasMany(Counties::class, 'country_id');
	}
}
