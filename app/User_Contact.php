<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class User_Contact extends Model
{
	const CONTACT = '0';
	const MAILING = '1';
	const BILLING = '2';
	const ADMIN   = '3';
	const TECH    = '4';

	protected $connection = 'site';

	protected $table = 'user_contacts';

	protected $fillable = [
		'user_id',
		'address_id',
		'type'
	];

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function address()
	{
		return $this->belongsTo(Address::class);
	}

	public function getAddress()
	{
		if ($this->address_id !== null) {
	        return $this->address;
		} else {
			$address = new Address;
		}

        return $address;
	}

	public function scopeType($query, $type)
    {
        return $query->where('type', $type);
    }
}
