<?php

namespace App\Http\Controllers;

use AppCountries;
use App\User;

use Auth;
use DB;
use Illuminate\Http\Request;

class CommonHelperController extends Controller
{
	public function getCounties($country)
	{
		try {
			$counties = Countries::findOrFail($country)->counties;

			return json_encode($counties);
		} catch (\Exception $e) {
			return '';
		}
	}

	public function postCustomerInfo(Request $request)
	{
		try {
			return json_encode(
				Auth::User()->customers->find($request->input('id'))
					->mailingContact->address
					->load('county')
					->load('country')
			);
		} catch (\Exception $e) {
			return '';
		}
	}

	public function getCustomers()
	{
		return Auth::User()->customers->toJSON();
	}

	public function getSearch(Request $request)
	{
		$q = $request->input('q');

		$email = false;
		if (strpos($q, '@') !== -1) {
			$email = true;
		}

		$userSearch = DB::table('users')
						->select([
							'id',
							'name',
							'username',
							'email',
							'account_type',
							DB::raw("
								CASE WHEN id = ? THEN 1
									ELSE MATCH (name, username, email) AGAINST (? IN BOOLEAN MODE)
								END as score")
						])->where('account_type', '!=', '1')
						->where(function($query) use ($q) {
							$query->where('id', $q);
							$query->orWhereRaw('MATCH(name, username, email) AGAINST(? IN BOOLEAN MODE)');
						})->setBindings([
							intval($q),
							!$email ? $q . '*' : "\"$q\"",
							'1',
							intval($q),
							!$email ? $q . '*' : "\"$q\"",
						])->orderBy('score', 'desc')
						->get();

		$invoiceSearch = [];
		if (!$email) {
			$invoiceSearch = DB::table('invoices')
								->join('users', 'users.id', '=', 'invoices.user_id')
								->select('invoices.id', 'invoices.invoice_number', 'users.name', 'invoices.total', 'invoices.status', 'invoices.due_at')
								->where('invoices.id', intval($q))
								->orWhere('invoices.invoice_number', $q)
								->get();
		}

		$results = [];
		foreach ($userSearch as $user) {
			if (isset($results["u{$user->id}"])) {
				continue;
			}

			$url = '';
			$type = '';
			switch ($user->account_type) {
				case '1':
					$type = 'You';
					$url = '/settings/my-account';
					break;
				case '2':
					$type = "Customer: {$user->id}";
					$url = "/customers/{$user->id}";
					break;
				case '3':
					$type = "Staff: {$user->id}";
					$url = "/settings/staff/{$user->id}/edit";
					break;
				default:
					continue 2;
			}

			$results["u{$user->id}"] = [
				'url' => $url,
				'text' => "{$type} <br>Name: {$user->name}<br>Username: {$user->username}<br>Email: {$user->email}"
			];
		}

		foreach ($invoiceSearch as $invoice) {
			if (isset($results["i{$invoice->id}"])) {
				continue;
			}

			$results["i{$invoice->id}"] = [
				'url' => "/invoices/{$invoice->id}",
				'text' => "Invoice: {$invoice->invoice_number}<br>Customer: {$invoice->name} <br> Total: {$invoice->total} &bull; Status: {$invoice->status} <br> Due: {$invoice->due_at}"
			];
		}

		return json_encode($results);
	}
}
