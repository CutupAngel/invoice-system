<?php

namespace App\Exceptions;

class SubscriptionException extends \Exception
{
	const INVOICELIMIT = 1;
	const CLIENTLIMIT = 2;
	const STAFFLIMIT = 3;
}