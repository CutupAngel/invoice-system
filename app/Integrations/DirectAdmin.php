<?php

namespace App\Integrations;

use Auth;
use Settings;
use App\User;
use App\User_Link;
use App\Integration;
use App\PackageSetting;
use App\Order;
use App\Order_Group;
use App\Order_Options;
use App\Order_Settings;
use App\Package;
use App\Package_Cycle;
use App\Package_Options;
use App\Package_Option_Values;
use App\Options_To_Packages;
use App\Jobs\ControlPanelIntegrationJob;
use App\Packages\Integrations\DirectAdmin as SDK;
use App\Mail\GeneralEmail;
use App\Mail\DirectAdminImportEmail;
use Response;
use Mail;

class DirectAdmin extends ControlPanelIntegration
{
    const TITLE = 'DirectAdmin';
    const SHORTNAME = 'directadmin';
    const DESCRIPTION = "Control panel for web hosting companies running Red Hat 7.x, 8.x, 9.x, Red Hat Enterprise and FreeBSD.";

    // Statuses
    const ERRORED = -1;
    const PENDING = 0;
    const CREATED = 1;
    const SUSPENDED = 2;
    const TERMINATED = 3;

    private static $statuses = [
        self::ERRORED => 'Error',
        self::PENDING => 'Pending',
        self::CREATED => 'Created',
        self::SUSPENDED => 'Suspended',
        self::TERMINATED => 'Terminated'
    ];

    private $api;
    private $lastError;

	/**
	 * The construct needs to handle passed configuration deatils but also auto pull settings.
	 *
	 * @param string $host     The host to connect to (false to autopull)
	 * @param string $username The username to use with the connection (false to autopull)
	 * @param string $password      The password or key to use to authenication (false to autopull)
	 */
	public function __construct($host = false, $username = false, $password = false)
    {
        if (!$host || !$username || !$password) {
            $host = Settings::get('directadmin.https') ? 'https://' : 'http://';
            $host .= Settings::get('directadmin.hostname') . ':' . Settings::get('directadmin.port', 2222);
            $username = Settings::get('directadmin.username');
            $password = Settings::get('directadmin.password');
        }

        $this->api = new SDK($host, $username, $password);
    }

	/**
	 * Set the order variable
	 * @param \App\Order $order The current order being process
	 */
	public function setOrder(\App\Order $order)
    {

    }

	/**
	 * Retriving the form to use on the package create/edit page.
	 * @param \Illuminate\Http\Request $request This is passed by App\Modules\Orders\Controllers\Client@getPackage
	 * @return view()
	 */

   public static function getPackageForm(\Illuminate\Http\Request $request)
   {
       $selectedPackage = '';
       if ($request->has('package')) {
           $package = \App\Package::find($request->input('package'));
           //$selectedPackage = $package->settings->where('name', 'directadmin.package')->first()->value;
       }

       if(is_numeric($request->integration_id)) {
         $integrations = Integration::where('id', (int)$request->integration_id)->get();
       }
       else {
         //get server in server groups
         $integrations = Integration::where('server_group_selected', 'like', '%' . $request->integration_id . '%')->get();
       }

       $package = [];
       foreach($integrations as $integration)
       {
           $https = 'http://';
           if($integration->https == 1) $https = "https://";
           $directadmin = new DirectAdmin(
                                          $https . $integration->hostname . ':' . $integration->port,
                                          $integration->username,
                                          $integration->password
                                        );
          try {
            $resellerPackages = $directadmin->getResellerPackages();
          }
          catch (\Exception $e) {
            $resellerPackages = $directadmin->getUserPackages();
          }

          foreach($resellerPackages['list'] as $resellerPackage)
          {
              $package[] = $resellerPackage;
          }
      }
      $package = array_unique($package);

       return view('Integrations.directAdminPackageForm', [
           'packages' => $package,
           //'selected' => $selectedPackage
       ]);
   }

