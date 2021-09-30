<?php

namespace App\Http\Controllers;

use Cart;
use Response;
use Gloudemans\Shoppingcart\CartCollection;
use Illuminate\Support\Collection;
use Gloudemans\Shoppingcart\CartRowCollection;

use Auth;
use DB;
use Integrations;
use Illuminate\Http\Request;
use App\Order_Group;
use App\Package;
use App\Package_Cycle;
use App\Package_Options;
use App\Package_File;
use App\Package_Option_Values;
use App\Invoice;
use App\Currency;

class CommonController extends Controller
{
		private $user;

		/**
		 * @var array
		 */
		private $optionTypes = [
			'options',
			'inputs',
			'numeric',
			'radio',
			'checkbox',
			'toogle',
		];

		public function __construct()
		{
			$user = Auth::User();
		}

		public static function factory()
		{
			return new self;
		}

		public function dashboard()
		{
			switch (Auth::User()->account_type) {
				case 0: //admin
				case 1: //client
				case 3: //staff
					return view('Common.clientDashboard');
					break;
				case 2: //customer

					return $this->customerDashboard();
					break;
				default:
					return abort(404);
			}
		}

		public function customerDashboard()
		{
			$counts = [];

			$invoices = Invoice::where('customer_id', $this->user->id)
				->get();

			$counts['invoices'] = $invoices->count();
			$counts['overdueInvoices'] = Invoice::where('customer_id', $this->user->id)
				->where('status', Invoice::OVERDUE)
				->count();

			return view('Common.customerDashboard', [
				'count' => $counts,
				'invoices' => $invoices
			]);
		}

		/**
		 * Remove item cart by rowid
		 *
		 * @param $request \Illuminate\Support\Facades\Request
		 * @return \Illuminate\Support\Facades\Response
		 */
		public function removeViewCart(Request $request)
		{
			/**
			 * Remove item cart
			 */
			Cart::remove($request->get('rowid'));

			/*
			 * Responce data view
			 */
			return $this->responceRenderView();
		}

		/**
		 * Remove item cart by rowid
		 *
		 * @param $request \Illuminate\Support\Facades\Request
		 * @return \Illuminate\Support\Facades\Response
		 */
		public function updateBascket(Request $request)
		{
			$action = $request->get('action');
			$rowId = $request->get('rowid');

			if ($action == 'remove') {
				return $this->removeViewCart($request);
			}

	        if ($request->get('qty') <= 0) {

	        	/**
				 * Remove item cart
				 */
	            Cart::remove($rowId);

	            /*
				 * Responce data view
				 */
		        return $this->responceRenderView();
	        }

			/**
			 * Update item cart by rowid
			 */
	        Cart::update($rowId, [
	            'qty' => $request->get('qty'),
	        ]);

	        /*
			 * Responce data view
			 */
	        return $this->responceRenderView();
		}

		public function responceRenderView()
		{
			/**
			 * Get data basket
			 */
			$data = $this->registerBasketInfo();
			/**
			 * Responce view cart items
			 */
			return response()->json([
				'view' => view('Common.cart.cart', $data)->render(),
				'haderbasckettotal' => view('Common.cart.header-total-bascket', $data)->render(),
			]);
		}

		/**
		 * Show view cart page
		 *
		 * @param $request \Illuminate\Support\Facades\Request
		 * @return \Illuminate\View\Factory
		 */
		public function getViewCart(Request $request)
		{
			/**
			 * Get data basket
			 */
			$data = $this->registerBasketInfo();
			$data['cart'] = Controller::formatCartData();

			return view('Common.viewcart', $data);
		}

		public function getNewCurrency($id)
		{
			session()->put('cart.currency', $id);

			return redirect()->back();
		}

