<?php

namespace App\Integrations;

use App\Jobs\ControlPanelIntegrationJob;

use Illuminate\Http\Request;
use Log;
use Settings;
use Mail;
use Auth;
use App\User;
use App\User_Link;
use App\Integration;
use App\IntegrationCpanel;
use App\Order;
use App\Order_Group;
use App\Order_Options;
use App\Order_Settings;
use App\Package;
use App\Package_Cycle;
use App\Package_Options;
use App\Package_Option_Values;
use App\PackageSetting;
use App\Options_To_Packages;
use App\Mail\GeneralEmail;
use App\Mail\CpanelImportEmail;
use App\Mail\CpanelNotificationEmail;
use Gufy\CpanelPhp\Cpanel as SDK;
use Illuminate\Support\Facades\Validator;

class CPanel extends ControlPanelIntegration
{
	const TITLE = 'cPanel/WHM';
	const SHORTNAME = 'cpanel';
	const DESCRIPTION = "With its first-class support and rich feature set, cPanel & WHM has been the web hosting industry's most reliable, intuitive control panel since 1997.";

	// Statuses
	const ERRORED = -1;
	const PENDING = 0;
	const CREATED = 1;
	const SUSPENDED = 2;
	const TERMINATED = 3;

	private static $statuses = [
		self::ERRORED => 'Errored',
		self::PENDING => 'Pending',
		self::CREATED => 'Created',
		self::SUSPENDED => 'Suspended',
		self::TERMINATED => 'Terminated'
	];

	private $api;
	private $lastError = false;
	private $order;

	public function __construct($host = false, $username = false, $key = false)
	{
		if ($host == false && $username == false && $key == false) {
			$host = Settings::get('cpanel.https') ? 'https://' : 'http://';
			$host .= Settings::get('cpanel.hostname') . ':' . Settings::get('cpanel.port');

			$username = Settings::get('cpanel.username');
			$key = Settings::get('cpanel.accesskey');
		}

		$this->api = new SDK([
			'host' => $host,
			'username' => $username,
			'auth_type' => 'hash',
			'password' => $key
		]);
	}

	public static function getInfo()
	{
		return [
			'title' => self::TITLE,
			'shortname' => self::SHORTNAME,
			'description' => self::DESCRIPTION,
			'status' => (boolean) self::checkEnabled()
		];
	}

	public static function checkEnabled()
	{
		return Settings::getAsUser(1, 'integration.cpanel');
	}

	public static function toggle()
	{
		if (self::checkEnabled()) {
			Settings::set([
				'integration.cpanel' => false
			]);
		} else {
			$cpanel = new CPanel();
			if ($cpanel->checkConnection()) {
				Settings::set([
					'integration.cpanel' => true
				]);

				return 1;
			} else {
				return $cpanel->getError();
			}
		}

		return 0;
	}

	public function setOrder(\App\Order $order)
	{
		$this->order = $order;
	}

	public static function getPackageForm(\Illuminate\Http\Request $request)
	{
		$selectedPackage = '';
		if ($request->has('package'))
		{
			$package = \App\Package::find($request->input('package'));
			if(isset($package->settings))
			{
					$selectedPackage = $package->settings->where('name', 'cpanel.package')
																						->where('package_id', $package->id)
																						->first();
			}
		}

		$integrationCpanel = IntegrationCpanel::find($request->integration_id);

		$cpanelHost = $integrationCpanel->https ? 'https://' : 'http://';
		$cpanelHost .= $integrationCpanel->hostname . ':' . $integrationCpanel->port;

		$cpanel = new CPanel(
			$cpanelHost,
			$integrationCpanel->username,
			$integrationCpanel->access_key
		);

		return view('Integrations.cpanelPackageForm', [
			'packages' => $cpanel->getPackages(),
			'selected' => $selectedPackage ? $selectedPackage->value : ''
		]);
	}

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public static function getIntegrationServerForm(\Illuminate\Http\Request $request)
    {
        $cpanels = IntegrationCpanel::all(['id', 'name']);

        return response()->json([
            'data' => $cpanels
        ]);
    }

	public static function savePackageDetails(\Illuminate\Http\Request $request, \App\Package $package)
	{
		\Validator::make($request->toArray(), ['cpanel_package' => 'required']);

		$setting = $package->settings->where('name', 'cpanel.server');
		if ($setting->count() !== 0) {
			$setting = $setting->first();
			$setting->value = $request->input('cpanel_server');
			$setting->save();
		} else {
			$package->settings()->create([
				'name' => 'cpanel.server',
				'value' => $request->input('cpanel_server')
			]);
		}

		$setting = $package->settings->where('name', 'cpanel.package');
		if ($setting->count() !== 0) {
			$setting = $setting->first();
			$setting->value = $request->input('cpanel_package');
			$setting->save();
		} else {
			$package->settings()->create([
				'name' => 'cpanel.package',
				'value' => $request->input('cpanel_package')
			]);
		}
	}

