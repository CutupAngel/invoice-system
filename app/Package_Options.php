<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Package_Options extends Model
{
	use SoftDeletes;

	/**
	 * @var string
	 */
	protected $connection = 'site';

	/**
	 * @var string
	 */
	protected $table = 'options';

	/**
	 * @var array
	 */
	protected $fillable = [
		'user_id',
		'internal_name',
		'display_name',
		'required',
		'type',
	];

	public function user()
	{
		return $this->belongsTo('App\User');
	}

	/**
     * Relation values
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
	public function values()
	{
		return $this->hasMany('App\Package_Option_Values','option_id','id');
	}
}
