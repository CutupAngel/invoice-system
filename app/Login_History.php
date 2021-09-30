<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Login_History extends Model
{
	protected $connection = 'site';
    protected $table = 'login_history';

    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}
