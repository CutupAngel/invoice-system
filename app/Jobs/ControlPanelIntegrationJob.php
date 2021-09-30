<?php

namespace App\Jobs;

use Integrations;

use App\Order;
use App\Jobs\Job;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ControlPanelIntegrationJob extends Job implements ShouldQueue
{
    use InteractsWithQueue;

    private $order;
    private $command;
    private $request;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $order, string $command, Request $request = null)
    {
        $this->order = $order;
        $this->command = $command;
        $this->request = $request;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
      //try {
            $order = Order::findOrfail($this->order);
            $success = Integrations::get($order->integration, 'queueHandler', [$this->command, $order, $this->request]);
            if ($success) {
                $this->delete();
            }
    //  } catch (\GuzzleHttp\Exception\ConnectException $e) {
            // This job had issues connecting, lets not flood cpanel.
            // Wait 60 seconds for the next attempt.
        //$this->release(60);
      //}
    }
}
