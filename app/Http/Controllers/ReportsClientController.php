<?php

namespace App\Http\Controllers;

use Auth;
use DateTime;
use DB;
use Permissions;

use App\Http\Controllers\Controller;
use App\Login_History;
use App\Invoice;
use App\Package;
use App\Package_Cycle;
use App\Order;
use App\User;

class ReportsClientController extends Controller
{
	public function __construct()
	{
		if (!Permissions::has('reports')) {
			//throw new Permissions::$exception;
			return redirect(route('login'));
		}
	}

	public function loginHistory()
	{
		$sessionLogout = intval(Config('session.lifetime'));

		// Populate all of the logins that never got a logout time.
		DB::update(
			"UPDATE login_history lh
			 LEFT JOIN (
				SELECT al.created_at, al.login_id
				FROM activity_log al
				ORDER BY al.created_at DESC
			 ) al ON al.login_id = lh.id
			 SET logout = coalesce(al.created_at, lh.created_at) + INTERVAL {$sessionLogout} MINUTE
			 WHERE logout is null
				AND username = :username
				AND lh.id != :current
				AND lh.created_at + INTERVAL {$sessionLogout} MINUTE < NOW()",
			[
				'username' => Auth::User()->username,
				'current' => Auth::User()->currentLogin->id
			]
		);

		$history = Login_History::where('username', Auth::User()->username)->get();
		return view('Reports.loginHistory', ['history' => $history]);
	}

	public function debtSheet()
	{
		$values = [
			'reportTitle' => 'Debt Sheet',
			'columns' => [
				'Name',
				'Phone',
				'Email',
				'Overdue',
				'Total'
			],
			'records' => []
		];

		$customers = array_values(Auth::User()->getCustomerIds()->all());

		$records = Auth::User()->invoices->where('status', Invoice::OVERDUE);
		foreach ($records as $record) {
			$values['records'][] = [
				'name' => $record->name,
				'phone' => $record->mailingContact->address->phone,
				'email' => $record->email,
				'overdue' => $record->invoices->where('status', Invoice::OVERDUE)->count(),
				'total' => number_format($record->invoices->where('status', Invoice::OVERDUE)->sum('total'), 2)
			];
		}

		return view('Reports.genericReport', $values);
	}

	public function customerInvoiceReport()
	{
		$values = [
			'reportTitle' => 'Customer Invoice Report',
			'columns' => [
				'Date',
				'Invoice',
				'Customer',
				'Tax',
				'Net',
				'Gross',
				'Payment Type'
			],
			'records' => []
		];

		$invoices = Auth::User()->invoices;
		foreach ($invoices as $invoice) {
			$values['records'][] = [
				'date' => $invoice->created_at,
				'invoice' => $invoice->invoice_number,
				'customer' => $invoice->customer->name,
				'tax' => '0.00',
				'net' => number_format($invoice->total, 2),
				'gross' => number_format($invoice->total, 2),
				'payment' => 'N/A'
			];
		}

		return view('Reports.genericReport', $values);
	}

	public function revenueTrend()
	{
		$values = [
			'reportTitle' => 'Revenue Trend',
			'columns' => [
				'Month'
			],
			'records' => [],
			'paging' => 'false',
			'searching' => 'false',
			'ordering' => 'false'
		];

		for ($m = 1; $m <= 12; $m++) {
			$values['records'][$m] = [
				'month' => DateTime::createFromFormat('!m', $m)->format('F')
			];
		}

		for ($i = 4; $i >= 0; $i--) {
			$year = date('Y', strtotime("-{$i} years"));
			$values['columns'][] = $year;

			foreach ($values['records'] as $month => $array) {
				$startDate = "{$year}-{$month}-01";
				$endDate = DateTime::createFromFormat('Y-m-d', $startDate)->format('Y-m-t');

				$values['records'][$month][$year] = number_format(
					Invoice::where('user_id', Auth::User()->id)
						->whereBetween('due_at', [$startDate, $endDate])
						->sum('total'),
					2
				);
			}
		}

		return view('Reports.genericReport', $values);
	}