		/**
		 * Process add item to basket cart
		 *
		 * @param \Illuminate\Http\Request $request
		 */
		public function postAddToCart(Request $request)
		{
			$this->validate($request, [
				'group' => 'required',
				'package' => 'required',
				'cycle' => 'required',
			]);

			//Check required field
			$options = [];
			foreach ($request->all() as $index => $item) {
				if (in_array($index, $this->optionTypes)) {
					foreach ($item as $key => $text) {
						$options[] = $text;
					}
				}
			}
			$listErrorRequired = [];
			$listFieldCheck = [];
			foreach($options as $key => $value) {
					if(is_array($options[$key])) {
						foreach($options[$key] as $key => $value) {
							$listErrorRequired[$key] = $value;
							$listFieldCheck[] = $key;
						}
					}
			}

			$errorRequired = [];
			if(count($listFieldCheck) > 0)
			{
					$optionValues = Package_Option_Values::whereIn('id', $listFieldCheck)->get();
					foreach ($optionValues as $optionValue) {
						$option = $optionValue->option();
						if($option->required) {
							if($listErrorRequired[$optionValue->id] == '') {
								$errorRequired[] = $optionValue->display_name . ' is required.';
							}
						}
					}
			}
			if(count($errorRequired) > 0) {
				return redirect()->back()->withInput()->withErrors($errorRequired);
			}

			$group = Order_Group::where('id', $request->get('group'))
				->where('user_id', self::site('id'))
				->firstOrFail();

			if (!$group) {
				return null;
			}

			$package = $group->package($request->get('package'));

			if (!$package) {
				return null;
			}

			$cycle = $package->cycle($request->get('cycle'));

			if ($cycle) {
				$data = [
					'group' => $request->get('group'),
					'package' => $request->get('package'),
					'cycle'=> $request->get('cycle'),
				];

				$quantity = 1;

				$cycle_price = $cycle->price;
				$maxDays = date('t');
				$currentDayOfMonth = date('j');

				if($package->prorate)
				{
						//Monthly
						if($cycle->cycle == 5)
						{
								$cycle_price = ($cycle->price / $maxDays) * ($maxDays - $currentDayOfMonth);
						}

						//every 2 months - 11 months
						if($cycle->cycle >= 6 && $cycle->cycle <= 15)
						{
								$months = 0;
								//2 months
								if($cycle->cycle == 6) $months = 2;
								//3 months
								if($cycle->cycle == 7) $months = 3;
								//4 months
								if($cycle->cycle == 8) $months = 4;
								//5 months
								if($cycle->cycle == 9) $months = 5;
								//6 months
								if($cycle->cycle == 10) $months = 6;
								//7 months
								if($cycle->cycle == 11) $months = 7;
								//8 months
								if($cycle->cycle == 12) $months = 8;
								//9 months
								if($cycle->cycle == 13) $months = 9;
								//10 months
								if($cycle->cycle == 14) $months = 10;
								//11 months
								if($cycle->cycle == 15) $months = 11;

								$dateStart = date_create(date("Y") . "-" . date("m") . "-01");
								$dateCurrent = date_create(date("Y") . "-" . date("m") . "-" . date("d"));
								$daysDiff = date_diff($dateStart, $dateCurrent);

								$dateTotal = date_create(date("Y-m-01", strtotime('+' . $months . ' months')));
								$totalDaysDiff = date_diff($dateStart, $dateTotal);
								$currentDaysDiff = date_diff($dateCurrent, $dateTotal);
								$maxDays = $totalDaysDiff->days;
								$currentDays = $currentDaysDiff->days;

								$cycle_price = ($cycle->price / $maxDays) * $currentDays;
						}

						//every 12 months
						if($cycle->cycle == 16)
						{
								$date1Total = date_create(date("Y") . "-01-01");
								$date2Total = date_create(date("Y") . "-12-31");
								$totalDaysYear = date_diff($date1Total, $date2Total);

								$maxDays = $totalDaysYear->days;
								$currentDayOfYear = date('z');
								$cycle_price = ($cycle->price / $maxDays) * ($maxDays - $currentDayOfYear);
						}

						//every 24 months
						if($cycle->cycle == 17)
						{
								$date1Total = date_create(date("Y") . "-01-01");
								$date2Total = date_create(date("Y-12-31", strtotime('+12 months')));
								$totalDaysYear = date_diff($date1Total, $date2Total);

								$maxDays = $totalDaysYear->days;
								$currentDayOfYear = date('z');
								$cycle_price = ($cycle->price / $maxDays) * ($maxDays - $currentDayOfYear);
						}

						//every 36 months
						if($cycle->cycle == 18)
						{
								$date1Total = date_create(date("Y") . "-01-01");
								$date2Total = date_create(date("Y-12-31", strtotime('+24 months')));
								$totalDaysYear = date_diff($date1Total, $date2Total);

								$maxDays = $totalDaysYear->days;
								$currentDayOfYear = date('z');
								$cycle_price = ($cycle->price / $maxDays) * ($maxDays - $currentDayOfYear);
						}
				}

				$cartItem = Cart::add(
		            $package->id,
		            $package->name,
		            $quantity,
		            $cycle_price,
		            [
	                	'fee' => $cycle->fee,
	                	'cycle_id' => $cycle->id,
                        'trial' => $package->trial,
	                	'options' => $this->formedPackageOptions($request)
		            ]
		        );

		    Cart::associate($cartItem->rowId, 'App\Package');

				session()->put('cart.mode', 'packages');
			}

			// Check if this package allows for domain integrations.
			if ($package->domainIntegration) {
				// Process the registration form and get a status.
				$status = Integrations::get('domain', 'processRegistrationForm', [$request]);

				// If the status wasn't true, then there is probably and error which is return with a response.
				// Return the response to the browser so we can show the end user.
				if ($status !== true) {
					return $status;
				}
			}

			if (!empty($package->integration)) {
				Integrations::get($package->integration, 'saveOrderForm', [$request]);
			}

			return redirect('/viewcart/');
		}

