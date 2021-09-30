<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\makeController::class,
        Commands\Install::class,
        Commands\Crons\ReoccuringOrders::class,
        Commands\Migrate::class,
        Commands\MigrateRevert::class,
        Commands\Crons\CheckTrialPackages::class,
        Commands\Crons\InvoiceAutoPay::class,
        Commands\Crons\MigrateSitesOnly::class,
        Commands\Crons\CheckInvoicePaid::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('BS:cron:reoccuringOrders')
                 ->daily()
                 ->appendOutputTo('/home/dev/public_html/storage/logs/cronLog');

        $schedule->command('BS:cron:InvoiceAutoPay')
                 ->daily()
                 ->appendOutputTo('/home/dev/public_html/storage/logs/cronLog');

        $schedule->command('BS:cron:subscriptions')
                 ->daily()
                 ->appendOutputTo('/home/dev/public_html/storage/logs/cronLog');

        $schedule->command('BS:cron:updateInvoicesAsLate')
                 ->daily()
                 ->appendOutputTo('/home/dev/public_html/storage/logs/cronLog');

        $schedule->command('BS:cron:MigrateSitesOnly')
                ->daily()
                ->appendOutputTo('/home/dev/public_html/storage/logs/cronLog');

        $schedule->command('BS:cron:CheckInvoicePaid')
            ->everyTenMinutes()
            ->appendOutputTo('/home/dev/public_html/storage/logs/cronLog');
    }
}
