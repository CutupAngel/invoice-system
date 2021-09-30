<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxZoneCounties extends Model
{
	protected $connection = 'site';
	protected $table = 'taxZoneCounties';
	protected $hidden = [
		'id',
		'created_at',
		'updated_at',
		'deleted_at'
	];

	protected $fillable = [
		'zone_id',
		'country_id',
		'county_id'
	];

	public function county()
	{
		return $this->hasOne(Counties::class, 'county_id', 'id');
	}
}
