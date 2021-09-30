<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class APIToken extends Model
{
	protected $table = 'api_tokens';
    protected $primaryKey = 'token';
    public $incrementing = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
