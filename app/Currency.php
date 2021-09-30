<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
	protected $connection = 'main';
    protected $table = 'currencies';
    protected $fillable = ['name', 'short_name', 'symbol', 'conversion', 'position'];
}