	public static function getOrderForm(\App\Package $package)
	{
		$domainForm = '';
		if ($package->domainIntegration) {
			$domainForm = \Integrations::get('domain', 'getRegistrationForm');
		}

		return view('Integrations.cpanelOrderForm', ['domainForm' => $domainForm]);
	}

	public static function saveOrderForm(\Illuminate\Http\Request $request)
	{
		$domain = '';
		if (\Session::has('integration.domain')) {
			$domain = \Session::get('integration.domain');
		}

		if (empty($domain)) {
			\Validator::make($request->toArray(), [
				'cpanel.domain' => 'required'
			]);

			$domain = $request->input('cpanel');
		}

		session()->put('cart.cpanel', $domain);
	}

	public static function completeOrder(\App\Order $order)
	{
		$details = session()->get('cart.cpanel');
		$cpanelPackage = $order->package->settings->where('name', 'cpanel.package')->first();

		$cpanelPackageValue = '';
		if($cpanelPackage) $cpanelPackageValue = $cpanelPackage->value;

		$username = strtolower(str_random(16)); //substr($details['domain'], 0, 8);
		$password = str_random(24);

		$packageSetting = PackageSetting::where('package_id', $order->package->id)
																			->where('name', 'cpanel.server')
																			->first();

		$server = IntegrationCpanel::find($packageSetting->value);

		$order->settings()->create([
			'setting_name' => 'cpanel.username',
			'setting_value' => $username
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.password',
			'setting_value' => $password
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.domain',
			'setting_value' => $details['domain']
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.package',
			'setting_value' => $cpanelPackageValue
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.error',
			'setting_value' => ''
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.status',
			'setting_value' => self::PENDING
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.server',
			'setting_value' => $server->id
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.nameserver_1',
			'setting_value' => $server->nameserver_1
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.nameserver_2',
			'setting_value' => $server->nameserver_2
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.nameserver_3',
			'setting_value' => $server->nameserver_3
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.nameserver_4',
			'setting_value' => $server->nameserver_4
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.nameserver_ip_1',
			'setting_value' => $server->nameserver_ip_1
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.nameserver_ip_2',
			'setting_value' => $server->nameserver_ip_2
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.nameserver_ip_3',
			'setting_value' => $server->nameserver_ip_3
		]);

		$order->settings()->create([
			'setting_name' => 'cpanel.nameserver_ip_4',
			'setting_value' => $server->nameserver_ip_4
		]);

		self::processCommand('create', $order);
	}

	public static function getOrderView(\App\Order $order, User $user = null)
	{
		$cpanel = $order->settings->pluck('setting_value', 'setting_name')->all();
		$cpanel['cpanel.statusText'] = self::$statuses[$cpanel['cpanel.status']];

		return view('Integrations.cpanelOrderView', ['order' => $order, 'user' => $user, 'settings' => $cpanel]);
	}

	public static function processCommand(string $command, \App\Order $order)
	{
		switch ($command)
		{
				case 'create':
						self::setStatus($order, self::PENDING);
						$integrationJob = new ControlPanelIntegrationJob($order->id, 'createacct');
						$integrationJob->handle();
						break;
				case 'suspend':
						$integrationJob = new ControlPanelIntegrationJob($order->id, 'suspend');
						$integrationJob->handle();
						break;
				case 'unsuspend':
						$integrationJob = new ControlPanelIntegrationJob($order->id, 'unsuspend');
						$integrationJob->handle();
						break;
				case 'terminate':
						$integrationJob = new ControlPanelIntegrationJob($order->id, 'terminate');
						$integrationJob->handle();
				break;
			default:
				return 0;
		}

		return 1;
	}

