<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Link extends Model
{
	protected $connection = 'site';
	protected $table = 'user_link';
	protected $primaryKey = 'user_id';
	public $timestamps = false;
    protected $fillable = [
        'user_id',
        'parent_id'
    ];
	public function parent()
	{
		$this->belongsTo(User::class, 'id', 'parent_id');
	}

	public function parentUser()
	{
		$this->belongsTo(User::class, 'id', 'parent_id');
	}

	public function user()
	{
		$this->belongsTo(User::class, 'id', 'user_id');
	}

	public static function inverse()
	{
		return new class extends User_Link {
			protected $primaryKey = 'parent_id';
		};
	}
}
