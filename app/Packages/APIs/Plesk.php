<?php

namespace App\Packages\APIs;

use PleskX\Api\Client;
use PleskX\Api\SimpleXMLElement;
use PleskX\Api\XmlResponse;

class Plesk extends Client
{
	/**
     * Perform HTTP request to end-point
     *
     * @param string $request
     * @return XmlResponse
     * @throws Exception
     */
    protected function _performHttpRequest($request)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, "$this->_protocol://$this->_host:$this->_port/enterprise/control/agent.php");
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $this->_getHeaders());
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);

        $result = curl_exec($curl);

        if (false === $result) {
            throw new Client\Exception(curl_error($curl), curl_errno($curl));
        }

        if (self::$_isExecutionsLogEnabled) {
            self::$_executionLog[] = [
                'trace' => debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS),
                'request' => $request,
                'response' => $result,
            ];
        }

        curl_close($curl);

        $xml = new XmlResponse($result);
        $this->_verifyResponse($xml);

        return $xml;
    }
}
