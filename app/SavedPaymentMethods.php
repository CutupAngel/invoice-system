<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SavedPaymentMethods extends Model
{
	protected $connection = 'site';
	protected $table = 'saved_payment_methods';
	protected $fillable = ['user_id', 'gateway_id', 'type', 'billing_address_id', 'last4', 'expiration_month', 'expiration_year', 'token', 'default', 'card_type'];

    public function address()
    {
        return $this->hasOne(Address::class,'id','billing_address_id');
    }
}
