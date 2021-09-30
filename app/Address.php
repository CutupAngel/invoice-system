<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User_Contact;

class Address extends Model
{
	protected $connection = 'site';

	protected $fillable = [
		'business_name',
		'contact_name',
		'phone',
		'fax',
		'email',
		'address_1',
		'address_2',
		'address_3',
		'address_4',
		'city',
		'county_id',
		'country_id',
		'postal_code'
	];

	protected $hidden = [
	    'id',
		'address_type',
		'created_at',
		'updated_at',
		'deleted_at'
	];

	protected $attributes = [
		'country_id' => 1
	];

	public function country()
	{
		return $this->belongsTo(Countries::class);
	}

	public function county()
	{
		return $this->belongsTo(Counties::class);
	}

  public function getGprdDownloadData($user_id)
	{
				$userContacts = User_Contact::where('user_id', $user_id)->get();

				$addressIds = [];
				foreach($userContacts as $contact)
				{
						$addressIds[] = $contact->address_id;
				}

      return $this->makeHidden(['deleted_at'])->whereIn('id', $addressIds)->get();
  }

	public function getFirstName()
	{
			$contactNameArr = explode(' ', $this->contact_name);
			return $contactNameArr[0];
	}

	public function getLastName()
	{
			$contactNameArr = explode(' ', $this->contact_name);
			if(count($contactNameArr) > 1) return $contactNameArr[1];
			else return '';
	}

}
