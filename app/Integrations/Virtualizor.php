<?php

namespace App\Integrations;

use Auth;
use Mail;
use Settings;
use Illuminate\Http\Request;
use App\IntegrationVirtualizor;
use App\Order;
use App\Order_Settings;
use App\User;
use App\Jobs\ControlPanelIntegrationJob;
use App\Mail\VirtualizorNotificationEmail;
use App\Packages\Integrations\Virtualizor\VirtualizorAdmin as Admin_SDK;
use App\Packages\Integrations\Virtualizor\VirtualizorEnduser as Enduser_SDK;

class Virtualizor extends CloudIntegration
{
    const TITLE = 'Virtualizor';
    const SHORTNAME = 'virtualizor';
    const DESCRIPTION = "Virtualizor is a powerful web based VPS Control Panel using which a user can deploy and manage VPS on servers with a single click.";

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
    private $lastError = '';
    private $connected = false;
  	private $order;

    public function __construct($hostname = false, $username = false, $password = false, $realm = 'pve', $port = 9009, $is_admin = true)
    {
        try {
            if($is_admin) $this->api = new Admin_SDK($hostname, $username, $password, $port);
            else $this->api = new Enduser_SDK($hostname, $username, $password, $port);

            $this->connected = true;
        } catch (\Exception $e) {
            $this->lastError = $e->getMessage();
        }

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
        return Settings::get('integration.virtualizor');
    }

    public static function toggle()
    {
        if (self::checkEnabled()) {
            Settings::set([
                'integration.virtualizor' => false
            ]);
            return 0;
        }
        else
        {
            Settings::set([
                'integration.virtualizor' => true
            ]);
            return 1;
        }
    }

    public function getError()
    {
        return $this->lastError;
    }

    public static function getSetupForm()
    {
        $integrations = IntegrationVirtualizor::get();
        return view('Integrations.virtualizorSetup', compact('integrations'));
    }

    public static function setup(\Illuminate\Http\Request $request)
    {
        \Validator::make($request->toArray(), [
            'hostname' => 'required',
            'port'     => 'numeric',
            'username' => 'required',
            'password' => 'required'
        ]);


        $virtualizor = new Virtualizor(
            $request->input('hostname'),
            $request->input('username'),
            $request->input('password'),
            'pve',
            $request->input('port')
        );

        if (! $virtualizor->checkConnection()) {
            return back()->withInput()->withErrors($virtualizor->getError());
        }

        $integrationVirtualizor = new IntegrationVirtualizor();
        $integrationVirtualizor->user_id = Auth::user()->id;
        $integrationVirtualizor->hostname = $request->input('hostname');
        $integrationVirtualizor->username = $request->input('username');
        $integrationVirtualizor->password = $request->input('password');
        $integrationVirtualizor->port = $request->input('port');
        $integrationVirtualizor->save();

        Settings::set([
            'integration.virtualizor' => true
        ]);

        return back()->with('status', trans('backend.da-connection-success'));
    }