	public static function queueHandler(string $command, \App\Order $order)
	{
		// Get the settings for the cPanel Account.
		$orderSettings = $order->settings->pluck('setting_value', 'setting_name');

		$serverId = '';
		if(isset($orderSettings['cpanel.server']))
		{
				$serverId = $orderSettings['cpanel.server'];
		}
		$username = $orderSettings['cpanel.username'];
		$password = $orderSettings['cpanel.password'];
		$domain = $orderSettings['cpanel.domain'];
		$package = $orderSettings['cpanel.package'];

		$nameserver_1 = '';
		if(isset($orderSettings['cpanel.nameserver_1']))
		{
				$nameserver_1 = $orderSettings['cpanel.nameserver_1'];
		}
		$nameserver_2 = '';
		if(isset($orderSettings['cpanel.nameserver_2']))
		{
				$nameserver_2 = $orderSettings['cpanel.nameserver_2'];
		}
		$nameserver_3 = '';
		if(isset($orderSettings['cpanel.nameserver_3']))
		{
				$nameserver_3 = $orderSettings['cpanel.nameserver_3'];
		}
		$nameserver_4 = '';
		if(isset($orderSettings['cpanel.nameserver_4']))
		{
				$nameserver_4 = $orderSettings['cpanel.nameserver_4'];
		}

		// Get the cPanel server settings
		$integrationCpanel = IntegrationCpanel::find($serverId);
		$cpanelHost = $integrationCpanel->https ? 'https://' : 'http://';
		$cpanelHost .= $integrationCpanel->hostname . ':' . $integrationCpanel->port;

		$cpanel = new CPanel(
			$cpanelHost,
			$integrationCpanel->username,
			$integrationCpanel->access_key
		);

		// Get the Cpanel server settings
		$setServer = true;

		$totalAccount = Order_Settings::where('setting_name', 'cpanel.server')
																		->where('setting_value', $serverId)
																		->count();

		if($integrationCpanel)
		{
				if($totalAccount >= $integrationCpanel->qty) {
						$setServer = false;
				}
		}

		$cpanel->setOrder($order);

		switch ($command)
		{
			case 'createacct':
				$success = $cpanel->create(
					$username,
					$password,
					$order->customer->email,
					$domain,
					$package,
					'n',
					$nameserver_1,
					$nameserver_2,
					$nameserver_3,
					$nameserver_4,
					true
				);

				if ($success) {
					$order->status = Order::SETUP;
					self::setStatus($order, self::CREATED);
				} else {
					$order->status = Order::PENDING;
					self::setStatus($order, self::ERRORED, $cpanel->getError());
				}

				$order->save();
				break;
			case 'suspend':
				$success = $cpanel->suspend($username);

				if ($success) {
					$order->status = Order::SUSPENDED;
					self::setStatus($order, self::SUSPENDED);
				} else {
					self::setStatus($order, false, $cpanel->getError());
				}

				$order->save();
				break;
			case 'unsuspend':
				$success = $cpanel->unsuspend($username);

				if ($success) {
					$order->status = Order::SETUP;
					self::setStatus($order, self::CREATED);
				} else {
					self::setStatus($order, self::SUSPENDED, $cpanel->getError());
				}

				$order->save();
				break;
			case 'terminate':
				$success = $cpanel->terminate($username);

				if ($success) {
					$order->status = Order::TERMINATED;
					self::setStatus($order, self::TERMINATED);
				} else {
					self::setStatus($order, false, $cpanel->getError());
				}

				$order->save();
				break;
			default:
				throw new \Exception('Unknown command');
		}

		// return true to delete the job from the job queue.
		return true;
	}

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
	public static function getSetupForm()
    {
        $integrationCpanels = IntegrationCpanel::all();
        return view('Integrations.cpanelSetup', compact('integrationCpanels'));
    }

    /**
     * @param Request $request
     * @return $this|\Illuminate\Http\JsonResponse
     */
    public static function setup(Request $request)
	{
	    $validator = Validator::make($request->all(), [
	        'hostname' => 'required',
            'port' => 'required|numeric',
            'username' => 'required|string',
            'access_key' => 'required|string',
            'https' => 'required|integer',
            'name' => 'required|string'
        ]);

	    $validator->after(function ($validator) use ($request) {
            $connection = $request->https ? 'https://' : 'http://';
            $host = "{$connection}{$request->hostname}:{$request->port}";
            $cpanel = new CPanel($host, $request->username, $request->access_key);

            if (!$cpanel->checkConnection()) {
                $validator->errors()->add('hostname', $cpanel->getError());
            }
        });

		if ($validator->fails()) {
		    return response()->json([
		        'errors' => $validator->errors()
            ], 422);
        }

        if ($request->filled('id')) {
		    $integrationCpanel = IntegrationCpanel::findOrFail($request->input('id'));
		    $integrationCpanel->update($request->except(['id']));
        } else {
            $request->merge(['user_id' => auth()->id()]);
            $integrationCpanel = IntegrationCpanel::create($request->all());
            Settings::set(['integration.cpanel'  => 1]);
        }

		return response()->json([
		    'success' => true,
            'integration_id' => $integrationCpanel->id
        ]);
	}

	public function getError()
	{
		return $this->lastError;
	}

