<?php

namespace App\Http\Controllers;

use App\Order_Group;
use App\Currency;
use App\Package;
use App\Package_Cycle;
use App\Package_File;
use App\PackageSetting;
use App\IntegrationVirtualizor;
use App\Integrations\Virtualizor;
use App\Http\Controllers\CommonController;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Integrations;
use Response;
use Storage;

use Cart;

class OrdersCustomerController extends Controller
{
	protected $groupThemes = [
		0 => 'style1-grouplist',
		1 => 'style1-package',
		#1 => 'style2-grouplist',
		#2 => 'style3-grouplist'
	];

	public function getGroup($group)
	{
		//$group = urlencode(ucwords(str_replace('-', ' ', $group)));

		try {
				$group = Order_Group::where('url', $group)->firstorFail();
		}
		catch(ModelNotFoundException $err) {
				return redirect(route('login'));
    }

		$data = [
			'currency' => Controller::setCurrency(),
			'default_currency' => Controller::setDefaultCurrency(),
			'cart' => Controller::formatCartData(),
			'group' => $group,
		];
		if (!$group) {
			$group = Order_Group::find($group);
		}

		$data['currency'] = $this->getCurrency();
		$data['default_currency'] = $this->getDefaultCurrency();
		$data['subTotal'] = Cart::total() ?: 0;

		if ($group->visible == '0') {
			//return abort(404);
			return redirect(route('login'));
		}

		$groupType = $this->groupThemes[0];

		if (isset($this->groupThemes[$group->type])) {
			// $groupType = $this->groupThemes[$group->type];
		}

		return view('Orders.orderViews.' . $groupType, $data);
	}

	public function getPackage($groupId, $packageId)
	{
		$group = Order_Group::where('id', $groupId)
													->where('user_id', self::site('id'))
													->first();

		if (!$group) {
			return redirect(url($groupId));
		}

		$data = [
			'currency' => Controller::setCurrency(),
			'default_currency' => Controller::setDefaultCurrency(),
			'cart' => Controller::formatCartData(),
			'group' => $group,
			'integration' => '',
			'domainIntegration' => ''
		];

		if (!is_numeric($packageId) && $group) {
			$package = $group->packageByUrl($packageId);
		} else {
			$package = $group->package($packageId);

			if (!$package) {
				$package = $group->packageByUrl($packageId);

				if (!$package) {
					//return abort(404);
					return redirect(route('login'));
				}
			}
			$data['currency'] = $this->getCurrency();
			$data['default_currency'] = $this->getDefaultCurrency();

			$bascket = CommonController::factory()->registerBasketInfo();
			$data['basketGrendTotal'] = 0;
			if($bascket)
			{
					$data['basketGrendTotal'] = $bascket['basketGrendTotal'];
			}
		}

		if ($group->visible == '0') {
			//return abort(404);
			return redirect(route('login'));
		}

		if ($package && $package->integration != '')
		{

				//if virtualizor
				$packageSettings = PackageSetting::where('package_id', $package->id)
																						->where('name', 'like', '%virtualizor%')
																						->get();

				if($packageSettings)
				{
						$oses = null;
						foreach($packageSettings as $packageSetting)
						{
								if($packageSetting->name == 'virtualizor.server')
								{
										$integrationVirtualizor = IntegrationVirtualizor::find($packageSetting->value);

										$virtualizor = new Virtualizor(
												$integrationVirtualizor->hostname,
												$integrationVirtualizor->username,
												$integrationVirtualizor->password,
												'pve',
												$integrationVirtualizor->port,
										);

										$oses = $virtualizor->getOsTemplates();
										$data['oses'] = $oses;
								}
						}

						$data['integration'] = Integrations::get($package->integration, 'getOrderForm', [
							$package, $oses
						]);
				}
				else
				{
						$data['integration'] = Integrations::get($package->integration, 'getOrderForm', [
							$package,
						]);
				}
		}
		elseif ($package->domainIntegration)
		{
				$data['domainIntegration'] = Integrations::get('domain', 'getRegistrationForm');
		}
		$groupType = $this->groupThemes[1];

		$data['package'] = $package;
		$data['group'] = $group;

		return view('Orders.orderViews.' . $groupType, $data);
	}

	public function getPackageByUrl($group, $packageUrl)
	{
		$group = urlencode($group);
		$packageUrl = urlencode($packageUrl);

		$group = Order_Group::where('url', $group)
			->where('user_id', self::site('id'))
			->firstOrFail();

		$data = [
			'currency' => Controller::setCurrency(),
			'cart' => Controller::formatCartData(),
			'group' => $group,
			'package' => $group ? $group->packageByUrl($packageUrl) : null,
		];

		return view('Orders.orderViews.style1-package', $data);
	}

	private function getCurrency()
	{
		return Currency::findOrFail($this->currencyBySession());
	}

	private function getDefaultCurrency()
	{
		return Currency::findOrFail($this->currencyDefaultBySession());
	}

	public function currencyBySession()
	{
		if (session()->has('cart.currency')) {
			return session()->get('cart.currency');
		}

		$id = self::site('defaultCurrency');

		if (!$id) {
			$id = 4;
		}

		session()->put('cart.currency', $id);

		return $id;
	}

	public function currencyDefaultBySession()
	{
		$id = self::site('defaultCurrency');

		if (!$id) {
			$id = 4;
		}

		session()->put('cart.default_currency', $id);

		return $id;
	}

	public function getFile($fileId)
	{
		$file = Package_File::findOrFail($fileId);

		if (!$file) {
			return "You do not have file.";
		}

		$fileContents = Storage::disk('minio')
			->get($file->path);

		return Response::make($fileContents, 200, [
			'Content-Type' => $file->mime,
			'Content-Disposition' => 'attachment; filename=' . $file->filename
		]);
	}

	public function displayOrder()
	{
			$orderGroups = Order_Group::where('visible', '1')->get();
			$featuredPackages = Package::where('is_featured', '1')->get();

			$data = [
				'currency' => Controller::setCurrency(),
				'cart' => Controller::formatCartData(),
				'orderGroups' => $orderGroups,
				'featuredPackages' => $featuredPackages
			];

			$data['default_currency'] = $this->getDefaultCurrency();

			return view('Orders.orderViews.index', $data);
	}

	public function getPackageFromGroup($id)
	{
			try {
				$packages = Order_Group::findOrFail($id)->packages;

				return json_encode($packages);
			} catch (\Exception $e) {
				return '';
			}
	}

	public function getPackageFeatured()
	{
			try {
				$packages = Package::join('order_group_package_cycles', 'order_group_packages.id', '=', 'order_group_package_cycles.package_id')
															->where('order_group_packages.is_featured', '1')
															->select('order_group_packages.*', 'order_group_package_cycles.price')
															->get();

				return json_encode($packages);
			} catch (\Exception $e) {
				return '';
			}
	}
}