		/**
		 * Formed options package basket cart
		 *
		 * @param \Illuminate\Http\Request $request
		 * @return array
		 */
		private function formedPackageOptions(Request $request): array
		{
			$options = [];

			foreach ($request->all() as $index => $item) {
				if (in_array($index, $this->optionTypes)) {
					foreach ($item as $key => $text) {
						$options[$index][$key] = $text;
					}
				}
			}

			$optionValueIsd = $this->getListPackageOptionValues($options);

			if (sizeof($optionValueIsd) > 0) {
				$optionValues = Package_Option_Values::whereIn('id', $optionValueIsd)
														->select('id', 'option_id', 'display_name', 'price', 'fee', 'cycle_type')
														->get()
														->toArray();
				$texts = $this->getTextInOptionValues($options);

				foreach ($optionValues as $key => $value) {
					$optionValues[$key]['value'] = isset($texts[$key]) ? $texts[$key] : '';
				}
			}
			else {
          $optionValues = [];
      }

			return $optionValues;
		}

		private function getListPackageOptionValues($options = [])
		{
			$list = [];
			foreach ($options as $type => $option) {
				if ($type == 'checkbox' || $type == 'toogle') {
					$list[] = $option;
				} else {
					if(!is_array($option)) {
						$list[] = $option;
					}
					else {
						foreach ($option as $id => $value) {
							if(is_array($value)) {
								foreach($value as $index => $val) {
									$list[] = $index;
								}
							}
							else {
								$list[] = $value;
							}
						}
					}
				}
			}
			return $list;//array_unique(array_filter($list));
		}

		private function getTextInOptionValues($options = [])
		{
			$text = [];
			foreach ($options as $type => $option) {
				if ($type != 'checkbox' && $type != 'toogle' && $type != 'radio') {
					if(!is_array($option)) {
						$text[] = $option;
					}
					else {
						foreach ($option as $id => $value) {
							if(is_array($value)) {
								foreach($value as $index => $val) {
									$text[] = $val;
								}
							}
							else {
								$text[] = $value;
							}
						}
					}
				}
			}

			return $text; //array_unique(array_filter($text));
		}

		public function formedOptions($data)
		{
			$options = [];

			foreach($$data as $key => $item) {
				foreach($item as $k => $v) {
					$options[] = [
						'input' => $v,
						'choice' => $item,
						'option' => $k,
					];
				}
			}

			return $options;
		}

