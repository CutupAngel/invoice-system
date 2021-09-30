<?php

namespace App\Packages\Integrations;

use Auth;
use App\User_Setting;

class Provider
{
	protected static $cached = [
		'gateways'=>[],
		'gatewaysByType'=>[],
	];

	public function __construct()
	{
		if (empty(self::$cached['gateways'])) {
			$gateways = glob(app_path('Gateways/*.php'));
			$gateways = array_map(function ($value) {
				return str_replace('.php', '', last(explode('/', $value)));
			}, $gateways);

			foreach ($gateways as $gateway) {
				$class = "\\App\\Gateways\\{$gateways}";

				if (!is_callable([$class, 'getInfo'])) {
					continue;
				}

				$info = $class::getInfo();
				$type = head(class_parents($class));
				self::$cached['gateways'][$info['shortname']] = [
					'title' => $info['title'],
					'shortname' => $info['shortname'],
					'description' => $info['description'],
					'type' => (new \ReflectionClass($type))->getShortName(),
					'status' => $info['status'],
					'class' => $class
				];

				if (!isset(self::$cached['gatewaysByType'][$type])) {
					self::$cached['gatewaysByType'][$type] = [
						'title' => $class::TYPETITLE,
						'gateways' => []
					];
				}

				self::$cached['gatewaysByType'][$type]['gateways'][] = $info['shortname'];
			}

			self::$cached['gateways'] = collect(self::$cached['gateways']);
		}
	}

	public function pay($invoice,$method,$saved = null)
	{
		
	}
}
