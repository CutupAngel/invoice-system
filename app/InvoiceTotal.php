<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceTotal extends Model
{
	protected $connection = 'site';
    protected $fillable = [
    	'invoice_id',
    	'item',
    	'price'
    ];
}
