<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxRates extends Model
{
	protected $connection = 'site';
	protected $table = 'taxRates';
	protected $hidden = [
		'id',
		'created_at',
		'updated_at',
		'deleted_at'
	];

	protected $fillable = [
		'rate',
		'zone_id',
		'class_id'
	];
}
