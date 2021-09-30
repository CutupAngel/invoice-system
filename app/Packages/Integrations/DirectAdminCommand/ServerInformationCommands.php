<?php

namespace App\Packages\Integrations\DirectAdminCommand;

trait ServerInformationCommands
{
    public function serverStats()
    {
        return $this->parse($this->guzzle->get('/CMD_API_LOGIN_TEST'));
    }
}
