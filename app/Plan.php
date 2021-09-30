<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
	protected $connection = 'main';
	protected $table = 'billingserv_plans';
	protected $fillable = ['name', 'description', 'tax', 'trial', 'clients', 'invoices', 'staff'];

	public function cycles()
	{
		return $this->hasMany('App\Plan_Cycle');
	}

	public function cycle($cycleId)
	{
		return $this->hasOne(Plan_Cycle::class, 'plan_id', 'id')->where('id', $cycleId)->first();
	}
}
