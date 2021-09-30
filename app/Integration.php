<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Integration extends Model
{
    protected $connection = 'site';
    protected $table = 'integrations';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
