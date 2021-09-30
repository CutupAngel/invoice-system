<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SSOToken extends Model
{
	protected $table = 'sso_tokens';
    protected $primaryKey = 'token';
    public $incrementing = false;

    protected $fillable = [
    	'user_id',
    	'token'
    ];

    public $dates = [
        'created_at'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
