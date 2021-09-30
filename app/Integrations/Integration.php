<?php

namespace App\Integrations;

use Settings;

abstract class Integration
{
	abstract public static function getInfo();

	abstract public static function checkEnabled();

	abstract public static function toggle();

	abstract public function getError();

	abstract public static function getSetupForm();

    abstract public static function setup(\Illuminate\Http\Request $request);

	protected static function __createIntegrationGroup()
	{
		$group = Settings::getAsUser(1, 'integration.packageGroup', -1);

		try {
			return \App\Order_Group::findOrFail($group);
		} catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
			$group = \App\Order_Group::create([
				'user_id' => 1,
				'name' => 'Integrations -- DO NOT DELETE',
				'visible' => 2
			]);

			Settings::set([
				'integration.packageGroup' => $group->id
			]);

			return $group;
		}
	}
}