	public function create($username, $password, $email, $domain, $package, $ip = 'n', $nameserver_1, $nameserver_2, $nameserver_3, $nameserver_4, $notify = true)
	{
		// Creating accounts can take quite a bit of time, so disable the timeout in order to avoid an error.
		$timeout = $this->api->getTimeout();
		$this->api->setTimeout(0);

		$success = $this->api->createacct($details = [
			'username' => $username,
			'domain' => $domain,
			'contactemail' => $email,
			'plan' => $package,
			'password' => $password,
			'ip' => $ip,
			'nameserver_1' => $nameserver_1,
			'nameserver_2' => $nameserver_2,
			'nameserver_3' => $nameserver_3,
			'nameserver_4' => $nameserver_4
		]);

		$success = json_decode(json_encode($success), FALSE)->result[0];

		if ($success->status !== 1) {
			$this->lastError = $success->statusmsg;
			return false;
		}

		if ($notify) {
			$cpanelServer = str_replace('2087', '2083', $this->api->getHost());

			$order = $this->order;

			$user = User::find($this->order->user_id);

			$userFrom = User::find($this->order->user->id);
			$userTo = $this->order->customer;
			$subject = 'Your cPanel Details';
			$content = 'cPanel: ' . $cpanelServer . '<br>
			Username: '. $username . '<br>
			Password: ' . $password;
			$view = 'Integrations.cpanelEmail';

			$site = Settings::getAsUser($this->order->user_id, 'site');

			Mail::to($userTo)->send(new CpanelNotificationEmail($userFrom, $subject, $details, $cpanelServer, $view));
		}

		$timeout = $this->api->setTimeout($timeout);

		return true;
	}

	public function getPackages()
	{
		return $this->api->listpkgs()['package'];
	}


	public function resetPassword($username, $password)
	{
			$result = $this->api->passwd([
						'api.version' => '1',
						'user' => $username,
						'password' => $password
				]);

			return true;
	}

	public function suspend($username, $reason = '')
	{
		$result = $this->api->suspendacct([
			'api.version' => '1',
			'user' => $username,
			'reason' => $reason,
			'leave-ftp-accts-enabled' => '0'
		]);

		return true;
	}

	public function unsuspend($username)
	{
		$result = $this->api->unsuspendacct([
			'api.version' => '1',
			'user' => $username
		]);

		return true;
	}

	public function terminate($username)
	{
		$this->api->removeacct([
			'user' => $username
		]);

		return true;
	}

	public function checkConnection()
	{
		$connection = $this->api->checkConnection();

		if ($connection['status'] === 0) {
			switch ($connection['error']) {
				case 'conn_error':
					$this->lastError = 'Unable to contact cPanel. Hostname or port maybe incorrect.';
					break;
				case 'auth_error':
					$this->lastError = 'Invalid username or access key supplied.';
					break;
				default:
					$this->lastError = 'An unknown error occurred while contacting cPanel.';
					break;
			}

			return false;
		}

		return true;
	}

	private static function setStatus(\App\Order $order, $status, $error = '')
	{
		$updates = [
			'status' => $order->settings()->where('setting_name', 'cpanel.status')->firstOrFail(),
			'error' => $order->settings()->where('setting_name', 'cpanel.error')->firstOrFail()
		];

		if (is_int($status)) {
			$updates['status']->setting_value = $status;
		}

		$updates['error']->setting_value = $error;

		$order->settings()->saveMany($updates);
	}

