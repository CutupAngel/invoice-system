<?php

namespace App\Console\Commands;

use App\Site;
use Config;
use \Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class Migrate extends Command
{
	private $database;

	protected $name = 'migrate:site';

	public function handle()
	{
		$this->info('Running Migrations for site databases.');
		$this->database = 'site';

		$site = $this->input->getOption('site');

		$args = [];
		if ($this->input->hasOption('force')) {
			$args = ['--force' => 'Y'];
		}

		$sites = [];
		if (is_null($site)) {
			$sites = Site::all();
		} else {
			$sites[] = Site::where('domain', $site)->first();
		}

		foreach ($sites as $site) {
			\DB::purge('site');
			$site->setDatabase();
			$connection = \DB::reconnect('site');
			\DB::setDefaultConnection('site');
			$this->info('Migrating '.$site->domain.'...');
			try{
				\Artisan::call('migrate', $args);
				//$this->call('migrate', $args);
			}
			catch(Exception $e)
			{
				$this->info($e->getMessage());
			}
		}

		$this->info('Completed Migrations for site databases.');
	}

	/**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['site', null, InputOption::VALUE_REQUIRED, 'Which site to perform the migration on. [(domain)]'],
            ['force', null, InputOption::VALUE_OPTIONAL, 'Force the operation to run when in production.']
        ];
    }
}
