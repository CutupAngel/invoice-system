<?php

namespace App\PaymentGateways;

use Settings;

abstract class PaymentGatewayIntegration
{
	abstract public static function getInfo();

	abstract public static function checkEnabled();

	abstract public static function toggle();

	abstract public function getError();
}
