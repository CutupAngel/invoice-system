<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TaxClasses extends Model
{
	protected $connection = 'site';
	protected $table = 'taxClasses';
	protected $hidden = [
		'id',
		'created_at',
		'updated_at',
		'deleted_at'
	];

	protected $fillable = [
		'name',
		'user_id',
		'default'
	];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function taxRate()
	{
		return $this->hasOne(\App\TaxRates::class, 'class_id');
	}
}