	public static function getIntegrationServerForm(\Illuminate\Http\Request $request)
    {
        $selectedPackage = '';
        if ($request->has('package')) {
            $package = \App\Package::find($request->input('package'));
            //$selectedPackage = $package->settings->where('name', 'directadmin.package')->first()->value;
        }

        //$directadmin = new DirectAdmin();
        //$resellerPackages = $directadmin->getResellerPackages();
        $resellerPackages = Integration::where('integration_type', 'directadmin')->get();

        $groups = '';
      	$totalPackage = count($resellerPackages);
      	$x = 1;
      	foreach($resellerPackages as $resellerPackage)
      	{
      			$groups .= $resellerPackage->server_group_selected;
      			if($x < $totalPackage) $groups .= ',';
      			if($x == $totalPackage)
      			{
      					if(substr($groups, strlen($groups) -1 , 1) == ',')
      					{
      							$groups = substr($groups, 0, 1);
      					}
      			}
      			$x++;
      	}
      	$groupsArr = explode(",", $groups);
        $groupsArr = array_unique($groupsArr);
        $objArr = [];
        $x = 0;
      	foreach($groupsArr as $group)
      	{
            $objArr[$x]['group'] = $group;
      		  $integrations = Integration::where('server_group_selected', 'like', '%' . $group . '%')->get();
            $integrationArr = [];
            foreach($integrations as $integration)
            {
                $integrationArr[] = $integration;
            }
            $objArr[$x]['integrations'] = $integrationArr;
            $x++;
      	}

        return view('Integrations.directAdminServerForm', [
            'packages' => $objArr,
            //'selected' => $selectedPackage
        ]);
    }

    public static function getResellerList(\Illuminate\Http\Request $request)
      {
          return view('Integrations.directAdminPackageForm', [
              'packages' => $resellerPackages,
              //'selected' => $selectedPackage
          ]);
      }

	/**
	 * Save information from the package form.
	 * @param \Illuminate\Http\Request $request This is passed by App\Modules\Orders\Controllers\Client@savePackage
	 * @param \App\Package $package This is the package model.
	 * @return null
	 */
	public static function savePackageDetails(\Illuminate\Http\Request $request, \App\Package $package)
    {
        \Validator::make($request->toArray(), ['directadmin.package' => 'required']);

        if(is_numeric($request->input('directadmin.server'))) {
          $setting = $package->settings->where('name', 'directadmin.server');
          $name = 'directadmin.server';
        }
        else {
          $setting = $package->settings->where('name', 'directadmin.server_group');
          $name = 'directadmin.server_group';
        }
        if ($setting->count() !== 0) {
            $setting = $setting->first();
            $setting->value = $request->input('directadmin.server');
            $setting->save();
        } else {
            $package->settings()->create([
                'name' => $name,
                'value' => $request->input('directadmin.server') ?: ''
            ]);
            if($name == 'directadmin.server_group') {
               PackageSetting::where('package_id', $package->id)->where('name', 'directadmin.server')->delete();
               return;
            }
            else {
               PackageSetting::where('package_id', $package->id)->where('name', 'directadmin.server_group')->delete();
            }
        }

        $setting = $package->settings->where('name', 'directadmin.package');
        if ($setting->count() !== 0) {
            $setting = $setting->first();
            $setting->value = $request->input('directadmin.package');
            $setting->save();
        } else {
            $package->settings()->create([
                'name' => 'directadmin.package',
                'value' => $request->input('directadmin.server') ?: ''
            ]);
        }
    }

	/**
	 * Retriving the form to use on the order page.
	 * @return view()
	 */
	public static function getOrderForm(\App\Package $package)
    {
        return view('Integrations.directAdminOrderForm');
    }

	/**
	 * Save the information from the order form.
	 * @param \Illuminate\Http\Request $request This is passed by App\Modules\Common\Controllers\Main@postAddToCart
	 * @return null
	 */
	public static function saveOrderForm(\Illuminate\Http\Request $request)
    {
        \Validator::make($request->toArray(), [
            'directadmin.domain' => 'required'
        ]);

        session()->put('cart.directadmin', $request->input('directadmin'));
    }

