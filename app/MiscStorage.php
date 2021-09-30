<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MiscStorage extends Model
{
	protected $connection = 'site';
	protected $table = "misc_storage";
    protected $casts = [
    	'value' => 'array'
    ];

    protected $fillable = [
    	'user_id',
    	'name',
    	'value'
    ];

    public function user()
    {
    	return $this->belongsTo(User::class);
    }
}
