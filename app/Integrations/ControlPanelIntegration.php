<?php

namespace App\Integrations;

abstract class ControlPanelIntegration extends Integration
{
	/**
	 * See Integration interface for the required heading.
	*/

	const TYPETITLE = 'Hosting Control Panels';

	/**
	 * The construct needs to handle passed configuration deatils but also auto pull settings.
	 *
	 * @param string $host     The host to connect to (false to autopull)
	 * @param string $username The username to use with the connection (false to autopull)
	 * @param string $key      The password or key to use to authenication (false to autopull)
	 */
	abstract public function __construct($host = false, $username = false, $key = false);

	/**
	 * Set the order variable
	 * @param AppOrder $order The current order being process
	 */
	abstract public function setOrder(\App\Order $order);

	/**
	 * Retriving the form to use on the package create/edit page.
	 * @param \Illuminate\Http\Request $request This is passed by App\Modules\Orders\Controllers\Client@getPackage
	 * @return view()
	 */
	abstract public static function getPackageForm(\Illuminate\Http\Request $request);

	/**
	 * Save information from the package form.
	 * @param \Illuminate\Http\Request $request This is passed by App\Modules\Orders\Controllers\Client@savePackage
	 * @param \App\Package $package This is the package model.
	 * @return null
	 */
	abstract public static function savePackageDetails(\Illuminate\Http\Request $request, \App\Package $package);

	/**
	 * Retriving the form to use on the order page.
	 * @return view()
	 */
	abstract public static function getOrderForm(\App\Package $package);

	/**
	 * Save the information from the order form.
	 * @param \Illuminate\Http\Request $request This is passed by App\Modules\Common\Controllers\Main@postAddToCart
	 * @return null
	 */
	abstract public static function saveOrderForm(\Illuminate\Http\Request $request);

	/**
	 * Complete the order and perfrom the next action (i.e. create account)
	 * @param \App\Order $order
	 * @return null
	 */
	abstract public static function completeOrder(\App\Order $order);

	/**
	 * Get the view file for viewing the order from the customer in the admin panel.
	 * The view *must* extend Orders::viewOrder
	 *
	 * @param  AppOrder $order The current order being viewed.
	 * @return view()
	 */
	abstract public static function getOrderView(\App\Order $order);

	/**
	 * The job queue calls this command with the action assigned to it.
	 *
	 * @param  string   $command Action assigned to the job.
	 * @param  AppOrder $order   Order the action is for.
	 * @return boolean (true to remove)
	 */
	abstract public static function queueHandler(string $command, \App\Order $order);

	/**
	 * Create account in control panel.
	 * @param string $username Username for new account
	 * @param string $password Password for new account
	 * @param string $email Email address for new account
	 * @param string $domain Domain to assign for new account
	 * @param string $package The package to assign for the new account
	 * @param string $ip IP to assign to account or shared for a shared ip.
	 * @param bool $notify true to send an email notification. (Must be implementented if not an option via API)
	 * @return bool
	 */
	abstract public function create($username, $password, $email, $domain, $package, $ip, $nameserver_1, $nameserver_2, $nameserver_3, $nameserver_4, $notify = false);

	abstract public function resetPassword($username, $password);

	abstract public function suspend($username, $reason = '');

	abstract public function unsuspend($username);

	abstract public function terminate($username);
}
