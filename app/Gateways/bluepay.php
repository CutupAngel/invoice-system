<?php

namespace App\Integrations;

use Settings;

class BluePay extends PaymentGatewayIntegration
{
    const TITLE = 'Bluepay';
    const SHORTNAME = 'bluepay';
    const DESCRIPTION = "";

    private $api;
    private $lastError = '';

    private static $instance;

    public function __construct($userid = false, $password = false, $testmode = false)
    {
        if ($userid === false || $password === false || $testmode === false) {
            $userid = Settings::getAsUser(1, 'enom.userid');
            $password = Settings::getAsUser(1, 'enom.password');
            $testmode = Settings::getAsUser(1, 'enom.testmode');
        }

        //$this->api = new SDK(
        //    $userid,
        //    $password,
        //    $testmode
        //);
    }

    public static function getInfo()
    {
        return [
            'title' => self::TITLE,
            'shortname' => self::SHORTNAME,
            'description' => self::DESCRIPTION,
            'status' => self::checkEnabled()
        ];
    }

    public static function checkEnabled()
    {
        return Settings::getAsUser(1, 'integration.enom') === '1';
    }

    public static function toggle()
    {
		
    }

    public function getError()
    {
        return $this->lastError;
    }
}