    public static function getIntegrationServerForm(\Illuminate\Http\Request $request)
    {
        $virtualizors = IntegrationVirtualizor::all(['id', 'hostname']);

        return response()->json([
            'data' => $virtualizors
        ]);
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
    					$selectedPackage = $package->settings->where('name', 'virtualizor.package')
    																						->where('package_id', $package->id)
    																						->first();
    			}
    		}

    		$integrationVirtualizor = IntegrationVirtualizor::find($request->integration_id);

    		$virtualizor = new Virtualizor(
            $integrationVirtualizor->hostname,
            $integrationVirtualizor->username,
            $integrationVirtualizor->password,
            'pve',
            $integrationVirtualizor->port,
    		);

    		return view('Integrations.virtualizorPackageForm', [
    			'packages' => $virtualizor->getPackages(),
    			'selected' => $selectedPackage ? $selectedPackage->value : ''
    		]);
    	}

    public function getPackages()
  	{
  		return $this->api->plans()['plans'];
  	}

    public function getOsTemplates()
  	{
  		return $this->api->ostemplates()['ostemplates'];
  	}

    public function getVirtualServers($page, $resel, $post)
  	{
  		return $this->api->listvs($page, $resel, $post);
  	}

    public function getVirtualServerStatus($vpsid)
  	{
  		return $this->api->status($vpsid);
  	}

    public function suspend($vpsid)
  	{
  		return $this->api->suspend($vpsid);
  	}

    public function unsuspend($vpsid)
  	{
  		return $this->api->unsuspend($vpsid);
  	}

    public function terminate($vpsid)
  	{
  		return $this->api->delete_vs($vpsid);
  	}

    public function updateHostname($vpsid, Request $request)
  	{
      $post = array();
      $post['vpsid'] = $vpsid;
      $post['hostname'] = $request->hostname;
      return $this->api->editvs($post);
    }

    public function updatePassword($vpsid, Request $request)
  	{
      $post = array();
      $post['vpsid'] = $vpsid;
      $post['rootpass'] = $request->new_password;
      return $this->api->editvs($post);
    }

    public function enableRescue($vpsid, Request $request)
  	{
      $post = array();
      $post['vpsid'] = $vpsid;
      $post['enable_rescue'] = '1';
      $post['rescue_pass'] = $request->root_password;
      $post['conf_rescue_pass'] = $request->root_password;
      return $this->api->managevps($post);
    }

    public function reinstall($vpsid, Request $request)
  	{
      $post = array();
      $post['vpsid'] = $vpsid;
      $post['osid'] = $request->osid;
      $post['newpass'] = $request->new_password;
      $post['conf'] = $request->new_password;
      return $this->api->rebuild($post);
    }

    public function stop($vpsid, Request $request)
  	{
      return $this->api->stop($vpsid);
    }

    public function restart($vpsid, Request $request)
  	{
      return $this->api->restart($vpsid);
    }

    public function poweroff($vpsid, Request $request)
  	{
      return $this->api->poweroff($vpsid);
    }

    public static function savePackageDetails(\Illuminate\Http\Request $request, \App\Package $package)
  	{
  		\Validator::make($request->toArray(), ['virtualizor_package' => 'required']);

  		$setting = $package->settings->where('name', 'virtualizor.server');
  		if ($setting->count() !== 0) {
  			$setting = $setting->first();
  			$setting->value = $request->input('virtualizor_server');
  			$setting->save();
  		} else {
  			$package->settings()->create([
  				'name' => 'virtualizor.server',
  				'value' => $request->input('virtualizor_server')
  			]);
  		}

  		$setting = $package->settings->where('name', 'virtualizor.package');
  		if ($setting->count() !== 0) {
  			$setting = $setting->first();
  			$setting->value = $request->input('virtualizor_package');
  			$setting->save();
  		} else {
  			$package->settings()->create([
  				'name' => 'virtualizor.package',
  				'value' => $request->input('virtualizor_package')
  			]);
  		}
  	}

    public static function getOrderForm(\App\Package $package, $oses)
    {
        return view('Integrations.virtualizorOrderForm', compact('oses'));
    }

    public static function getOrderView(\App\Order $order, User $user = null, Request $request)
  	{
  		$virtualizor = $order->settings->pluck('setting_value', 'setting_name')->all();
  		$virtualizor['virtualizor.status'] = self::$statuses[$virtualizor['virtualizor.status']];

      //get VPS details
      $page = 0;
      $reslen = 0;
      $post = array();
      $post['vpsid'] = $virtualizor['virtualizor.vpsid'];

      $virtualizorServer = '';
      $orderPackageSettings = $order->package->settings;
      foreach($orderPackageSettings as $orderPackageSetting)
      {
          if($orderPackageSetting->name == 'virtualizor.server')
          {
              $virtualizorServer = $orderPackageSetting->value;
              break;
          }
      }
      $integrationVirtualizor = IntegrationVirtualizor::find($virtualizorServer);

      $virtualizor = new Virtualizor(
          $integrationVirtualizor->hostname,
          $integrationVirtualizor->username,
          $integrationVirtualizor->password,
          'pve',
          $integrationVirtualizor->port,
      );

      $vpsDetails = $virtualizor->getVirtualServers($page ,$reslen ,$post);

      $vps = '';
      foreach($vpsDetails as $vpsDetail)
      {
          $vps = $vpsDetail;
          break;
      }

      $vpsStatuses = $virtualizor->getVirtualServerStatus(array($post['vpsid']));
      $vpsStatus = '';
      if($vpsStatuses)
      {
          foreach($vpsStatuses as $status)
          {
              $vpsStatus = $status;
              break;
          }
      }

      $oses = $virtualizor->getOsTemplates();

  		return view('Integrations.virtualizorOrderView', ['order' => $order, 'user' => $user, 'settings' => $virtualizor, 'vpsDetail' => $vps, 'vpsStatus' => $vpsStatus, 'oses' => $oses]);
  	}

    /**
  	 * Save the information from the order form.
  	 * @param \Illuminate\Http\Request $request This is passed by App\Modules\Common\Controllers\Main@postAddToCart
  	 * @return null
  	 */
  	public static function saveOrderForm(\Illuminate\Http\Request $request)
    {
        session()->put('cart.virtualizor', $request->input('virtualizor'));
    }

    /**
  	 * Complete the order and perfrom the next action (i.e. create account)
  	 * @param \App\Order $order
  	 * @return null
  	 */
  	public static function completeOrder(\App\Order $order)
    {
        $details = session()->get('cart.virtualizor');

        try {
          $virtualizorPackage = $order->package->settings->where('name', 'virtualizor.package')->first()->value;
        }
        catch(\Exception $e) {
          $virtualizorPackage = null;
        }
        try {
          $virtualizorServer = $order->package->settings->where('name', 'virtualizor.server')->first()->value;
        }
        catch(\Exception $e) {
          $virtualizorServer = null;
        }

        if($virtualizorServer)
        {
            $order->settings()->create([
                'setting_name' => 'virtualizor.server',
                'setting_value' => $virtualizorServer
            ]);
        }

        if($virtualizorPackage)
        {
          $order->settings()->create([
              'setting_name' => 'virtualizor.package',
              'setting_value' => $virtualizorPackage
          ]);
        }

        $order->settings()->create([
            'setting_name' => 'virtualizor.os',
            'setting_value' => $details['os']
        ]);

        $order->settings()->create([
            'setting_name' => 'virtualizor.hostname',
            'setting_value' => $details['hostname']
        ]);

        $order->settings()->create([
            'setting_name' => 'virtualizor.password',
            'setting_value' => $details['password']
        ]);

        $order->settings()->create([
            'setting_name' => 'virtualizor.error',
            'setting_value' => ''
        ]);

        $order->settings()->create([
            'setting_name' => 'virtualizor.status',
            'setting_value' => self::PENDING
        ]);

        self::processCommand('create', $order);
    }

    public static function processCommand(string $command, \App\Order $order, Request $request = null)
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
            case 'updateHostname':
                $integrationJob = new ControlPanelIntegrationJob($order->id, 'updateHostname', $request);
                $integrationJob->handle();
                break;
            case 'changePassword':
                $integrationJob = new ControlPanelIntegrationJob($order->id, 'changePassword', $request);
                $integrationJob->handle();
                break;
            case 'enableRescue':
                $integrationJob = new ControlPanelIntegrationJob($order->id, 'enableRescue', $request);
                $integrationJob->handle();
                break;
            case 'reinstall':
                $integrationJob = new ControlPanelIntegrationJob($order->id, 'reinstall', $request);
                $integrationJob->handle();
                break;
            case 'create_from_view':
                $integrationJob = new ControlPanelIntegrationJob($order->id, 'create_from_view', $request);
                $integrationJob->handle();
                break;
            case 'stop':
                $integrationJob = new ControlPanelIntegrationJob($order->id, 'stop', $request);
                $integrationJob->handle();
                break;
            case 'restart':
                $integrationJob = new ControlPanelIntegrationJob($order->id, 'restart', $request);
                $integrationJob->handle();
                break;
            case 'poweroff':
                $integrationJob = new ControlPanelIntegrationJob($order->id, 'poweroff', $request);
                $integrationJob->handle();
                break;
            default:
                return 0;
        }

        return 1;
    }

    public static function queueHandler(string $command, \App\Order $order, Request $request = null)
  	{
  		// Get the settings for the virtualizor Account.
  		$orderSettings = $order->settings->pluck('setting_value', 'setting_name');

  		$serverId = '';
  		if(isset($orderSettings['virtualizor.server']))
  		{
  				$serverId = $orderSettings['virtualizor.server'];
  		}
  		$os = $orderSettings['virtualizor.os'];
  		$package = $orderSettings['virtualizor.package'];
  		$hostname = $orderSettings['virtualizor.hostname'];
  		$password = $orderSettings['virtualizor.password'];
  		$vpsid = @$orderSettings['virtualizor.vpsid'];

  		// Get the virtualizor server settings
  		$integrationVirtualizor = IntegrationVirtualizor::find($serverId);

      $is_admin = true;

      $virtualizor = new Virtualizor(
          $integrationVirtualizor->hostname,
          $integrationVirtualizor->username,
          $integrationVirtualizor->password,
          'pve',
          $integrationVirtualizor->port,
          $is_admin
      );

  		$virtualizor->setOrder($order);

  		switch ($command)
  		{
  			case 'createacct':
  				$success = $virtualizor->create(
            $integrationVirtualizor->hostname,
  					$os,
            $hostname,
  					$password,
  					$order->customer->email,
  					$package,
  					true,
            $order
  				);

  				if ($success) {
  					$order->status = Order::SETUP;
  					self::setStatus($order, self::CREATED);
            session()->put('status', 'Virtualizor Created Successfully.');
  				} else {
  					$order->status = Order::PENDING;
  					self::setStatus($order, self::ERRORED, $virtualizor->getError());
            session()->put('error', $virtualizor->getError());
  				}

  				$order->save();
  				break;
  			case 'suspend':
  				$success = $virtualizor->suspend($vpsid);

  				if ($success['done'] == '1') {
  					$order->status = Order::SUSPENDED;
  					self::setStatus($order, self::SUSPENDED);
            session()->put('status', 'Virtualizor Suspended Successfully.');
  				} else {
  					self::setStatus($order, false, $virtualizor->getError());
            session()->put('error', $virtualizor->getError());
  				}

  				$order->save();
  				break;
  			case 'unsuspend':
  				$success = $virtualizor->unsuspend($vpsid);

  				if ($success['done'] == '1') {
  					$order->status = Order::SETUP;
  					self::setStatus($order, self::CREATED);
            session()->put('status', 'Virtualizor Unsuspended Successfully.');
  				} else {
  					self::setStatus($order, self::SUSPENDED, $virtualizor->getError());
            session()->put('error', $virtualizor->getError());
  				}

  				$order->save();
  				break;
  			case 'terminate':
  				$success = $virtualizor->terminate($vpsid);

  				if ($success) {
  					$order->status = Order::TERMINATED;
  					self::setStatus($order, self::TERMINATED);
            session()->put('status', 'Virtualizor Terminated Successfully.');
  				} else {
  					self::setStatus($order, false, $virtualizor->getError());
            session()->put('error', $virtualizor->getError());
  				}

  				$order->save();
  				break;
        case 'updateHostname':
          $success = $virtualizor->updateHostname($vpsid, $request);
          if($success['done']['done'])
          {
              $orderSetting = Order_Settings::where('order_id', $order->id)
                                              ->where('setting_name', 'virtualizor.hostname')
                                              ->first();
              if($orderSetting)
              {
                  $orderSetting->setting_value = $success['vs_info']['hostname'];
                  $orderSetting->save();
              }
              session()->put('status', 'Hostname Updated Successfully.');
          }
          else
          {
              session()->put('error', 'Hostname Update Error.');
          }
        case 'changePassword':
            $success = $virtualizor->updatePassword($vpsid, $request);
            if($success['done']['done'])
            {
                $orderSetting = Order_Settings::where('order_id', $order->id)
                                                ->where('setting_name', 'virtualizor.password')
                                                ->first();
                if($orderSetting)
                {
                    $orderSetting->setting_value = $request->new_password;
                    $orderSetting->save();
                }

                session()->put('status', 'Password Changed Successfully.');
            }
            else
            {
                session()->put('error', 'Password Change Error.');
            }
        break;
        case 'enableRescue':
            $success = $virtualizor->enableRescue($vpsid, $request);
            if($success['done']['msg'])
            {
                $orderSetting = Order_Settings::where('order_id', $order->id)
                                                ->where('setting_name', 'virtualizor.enable_rescue')
                                                ->first();

                if(!$orderSetting) $orderSetting = new Order_Settings();
                $orderSetting->order_id = $order->id;
                $orderSetting->setting_name = 'virtualizor.enable_rescue';
                $orderSetting->setting_value = 1;
                $orderSetting->save();
            }
        break;
        case 'reinstall':
            $success = $virtualizor->reinstall($vpsid, $request);

            if($success['done'] == '1')
            {
              $orderSetting = Order_Settings::where('order_id', $order->id)
                                              ->where('setting_name', 'virtualizor.os')
                                              ->first();

              if(!$orderSetting) $orderSetting = new Order_Settings();
              $orderSetting->order_id = $order->id;
              $orderSetting->setting_name = 'virtualizor.os';
              $orderSetting->setting_value = $request->osid;
              $orderSetting->save();

              $orderSetting = Order_Settings::where('order_id', $order->id)
                                              ->where('setting_name', 'virtualizor.password')
                                              ->first();

              if(!$orderSetting) $orderSetting = new Order_Settings();
              $orderSetting->order_id = $order->id;
              $orderSetting->setting_name = 'virtualizor.password';
              $orderSetting->setting_value = $request->new_password;
              $orderSetting->save();

              session()->put('status', 'Reinstall Done Successfully.');
            }
            else
            {
                session()->put('error', 'Reinstall Error.');
            }
        break;
        case 'create_from_view':
  				$success = $virtualizor->create(
            $request->hostname,
  					$request->osid,
            $request->hostname,
  					$request->new_password,
  					$order->customer->email,
  					$package,
  					true,
            $order,
            true
  				);

  				if ($success) {
  					$order->status = Order::SETUP;
  					self::setStatus($order, self::CREATED);
            session()->put('status', 'Virtualizor Created Successfully.');
  				} else {
  					$order->status = Order::PENDING;
  					self::setStatus($order, self::ERRORED, $virtualizor->getError());
            session()->put('error', $virtualizor->getError());
  				}

  				$order->save();
		      break;
        case 'stop':
          $success = $virtualizor->stop($vpsid, $request);

          if ($success) {
            session()->put('status', 'Virtualizor Stopped Successfully.');
          } else {
            session()->put('error', $virtualizor->getError());
          }
          break;
        case 'restart':
          $success = $virtualizor->restart($vpsid, $request);

          if ($success) {
            session()->put('status', 'Virtualizor Restarted Successfully.');
          } else {
            session()->put('error', $virtualizor->getError());
          }
          break;
        case 'poweroff':
          $success = $virtualizor->poweroff($vpsid, $request);

          if ($success) {
            session()->put('status', 'Virtualizor Has Been Poweroff Successfully.');
          } else {
            session()->put('error', $virtualizor->getError());
          }
          break;
  			default:
  				throw new \Exception('Unknown command');
  		}
  		// return true to delete the job from the job queue.
  		return true;
  	}

    public function create($ip, $os, $hostname, $password, $email, $package, $notify = true, $order, $from_order_view = false)
  	{
        //get storage
        $page = 1;
        $reslen = 50;
        $postStorage = array();
        $postStorage['name'] = '';
        $postStorage['path'] = '';

        $storages = $this->api->storages($postStorage, $page, $reslen);
        $st_uuids = [];
        foreach($storages['storage'] as $storage)
        {
            $st_uuids[] = $storage['st_uuid'];
        }

        $arrSpace = [];
        foreach($st_uuids as $st_uuid)
        {
            $spaceDetails['size'] = 2;
            $spaceDetails['st_uuid'] = $st_uuid;
            $arrSpace[] = $spaceDetails;
        }

        //get Plans
        $page = 1;
        $reslen = 20;
        $post = array();

        $plans = $this->api->plans($page,$reslen,$post);
        $ram = 1024;
        $bandwidth = 0;
        $cores = 1;
        $virt = 1;
        $osId = 1;
        foreach($plans['plans'] as $plan)
        {
            if($plan['plid'] == $package)
            {
                $ram = $plan['ram'];
                $bandwidth = $plan['bandwidth'];
                $cores = $plan['cores'];
                $virt = $plan['virt'];
                $osId = $plan['osid'];
                break;
            }
        }

        if($from_order_view) $osId = $os;

        //get IPs
        $page = 1;
        $reslen = 20;

        $ips = $this->api->ips($page,$reslen);
        $arrayIp = [];
        foreach($ips['ips'] as $ip)
        {
            if($ip['vpsid'] == '0' && $ip['locked'] == '0')
            {
                $arrayIp[] = $ip['ip'];
                break;
            }
        }

        //create VPS
    		$success = $this->api->addvs_v2($details = [
    			'serid' => '0',
    			'virt' => $virt,
    			'user_email' => $email,
    			'user_pass' => $password,
    			'osid' => $osId,
          'ip' => $ip,
    			'hostname' => $hostname,
    			'rootpass' => $password,
    			'ips' => $arrayIp,
          'space' => $arrSpace,
          'ram' => $ram,
          'bandwidth' => $bandwidth,
          'cores' => $cores
    		]);

    		if ($success['error'])
        {
            foreach($success['error'] as $err)
            {
                if($this->lastError != '')
                {
                    $this->lastError .= '<br/>';
                }
                $this->lastError .= $err;
            }
      			return false;
    		}

        $orderSetting = Order_Settings::where('order_id', $order->id)
                                        ->where('setting_name', 'virtualizor.vpsid')
                                        ->first();

        if(!$orderSetting)
        {
            $order->settings()->create([
                'setting_name' => 'virtualizor.vpsid',
                'setting_value' => $success['vs_info']['vpsid']
            ]);
        }
        else
        {
            $orderSetting->setting_value = $success['vs_info']['vpsid'];
            $orderSetting->save();
        }

        $orderSetting = Order_Settings::where('order_id', $order->id)
                                        ->where('setting_name', 'virtualizor.hostname')
                                        ->first();

        if(!$orderSetting)
        {
            $order->settings()->create([
                'setting_name' => 'virtualizor.hostname',
                'setting_value' => $success['vs_info']['hostname']
            ]);
        }
        else
        {
            $orderSetting->setting_value = $success['vs_info']['hostname'];
            $orderSetting->save();
        }

        $orderSetting = Order_Settings::where('order_id', $order->id)
                                        ->where('setting_name', 'virtualizor.os')
                                        ->first();

        if(!$orderSetting)
        {
            $order->settings()->create([
                'setting_name' => 'virtualizor.os',
                'setting_value' => $success['vs_info']['osid']
            ]);
        }
        else
        {
            $orderSetting->setting_value = $success['vs_info']['osid'];
            $orderSetting->save();
        }

        $orderSetting = Order_Settings::where('order_id', $order->id)
                                        ->where('setting_name', 'virtualizor.password')
                                        ->first();

        if(!$orderSetting)
        {
            $order->settings()->create([
                'setting_name' => 'virtualizor.os',
                'setting_value' => $success['vs_info']['pass']
            ]);
        }
        else
        {
            $orderSetting->setting_value = $success['vs_info']['pass'];
            $orderSetting->save();
        }

    		if ($notify) {
    			$order = $this->order;

    			$user = User::find($this->order->user_id);

    			$userFrom = User::find($this->order->user->id);
    			$userTo = $this->order->customer;
    			$subject = 'Your Virtualizor Details';

    			$view = 'Integrations.virtualizorEmail';

    			$site = Settings::getAsUser($this->order->user_id, 'site');

    			Mail::to($userTo)->send(new VirtualizorNotificationEmail($userFrom, $subject, $details, $view));
    		}

    		return true;
  	}

    private static function setStatus(\App\Order $order, $status, $error = '')
  	{
  		$updates = [
  			'status' => $order->settings()->where('setting_name', 'virtualizor.status')->firstOrFail(),
  			'error' => $order->settings()->where('setting_name', 'virtualizor.error')->firstOrFail()
  		];

  		if (is_int($status)) {
  			$updates['status']->setting_value = $status;
  		}

  		$updates['error']->setting_value = $error;

  		$order->settings()->saveMany($updates);
  	}

    public function checkConnection()
    {
        $page = 1;
        $reslen = 20;
        $post['test'] = 1;

        $output = $this->api->pdns($page, $reslen, $post);
        if($output) return true;

        $this->lastError = trans('backend.da-connection-fail');
        return false;
    }
}
