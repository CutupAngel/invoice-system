<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Packages\Integrations\IntegrationNotFoundException;
use App\Integrations\DirectAdmin;

use App\Integration;
use App\IntegrationCpanel;
use App\IntegrationVirtualizor;
use App\Order_Options;
use App\Order_Settings;
use Gufy\CpanelPhp\Cpanel as CpanelSdk;
use Integrations;
use Route;
use Settings;
use Permissions;
use Response;

class IntegrationController extends Controller
{
	public function __construct()
	{
		if (!Permissions::has('settings')) {
			throw new Permissions::$exception;
		}
	}

	public function get($integration = '')
	{
		Route::current()->setUri('settings/integrations');

		try {
			return Integrations::get($integration, 'getSetupForm');
		} catch (IntegrationNotFoundException $e) {
			return $this->getIntegrations();
		}
	}

	public function post(Request $request, $integration = '')
	{
		try {
			return Integrations::get($integration, 'setup', [$request]);
		} catch (IntegrationNotFoundException $e) {
			return abort(404);
		}
	}

    /**
     * @param Request $request
     * @param string $integrationimportCustomersStep1
     * @param $id
     */
	public function update(Request $request, $integration = '', $id)
    {
        try {
            $request->merge(compact('id'));
            return Integrations::get($integration, 'setup', [$request]);
        } catch (IntegrationNotFoundException $e) {
            return abort(404);
        }
    }

	public function importCustomers(Request $request, $integration = '')
	{
		try {
			return Integrations::get($integration, 'import_customers', [$request]);
		} catch (IntegrationNotFoundException $e) {
			return abort(404);
		}
	}

	public function toggle(Request $request)
	{
		$this->validate($request, ['integration' => 'required']);

		$integration = $request->input('integration');

		return Integrations::get($integration, 'toggle');
	}

	public function getIntegrations()
	{
		return view('Settings.integrationsListing', [
			'types' => Integrations::getIntegrationsByType(),
			'integrations' => Integrations::getIntegrations()
		]);
	}

	public function importCustomersResult(Request $request, $id)
	{
		$integration = Integration::find($id);

		$port = ($integration->port != '') ? $integration->port : 2222;

		$host = ($integration->https == 1) ? 'https://' : 'http://';
		$host .= $integration->hostname . ':' . $port;

		$directadmin = new DirectAdmin(
				$host,
				$integration->username,
				$integration->password
		);

		try {
				$accounts = $directadmin->listResellerAccounts();
		}
		catch (\Exception $e) {
				$accounts = $directadmin->listUserAccounts();
		}

		$accounts = $accounts['list'];
		$importedList = [];
		$x = 0;
		foreach($accounts as $account)
		{
				$orderOption = Order_Options::where('value', $account)->first();
				$orderOptions = Order_Options::where('order_id', $orderOption->order_id)
																				->orderBy('id', 'asc')
																				->get();
				$y = 0;
				foreach($orderOptions as $orderOption)
				{
						if($y == 0) $importedList[$x]['username'] = $orderOption->value;
						if($y == 1) $importedList[$x]['domain'] = $orderOption->value;
						if($y == 2)
						{
							$importedList[$x]['ip'] = $orderOption->value;
						}
						if($y == 3)
						{
							$importedList[$x]['date_created'] = $orderOption->value;
							$importedList[$x]['package'] = $orderOption->order->package->name;
						}
						$y++;
				}
				$x++;
		}

		return view('Integrations.directadminImport', ['accounts' => $importedList]);
	}