    /**
     * Load basket data into the page
     * @return array
     */
    public function registerBasketInfo(): array
    {
        $content = Cart::content();
		
		
		
		
		
        $basket  = new Collection();

				$totalTax = 0;
				$totalTaxTrue = 0;
				$totalTaxBascket = 0;
				$totalTaxBascketTrue = 0;
				
				if(session()->has('tax_amount'))
				{
						$op_tax = session()->get('tax_amount') * $this->getTotalSetupFee($content);
						$totalTax = session()->get('tax_amount');
						$totalTaxTrue = session()->get('tax_amount');

						//dd('tax_amount');

						$totalTaxBascket = $totalTax;
						$totalTaxBascketTrue = $totalTaxTrue;

						session()->put('cart.grandTotal', $this->gettotalBasket($content) + $totalTax);
				}
				else if(session()->has('cart.taxrates'))
				{
						$op_tax = ( session()->get('cart.taxrates') / 100 ) * $this->getTotalSetupFee($content);
						$totalTax = ( session()->get('cart.taxrates') / 100 ) + $op_tax;
						$totalTaxTrue = ( session()->get('cart.taxrates') / 100 );

						//on checkout
						//dd('checkout',($content));

						$totalTaxBascket = $totalTax * $this->gettotalBasket($content);
						$totalTaxBascketTrue = $totalTaxTrue * ($this->gettotalBasket($content,true));
						
						session()->put('cart.grandTotal', $this->gettotalBasket($content) + $totalTaxBascket);
						
				}
				else
				{
						$op_tax = $this->getTotalTax($content) * $this->getTotalSetupFee($content);
						$totalTax = $this->getTotalTax($content) + $op_tax;
						$totalTaxTrue = $this->getTotalTax($content, true);

						//on cart
						
						

						$totalTaxBascket = $totalTax;
						$totalTaxBascketTrue = $totalTaxTrue;

						session()->put('cart.grandTotal', $this->gettotalBasket($content) + $totalTaxBascket);
				}
        session()->put('cart.subTotal', $this->gettotalBasket($content));


        return [
			'debug'=> $totalTax." - ". ($totalTax / 100) . " - " . $this->gettotalBasket($content),
        	'currency' => Controller::setCurrency(),
					'default_currency' => Controller::setDefaultCurrency(),
        	'mode' => session()->get('cart.mode'),
        	'basketItems' => $content,
        	'basketCount' => Cart::count() ?: 0,
        	'basketTotal' => $this->gettotalBasket($content),
        	'basketTotalNormal' => $this->gettotalBasket($content, true),
        	'basketGrendTotal' => $this->gettotalBasket($content) + ($totalTaxBascket),
			'backetGrandTotalAllIn' => $this->gettotalBasket($content),
        	'basketGrendTotalNormal' => $this->gettotalBasket($content, null, true) + $totalTaxBascketTrue,
        	'totalSetupFee' => $this->getTotalSetupFee($content),
        	'totalTax' => $totalTaxBascket ,
        	'totalTaxNormal' => $totalTaxBascketTrue,
        	'taxes' => $this->generateTaxes($content),
        	'taxesNormal' => $this->generateTaxes($content, true),
        ];
    }

    public function generateTaxes($content, $normal = false)
	{
		$taxes = null;
		session()->put('_options', $content);
		
		
		
		foreach ($content as $item)
		{
			# Verify that item's option object has trial value, otherwise, set default value
		  if (!isset($item->options->trial)) {
			  $item->options->trial = 0;
		  }

			if((int)$item->options->trial === 0 || $normal)
			{
			    if (session()->has('cart.mode') && session()->get('cart.mode') === 'invoice')
				{
			    		session()->put('_mode', 'invoice');
			        $package = null;
				}
				else
				{
					$package = Package::find($item->id);
				}

	    		if ($package)
				{
							$tax = $this->getTaxRate($package->getTaxRateAttribute(), $item);


							$taxes[$item->id] = [
								'name' => $package->getTaxRateAttribute(),
								'tax' => $tax,
							];
	    		}
				else
				{
					
	    			session()->put('_mode', 'going here ?');
					$tax = $this->getTaxRate($item->taxRate, $item);

	    		    $taxes[$item->id] = [
	    		        'name' => $item->taxRate,
                        'tax' => $tax
                    ];
				}
			}
		}
		
		
		return $taxes;
	}