	/**
	 * Complete the order and perfrom the next action (i.e. create account)
	 * @param \App\Order $order
	 * @return null
	 */
	public static function completeOrder(\App\Order $order)
    {
        $details = session()->get('cart.directadmin');

        try {
          $directadminPackage = $order->package->settings->where('name', 'directadmin.package')->first()->value;
        }
        catch(\Exception $e) {
          $directadminPackage = null;
        }
        try {
          $directadminServer = $order->package->settings->where('name', 'directadmin.server')->first()->value;
        }
        catch(\Exception $e) {
          $directadminServer = null;
        }
        try {
          $directadminServerGroup = $order->package->settings->where('name', 'directadmin.server_group')->first()->value;
        }
        catch(\Exception $e) {
          $directadminServerGroup = null;
        }

        $username = self::getRandomLetters(12);
        $password = str_random(13);

        $order->settings()->create([
            'setting_name' => 'directadmin.username',
            'setting_value' => $username
        ]);

        $order->settings()->create([
            'setting_name' => 'directadmin.password',
            'setting_value' => $password
        ]);

        $order->settings()->create([
            'setting_name' => 'directadmin.domain',
            'setting_value' => $details['domain']
        ]);

        if($directadminPackage) {
          $order->settings()->create([
              'setting_name' => 'directadmin.package',
              'setting_value' => $directadminPackage
          ]);
        }

        if($directadminServer) {
          $order->settings()->create([
              'setting_name' => 'directadmin.server',
              'setting_value' => $directadminServer
          ]);
        }

        if($directadminServerGroup) {
          $order->settings()->create([
              'setting_name' => 'directadmin.server_group',
              'setting_value' => $directadminServerGroup
          ]);
        }

        $order->settings()->create([
            'setting_name' => 'directadmin.error',
            'setting_value' => ''
        ]);

        $order->settings()->create([
            'setting_name' => 'directadmin.status',
            'setting_value' => self::PENDING
        ]);

        self::processCommand('create', $order);
    }

