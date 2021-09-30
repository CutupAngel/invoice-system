<?php

namespace App\Http\Controllers;

use Auth;
use Illuminate\Http\Request;

use App\Discount;

class CodesController extends DiscountsController
{
		public function __construct()
		{
			$this->type = Discount::CODE;
			parent::__construct();
		}
}
