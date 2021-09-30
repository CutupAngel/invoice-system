<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order_Options extends Model
{
	// use SoftDeletes;

	// Statuses
	const RECENT = 0; // Recent is used as NEW is a keyword.
	const PENDING = 1;
	const SETUP = 2;
	const SHIPPED = 3; // For mailed items.
	const CANCELLED = 4;
	const RETURNED = 5; // For mailed items.
	const TERMINATED = 6;
	const SUSPENDED = 7;

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

	private $statusToText = [
		self::RECENT => 'Recent',
		self::PENDING => 'Pending',
		self::SETUP => 'Setup',
		self::SHIPPED => 'Shipped',
		self::CANCELLED => 'Cancelled',
		self::RETURNED => 'Returned',
		self::TERMINATED => 'Terminated',
		self::SUSPENDED => 'Suspended'
	];

	protected $connection = 'site';
	protected $table = 'order_options';
	protected $fillable = ['order_id', 'option_value_id', 'amount', 'value', 'cycle_type', 'status','last_invoice'];
	protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];

	public function order()
	{
		return $this->belongsTo(Order::class);
	}

	public function option_value()
	{
		return $this->belongsTo(Package_Option_Values::class, 'option_value_id', 'id');
	}
}
