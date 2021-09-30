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

class UpdateInvoicesAsLate extends Command
{
    protected $signature = 'BS:cron:updateInvoicesAsLate {--server=}';
    protected $description = 'Updates Invoices to Late status.';

	public function __construct()
	{
		parent::__construct();
	}

    public function handle()
    {
		$sites = Site::all();
		//$this->info("Running Reoccuring Orders " . date('Y-m-d h:m:s'));

		foreach($sites as $k=>$site)
		{
      if($site->server_region_id == $this->option('server'))
      {
			$this->info("Site:" . $site->domain);
			$site->setDatabase();

			$connection = \DB::reconnect('site');
			\DB::setDefaultConnection('site');

			Invoice::where('updated_at','<=',date("Y-m-d H:i:s"))->where('status',0)->update(['status'=>2]);
      }
    }
  }
}
