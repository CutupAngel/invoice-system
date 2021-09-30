<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Order_Group extends Model
{
	protected $connection = 'site';
	protected $table = 'order_groups';
	protected $fillable = ['user_id', 'name', 'description', 'url'];

	public function user()
	{
		return $this->belongsTo('App\User');
	}

	public function packages()
	{
		return $this->hasMany('App\Package', 'group_id');
	}

	public function package($packageId)
	{
		return $this->hasOne('App\Package', 'group_id')->with('link.option.values')->where('id', $packageId)->firstOrFail();
	}

	public function packageByUrl($packageSlug)
	{
		return $this->hasOne('App\Package', 'group_id')->where('url', urlencode($packageSlug))->firstOrFail();
	}

	public function permissionCheck($userId)
	{
		if ($this->user_id !== $userId) {
			throw new \Exception("User does not own this group");
		}
	}
}
