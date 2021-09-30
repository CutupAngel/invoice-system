<?php

namespace App\Packages\Integrations\DirectAdminCommand;

trait IPCommands
{
    public function getIPs($ip = false)
    {
        return $this->parse($this->guzzle->post('/CMD_API_SHOW_RESELLER_IPS', ['form_params' => [
            'ip' => $ip
        ]]));
    }
}
