<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Plan_Cycle extends Model
{
	protected $connection = 'main';
	protected $table = 'billingserv_plan_cycles';
	protected $fillable = ['plan_id', 'price', 'fee', 'cycle', 'currency_id'];

	public static $cycles = [
		1 => 'One-Off',
		2 => 'Daily',
		3 => 'Weekly',
		4 => 'Fortnightly',
		5 => 'Monthly',
		6 => 'Every 2 Months',
		7 => 'Every 3 Months',
		8 => 'Every 4 Months',
		9 => 'Every 5 Months',
		10 => 'Every 6 Months',
		11 => 'Every 7 Months',
		12 => 'Every 8 Months',
		13 => 'Every 9 Months',
		14 => 'Every 10 Months',
		15 => 'Every 11 Months',
		16 => 'Every 12 Months',
		17 => 'Every 24 Months',
		18 => 'Every 36 Months'
	];

	public function plan()
	{
		return $this->belongsTo('App\Plan');
	}

	public function cycle()
	{
		return self::$cycles[$this->cycle];
	}

	public function subscriptions()
	{
		return $this->hasMany(Subscription::class);
	}

	public function currency()
	{
		return $this->hasOne(Currency::class,'currency_id','id');
	}

	public function activeSubscriptions()
	{
		return $this->hasMany(Subscription::class)->where('status', Subscription::SETUP);
	}
}
