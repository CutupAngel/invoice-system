<?php

namespace App\Packages\Settings;

use App\User;
use App\User_Setting;
use Illuminate\Support\Arr;

class Provider
{
    /**
     * @var array
     */
	protected static $cached = [];

    /**
     * @var array
     */
	protected static $cachedForUser = [];

    /**
     * Provider constructor.
     */
	public function __construct()
	{
		if (empty(self::$cached) && auth()->check()) {
			auth()->user()
                ->settings
                ->each(function ($setting) {
                    Arr::set(self::$cached, $setting->name, $setting->value);
                });
		}
	}

    /**
     * @param $key
     * @param null $default
     * @return mixed|null
     */
	public function get($key, $default = null)
	{
	    if (Arr::has(self::$cached, $key)) {
	        return Arr::get(self::$cached, $key);
        }

		return $default;
	}

    /**
     * @param $user
     * @param $key
     * @param null $default
     * @return mixed|null
     */
	public function getAsUser($user, $key, $default = null)
	{
		if ($user instanceof User) {
			$user = $user->id;
		}

		if (!isset(self::$cachedForUser[$user])) {
			User_Setting::where('user_id', $user)
                ->get()
                ->each(function ($setting) use ($user) {
                   Arr::set(self::$cachedForUser[$user], $setting->name, $setting->value);
                });
		}

		if (Arr::has(self::$cachedForUser[$user], $key)) {
			return Arr::get(self::$cachedForUser[$user], $key);
		}

		return $default;
	}

    /**
     * @param $settings
     * @param null $user_id
     * @return bool
     */
	public function set($settings, $user_id = null)
	{
		if(auth()->check()) {
            $user_id = auth()->id();
        }

		foreach ($settings as $key => $value) {
			if (Arr::has(self::$cached, $key)) {
				$setting = User_Setting::where('name', $key)
                    ->where('user_id', $user_id)
                    ->first();

				$setting->update(compact('value'));
				Arr::set(self::$cached, $key, $value);
			} else {
				$setting = new User_Setting();
				$setting->name = $key;
				$setting->value = $value;
				$setting->user_id = $user_id;
				$setting->save();
				Arr::set(self::$cached, $key, $value);
			}
		}

		return true;
	}
}
