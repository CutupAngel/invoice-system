<?php

namespace App\Console\Commands\Crons;

use App\Integrations\DirectAdmin;
use App\Integrations\CPanel;
use App\Integration;
use App\IntegrationCpanel;
use App\Order;
use App\Site;
use App\Invoice;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDOException;

class CheckInvoicePaid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'BS:cron:CheckInvoicePaid {--server=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check Invoice Paid';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $sites = Site::all();

        foreach ($sites as $site) {

          if($site->server_region_id == $this->option('server'))
          {
            $this->info("Site: {$site->domain}");
            try {
                $site->setDatabase();
                DB::reconnect('site');
                DB::setDefaultConnection('site');
            } catch (PDOException $e) {
                $this->line('Connection failed on: ' . $site->domain);
                continue;
            }

            Invoice::where('status', Invoice::PAID)
                ->get()
                ->each(function (Invoice $invoice) {
                    if(isset($invoice->order->integration))
                    {
                        if (($invoice->order->integration == 'directadmin') && ($invoice->order->status == Order::SUSPENDED))
                        {
                            $this->autoUnSuspend($invoice->order);
                            $this->line("UnSuspend orderID: {$invoice->order->id}");
                        }
                    }
                });

            Invoice::where('status', Invoice::PAID)
                ->get()
                ->each(function (Invoice $invoice) {
                    if(isset($invoice->order->integration))
                    {
                        if (($invoice->order->integration == 'cpanel') && ($invoice->order->status == Order::SUSPENDED))
                        {
                            $this->autoUnSuspend($invoice->order);
                            $this->line("UnSuspend orderID: {$invoice->order->id}");
                        }
                    }
                });
        }
    }
  }

    /**
     * @param Order $order
     */
    public function autoUnSuspend(Order $order)
    {
        $orderSettings = $order
            ->settings
            ->pluck('setting_value', 'setting_name')
            ->all();

        $server = $orderSettings['directadmin.server'];
        $username = $orderSettings['directadmin.username'];

        // Get the DirectAdmin server settings
        $integration = Integration::find($server);
        $port = $integration->port ?? 2222;
        $host = $integration->https ? 'https://' : 'http://';
        $host .= $integration->hostname . ':' . $port;

        $directAdmin = new DirectAdmin($host, $integration->username, $integration->password);
        $success = $directAdmin->unsuspend($username);
        if ($success) {
            $order->status = Order::SETUP;
            $order->save();
        }
    }

    public function autoUnSuspendCpanel(Order $order)
    {
        $orderSettings = $order
            ->settings
            ->pluck('setting_value', 'setting_name')
            ->all();

        $server = $orderSettings['cpanel.server'];
        $username = $orderSettings['cpanel.username'];
        $password = $orderSettings['cpanel.password'];

        // Get the cPanel server settings
        $integrationCpanel = IntegrationCpanel::find($server);
        $cpanelHost = $integrationCpanel->https ? 'https://' : 'http://';
        $cpanelHost .= $integrationCpanel->hostname . ':' . $integrationCpanel->port;

        $cpanel = new CPanel($cpanelHost, $integrationCpanel->username, $integrationCpanel->access_key);
        $success = $cpanel->unsuspend($username);
        if ($success) {
            $order->status = Order::SETUP;
            $order->save();
        }
    }
}
