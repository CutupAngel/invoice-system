<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
	protected $connection = 'site';
	protected $table = 'transactions';
	protected $hidden = [
		'id',
		'updated_at',
		'deleted_at'
	];

	protected $fillable = [
		'id',
		'transaction_id',
		'invoice_id',
		'user_id',
		'customer_id',
		'gateway_id',
		'amount',
		'payment_method',
		'status',
		'json_response',
		'currency_id',
		'transaction_key'
	];

	protected $casts = [
		'amount' => 'float'
	];

	public static $paymentMethods = [
		0 => 'with Credit Card',
		1 => 'with Bank Account',
		2 => 'with Paypal',
		3 => 'Other'
	];

	public function payment_method()
	{
		return self::$paymentMethods[$this->payment_method];
	}

	public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

	public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function getCredit()
    {
    	return $this->user && $this->user->getCredit() ? $this->user->getCredit()->value : '0.00';
    }

    public function getGprdDownloadData($user_id){
        return $this->makeHidden(['deleted_at'])
            ->where('customer_id',$user_id)->get();
    }
}
