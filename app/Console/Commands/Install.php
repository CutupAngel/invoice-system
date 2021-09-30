<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use DB;

use App\Site;
use App\User;
use App\User_Setting;

class Install extends Command
{
	protected $name = "install";
	protected $description = "Run install scripts after .env has been setup.";

	public function fire()
	{
		$this->line('Inital BillingServ Setup');
		$domain = $this->ask('Domain');
		$siteName = $this->ask('Site Name');
		$name = $this->ask('Name');
		$username = $this->ask('Username');
		$password = $this->secret('Password (Will Not Display)');
		$email = $this->ask('Email Address');

		$this->call('migrate', ['--type' => 'main']);

		$dbDomain = str_replace(".", "_", $domain);
		$dbPassword = str_random(12);

		$this->info('Creating Site Database');
		DB::statement(
			"CREATE USER '{$dbDomain}'@'localhost' IDENTIFIED BY '{$dbPassword}'"
		);

		DB::statement(
			"CREATE DATABASE `site_{$dbDomain}`"
		);

		DB::statement(
			"GRANT ALL ON `site_{$dbDomain}`.* TO '{$dbDomain}'@'localhost'"
		);

		DB::statement(
			"GRANT SELECT ON main.countries TO '{$dbDomain}'@'localhost'"
		);

		DB::statement(
			"GRANT SELECT ON main.counties TO '{$dbDomain}'@'localhost'"
		);

		DB::statement(
			"GRANT SELECT ON main.currencies TO '{$dbDomain}'@'localhost'"
		);

		DB::statement(
			"GRANT SELECT ON main.billingserv_plans TO '{$dbDomain}'@'localhost'"
		);

		DB::statement(
			"GRANT SELECT ON main.billingserv_plan_cycles TO '{$dbDomain}'@'localhost'"
		);

		$this->info('Saving Database Information');
		$site = new Site();
		$site->domain = $domain;
		$site->database_host = 'localhost';
		$site->database_name = "site_{$dbDomain}";
		$site->database_username = $dbDomain;
		$site->database_password = $dbPassword;
		$site->save();

		$this->call('migrate', ['--site' => $domain]);

		$this->info('Creating User');
		$user = new User();
		$user->name = $name;
		$user->username = $username;
		$user->password = bcrypt($password);
		$user->email = $email;
		$user->save();

		$user->settings()->create([
			'name' => 'site.name',
			'value' => $siteName
		]);

		$this->info('BillingServ is set up.');
	}
}
