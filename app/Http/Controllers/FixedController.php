<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

use App\Discount;

class FixedController extends DiscountsController
{
		public function __construct()
		{
			$this->type = Discount::FIXED;
			parent::__construct();
		}
}