	public function customerTrend()
	{
		$values = [
			'reportTitle' => 'Customer Trend',
			'columns' => [
				'Month'
			],
			'records' => []
		];

		for ($m = 1; $m <= 12; $m++) {
			$values['records'][$m] = [
				'month' => DateTime::createFromFormat('!m', $m)->format('F')
			];
		}

		for ($i = 4; $i >= 0; $i--) {
			$year = date('Y', strtotime("-{$i} years"));
			$values['columns'][] = $year;

			foreach ($values['records'] as $month => $array) {
				$startDate = "{$year}-{$month}-01";
				$endDate = DateTime::createFromFormat('Y-m-d', $startDate)->format('Y-m-t');

				$values['records'][$month][$year] = DB::table('users')
					->join('user_link', 'users.id', '=', 'user_link.user_id')
					->where('users.account_type', User::CUSTOMER)
					->where('user_link.parent_id', Auth::User()->id)
					->whereBetween('users.created_at', [$startDate, $endDate])
					->count();
			}
		}

		return view('Reports.genericReport', $values);
	}

	public function packageLeaderboard()
	{
		$values = [
			'reportTitle' => 'Package Leaderboard',
			'columns' => [
				'Package Name',
				'One-Off',
				'Daily',
				'Weekly',
				'Fortnightly',
				'Monthly',
				'2 Months',
				'3 Months',
				'4 Months',
				'5 Months',
				'6 Months',
				'7 Months',
				'8 Months',
				'9 Months',
				'10 Months',
				'11 Months',
				'12 Months',
				'24 Months',
				'36 Months'
			],
			'records' => []
		];

		$packages = Package::whereHas('group', function ($q) {
			$q->where('user_id', Auth::User()->id);
		})->get();

		foreach ($packages as $package) {
			$record = [
				$package->name,
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A',
				'N/A'
			];

			foreach ($package->cycles as $cycle) {
				$record[$cycle->cycle] = Order::where('cycle_id', $cycle->id)
					->where('package_id', $package->id)
					->count();
			}

			$values['records'][] = $record;
		}

		return view('Reports.genericReport', $values);
	}

	public function customerCredit()
	{
		$values = [
			'reportTitle' => 'Customer Credit Report',
			'columns' => [
				'Customer',
				'Credit'
			],
			'records' => []
		];

		$customers = Auth::User()->customers;

		foreach ($customers as $customer) {
			$values['records'][] = [
				$customer->name,
				number_format($customer->getCredit()->value, 2)
			];
		}

		return view('Reports.genericReport', $values);
	}

	public function annualSales()
	{
		$values = [
			'reportTitle' => 'Annual Sales',
			'columns' => [
				'Cycle',
				'Active Orders',
				'Monthly Income',
				'Annual Income',
			],
			'records' => [],
			'ordering' => false,
			'paging' => false
		];

		$cycleTerms = [
			'One-Off',
			'Daily',
			'Weekly',
			'Fortnightly',
			'Monthly',
			'2 Months',
			'3 Months',
			'4 Months',
			'5 Months',
			'6 Months',
			'7 Months',
			'8 Months',
			'9 Months',
			'10 Months',
			'11 Months',
			'12 Months',
			'24 Months',
			'36 Months'
		];

		for ($i = 0; $i <= 17; $i++) {
			$record = [
				'cycle' => $cycleTerms[$i],
				'active' => 0,
				'monthy' => 0.00,
				'yearly' => 0.00
			];

			$cycles = Package_Cycle::where('cycle', $i);
			foreach ($cycles as $cycle) {
				$record['active'] += $cycle->activeOrders->count();
				$record['monthy'] += floatval($cycle->price * $cycle->activeOrders->count());
				$record['yearly'] += floatval($cycle->price * $cycle->activeOrders->count() * 12);
			}

			$values['records'][] = $record;
		}

		return view('Reports.genericReport', $values);
	}

	public function customerReceipts()
	{
		$values = [
			'reportTitle' => 'Customer Receipt Report',
			'columns' => [
				'Date',
				'Invoice #',
				'Customer',
				'Gross',
				'Payment Type',
				'Authorization ID'
			],
			'records' => []
		];

		foreach (Auth::User()->invoices->where('status', Invoice::PAID) as $invoice) {
			$values['records'][] = [
				$invoice->due_at,
				$invoice->invoice_number,
				$invoice->customer->name,
				$invoice->total,
				'',
				''
			];
		}

		return view('Reports.genericReport', $values);
	}

