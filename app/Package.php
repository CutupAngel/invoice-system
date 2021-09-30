<?php

namespace App;

use App\TaxRates;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
	protected $connection = 'site';
	protected $table = 'order_group_packages';
	protected $fillable = ['group_id', 'name', 'description', 'tax', 'prorate', 'trial', 'filepath', 'integration', 'integration_id'];

	protected $casts = [
		'domainIntegration' => 'boolean'
	];

	public function group()
	{
		return $this->belongsTo('App\Order_Group');
	}

	public function link()
	{
		return $this->hasMany('App\Options_To_Packages','package_id','id');
	}

	public function getLinkOptions()
	{
        return Package_Options::whereIn('id', $this->link->lists('option_id'))->get();
	}

	public function opt()
	{
		return $this->hasManyThrough('App\Package_Options','App\Options_To_Packages','package_id','id');
	}

	public function options()
	{
		return $this->hasManyThrough('App\Package_Options','App\Options_To_Packages','package_id','id');
	}

	public function cycles()
	{
		return $this->hasMany('App\Package_Cycle');
	}

	public function cycle($cycleId)
	{
		return $this->hasOne(Package_Cycle::class,'package_id','id')->where('id', $cycleId)->first();
	}

	public function files()
	{
		return $this->hasMany('App\Package_File');
	}

	public function images()
	{
		return $this->hasMany('App\Package_Image');
	}

	public function settings()
	{
		return $this->hasMany('App\PackageSetting');
	}

	public function getTaxRateAttribute()
	{
		if ($this->tax <= 0) {
			return 0;
		}

		if ($tax = TaxRates::where('class_id', $this->tax)->first()) {
			return $tax->rate;
		}
	}
}