    public static function processCommand(string $command, \App\Order $order)
    {
        switch ($command)
        {
            case 'create':
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

	/**
	 * Get the view file for viewing the order from the customer in the admin panel.
	 * The view *must* extend Orders::viewOrder
	 *
	 * @param  AppOrder $order The current order being viewed.
	 * @return view()
	 */
	public static function getOrderView(\App\Order $order, $user = null)
    {
        $directadmin = $order->settings->pluck('setting_value', 'setting_name')->all();
        $directadmin['directadmin.statusText'] = empty($directadmin['directadmin.status']) ? 'Error' : self::$statuses[$directadmin['directadmin.status']];

        if($directadmin)
        {
            return view('Integrations.directAdminOrderView',
                            [
                              'settings' => $directadmin,
                              'order' => $order,
                              'user' => $user
                            ]
                        );
        }
    }

	/**
	 * The job queue calls this command with the action assigned to it.
	 *
	 * @param  string   $command Action assigned to the job.
	 * @param  AppOrder $order   Order the action is for.
	 * @return boolean (true to remove)
	 */
	public static function queueHandler(string $command, \App\Order $order)
    {
        // Get the settings for the DirectAdmin Account.
        $orderSettings = $order
            ->settings
            ->pluck('setting_value', 'setting_name')
            ->all();

        $username = $orderSettings['directadmin.username'];
        $password = $orderSettings['directadmin.password'];
        $domain = $orderSettings['directadmin.domain'];

        $server = $orderSettings['directadmin.server'] ?: null;
        $package = $orderSettings['directadmin.package'] ?: null;
        $nameserver_1 = $orderSettings['directadmin.nameserver_1'] ?: '';
        $nameserver_2 = $orderSettings['directadmin.nameserver_1'] ?: '';
        $nameserver_3 = $orderSettings['directadmin.nameserver_1'] ?: '';
        $nameserver_4 = $orderSettings['directadmin.nameserver_1'] ?: '';

        //random package if package set to server group
        try {
            $serverAvailables = Integration::where('integration_type', 'directadmin')
                ->where('server_group_selected', 'like',  '%' . $orderSettings['directadmin.server_group'] . '%')
                ->get();
            //->random(1)
            //->first();

            $accountsArr = [];
            $totalAccount = 0;
            $totalAccountServer = 0;
            foreach($serverAvailables as $serverAvailable)
            {
                $totalAccountServer += $serverAvailable->qty;

                $https = 'http://';
                if($serverAvailable->https == 1) $https = "https://";
                $directadmin = new DirectAdmin(
                    $https . $serverAvailable->hostname . ':' . $serverAvailable->port,
                    $serverAvailable->username,
                    $serverAvailable->password
                );

                try {
                    $numAccounts = $directadmin->listResellerAccounts();
                }
                catch (\Exception $e) {
                    $numAccounts = $directadmin->listUserAccounts();
                }

                if(array_key_exists("list", $numAccounts)) {
                    $accountsArr[$serverAvailable->id] = count($numAccounts['list']);
                    $totalAccount += count($numAccounts['list']);
                }
            }
            //check if server is full
            $serverGroupFull = false;
            if($totalAccount >= $totalAccountServer) {
                $serverGroupFull = true;
            }

            if($serverGroupFull) {
                throw new \Exception('Server Group: ' . $orderSettings['directadmin.server_group'] . ' Full');
            }
            else { //assign to available server
                $setServer = false;
                while(!$setServer) {
                    $setServerAvailable = Integration::where('integration_type', 'directadmin')
                        ->where('server_group_selected', 'like',  '%' . $orderSettings['directadmin.server_group'] . '%')
                        ->get()
                        ->random(1)
                        ->first();

                    foreach($serverAvailables as $serverAvailable) {
                        if(isset($accountsArr[$serverAvailable->id]))
                        {
                            if($accountsArr[$serverAvailable->id] <= $setServerAvailable->qty)
                            {
                                $setServer = true;
                                break;
                            }
                        }
                    }
                }
                $server = $setServerAvailable->id;
            }

            try {
                $packagesAvailable = $directadmin->getResellerPackages();
            }
            catch (\Exception $e) {
                $packagesAvailable = $directadmin->getUserPackages();
            }
            $randomPackage = array_rand($packagesAvailable['list']);
            $package= $packagesAvailable['list'][$randomPackage];
        }
        catch (\Exception $e) {
            $setServer = true;
        }

        // Get the DirectAdmin server settings
        $integration = Integration::find($server);
        $host = $integration->https ? 'https://' : 'http://';
        $host .= $integration->hostname . ':' . $integration->port;

        $directAdmin = new DirectAdmin(
            $host,
            $integration->username,
            $integration->password
        );

        //check for individual server if full or not
        $numAccounts = 0;
        try {
            $numAccounts = $directAdmin->listResellerAccounts();
        }
        catch (\Exception $e) {
            $numAccounts = $directAdmin->listUserAccounts();
        }

        $totalAccount = 0;
        if(array_key_exists("list", $numAccounts)) {
            $totalAccount = count($numAccounts['list']);
        }

        $directAdmin->setOrder($order);

        switch ($command)
        {
            case 'createacct':
                if($setServer) {
                    $success = $directAdmin->create(
                        $username,
                        $password,
                        $order->customer->email,
                        $domain,
                        $package,
                        $directAdmin->getSharedIP(),
                        $nameserver_1,
                        $nameserver_2,
                        $nameserver_3,
                        $nameserver_4
                    );

                    if ($success) {
                        $order->status = Order::SETUP;
                        self::setStatus($order, self::CREATED);
                    } else {
                        $order->status = Order::PENDING;
                        self::setStatus($order, self::ERRORED, $directAdmin->getError());
                    }

                    $order->save();
                }
                break;
            case 'suspend':
                $success = $directAdmin->suspend($username);
                if ($success) {
                    $order->status = Order::SUSPENDED;
                    self::setStatus($order, self::SUSPENDED);
                } else {
                    self::setStatus($order, false, $directAdmin->getError());
                }

                $order->save();
                break;
            case 'unsuspend':
                $success = $directAdmin->unsuspend($username);

                if ($success) {
                    $order->status = Order::SETUP;
                    self::setStatus($order, self::CREATED);
                } else {
                    self::setStatus($order, self::SUSPENDED, $directAdmin->getError());
                }

                $order->save();
                break;
            case 'terminate':
                $success = $directAdmin->terminate($username);

                if ($success) {
                    $order->status = Order::TERMINATED;
                    self::setStatus($order, self::TERMINATED);
                } else {
                    self::setStatus($order, false, $directAdmin->getError());
                }

                $order->save();
                break;
            default:
                throw new \Exception('Unknown command');
        }

        // return true to delete the job from the job queue.
        return true;
    }

    public function create($username, $password, $email, $domain, $package, $ip, $nameserver_1, $nameserver_2, $nameserver_3, $nameserver_4, $notify = false)
    {
        $ip = $this->getSharedIP();

        try {
            $success = $this->api->createResellerAccount(
                $username,
                $email,
                $password,
                $domain,
                $package,
                $ip,
                $notify
            );
        }
        catch (\Exception $e)
        {
            $ip = $this->getUserIP();

            $success = $this->api->createUserAccount(
                $username,
                $email,
                $password,
                $domain,
                $package,
                $ip,
                $notify
            );
        }

        if ($success['error'] !== '0') {
            $this->lastError = $success['details'];
            return false;
        }

        return true;
    }

    public function resetPassword($username, $password)
    {
        // TODO: Implement resetPassword() method.
    }

    public function suspend($username, $reason = '')
    {
        $success = $this->api->suspendAccount($username);

        if ($success['error'] !== '0') {
            $this->lastError = $success['details'];
            return false;
        }

        return true;
    }

    public function unsuspend($username)
    {
        $success = $this->api->unsuspendAccount($username);

        if ($success['error'] !== '0') {
            $this->lastError = $success['details'];
            return false;
        }

        return true;
    }

    public function terminate($username)
    {
        $success = $this->api->deleteAccount($username);

        if ($success['error'] !== '0') {
            $this->lastError = $success['details'];
            return false;
        }

        return true;
    }

    public static function getInfo()
    {
        return [
            'title' => self::TITLE,
            'shortname' => self::SHORTNAME,
            'description' => self::DESCRIPTION,
            'status' => self::checkEnabled()
        ];
    }

    public static function checkEnabled()
    {
        return Settings::get('integration.directadmin') === '1' ? true : false;
    }

    public static function toggle()
    {
        if (self::checkEnabled()) {
            Settings::set([
                'integration.directadmin' => false
            ]);

            return 0;
        } else {
            Settings::set([
                'integration.directadmin' => true
            ]);

            return 1;
            /*
            $directadmin = new DirectAdmin();
            if ($directadmin->checkConnection()) {
                Settings::set([
                    'integration.directadmin' => true
                ]);

                return 1;
            } else {
                return $directadmin->getError();
            } */
        }
    }

    public function getError()
    {
        return $this->lastError;
    }

    public function checkConnection()
    {
        try {
            $this->api->serverStats();
            return true;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            switch ($e->getMessage()) {
                case 'cURL error 35: TCP connection reset by peer (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)':
                    $this->lastError = 'The connection was reset, most likely invalid protocol used.';
                    break;
                case 'cURL error 7: Failed connect to 167.114.207.30:2221; Connection refused (see http://curl.haxx.se/libcurl/c/libcurl-errors.html)':
                    $this->lastError = 'Connection was refused. Either a firewall is blocking the connection or the hostname and port is incorrect.';
                    break;
                default:
                    $this->lastError = $e->getMessage();
                    break;
            }
        } catch (\arleslie\DirectAdmin\Exceptions\InvalidLoginException $e) {
            $this->lastError = $e->getMessage();
        }

        return false;
    }

    public static function getSetupForm()
    {
        $integrations = Integration::where('integration_type', 'directadmin')->get();
        return view('Integrations.directadminSetup', compact('integrations'));
    }

    public static function setup(\Illuminate\Http\Request $request)
    {
        /* \Validator::make($request->toArray(), [
            'hostname' => 'required',
            'port' => 'required|numeric',
            'username' => 'required',
            'password' => 'required'
        ]); */

        $port = ($request->input('port') != '') ? $request->input('port') : 2222;

        $host = ($request->input('https') == 1) ? 'https://' : 'http://';
        $host .= $request->input('hostname') . ':' . $port;

        $directadmin = new DirectAdmin(
            $host,
            $request->input('username'),
            $request->input('password')
        );

        if (! $directadmin->checkConnection()) {
            if($request->ajax()) {
                return Response::json([
        					'success' => false,
        					'errors' => $directadmin->getError()
        				]);
            }
            return back()->withInput()->withErrors($directadmin->getError());
        }

        /* Old Settings */
        /* Settings::set([
            'directadmin.hostname' => $request->input('hostname'),
            'directadmin.https' => $request->has('https'),
            'directadmin.port' => $port,
            'directadmin.username' => $request->input('username'),
            'directadmin.password' => $request->input('password')
        ]); */
        /* End of Old Settings */

        if($request->integration_id == '') {
            $integration = new Integration();
        }
        else {
            $integration = Integration::find($request->integration_id);
        }
        $integration->user_id = Auth::user()->id;
        $integration->hostname = $request->hostname;
        $integration->https = $request->https;
        $integration->port = $request->port;
        $integration->username = $request->username;
        $integration->password = $request->password;
        $integration->nameserver_1 = $request->nameserver_1;
        $integration->nameserver_2 = $request->nameserver_2;
        $integration->nameserver_3 = $request->nameserver_3;
        $integration->nameserver_4 = $request->nameserver_4;
        $integration->nameserver_ip_1 = $request->nameserver_ip_1;
        $integration->nameserver_ip_2 = $request->nameserver_ip_2;
        $integration->nameserver_ip_3 = $request->nameserver_ip_3;
        $integration->nameserver_ip_4 = $request->nameserver_ip_4;
        $integration->qty = $request->qty;
        $integration->server_group_selected = $request->server_group_selected;
        $integration->server_group_available = $request->server_group_available;
        $integration->integration_type = $request->integration_type;
        $integration->save();

        $successStatus = trans('backend.da-connection-success');

        Settings::set([
            'integration.directadmin' => true
        ]);

        if($request->ajax()) {
          return Response::json([
            'success' => true,
            'status' => $successStatus,
            'id' => $integration->id
          ]);
        }

        return back()->with('status', $successStatus);
    }

    public function getUserPackages()
    {
        return $this->api->getUserPackages();
    }

    public function getResellerPackages()
    {
        return $this->api->getResellerPackages();
    }

    public function getSharedIP()
    {
        return 'shared'; //$this->api->getIPs()['list'][0];
    }

    public function getUserIP()
    {
        return $this->api->getIPs()['list'][0];
    }

    public function listResellerAccounts()
    {
        return $this->api->listResellerAccounts();
    }

    public function listUserAccounts()
    {
        return $this->api->listUserAccounts();
    }

    public function getUserConfig($username)
    {
        return $this->api->getUserConfig($username);
    }

    public function resetAccountPassword($username, $password)
    {
        return $this->api->resetAccountPassword($username, $password);
    }

    private static function setStatus(\App\Order $order, $status, $error = '')
    {
        $updates = [
            'status' => $order->settings->where('setting_name', 'directadmin.status')->first(),
            'error' => $order->settings->where('setting_name', 'directadmin.error')->first() ?: ''
        ];

        if (is_int($status)) {
            $updates['status']->setting_value = $status;
        }

        //STATUS
        $orderSetting = Order_Settings::where('order_id', $order->id)
                                        ->where('setting_name', 'directadmin.status')
                                        ->first();

        if(!$orderSetting)
        {
            $orderSetting = new Order_Settings();
        }

        $orderSetting->order_id = $order->id;
        $orderSetting->setting_name = 'directadmin.status';
        $orderSetting->setting_value = $status;
        $orderSetting->save();

        //ERROR
        $orderSetting = Order_Settings::where('order_id', $order->id)
                                        ->where('setting_name', 'directadmin.error')
                                        ->first();

        if(!$orderSetting)
        {
            $orderSetting = new Order_Settings();
        }

        $orderSetting->order_id = $order->id;
        $orderSetting->setting_name = 'directadmin.error';
        $orderSetting->setting_value = $error;
        $orderSetting->save();
    }

    public static function import_customers(\Illuminate\Http\Request $request)
    {
        $port = ($request->input('port') != '') ? $request->input('port') : 2222;

        $host = ($request->input('https') == 1) ? 'https://' : 'http://';
        $host .= $request->input('hostname') . ':' . $port;

        $directadmin = new DirectAdmin(
            $host,
            $request->input('username'),
            $request->input('password')
        );

        /* try {
            $accounts = $directadmin->listResellerAccounts();
        }
        catch (\Exception $e) {
            $accounts = $directadmin->listUserAccounts();
        } */

        $accounts = [];
        foreach($request->check_usernames as $username)
        {
            $accounts[] = $username['username'];
        }
        $accountDetails = [];
        $x = 0;
        foreach($accounts as $username)
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
            $directAdminGroupName = 'DirectAdmin Import Sync Products';
            $orderGroup = Order_Group::where('name', 'DirectAdmin Import Sync Products')->first();
            if(!$orderGroup)
            {
                $orderGroup = new Order_Group();
                $orderGroup->user_id = Auth::user()->id;
                $orderGroup->name = $directAdminGroupName;
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
                $package->integration = 'directadmin';
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
            $optionName = 'da_username';
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

            $optionDomain = 'da_domain';
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

            $optionIp = 'da_ip';
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

            $optionDate = 'da_date_created';
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

            $optionRecurring = 'da_recurring_billing';
            $packageOptionRecurring = Package_Options::where('internal_name', $optionRecurring)->first();
            if(!$packageOptionRecurring)
            {
                $packageOptionRecurring = new Package_Options();
                $packageOptionRecurring->user_id = Auth::user()->id;
                $packageOptionRecurring->internal_name = $optionRecurring;
                $packageOptionRecurring->display_name = $optionRecurring;
                $packageOptionRecurring->type = 1;
                $packageOptionRecurring->save();
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

            $optionToPackageRecurring = Options_To_Packages::where('option_id', $packageOptionRecurring->id)
                                                    ->where('package_id', $package->id)
                                                    ->first();

            if(!$optionToPackageRecurring)
            {
                $optionToPackageRecurring = new Options_To_Packages();
                $optionToPackageRecurring->option_id = $packageOptionRecurring->id;
                $optionToPackageRecurring->package_id = $package->id;
                $optionToPackageRecurring->save();
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

            $packageOptionValueRecurring = Package_Option_Values::where('option_id', $packageOptionRecurring->id)->first();
            if(!$packageOptionValueRecurring)
            {
                $packageOptionValueRecurring = new Package_Option_Values();
                $packageOptionValueRecurring->option_id = $packageOptionRecurring->id;
                $packageOptionValueRecurring->display_name = $optionRecurring;
                $packageOptionValueRecurring->price = 0.00;
                $packageOptionValueRecurring->fee = 0.00;
                $packageOptionValueRecurring->cycle_type = 5;
                $packageOptionValueRecurring->save();
            }

            //Make Order
            $order = Order::where('user_id', Auth::user()->id)
                            ->where('customer_id', $user->id)
                            ->where('package_id', $package->id)
                            ->where('cycle_id', $packageCycle->id)
                            ->first();

            if(!$order)
            {
                $order = new Order();
                $order->user_id = Auth::user()->id;
                $order->customer_id = $user->id;
                $order->package_id = $package->id;
                $order->cycle_id = $packageCycle->id;
                $order->status = 0;
                $order->last_invoice = date('Y-m-d H:i:s');
                $order->price = 0.00;
    						$order->integration = 'directadmin';
    						$order->domainIntegration = 0;
                $order->save();
            }

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

            $orderOptionRecurring = Order_Options::where('order_id', $order->id)
                                          ->where('option_value_id', $packageOptionValueRecurring->id)
                                          ->first();
            if(!$orderOptionRecurring)
            {
                $orderOptionRecurring = new Order_Options();
                $orderOptionRecurring->order_id = $order->id;
                $orderOptionRecurring->option_value_id = $packageOptionValueRecurring->id;
                $orderOptionRecurring->amount = 0.00;

                //set recurring billing information
                if($request->recurring_billing_info == 'yes')
                {
                    $orderOptionRecurring->value = 1;
                }
                else
                {
                    $orderOptionRecurring->value = 0;
                }

                $orderOptionRecurring->cycle_type = 5;
                $orderOptionRecurring->status = 2;
                $orderOptionRecurring->last_invoice = Date('Y-m-d H:i:s');
                $orderOptionRecurring->save();
            }

            //reset account password
            $contentEmail = '';
            $password = '';
            if($request->reset_password == 'yes')
            {
                $password = str_random(13);
                $response = $directadmin->resetAccountPassword($account['username'], $password);
                $accountResetPassword[$x]['username'] = $account['username'];
                $accountResetPassword[$x]['password'] = $password;
                $contentEmail .= 'username: ' . $account['username'] . '<br/>' . 'new password: ' . $password . '<br/><br/>';
            }

            //Order Settings
            $orderSetting = new Order_Settings();
            $orderSetting->order_id = $order->id;
            $orderSetting->setting_name = 'directadmin.username';
            $orderSetting->setting_value = $account['username'];
            $orderSetting->save();

            //DA Password
            $orderSetting = new Order_Settings();
            $orderSetting->order_id = $order->id;
            $orderSetting->setting_name = 'directadmin.password';
            $orderSetting->setting_value = $password;
            $orderSetting->save();

            //DA Domain
            $orderSetting = new Order_Settings();
            $orderSetting->order_id = $order->id;
            $orderSetting->setting_name = 'directadmin.domain';
            $orderSetting->setting_value = $account['domain'];
            $orderSetting->save();

            //DA Server
            $orderSetting = new Order_Settings();
            $orderSetting->order_id = $order->id;
            $orderSetting->setting_name = 'directadmin.server';
            $orderSetting->setting_value = $request->integration_id;
            $orderSetting->save();


            //DA Package
            $orderSetting = new Order_Settings();
            $orderSetting->order_id = $order->id;
            $orderSetting->setting_name = 'directadmin.package';
            $orderSetting->setting_value = $account['package'];
            $orderSetting->save();

            //DA Status
            $orderSetting = new Order_Settings();
            $orderSetting->order_id = $order->id;
            $orderSetting->setting_name = 'directadmin.status';
            $orderSetting->setting_value = 1;
            $orderSetting->save();

            //send welcome email
            $customer = $user;
            if($request->client_welcome_email == 'yes')
            {
                //send welcome email with option
                if($request->client_welcome_email_option == 'automated')
                {
                    $directadmin->sendMailDirectAdmin('automated', $customer, $contentEmail);
                }
                else if($request->client_welcome_email_option == 'signup')
                {
                    $directadmin->sendMailDirectAdmin('signup', $customer, 'Hello' . ' ' . 'client signup email');
                }
            }

            //send service welcome email
            if($request->service_welcome_email == 'yes')
            {
                $directadmin->sendMailDirectAdmin('service_welcome_email', $customer, 'Hello' . ' ' . 'welcome email');
            }

            $x++;
        }

        return response()->json([
  				'success' => true,
  				'status' => 'Data DA Sync Complete.' . ' ' .  count($accounts) . ' imported successfully.',
          'integration_id' => $request->integration_id,
          'total' => count($accounts)
  			]);
    }

    public function sendMailDirectAdmin($type, $customer, $contentEmail)
  	{
  		//return;

      $user = Auth::User();
      Mail::to($customer)->send(new DirectAdminImportEmail($user, $type, $contentEmail));
  	}

    private static function getRandomLetters($length)
    {
       return substr(str_shuffle('abcdefghijklmnopqrstuvwxyz'), 0, $length);
    }
}
