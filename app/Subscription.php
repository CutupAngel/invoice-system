<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscription extends Model
{
	use SoftDeletes;

	protected $connection = 'main';
	protected $table = 'billingserv_subscriptions';

	// Statuses
	const RECENT = '0';
	const PENDING = '1';
	const SETUP = '2';
	const UPGRADED = '3';
	const CANCELLED = '4';
	const RETURNED = '5';
	const TERMINATED = '6';

	protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function plan()
	{
		return $this->belongsTo(Plan::class);
	}

	public function cycle()
	{
		return $this->belongsTo(Plan_Cycle::class);
	}
}