	private function getTaxRate($tax, $item)
	{
		$addTax = 0;
		if(($item->options->options))
		{
				foreach($item->options->options as $option)
				{
						$addTax += $option['price'] + $option['fee'];
				}
		}
		$total = ($item->price * $item->qty) + (isset($item->options['fee']) ? ($item->options['fee'] * $item->qty) : 0) + $addTax;

		return $total * ($tax / 100);
	}

    public function getBascketTotal($content = null, $normal = false)
    {
				if($content)
				{
						$subTotal = 0;
						foreach ($content as $item)
						{
								if($item->options->trial == 0 || $normal)
								{
										$subTotal += ($item->price * $item->qty) + (isset($item->options['fee']) ? ($item->options['fee'] * $item->qty) : 0);
										
										if(isset($item->options->options))
										{
												foreach($item->options->options as $option)
												{
														$packageOption = Package_Options::where('id', $option['option_id'])
																										->first();

														if($packageOption->type == 2) {
																$subTotal += ($option['price'] * (int)$option['value']) + $option['fee'];
														}
														else {
																$subTotal += $option['price'] + $option['fee'];
														}
												}
										}
								}

						}
						return $subTotal;
				}
    	  return str_replace([' ', ','], '', Cart::subtotal()) ?: 0;
    }

    public function getTotalTax($content, $normal = false)
    {
	    	$taxes = $this->generateTaxes($content, $normal);

	    	if (is_null($taxes) || sizeof($taxes) == 0) {
	    		return 0;
	    	}

	    	$total = array_column($taxes, 'tax');

	    	return array_sum($total);
    }

    public function getTotalSetupFee($content)
    {
    	return $this->gettotalBasket($content, 'setupfee');
    }

    public function gettotalBasket($content, $action = null, $normal = false)
    {
    	$total = 0;

    	foreach ($content as $item)
			{
    		if ($action == null && ($item->options->trial == 0 || $normal))
				{
					if (session()->has('cart.mode') && session()->get('cart.mode') === 'invoice')
					{
						$invoices = Invoice::where(['customer_id'=>Auth::User()->id])->first();
						if($invoices){
							$total += $invoices->total;
						}else{
							$total += ($item->price * $item->qty) + (isset($item->options['fee']) ? ($item->options['fee'] * $item->qty) : 0);
						}
					}
					else
					{
						$total += ($item->price * $item->qty) + (isset($item->options['fee']) ? ($item->options['fee'] * $item->qty) : 0);
					}
					
					if($item->options->options)
					{
						foreach($item->options->options as $option)
						{
								$packageOption = Package_Options::where('id', $option['option_id'])
																								->first();


								if($packageOption->type == 2) {
										$total += ($option['price'] * (int)$option['value']) + $option['fee'];
								}
								else {
										$total += $option['price'] + $option['fee'];
								}
						}
					}
    		}

    		if ($action == 'setupfee' && $item->options->trial == 0)
				{
    			$total += isset($item->options['fee']) ? $item->options['fee'] * $item->qty : 0;
    		}

    	}

        return $total;
    }

    public function checkRemoved(CartCollection $items)
    {
        foreach ($items as $item) {
            $package = Package::find($item->id);

            if ($package) {
                continue;
            }

            Cart::remove($item->rowid);
        }
    }

    /**
     * Create a collection from a basket row that merges model data
     * @param   CartRowCollection    $item
     * @return  Collection
     */
    protected function makeBasketRow($item)
    {
        $package = Package::find($item->id);

        // $row = new collectionn($item);

        if ($package && isset($item->options['fee'])) {
	        $item->put('setupfeeTotal', $item->options['fee'] > 0 ? $item->options['fee'] : 0);
	    	}

        return $item;
    }

		/**
		 * Show fraud page
		 *
		 * @param $request \Illuminate\Support\Facades\Request
		 * @return \Illuminate\View\Factory
		 */
		public function getFraud(Request $request)
		{
			/**
			 * Get data basket
			 */
			$data = $this->registerBasketInfo();
			$data['cart'] = Controller::formatCartData();
			$data['fraudlabs_result'] = session()->get('fraudlabs.response');
			$data['fraudlabs_status'] = session()->get('fraudlabs.status');

			return view('Common.fraud', $data);
		}

}
