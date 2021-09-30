<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Options_To_Packages extends Model
{
	protected $connection = 'site';
	protected $table = 'options_to_packages';
	protected $fillable = ['id', 'option_id', 'package_id'];

	public function option()
	{
		return $this->hasOne('App\Package_Options','id','option_id');
	}
}
