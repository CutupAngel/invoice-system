<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
	protected $connection = 'site';
    protected $fillable = [
    	'invoice_id',
    	'item',
    	'product',
    	'description',
    	'price',
    	'quantity',
		'package_id',
		'tax_class'
    ];

		public function invoice()
		{
			return $this->hasOne(Invoice::class, 'id', 'invoice_id');
		}
}
