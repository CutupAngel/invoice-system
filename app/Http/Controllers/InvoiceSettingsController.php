<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Currency;
use App\User_Setting;
use App\TaxClasses;

use Auth;
use Permissions;

class InvoiceSettingsController extends Controller
{
	public function __construct()
	{
		if (!Permissions::has('settings')) {
			//throw new Permissions::$exception;
			return redirect(route('login'));
		}
	}

	public function view()
	{
		$paymentOptions = [
			0 => 'Date upon Receipt',
			7 => 'NET 7',
			15 => 'NET 15',
			30 => 'NET 30',
			45 => 'NET 45',
			60 => 'NET 60',
			90 => 'NET 90'
		];

		$paymentTypes = [
			0 => 'Cheque / Check',
			1 => 'Credit Card',
			2 => 'Card Over Phone',
			3 => 'Bank Transfer'
		];

		$creditCards = [
			0 => 'Visa',
			1 => 'Switch',
			2 => 'Visa Delta',
			3 => 'Solo',
			4 => 'Visa Electron',
			5 => 'Maestro',
			6 => 'American Express',
			7 => 'Diner\'s Club',
			8 => 'Mastercard',
			9 => 'Discover',
			10 => 'Other'
		];

		return view('Settings.invoiceSettings', [
			'currencies' => Currency::all(),
			'paymentOptions' => $paymentOptions,
			'paymentTypes' => $paymentTypes,
			'creditCards' => $creditCards,
			'taxclasses' => TaxClasses::all()
		]);
	}

	public function save(Request $request)
	{
		$this->validate($request, [
			'lateFees' => 'numeric|min:0',
			'days2send' => 'numeric|min:0',
			'exchangerate' => 'numeric|min:0',
			'total-order.*' => 'in:fixeddiscount,discountcode,customtotals,shipping,tax'
		]);

		$settings = User_Setting::where('user_id', 1)->where('name', 'LIKE', 'invoice.%')->delete();

		$ignore = [
			'_token'
		];

		$values = [];
		$insertTime = date('Y-m-d H:i:s');

		foreach ($request->all() as $setting => $value) {
			if (in_array($setting, $ignore)) {
				continue;
			}
			if($setting === 'defaultCurrency')
			{
				$values[] = [
					'name' => "site.defaultCurrency",
					'value' => $value,
					'user_id' => Auth::User()->id,
					'created_at' => $insertTime,
					'updated_at' => $insertTime
				];
			}
			elseif (is_array($value)) {
				foreach ($value as $subSetting => $subvalue) {
					$values[] = [
						'name' => "invoice.{$setting}.{$subSetting}",
						'value' => $subvalue,
						'user_id' => Auth::User()->id,
						'created_at' => $insertTime,
						'updated_at' => $insertTime
					];
                }
            } else {
                $values[] = [
					'name' => "invoice.{$setting}",
					'value' => $value,
					'user_id' => Auth::User()->id,
					'created_at' => $insertTime,
					'updated_at' => $insertTime
				];
            }
        }
		foreach ($values as $val){
            $user_setting = User_Setting::where([['user_id',$val['user_id']],['name',$val['name']]])->first();
            if (!empty($user_setting)){
                $user_setting->value = $val['value'];
                $user_setting->updated_at = $val['updated_at'];
                $user_setting->save();
            }else{
                User_Setting::create($val);
            }
        }

		return redirect('/settings/invoice-settings')->with('success', 'Settings saved successfully.');
	}
}
