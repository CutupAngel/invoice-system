<?php

namespace App\Http\Controllers;

use App\Currency;
use App\Plan;
use App\Plan_Cycle;
use Illuminate\Http\Request;

use Response;
use Storage;

class PlansClientController extends Controller
{
	protected $groupThemes = [
		0 => 'style1-grouplist',
		1 => 'style2-grouplist',
		2 => 'style3-grouplist'
	];


	public function getPlan($packageId)
	{
		$data = ['currency'=>Controller::setCurrency()];
		$data['cart'] = Controller::formatCartData();
		if(!is_numeric($packageId))
		{
			$data['package'] = $data['group']->packageByUrl($packageId);
		}
		else
		{
			$data['group'] = Order_Group::where('url', $group)->where('user_id', self::site('id'))->firstOrFail();
			$data['package'] = $data['group']->package($packageId);
			if(empty($data['package']) || !is_object($data['package']))
			{
				$data['package'] = $data['group']->packageByUrl($packageId);
				if(empty($data['package']) || !is_object($data['package']))
				{
					abort(404);
				}
			}
			$data['currency'] = $this->getCurrency();
			$data['subTotal'] = session()->get('cart.subTotal');
		}
		return view('Plans.orderViews.style1-package', $data);
	}

	public function getPackageByUrl($group, $packageUrl)
	{
		$data = ['currency'=>Controller::setCurrency()];
		$data['cart'] = Controller::formatCartData();
		$data['group'] = Order_Group::where('url', $group)->where('user_id', self::site('id'))->firstOrFail();
		$data['package'] = $data['group']->packageByUrl($packageUrl);
		return view('Plans.orderViews.style1-package', $data);
	}

	private function getCurrency()
	{
		if(session()->has('cart.currency'))
		{
			$id = session()->get('cart.currency');
		}
		else
		{
			$id = self::site('defaultCurrency');
			if(!$id)
			{
				$id = 4;
			}
			session()->put('cart.currency',$id);
		}
		return Currency::findOrFail($id);
	}
}
