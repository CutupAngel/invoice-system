<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    const FIXED = "0";
    const CODE = "1";

    protected $connection = 'site';

    public static function factory()
    {
    	return new self;
    }

    public function owner()
    {
    	return $this->hasOne(User::class);
    }

    /**
     * Method for get discount codes
     *
     * @param $user \App\User
     * @return int
     */
    public function getDiscountCount($user)
    {
    	if (!$user) {
    		return 0;
    	}

    	return $this->where('user_id', $user->id)
    		->where('type', self::CODE)
    		->count();
    }
}