	public function importCustomersStep1(Request $request, $integration_type, $id)
	{
			if($integration_type == 'directadmin')
			{
					$integration = Integration::find($id);

					$port = ($integration->port != '') ? $integration->port : 2222;

					$host = ($integration->https == 1) ? 'https://' : 'http://';
					$host .= $integration->hostname . ':' . $port;

					$directadmin = new DirectAdmin(
							$host,
							$integration->username,
							$integration->password
					);

					try {
							$accounts = $directadmin->listResellerAccounts();
					}
					catch (\Exception $e) {
							$accounts = $directadmin->listUserAccounts();
					}

					if(!array_key_exists('list', $accounts))
					{
						return Response::json([
							'success' => false,
							'errors' => $integration,
							'account' => $accounts
						]);
					}

					$accountDetails = [];
					$x = 0;
					foreach($accounts['list'] as $username)
					{
							$response = $directadmin->getUserConfig($username);
							$accountDetails[$x]['email'] = $response['email'];
							$accountDetails[$x]['username'] = $username;
							$accountDetails[$x]['domain'] = $response['domain'];
							$accountDetails[$x]['ip'] = $response['ip'];
							$accountDetails[$x]['date_created'] = $response['date_created'];
							$accountDetails[$x]['package'] = $response['package'];
							$x++;
					}

					$importedList = [];

					//check from Order_Options
					$x = 0;
					foreach($accountDetails as $account)
					{
							$orderOption = Order_Options::where('value', $account['username'])->first();
							if($orderOption) //if imported
							{
									$accountDetails[$x]['imported'] = 1;
							}
							else
							{
									$accountDetails[$x]['imported'] = 0;
							}
							$x++;
					}

					//check from Order_Settings
					$x = 0;
					foreach($accountDetails as $account)
					{
							$orderSetting = Order_Settings::where('setting_value', $account['username'])->first();
							if($orderSetting) //if imported
							{
									$accountDetails[$x]['imported'] = 1;
							}
							else
							{
									$accountDetails[$x]['imported'] = 0;
							}
							$x++;
					}

					return Response::json([
						'success' => true,
						'data' => "abcdadfadfadf"
					]);
			}
			elseif($integration_type == 'cpanel')
			{
					$integrationCpanel = IntegrationCpanel::find($id);

					$host = $integrationCpanel->https ? 'https://' : 'http://';
					$host .= $integrationCpanel->hostname . ':' . $integrationCpanel->port;

					$username = $integrationCpanel->username;
					$key = $integrationCpanel->access_key;

					$cpanel = new CpanelSdk([
						'host' => $host,
						'username' => $username,
						'auth_type' => 'hash',
						'password' => $key
					]);

					$accounts = $cpanel->listaccts();

					if($accounts['status'] == 1)
					{
							$accountDetails = [];
							$x = 0;
							foreach($accounts['acct'] as $account)
							{
									$accountDetails[$x]['email'] = $account['email'];
									$accountDetails[$x]['username'] = $account['user'];
									$accountDetails[$x]['domain'] = $account['domain'];
									$accountDetails[$x]['ip'] = $account['ip'];
									$accountDetails[$x]['date_created'] = $account['startdate'];
									$accountDetails[$x]['package'] = $account['plan'];
									$x++;
							}

							$importedList = [];

							//check from Order_Options
							$x = 0;
							foreach($accountDetails as $account)
							{
									$orderOption = Order_Options::where('value', $account['username'])->first();
									if($orderOption) //if imported
									{
											$accountDetails[$x]['imported'] = 1;
									}
									else
									{
											$accountDetails[$x]['imported'] = 0;
									}
									$x++;
							}

							//check from Order_Settings
							$x = 0;
							foreach($accountDetails as $account)
							{
									$orderSetting = Order_Settings::where('setting_value', $account['username'])->first();
									if($orderSetting) //if imported
									{
											$accountDetails[$x]['imported'] = 1;
									}
									else
									{
											$accountDetails[$x]['imported'] = 0;
									}
									$x++;
							}

							return Response::json([
								'success' => true,
								'data' => $accountDetails
							]);
					}
			}
			return response()->json([
				'success' => true,
				'message' => "Integration_imoprt {$integration_type}, {$id} deleted."
			]);
	}

    /**
     * @param string $integration
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
	public function destroy($integration = '', $id)
	{
			if($integration == 'directadmin') $integration = Integration::findOrFail($id);
			else if($integration == 'cpanel') $integration = IntegrationCpanel::findOrFail($id);
			else if($integration == 'virtualizor') $integration = IntegrationVirtualizor::findOrFail($id);

		 $integration->forceDelete();

		 return response()->json([
             'success' => true,
             'message' => "Integration {$integration} deleted."
         ]);
	}
}
