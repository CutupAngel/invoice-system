<?php

namespace App\Console\Commands\Crons;

use Auth;
use Invoices;
use Settings;

use App\Address;
use App\Currency;
use App\Site;
use App\Invoice;
use App\InvoiceItem;
use App\Package;
use App\Package_Cycle;
use App\Order;
use App\Order_Options;
use App\User;
use App\Modules\Checkout\Controllers\PaymentController;

use Illuminate\Console\Command;

class MigrateSitesOnly extends Command
{
    //protected $name = 'BS:cron:MigrateSitesOnly';
    protected $signature = 'BS:cron:MigrateSitesOnly {--server=}';
    protected $description = 'Run migration only for all site_* only.';

	public function __construct()
	{
		parent::__construct();
	}

  public function handle()
  {
    $sites = Site::all();

    foreach($sites as $site)
    {
        if($site->server_region_id == $this->option('server'))
        {
            $this->info('Site : ' . $site->domain);
            $site->setDatabase();

            $connection = \DB::reconnect('site');
      			\DB::setDefaultConnection('site');
    				$output = shell_exec("php /home/public_html/artisan migrate:site --site '" . $site->domain . "' --force Y");
    				$this->info($output);
        }
		}
  }
}