	public static function import_customers(\Illuminate\Http\Request $request)
	{
		$integrationCpanel = IntegrationCpanel::find($request->id);

		$host = $integrationCpanel->https ? 'https://' : 'http://';
		$host .= $integrationCpanel->hostname . ':' . $integrationCpanel->port;

		$username = $integrationCpanel->username;
		$key = $integrationCpanel->access_key;

		$cpanel = new CPanel($host, $username, $key);

		$accounts = [];
		foreach($request->check_usernames as $username)
		{
				$accounts[] = $username['username'];
		}
		$accountDetails = [];
		$x = 0;
		foreach($accounts as $username)
		{
				$accountsCpanel = $cpanel->api->listaccts(['searchtype'=> 'user', 'search'=> '', 'exact', 'search'=> $username]);

				foreach($accountsCpanel['acct'] as $account)
				{
						$accountDetails[$x]['email'] = $account['email'];
						$accountDetails[$x]['username'] = $username;
						$accountDetails[$x]['domain'] = $account['domain'];
						$accountDetails[$x]['ip'] = $account['ip'];
						$accountDetails[$x]['date_created'] = $account['startdate'];
						$accountDetails[$x]['package'] = $account['plan'];
						$x++;
				}
		}

		//create users if email not exists, otherwise make customer for certain user
		$accountResetPassword = [];
		$x = 0;
		foreach($accountDetails as $account)
		{
				$user = User::where('email', $account['email'])->first();
				if(!$user)
				{
						$user = new User();
						$user->name = $account['username'];
						$user->username = $account['username'];
						$user->email = $account['email'];
						$user->password = '';
						$user->account_type = 2;
						$user->authEnabled = 0;
						$user->authSecret = '';
						$user->stripeId = '';
						$user->save();

						$userLink = new User_Link();
						$userLink->user_id = $user->id;
						$userLink->parent_id = Auth::user()->id;
				}

				//create package group and package if not exists
				$cpanelAdminGroupName = 'Cpanel Import Sync Products';
				$orderGroup = Order_Group::where('name', 'Cpanel Import Sync Products')->first();
				if(!$orderGroup)
				{
						$orderGroup = new Order_Group();
						$orderGroup->user_id = Auth::user()->id;
						$orderGroup->name = $cpanelAdminGroupName;
						$orderGroup->description = '';
						$orderGroup->url = '';
						$orderGroup->type = 2;
						$orderGroup->visible = 0;
						$orderGroup->save();
				}

				$package = Package::where('name', $account['package'])
														->where('group_id', $orderGroup->id)
														->first();

				if(!$package)
				{
						$package = new Package();
						$package->group_id = $orderGroup->id;
						$package->name = $account['package'];
						$package->description = '';
						$package->tax = 0.00;
						$package->prorate = 0.00;
						$package->trial = 0.00;
						$package->theme = 0.00;
						$package->type = 0.00;
						$package->url = 0.00;
						$package->integration = 'cpanel';
						$package->domainIntegration = 0;
						$package->save();
				}

				$packageCycle = Package_Cycle::where('package_id', $package->id)->first();
				if(!$packageCycle)
				{
						$packageCycle = new Package_Cycle();
						$packageCycle->package_id = $package->id;
						$packageCycle->price = 0.00;
						$packageCycle->fee = 0.00;
						$packageCycle->cycle = 5;
						$packageCycle->save();
				}

				//create Options
				$optionName = 'cpanel_username';
				$packageOption = Package_Options::where('internal_name', $optionName)->first();
				if(!$packageOption)
				{
						$packageOption = new Package_Options();
						$packageOption->user_id = Auth::user()->id;
						$packageOption->internal_name = $optionName;
						$packageOption->display_name = $optionName;
						$packageOption->type = 1;
						$packageOption->save();
				}

				$optionDomain = 'cpanel_domain';
				$packageOptionDomain = Package_Options::where('internal_name', $optionDomain)->first();
				if(!$packageOptionDomain)
				{
						$packageOptionDomain = new Package_Options();
						$packageOptionDomain->user_id = Auth::user()->id;
						$packageOptionDomain->internal_name = $optionDomain;
						$packageOptionDomain->display_name = $optionDomain;
						$packageOptionDomain->type = 1;
						$packageOptionDomain->save();
				}

				$optionIp = 'cpanel_ip';
				$packageOptionIp = Package_Options::where('internal_name', $optionIp)->first();
				if(!$packageOptionIp)
				{
						$packageOptionIp = new Package_Options();
						$packageOptionIp->user_id = Auth::user()->id;
						$packageOptionIp->internal_name = $optionIp;
						$packageOptionIp->display_name = $optionIp;
						$packageOptionIp->type = 1;
						$packageOptionIp->save();
				}

				$optionDate = 'cpanel_date_created';
				$packageOptionDate = Package_Options::where('internal_name', $optionDate)->first();
				if(!$packageOptionDate)
				{
						$packageOptionDate = new Package_Options();
						$packageOptionDate->user_id = Auth::user()->id;
						$packageOptionDate->internal_name = $optionDate;
						$packageOptionDate->display_name = $optionDate;
						$packageOptionDate->type = 1;
						$packageOptionDate->save();
				}

				//create Option To Packages
				$optionToPackage = Options_To_Packages::where('option_id', $packageOption->id)
																								->where('package_id', $package->id)
																								->first();

				if(!$optionToPackage)
				{
						$optionToPackage = new Options_To_Packages();
						$optionToPackage->option_id = $packageOption->id;
						$optionToPackage->package_id = $package->id;
						$optionToPackage->save();
				}

				$optionToPackageDomain = Options_To_Packages::where('option_id', $packageOptionDomain->id)
																								->where('package_id', $package->id)
																								->first();

				if(!$optionToPackageDomain)
				{
						$optionToPackageDomain = new Options_To_Packages();
						$optionToPackageDomain->option_id = $packageOptionDomain->id;
						$optionToPackageDomain->package_id = $package->id;
						$optionToPackageDomain->save();
				}

				$optionToPackageIp = Options_To_Packages::where('option_id', $packageOptionIp->id)
																								->where('package_id', $package->id)
																								->first();

				if(!$optionToPackageIp)
				{
						$optionToPackageIp = new Options_To_Packages();
						$optionToPackageIp->option_id = $packageOptionIp->id;
						$optionToPackageIp->package_id = $package->id;
						$optionToPackageIp->save();
				}

				$optionToPackageDate = Options_To_Packages::where('option_id', $packageOptionDate->id)
																								->where('package_id', $package->id)
																								->first();

				if(!$optionToPackageDate)
				{
						$optionToPackageDate = new Options_To_Packages();
						$optionToPackageDate->option_id = $packageOptionDate->id;
						$optionToPackageDate->package_id = $package->id;
						$optionToPackageDate->save();
				}

				//create Option Values
				$packageOptionValue = Package_Option_Values::where('option_id', $packageOption->id)->first();
				if(!$packageOptionValue)
				{
						$packageOptionValue = new Package_Option_Values();
						$packageOptionValue->option_id = $packageOption->id;
						$packageOptionValue->display_name = $optionName;
						$packageOptionValue->price = 0.00;
						$packageOptionValue->fee = 0.00;
						$packageOptionValue->cycle_type = 5;
						$packageOptionValue->save();
				}

				$packageOptionValueDomain = Package_Option_Values::where('option_id', $packageOptionDomain->id)->first();
				if(!$packageOptionValueDomain)
				{
						$packageOptionValueDomain = new Package_Option_Values();
						$packageOptionValueDomain->option_id = $packageOptionDomain->id;
						$packageOptionValueDomain->display_name = $optionDomain;
						$packageOptionValueDomain->price = 0.00;
						$packageOptionValueDomain->fee = 0.00;
						$packageOptionValueDomain->cycle_type = 5;
						$packageOptionValueDomain->save();
				}

				$packageOptionValueIp = Package_Option_Values::where('option_id', $packageOptionIp->id)->first();
				if(!$packageOptionValueIp)
				{
						$packageOptionValueIp = new Package_Option_Values();
						$packageOptionValueIp->option_id = $packageOptionIp->id;
						$packageOptionValueIp->display_name = $optionIp;
						$packageOptionValueIp->price = 0.00;
						$packageOptionValueIp->fee = 0.00;
						$packageOptionValueIp->cycle_type = 5;
						$packageOptionValueIp->save();
				}

				$packageOptionValueDate = Package_Option_Values::where('option_id', $packageOptionDate->id)->first();
				if(!$packageOptionValueDate)
				{
						$packageOptionValueDate = new Package_Option_Values();
						$packageOptionValueDate->option_id = $packageOptionDate->id;
						$packageOptionValueDate->display_name = $optionDate;
						$packageOptionValueDate->price = 0.00;
						$packageOptionValueDate->fee = 0.00;
						$packageOptionValueDate->cycle_type = 5;
						$packageOptionValueDate->save();
				}

				//Make Order
				$order = new Order();
				$order->user_id = Auth::user()->id;
				$order->customer_id = $user->id;
				$order->package_id = $package->id;
				$order->cycle_id = $packageCycle->id;
				$order->status = 0;
				$order->last_invoice = date('Y-m-d H:i:s');
				$order->price = 0.00;
				$order->integration = 'cpanel';
				$order->domainIntegration = 0;
				$order->save();

				//Make Order Options
				$orderOption = Order_Options::where('order_id', $order->id)
																			->where('option_value_id', $packageOptionValue->id)
																			->first();
				if(!$orderOption)
				{
						$orderOption = new Order_Options();
						$orderOption->order_id = $order->id;
						$orderOption->option_value_id = $packageOptionValue->id;
						$orderOption->amount = 0.00;
						$orderOption->value = $account['username'];
						$orderOption->cycle_type = 5;
						$orderOption->status = 2;
						$orderOption->last_invoice = date('Y-m-d H:i:s');
						$orderOption->save();
				}

				$orderOptionDomain = Order_Options::where('order_id', $order->id)
																			->where('option_value_id', $packageOptionValueDomain->id)
																			->first();
				if(!$orderOptionDomain)
				{
						$orderOptionDomain = new Order_Options();
						$orderOptionDomain->order_id = $order->id;
						$orderOptionDomain->option_value_id = $packageOptionValueDomain->id;
						$orderOptionDomain->amount = 0.00;
						$orderOptionDomain->value = $account['domain'];
						$orderOptionDomain->cycle_type = 5;
						$orderOptionDomain->status = 2;
						$orderOptionDomain->last_invoice = date('Y-m-d H:i:s');
						$orderOptionDomain->save();
				}

				$orderOptionIp = Order_Options::where('order_id', $order->id)
																			->where('option_value_id', $packageOptionValueIp->id)
																			->first();
				if(!$orderOptionIp)
				{
						$orderOptionIp = new Order_Options();
						$orderOptionIp->order_id = $order->id;
						$orderOptionIp->option_value_id = $packageOptionValueIp->id;
						$orderOptionIp->amount = 0.00;
						$orderOptionIp->value = $account['ip'];
						$orderOptionIp->cycle_type = 5;
						$orderOptionIp->status = 2;
						$orderOptionIp->last_invoice = date('Y-m-d H:i:s');
						$orderOptionIp->save();
				}

				$orderOptionDate = Order_Options::where('order_id', $order->id)
																			->where('option_value_id', $packageOptionValueDate->id)
																			->first();
				if(!$orderOptionDate)
				{
						$orderOptionDate = new Order_Options();
						$orderOptionDate->order_id = $order->id;
						$orderOptionDate->option_value_id = $packageOptionValueDate->id;
						$orderOptionDate->amount = 0.00;
						$orderOptionDate->value = $account['date_created'];
						$orderOptionDate->cycle_type = 5;
						$orderOptionDate->status = 2;
						$orderOptionDate->last_invoice = date('Y-m-d H:i:s');
						$orderOptionDate->save();
				}

				//reset account password
				$contentEmail = '';
				$password = '';

				if($request->reset_password == 'yes')
				{
						$password = str_random(13);
						$response = $cpanel->resetPassword($account['username'], $password);
						$accountResetPassword[$x]['username'] = $account['username'];
						$accountResetPassword[$x]['password'] = $password;
						$contentEmail .= 'username: ' . $account['username'] . '<br/>' . 'new password: ' . $password . '<br/><br/>';
				}

				//cpanel Username
				$orderSetting = Order_Settings::where('order_id', $order->id)
																				->where('setting_name', 'cpanel.username')
																				->where('setting_value', $account['username'])
																				->first();

				if(!$orderSetting)
				{
						$orderSetting = new Order_Settings();
						$orderSetting->order_id = $order->id;
				}
				$orderSetting->setting_name = 'cpanel.username';
				$orderSetting->setting_value = $account['username'];
				$orderSetting->save();

				//cpanel Password
				$orderSetting = Order_Settings::where('order_id', $order->id)
																				->where('setting_name', 'cpanel.password')
																				->where('setting_value', $password)
																				->first();

				if(!$orderSetting)
				{
						$orderSetting = new Order_Settings();
						$orderSetting->order_id = $order->id;
				}
				$orderSetting->setting_name = 'cpanel.password';
				$orderSetting->setting_value = $password;
				$orderSetting->save();

				//cpanel Domain
				$orderSetting = Order_Settings::where('order_id', $order->id)
																				->where('setting_name', 'cpanel.domain')
																				->where('setting_value', $account['domain'])
																				->first();

				if(!$orderSetting)
				{
						$orderSetting = new Order_Settings();
						$orderSetting->order_id = $order->id;
				}
				$orderSetting->setting_name = 'cpanel.domain';
				$orderSetting->setting_value = $account['domain'];
				$orderSetting->save();

				//cpanel Server
				$orderSetting = Order_Settings::where('order_id', $order->id)
																				->where('setting_name', 'cpanel.server')
																				->where('setting_value', $integrationCpanel->id)
																				->first();

				if(!$orderSetting)
				{
						$orderSetting = new Order_Settings();
						$orderSetting->order_id = $order->id;
				}
				$orderSetting->setting_name = 'cpanel.server';
				$orderSetting->setting_value = $integrationCpanel->id;
				$orderSetting->save();

				//cpanel Package
				$orderSetting = Order_Settings::where('setting_name', 'cpanel.package')
																				->where('setting_value', $account['package'])
																				->first();

				if(!$orderSetting)
				{
						$orderSetting = new Order_Settings();
						$orderSetting->order_id = $order->id;
				}
				$orderSetting->setting_name = 'cpanel.package';
				$orderSetting->setting_value = $account['package'];
				$orderSetting->save();

				//cpanel Nameserver_1
				$orderSetting = Order_Settings::where('order_id', $order->id)
																				->where('setting_name', 'cpanel.nameserver_1')
																				->where('setting_value', $integrationCpanel->nameserver_1)
																				->first();

				if(!$orderSetting)
				{
						$orderSetting = new Order_Settings();
						$orderSetting->order_id = $order->id;
				}
				$orderSetting->setting_name = 'cpanel.nameserver_1';
				$orderSetting->setting_value = $integrationCpanel->nameserver_1;
				$orderSetting->save();

				//cpanel Nameserver_2
				$orderSetting = Order_Settings::where('order_id', $order->id)
																				->where('setting_name', 'cpanel.nameserver_2')
																				->where('setting_value', $integrationCpanel->nameserver_2)
																				->first();

				if(!$orderSetting)
				{
						$orderSetting = new Order_Settings();
						$orderSetting->order_id = $order->id;
				}
				$orderSetting->setting_name = 'cpanel.nameserver_2';
				$orderSetting->setting_value = $integrationCpanel->nameserver_2;
				$orderSetting->save();

				//cpanel Nameserver_3
				$orderSetting = Order_Settings::where('order_id', $order->id)
																				->where('setting_name', 'cpanel.nameserver_3')
																				->where('setting_value', $integrationCpanel->nameserver_3)
																				->first();

				if(!$orderSetting)
				{
						$orderSetting = new Order_Settings();
						$orderSetting->order_id = $order->id;
				}
				$orderSetting->setting_name = 'cpanel.nameserver_3';
				$orderSetting->setting_value = $integrationCpanel->nameserver_3;
				$orderSetting->save();

				//cpanel Nameserver_4
				$orderSetting = Order_Settings::where('order_id', $order->id)
																				->where('setting_name', 'cpanel.nameserver_4')
																				->where('setting_value', $integrationCpanel->nameserver_4)
																				->first();

				if(!$orderSetting)
				{
						$orderSetting = new Order_Settings();
						$orderSetting->order_id = $order->id;
				}
				$orderSetting->setting_name = 'cpanel.nameserver_4';
				$orderSetting->setting_value = $integrationCpanel->nameserver_4;
				$orderSetting->save();

				//cpanel Status
				$orderSetting = Order_Settings::where('order_id', $order->id)
																				->where('setting_name', 'cpanel.status')
																				->where('setting_value', '1')
																				->first();

				if(!$orderSetting)
				{
						$orderSetting = new Order_Settings();
						$orderSetting->order_id = $order->id;
				}
				$orderSetting->setting_name = 'cpanel.status';
				$orderSetting->setting_value = 1;
				$orderSetting->save();

				//send welcome email
				$customer = $user;
				if($request->client_welcome_email == 'yes')
				{
						$details = [
							'username' => $username,
							'domain' => $account['domain'],
							'contactemail' => $account['email'],
							'plan' => $account['package'],
							'password' => $password,
							'ip' => $account['ip'],
							'nameserver_1' => $integrationCpanel->nameserver_1,
							'nameserver_2' => $integrationCpanel->nameserver_2,
							'nameserver_3' => $integrationCpanel->nameserver_3,
							'nameserver_4' => $integrationCpanel->nameserver_4
						];

						$host = $integrationCpanel->https ? 'https://' : 'http://';
						$host .= $integrationCpanel->hostname . ':' . $integrationCpanel->port;

						$username = $integrationCpanel->username;
						$key = $integrationCpanel->access_key;

						$api = new SDK([
							'host' => $host,
							'username' => $username,
							'auth_type' => 'hash',
							'password' => $key
						]);

						$cpanelServer = str_replace('2087', '2083', $api->getHost());

						//send welcome email with option
						if($request->client_welcome_email_option == 'automated')
						{
								$cpanel->sendMailCpanelAdmin('automated', $customer, $details, $cpanelServer);
						}
						else if($request->client_welcome_email_option == 'signup')
						{
								$cpanel->sendMailCpanelAdmin('signup', $customer, $details, $cpanelServer);
						}
				}

				//send service welcome email
				if($request->service_welcome_email == 'yes')
				{
						$cpanel->sendMailCpanelAdmin('service_welcome_email', $customer, $details, $cpanelServer);
				}

				$x++;
		}

		return response()->json([
			'success' => true,
			'status' => 'Data Cpanel Sync Complete.' . ' ' .  count($accounts) . ' imported successfully.',
			'integration_id' => $request->integration_id,
			'total' => count($accounts)
		]);
	}

	public function sendMailCpanelAdmin($type, $customer, $details, $cpanelServer)
	{
		$user = Auth::User();
		Mail::to($customer)->send(new CpanelImportEmail($user, $type, $details, $cpanelServer));
	}
}
