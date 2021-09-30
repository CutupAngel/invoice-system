<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package_Option_Values extends Model
{
	use SoftDeletes;
	protected $connection = 'site';
	protected $table = 'option_values';
	protected $fillable = ['option_id', 'display_name', 'cycle_type', 'price', 'fee'];

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

	public function option()
	{
		return Package_Options::where('id',$this->option_id)->first();
		//cant figure out whats wrong with this atm...
		//return $this->belongsTo('App\Package_Options','id','option_id');
	}

	public function cycle()
	{
		return self::$cycles[$this->cycle_type];
	}
}
