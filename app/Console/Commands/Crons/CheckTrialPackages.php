<?php

namespace App\Console\Commands\Crons;

use App\Site;
use App\Order;
use Illuminate\Console\Command;

class CheckTrialPackages extends Command
{
  /**
  * The name and signature of the console command.
  *
  * @var string
  */
  protected $signature = 'BS:cron:CheckTrialPackages {--server=}';

  /**
  * The console command description.
  *
  * @var string
  */
  protected $description = 'Checks Trial Packages within BillingServ';

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
  * @return mixed
  */
  public function handle()
  {
    /*
    * Get All orders where trial is 1
    */
    $sites = Site::all();

    foreach($sites as $site)
    {
      if($site->server_region_id == $this->option('server'))
      {
        $this->info('Site : ' . $site->domain);
        $site->setDatabase();

        $connection = \DB::reconnect('site');
  			\DB::setDefaultConnection('site');

        $orders = Order::where('trial_order', 1)
                          ->where('trial_expire_date', date('Y-m-d'))
                          ->where('trial_expire_time','<=', date('H:i:s'))
                          ->get();

        foreach($orders as $order)
        {
          $order->update([
            'trial_order' => 0,
            'trial_expire_date' => null,
            'trial_expire_time' => null,
          ]);
          $this->info('User ID : '.$order->user_id.' Trial Removed');
        }
      }
    }
  }
}
