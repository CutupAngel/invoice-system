<?php

namespace App\Http\Controllers;

use App\Address;
use App\TaxRates;
use App\Discount;
use App\User;
use App\Currency;
use App\Package;
use App\Package_Options;
use App\Package_Option_Values;
use App\Invoice;
use Session;
use Settings;

use DB;
use Cart;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    private static $currentSite = [];
    private static $siteModal;
    private static $defaultCurrency;
    private static $currency;
    private static $mode;
    private static $invoice;
    static $tax_exempt = 0;

    public static function site($var)
    {
		
        if (empty(self::$currentSite)) {
            $settings = self::siteModal()->settings()->where('name', 'LIKE', 'site.%')->get()->pluck('value', 'name')->all();

            foreach ($settings as $name => $value) {
                self::$currentSite[str_replace('site.', '', $name)] = $value;
            }

            self::$currentSite['id'] = self::siteModal()->id;
            self::$currentSite['modal'] = Config('app.site');
        }

        if (!empty(self::$currentSite[$var])) {
            return self::$currentSite[$var];
        }

        return false;
    }

    public static function siteModal()
    {
        if (empty(self::$siteModal)) {
            self::$siteModal = User::findOrFail(1);
        }

        return self::$siteModal;
    }

	public static function setInvoiceMode($id)
	{
		self::$mode = 'invoice';
		self::$invoice = $id;
	}

	public static function formatCartData()
	{
		$cart = [
			'cycles'=>[],
			'items'=>[],
			'totals'=>[],
			'subTotal'=>0,
			'proratedToRemove'=>0,
			'formattedSubTotal'=>'',
			'termTotals'=>[],
			'taxables'=>[],
			'discountCodeAmount'=>0,
			'formattedDiscountCodeAmount'=>0,
			'tax'=>0,
			'formattedTax'=>'',
			'taxDiscounted'=>0,
			'formattedTaxDiscounted'=>'',
			'grandTotal'=>0,
			'formattedGrandTotal'=>'',
			'grandTotalDiscounted'=>0,
			'formattedGrandTotalDiscounted'=>'',
			'availableCurrencies'=>Currency::whereIn('id',Settings::get('invoice.currency',[3,4,5]))->get()
		];

		if(session()->has('cart.mode'))
		{
			$mode = session()->get('cart.mode');
			if($mode === 'invoice')
			{
				$invoice_id = session()->get('cart.inv');
			}
		}
		elseif(!empty(self::$mode))
		{
			$mode = self::$mode;
			$invoice_id = self::$invoice;
		}
		if(!empty($mode))
		{
			if(session()->has('cart.county_id'))
			{
				$county_id = session()->get('cart.county_id');
			}
			$totalingOrder = Settings::get('invoice.total-order',['fixeddiscount','discountcode','customtotals','shipping','tax']);
			if($mode == 'packages')
			{
				$cartProductsDict = [];
                $cartProducts = collect(session()->get('cart.products'));
				foreach($cartProducts as $k=>$v)
				{
					$cartProductsDict[$v['package']] = $v;
				}
                $products = Package::with('link.option.values')->whereIn('id', $cartProducts->pluck('package'))->get();
			}
			elseif($mode == 'invoice')
			{
				$invoice = Invoice::findOrFail($invoice_id);
				if(!isset($county_id))
				{
					$county_id = Address::findOrFail($invoice->address_id)->county_id;
				}
				$products = $invoice->items;

				foreach($invoice->totals as $k=>$v)
				{
					$cart['totals'][] = [
						'desc'=>$v->item,
						'amount'=>$v->price,
						'formattedAmount'=>self::formatPriceWithCurrency($v->price),
						'taxclass'=>$v->tax_class
					];
				}
			}

			$arrRates = [];
			$currency = self::setCurrency();
			if(isset($county_id))
			{
				$arrRates = TaxRates::join('taxZones','taxRates.zone_id','=','taxZones.id')
					->join('taxZoneCounties','taxZoneCounties.zone_id','=','taxZones.id')
  				->join('taxClasses','taxRates.class_id','=','taxClasses.id')
					->where('taxZones.user_id','=',self::site('id'))
					->where('taxZoneCounties.county_id','=',$county_id)
          //->where('taxClasses.default', '1')
          ->get();
			}
			$rates = [];
      $rateVal = 0;
			foreach($arrRates as $k=>$v)
			{
				$rates[$v->class_id] = $v->rate;
        $rateVal = $v->rate;
			}

      $content = Cart::content();
      foreach($content as $contentItem)
      {
          Cart::setTax($contentItem->rowId, $rateVal);
      }

			foreach($products as $k=>$v)
			{
				if($mode == 'packages')
				{

					$product = $cartProductsDict[$v->id];

					$options = [];

					if (isset($product['domain'])) {
						$amount = $product['price'];
						$name = 'Domain Registration for '. $product['domain'];
					} else {
						$name = $v->name;
						$cart['cycles'][$v->id] = $product['cycle'];
						$cycle = $product['cycle'];
						$amount = $v->cycle($product['cycle'])->price;

						if (isset($cart['termTotals'][$v->cycle($product['cycle'])->cycle()])) {
							$cart['termTotals'][$v->cycle($product['cycle'])->cycle()] += $amount;
						} else {
							$cart['termTotals'][$v->cycle($product['cycle'])->cycle()] = $amount;
						}
						$optionTotal = 0;
						foreach ($product['options'] as $k2=>$sessionOption)
						{
							$cost = 0;
							//set current option
							$option = null;
							foreach($v->link as $k3=>$v3)
							{
								if($v3->option->id == $sessionOption['option'])
								{
									$option = $v3->option;
								}
							}
							if(!empty($option))
							{
								switch($option->type)
								{
									case 1:
										//text
										$renewal = 0;
										$cost = 0;
										foreach($option->values as $k3=>$optionValue)
										{
											//foreach($v2 as $k4=>$sessionOption)
											//{
												if($sessionOption['choice'] == $optionValue->id)
												{
													$renewal = $optionValue->price;
													$options[] = [
														'name'=>$optionValue->display_name,
														'value'=>$sessionOption['input'],
														'type'=>$option->type,
														'desc'=>$sessionOption['input'],
														'setup'=>$cost,
														'renewal'=>$optionValue->price,
														'cycle'=>$optionValue->cycle_type
													];
												}
											//}
										}

									break;
									case 2:
										//number
										$renewal = 0;
										$cost = 0;
										foreach($option->values as $k3=>$optionValue)
										{
											//foreach($v2 as $k4=>$sessionOption)
											//{
												if($sessionOption['choice'] == $optionValue->id)
												{
													$renewal = $optionValue->price;
													$cost = $v3->fee;
													$options[] = [
														'name'=>$optionValue->display_name,
														'value'=>$sessionOption['input'],
														'type'=>$option->type,
														'desc'=>$sessionOption['input'],
														'setup'=>$cost,
														'renewal'=>$optionValue->price,
														'cycle'=>$optionValue->cycle_type
													];
												}
											//}
										}
									break;
									case 4:
										//checkbox
										$renewal = 0;
										$cost = 0;

									break;
									case 5:
										//toggle
										$renewal = 0;
										$cost = 0;

									break;
									case 0:
										//select
										$renewal = 0;
										$cost = 0;
										foreach($option->values as $k3=>$optionValue)
										{


											//foreach($v2 as $k4=>$sessionOption)
											//{
												if($sessionOption['choice'] == $optionValue->id)
												{
													$renewal = $optionValue->price;
													$cost = $optionValue->fee;
													$options[] = [
														'name'=>$option->display_name,
														'value'=>$sessionOption['input'],
														'type'=>$option->type,
														'desc'=>$optionValue->display_name,
														'setup'=>$cost,
														'renewal'=>$optionValue->price,
														'cycle'=>$optionValue->cycle_type
													];
												}
											//}
										}
									break;
									case 3:
										//radio
										$renewal = 0;
										$cost = 0;
										foreach($option->values as $k3=>$optionValue)
										{
											//foreach($v2 as $k4=>$sessionOption)
											//{
												if($sessionOption['choice'] == $optionValue->id)
												{
													$renewal = $optionValue->price;
													$cost = $optionValue->fee;
													$options[] = [
														'name'=>$option->display_name,
														'value'=>$sessionOption['input'],
														'type'=>$option->type,
														'desc'=>$optionValue->display_name,
														'setup'=>$cost,
														'renewal'=>$renewal,
														'cycle'=>$optionValue->cycle_type
													];
												}
											//}
										}
									break;
								}
								if (isset($cart['termTotals'][$optionValue->cycle()])) {
									$cart['termTotals'][$optionValue->cycle()] += $renewal;
								} else {
									$cart['termTotals'][$optionValue->cycle()] = $renewal;
								}
							}
							$optionTotal = $optionTotal + $cost;
						}
					}
					$id = $v->id;
					$packageId = $v->id;
					$qty = 1;
					$taxclass = $v->tax;
				}
				elseif($mode == 'invoice')
				{
					$name = ($v->quantity > 1 ? $v->description . " x " . $v->quantity : $v->description);
					$amount = $v->price;
					$id = $v->id;
					$packageId = null;
					$cycle = null;
					$options = null;
					$optionTotal = 0;
					$qty = $v->quantity;
					$taxclass = $v->tax_class;

					if ($v->tax_class && self::$tax_exempt < 1) {
						if (isset($cart['taxables'][$v->tax_class])) {
							$cart['taxables'][$v->tax_class] += $amount;
						} else {
							$cart['taxables'][$v->tax_class] = $amount;
						}
					}
				}

				$cart['items'][] = [
					'desc' => $name,
					'amount' => $amount,
					'subTotal' => $amount * $qty,
					'formattedPrice'=>self::formatPriceWithCurrency($optionTotal + $amount),//item price
					'formattedAmount' => self::formatPriceWithCurrency(($optionTotal + $amount) * $qty),//item subtotal
					'id' => $id,
					'cycle_id' => $cycle,
					'options' => $options,
					'package_id' => $packageId,
					'qty'=>$qty,
					'tax_class'=>$taxclass
				];
			}

			foreach($cart['termTotals'] as $k=>$v)
			{
				$cart['termTotals'][$k] = [
					'amount'=>$v,
					'formattedAmount'=>self::formatPriceWithCurrency($v)
				];
			}

			foreach($cart['items'] as $k=>$v)
			{
				$cart['subTotal'] += $v['subTotal'];
				// if (isset(($v['options'])) {
				// 	foreach($v['options'] as $k2=>$v2)
				// 	{
				// 		$cart['subTotal'] += $v2['setup'];
				// 	}
				// }
			}


			$runningTotal = $cart['subTotal'];
			$runningTotalWithOutDiscounts = $cart['subTotal'];
			$taxTotalWithOutDiscounts = 0;
			$totalDiscounts = 0;
			foreach($totalingOrder as $k=>$v)
			{
				switch($v)
				{
					case 'fixeddiscount':
						$fixedDiscount = Discount::where('user_id', Controller::site('id'))->where('type', Discount::FIXED)->where('value','<=',$runningTotal)->where('start','<=',strtotime(date('Y-m-d h:m:s')))->where(function($query){
							$query->where('end','0000-00-00')
							->orWhere('end','>=',strtotime(date('Y-m-d h:m:s')));
						})->first();
						if(!empty($fixedDiscount))
						{
							$foundTax = -1;
							foreach($totalingOrder as $k2=>$v2)
							{
								if($v2 == 'tax')
								{
									$foundTax = $k2;
								}
							}

							if($foundTax > -1 && $foundTax > $k)
							{
								//discount is being calculated before tax
								$largestTaxRate = null;
								$largestTaxRateId = null;
								foreach($rates as $k2=>$v2)
								{
									if(empty($largestTaxRate))
									{
										$largestTaxRate = $v2;
										$largestTaxRateId = $k2;
									}
									elseif($largestTaxRate < $v2)
									{
										$largestTaxRate = $v2;
										$largestTaxRateId = $k2;
									}
								}
								if(empty($largestTaxRateId))
								{
									//no tax rates
									$runningTotal = $runningTotal - ($fixedDiscount->discount / 100 * $runningTotal);
								}
								elseif(empty($cart['taxables']))
								{
									$cart['taxables'] = [];
									$cart['taxables'][$largestTaxRateId] = 0 - ($fixedDiscount->discount / 100 * $runningTotal);
								}
								elseif(empty($cart['taxables'][$largestTaxRateId]))
								{
									$cart['taxables'][$largestTaxRateId] = 0 - ($fixedDiscount->discount / 100 * $runningTotal);
								}
								else
								{
									$runningTotal = $runningTotal - $fixedDiscount->discount;
									$cart['taxables'][$largestTaxRateId] = $cart['taxables'][$largestTaxRateId] - ($fixedDiscount->discount / 100 * $runningTotal);
								}
							}
							elseif($foundTax > -1 && $foundTax < $k)
							{
								//discount is being calculated after tax
								$runningTotal = $runningTotal - ($fixedDiscount->discount / 100 * $runningTotal);
							}
							$cart['fixedDiscountAmount'] = $fixedDiscount->discount / 100 * $runningTotal;
							$totalDiscounts = $totalDiscounts + ($fixedDiscount->discount / 100 * $runningTotal);
						}
					break;
					case 'discountcode':
						if(session()->has('cart.discountCodePercent'))
						{
							$cart['discountCodeAmount'] = session()->get('cart.discountCodePercent') / 100 * $runningTotal;

							$foundTax = -1;
							foreach($totalingOrder as $k2=>$v2)
							{
								if($v2 == 'tax')
								{
									$foundTax = $k2;
								}
							}

							if($foundTax > -1 && $foundTax > $k)
							{
								//discountcode is being calculated before tax
								$largestTaxRate = null;
								$largestTaxRateId = null;
								foreach($rates as $k2=>$v2)
								{
									if(empty($largestTaxRate))
									{
										$largestTaxRate = $v2;
										$largestTaxRateId = $k2;
									}
									elseif($largestTaxRate < $v2)
									{
										$largestTaxRate = $v2;
										$largestTaxRateId = $k2;
									}
								}
								if(empty($largestTaxRateId))
								{
									//no tax rates
									$runningTotal = $runningTotal - $cart['discountCodeAmount'];
								}
								elseif(empty($cart['taxables']))
								{
									$cart['taxables'] = [];
									$cart['taxables'][$largestTaxRateId] = 0 - $cart['discountCodeAmount'];
								}
								elseif(empty($cart['taxables'][$largestTaxRateId]))
								{
									$cart['taxables'][$largestTaxRateId] = 0 - $cart['discountCodeAmount'];
								}
								else
								{
									$cart['taxables'][$largestTaxRateId] = $cart['taxables'][$largestTaxRateId] - $cart['discountCodeAmount'];
									$runningTotal = $runningTotal - $cart['discountCodeAmount'];
								}
							}
							elseif($foundTax > -1 && $foundTax < $k)
							{
								//discountcode is being calculated after tax
								$runningTotal = $runningTotal - $cart['discountCodeAmount'];
							}
							$totalDiscounts = $totalDiscounts + $cart['discountCodeAmount'];
						}
					break;
					case 'customtotals':
						if(isset($invoice))
						{
							foreach($cart['totals'] as $k2=>$v2)
							{
								$runningTotal += $v2['amount'];
								$runningTotalWithOutDiscounts += $v2['amount'];
							}
						}
					break;
					case 'shipping':

					break;
					case 'tax':
						$taxWithDiscount = 0;
						if($mode == 'invoice')
						{
							$taxWithDiscount = $invoice->tax;
						}
						else
						{
							if (session()->has('cart.county_id') && session()->has('cart.taxrates')) {
								foreach($cart['items'] as $k2=>$v2)
								{
									if ($v2['tax_class'] && self::$tax_exempt == 0) {
										if (isset($cart['taxables'][$v2['tax_class']])) {
											$cart['taxables'][$v2['tax_class']] += $v2['subTotal'];
										} else {
											$cart['taxables'][$v2['tax_class']] = $v2['subTotal'];
										}
									}
								}
								foreach($cart['totals'] as $k2=>$v2)
								{
									if ($v2['tax_class'] && self::$tax_exempt < 1) {
										if (isset($cart['taxables'][$v2['tax_class']])) {
											$cart['taxables'][$v2['tax_class']] += $v2['amount'];
										} else {
											$cart['taxables'][$v2['tax_class']] = $v2['amount'];
										}
									}
								}
								foreach($cart['taxables'] as $k2=>$v2)
								{

										if(isset($rates[$k2]))
										{
											$taxWithDiscount += $rates[$k2] * $v2 / 100;
										}
								}
							}
						}
						$runningTotal += $taxWithDiscount;
						$runningTotalWithOutDiscounts += $taxWithDiscount;
						$taxTotalWithOutDiscounts = 0;
						if((isset($cart['discountCodeAmount']) && $cart['discountCodeAmount'] > 0)|| (isset($cart['fixedDiscountAmount']) && $cart['fixedDiscountAmount'] > 0))
						{
							if (session()->has('cart.county_id') && session()->has('cart.taxrates')) {
								$discountAmount = $cart['discountCodeAmount'] + $cart['fixedDiscountAmount'];
								$tempTaxables = [];
								foreach($cart['items'] as $k2=>$v2)
								{
									if ($v2['tax_class'] && self::$tax_exempt < 1) {
										if (isset($tempTaxables[$v2['tax_class']])) {
											$tempTaxables[$v2['tax_class']] += $v2['subTotal'];
										} else {
											$tempTaxables[$v2['tax_class']] = $v2['subTotal'];
										}
									}
								}
								foreach($cart['totals'] as $k2=>$v2)
								{
									if ($v2['tax_class'] && self::$tax_exempt < 1) {
										if (isset($tempTaxables[$v2['tax_class']])) {
											$tempTaxables[$v2['tax_class']] += $v2['amount'];
										} else {
											$tempTaxables[$v2['tax_class']] = $v2['amount'];
										}
									}
								}
								foreach($tempTaxables as $k2=>$v2)
								{
									if(isset($rates[$v2->tax]))
									{
										$taxTotalWithOutDiscounts += $rates[$k2] * $v2 / 100;
									}
								}
							}
						}
						else
						{
							$taxTotalWithOutDiscounts = $taxWithDiscount;
						}
					break;
				}
			}

			$cart['formattedSubTotal']=self::formatPriceWithCurrency($cart['subTotal']);
			$cart['formattedDiscountCodeAmount']=self::formatPriceWithCurrency((isset($cart['discountCodeAmount']) ? $cart['discountCodeAmount'] : 0));
			$cart['formattedFixedDiscountAmount']=self::formatPriceWithCurrency((isset($cart['fixedDiscountAmount']) ? $cart['fixedDiscountAmount'] : 0));
			$cart['tax']= $taxTotalWithOutDiscounts;
			$cart['formattedTax']=self::formatPriceWithCurrency($cart['tax']);
			$cart['taxDiscounted']= $taxWithDiscount;
			$cart['formattedTaxDiscounted']= self::formatPriceWithCurrency((isset($cart['taxWithDiscount']) ? $cart['taxWithDiscount'] : 0));
			$cart['grandTotal']=$runningTotalWithOutDiscounts;
			$cart['formattedGrandTotal']=self::formatPriceWithCurrency($runningTotalWithOutDiscounts);
			$cart['grandTotalDiscounted']=$runningTotal;
			$cart['formattedGrandTotalDiscounted']=self::formatPriceWithCurrency($runningTotal);
			$cart['totalDiscounts']=$totalDiscounts;
			$cart['formattedTotalDiscounts']=self::formatPriceWithCurrency($totalDiscounts);
		}
		session()->put('totals',$cart);

		return $cart;
	}

	public static function formatPriceWithCurrency($amount)
	{
		if(!is_object(self::$defaultCurrency))
		{
			self::setCurrency();
		}
		$amount = number_format($amount,2);
		$ret = self::$defaultCurrency->symbol.$amount;
		if(!empty(self::$currency))
		{
			if(self::$currency->id !== self::$defaultCurrency->id)
			{
				$ret = $ret.' ('.self::$currency->symbol.self::convertToCurrency($amount,self::$defaultCurrency,self::$currency).')';
			}
		}
		return $ret;
	}
	public static function convertToCurrency($amount, $oldCurrency, $newCurrency)
	{
		$old = self::getCurrencyByType($oldCurrency);
		$new = self::getCurrencyByType($newCurrency);

		$feePercent = Settings::get('invoice.exchangerate','0');
		$total = $amount / $old->conversion * $new->conversion;

		if ($feePercent > 0) {
			$total = $total + ($total * $feePercent / 100);
		}

		return round($total * 100) / 100;
	}

	public static function getCurrencyByType($currency)
	{
		if (is_numeric($currency)) {
			return Currency::findOrFail($currency);
		}

		return $currency;
	}

	public static function setCurrency()
	{
		if (empty(self::$defaultCurrency)) {
			$currency = Currency::find(self::site('defaultCurrency'));

			if (empty($currency)) {
				$currency = Currency::findOrFail(4);
			}

			self::$defaultCurrency = $currency;
		}

		if (empty(self::$currency) && session()->has('cart.currency') && session()->get('cart.currency') != self::site('defaultCurrency')) {
			self::$currency = Currency::findOrFail(session()->get('cart.currency'));
		}

		if (!empty(self::$currency)) {
			return self::$currency;
		}

		return self::$defaultCurrency;
	}

  public static function setDefaultCurrency()
	{
		if (empty(self::$defaultCurrency)) {
			$currency = Currency::find(self::site('defaultCurrency'));

			if (empty($currency)) {
				$currency = Currency::findOrFail(4);
			}

			self::$defaultCurrency = $currency;
		}

		return self::$defaultCurrency;
	}
}
