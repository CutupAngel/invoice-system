<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxZones extends Model
{
	protected $connection = 'site';
	protected $table = 'taxZones';
	protected $hidden = [
		'id',
		'created_at',
		'updated_at',
		'deleted_at'
	];

	protected $fillable = [
		'name',
		'user_id'
	];

	public function zoneCounties()
	{
		return $this->hasMany(TaxZoneCounties::class, 'zone_id', 'id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}
}
