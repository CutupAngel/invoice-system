<?php

namespace App\Packages\Integrations;

use Auth;
use App\User_Setting;

class Provider
{
	protected static $cached = [
		'integrations' => [],
		'integrationsByType' => []
	];

	private static $setupCommands = [
		'getSetupForm',
		'setup',
		'toggle'
	];

	public function __construct()
	{
		if (empty(self::$cached['integrations'])) {
			$integrations = glob(app_path('Integrations/*.php'));
			$integrations = array_map(function ($value) {
				return str_replace('.php', '', last(explode('/', $value)));
			}, $integrations);

			foreach ($integrations as $integration) {
				$class = "\\App\\Integrations\\{$integration}";

				if (!is_callable([$class, 'getInfo'])) {
					continue;
				}

				$info = $class::getInfo();
				$type = head(class_parents($class));
				self::$cached['integrations'][$info['shortname']] = [
					'title' => $info['title'],
					'shortname' => $info['shortname'],
					'description' => $info['description'],
					'type' => (new \ReflectionClass($type))->getShortName(),
					'status' => $info['status'],
					'class' => $class
				];

				if (!isset(self::$cached['integrationsByType'][$type])) {
					self::$cached['integrationsByType'][$type] = [
						'title' => $class::TITLE,
						'integrations' => []
					];
				}

				self::$cached['integrationsByType'][$type]['integrations'][] = $info['shortname'];
			}

			self::$cached['integrations'] = collect(self::$cached['integrations']);
		}
	}

	public function get($integration, $command = false, $parameters = [])
	{
		// Temporary Hack until I can think of a better way to do this - arleslie
		if ($integration === 'domain') {
			$integration = $this->getIntegrations()->where('type', 'DomainRegistarIntegration')->first()['shortname'];
		}

		if ($integration === 'cpanel') {
			$integration = $this->getIntegrations()->where('type', 'ControlPanelIntegration')->first()['shortname'];
		}

		if ($integration === 'virtualizor') {
			$integration = $this->getIntegrations()->where('type', 'CloudIntegration')->first()['shortname'];
		}

		if (isset(self::$cached['integrations'][$integration])) {
			// Integration is no longer active.
			if (!in_array($command, self::$setupCommands) && self::$cached['integrations'][$integration]['status'] == false) {
				//return null;
			}

			$class = self::$cached['integrations'][$integration]['class'];
			if (!$command) {
				return new $class;
			} else {
				//dd($class); //\App\Integrations\ImportExport
				//dd($command); //getSetupForm
				//dd($parameters); //[]
				return call_user_func_array("{$class}::{$command}", $parameters);
			}
		}

		throw new IntegrationNotFoundException;
	}

	public function getIntegrations()
	{
		return self::$cached['integrations'];
	}

	public function getAvailableIntegrations()
	{
		return self::$cached['integrations']->where('status', true);
	}

	public function getAvailableIntegrationsCpanel()
	{
		return self::$cached['integrations']->where('status', true);
	}

	public function getAvailableIntegrationsVirtualizor()
	{
		return self::$cached['integrations'];
	}

	public static function getIntegrationsByType()
	{
		return self::$cached['integrationsByType'];
	}
}