	public function salesByStaff($type = 'monthly', $date = null)
	{
		if ($date === null) {
			$date = date('Y-m-d');
		}

		$values = [
			'report' => 'Staff',
			'type' => ucwords($type),
			'columns' => [
				'Staff Member'
			],
			'actualDate' => $date,
			'records' => [],
			'paging' => 'false',
			'searching' => 'false',
			'ordering' => 'false'
		];

		$staffMembers = Auth::User()->staff;
		$staffMembers[] = Auth::User();
		foreach ($staffMembers as $staff) {
			$values['records'][$staff->id] = [
				'name' => $staff->name
			];
		}

		switch (strtolower($type))
		{
			case 'yearly':
				$values['date'] = "Last 10 years from ". date('Y', strtotime($date));
				for ($i = 10; $i >= 0; $i--) {
					$year = date('Y', strtotime("-{$i} years", strtotime($date)));
					$values['columns'][] = $year;

					foreach ($staffMembers as $staff) {
						$values['records'][$staff->id][$i] = Invoice::where('user_id', $staff->id)
							->whereBetween('created_at', ["{$year}-01-01 00:00:00", "{$year}-12-31 23:59:59"])
							->count();
					}
				}
				break;
			case 'daily':
				$date = DateTime::createFromFormat('Y-m-d', $date);
				$values['date'] = $date->format('F Y');

				$yearMonth = $date->format('Y-m-');
				$days = $date->format('t');
				for ($i = 1; $i <= $days; $i++) {
					$values['columns'][] = $i;

					foreach ($staffMembers as $staff) {
						$values['records'][$staff->id][$i] = Invoice::where('user_id', $staff->id)
							->whereBetween('created_at', ["{$yearMonth}{$i} 00:00:00", "{$yearMonth}{$i} 23:59:59"])
							->count();
					}
				}
				break;
			case 'monthly':
			default:
				$year = date('Y', strtotime($date));
				$values['date'] = $year;
				for ($i = 1; $i <= 12; $i++) {
					$values['columns'][] = DateTime::createFromFormat('!m', $i)->format('F');
					$startDate = Date("{$year}-{$i}-01");
					$endDate = DateTime::createFromFormat('Y-m-d', $startDate)->format('Y-m-t');

					foreach ($staffMembers as $staff) {
						$values['records'][$staff->id][$i] = Invoice::where('user_id', $staff->id)
							->whereBetween('created_at', [$startDate, $endDate])
							->count();
					}
				}
		}

		return view('Reports.salesReports', $values);
	}

	public function salesByCustomers($type = 'monthly', $date = null)
	{
		if ($date === null) {
			$date = date('Y-m-d');
		}

		if($date != null)
		{
				$dateArr = explode('-', $date);
				if(!checkdate($dateArr[1], $dateArr[2], $dateArr[0]))
				{
						$date = date('Y-m-d');
				}
		}

		if(date('Y', strtotime($date)) < 1970)
		{
				$date = date('Y-m-d');
		}

		$values = [
			'report' => 'Customer',
			'type' => ucwords($type),
			'columns' => [
				'Customer'
			],
			'actualDate' => $date,
			'records' => []
		];

		$customers = Auth::User()->customers;
		foreach ($customers as $customer) {
			$values['records'][$customer->id] = [
				'name' => $customer->name
			];
		}

		switch (strtolower($type))
		{
			case 'yearly':
				$values['date'] = "Last 10 years from ". date('Y', strtotime($date));
				for ($i = 10; $i >= 0; $i--) {
					$year = date('Y', strtotime("-{$i} years", strtotime($date)));
					$values['columns'][] = $year;

					foreach ($customers as $customer) {
						$values['records'][$customer->id][$i] = Invoice::where('customer_id', $customer->id)
							->whereBetween('created_at', ["{$year}-01-01 00:00:00", "{$year}-12-31 23:59:59"])
							->count();
					}
				}
				break;
			case 'daily':
				$date = DateTime::createFromFormat('Y-m-d', $date);
				$values['date'] = $date->format('F Y');

				$yearMonth = $date->format('Y-m-');
				$days = $date->format('t');
				for ($i = 1; $i <= $days; $i++) {
					$values['columns'][] = $i;

					foreach ($customers as $customer) {
						$values['records'][$customer->id][$i] = Invoice::where('customer_id', $customer->id)
							->whereBetween('created_at', ["{$yearMonth}{$i} 00:00:00", "{$yearMonth}{$i} 23:59:59"])
							->count();
					}
				}
				break;
			case 'monthly':
			default:
				$year = date('Y', strtotime($date));
				$values['date'] = $year;
				for ($i = 1; $i <= 12; $i++) {
					$values['columns'][] = DateTime::createFromFormat('!m', $i)->format('F');
					$startDate = Date("{$year}-{$i}-01");
					$endDate = DateTime::createFromFormat('Y-m-d', $startDate)->format('Y-m-t');

					foreach ($customers as $customer) {
						$values['records'][$customer->id][$i] = Invoice::where('customer_id', $customer->id)
							->whereBetween('created_at', [$startDate, $endDate])
							->count();
					}
				}
		}

		return view('Reports.salesReports', $values);
	}
}
