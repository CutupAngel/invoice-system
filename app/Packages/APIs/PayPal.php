<?php

namespace App\Packages\APIs;

use \PayPal\Rest\ApiContext;
use \PayPal\Auth\OAuthTokenCredential;
use \PayPal\Api\CreditCard;
use \PayPal\Api\OpenIdUserinfo;
use \PayPal\Api\OpenIdTokenInfo;

class PayPal
{
	private $ApiContext;
	private $accessToken;

	public function __construct($clientId, $clientSecret, $sandbox = false)
	{
		$oauth = new OAuthTokenCredential(
			$clientId,
			$clientSecret
		);

		$this->ApiContext = new ApiContext($oauth);

		if (!$sandbox) {
			$this->ApiContext->setConfig(['mode' => 'live']);
		}

		$this->accessToken = $oauth->getAccessToken($this->ApiContext->getConfig());
	}

	public function creditCard($type, $number, $expireMonth, $expireYear, $ccv2, $firstname, $lastname)
	{
		$creditCard = new CreditCard([
			'type' => $type,
			'number' => $number,
			'expire_month' => $expireMonth,
			'expire_year' => $expireYear,
			'ccv2' => $ccv2,
			'first_name' => $firstname,
			'last_name' => $lastname
		]);

		$creditCard->create($this->ApiContext);

		return $creditCard;
	}

	public function userInfo()
	{
		$userInfo = new OpenIdUserinfo();
		return $userInfo->getUserinfo([
			'access_token' => $this->accessToken
		], $this->ApiContext);
	}
}
