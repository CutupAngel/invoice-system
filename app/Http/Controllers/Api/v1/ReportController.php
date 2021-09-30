<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Address;
use App\Currency;
use App\Counties;
use App\Countries;
use App\Integration;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceTotal;
use App\Login_History;
use App\MiscStorage;
use App\Order;
use App\Order_Options;
use App\Package;
use App\Package_Cycle;
use App\Package_Option_Values;
use App\User;
use App\User_Contact;
use App\User_Link;
use App\User_Setting;
use App\Transactions;
use App\Http\Controllers\PaymentController;
use App\Mail\GeneralEmail;
use App\Mail\InvoiceEmail;
use Auth;
use DB;
use DateTime;
use Hash;
use Invoices;
use Permissions;
use Mail;
use Settings;
use Response;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Password;
use FraudLabsPro\Configuration as FraudConfiguration;
use FraudLabsPro\Order as FraudOrder;

class ReportController extends Controller
{
		public $user = null;
		public $api_type = null;

		public function __construct(Request $request)
		{
				$user = User::where('sandbox_api_key', $request->header('token'))
											->orWhere('live_api_key', $request->header('token'))
											->first();

				if(!$user)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Invalid token.'
																	],
																	401);
				}

				$this->user = $user;

				if($user->sandbox_api_key == $request->header('token')) $this->api_type = 'sandbox';
				else if($user->live_api_key == $request->header('token')) $this->api_type = 'live';
		}

		/* Get Annual Sales Report
			 Params
			 Header:
				- token: string (required)
		*/
		public function getAnnualSalesReport(Request $request)
		{
				$errorMessage = '';
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

				return Response::json([
						'success' => true,
						'data' => $values
				], 200);
		}

		/* Get Sales By Staff Report
			 Params
			 Header:
				- token: string (required)

			 Body:
			  -	timeframe: string (yearly | monhly | daily) (optional)
 			  -	date: date (yyyy-mm-dd) (optional)
		*/
		public function getSalesByStaffReport(Request $request)
		{
				$errorMessage = '';

				$type = null;
				if($request->has('timeframe')) $type = $request->timeframe;

				$date = null;
				if($request->has('date')) $date = $request->date;

				if ($date === null) {
					$date = date('Y-m-d');
				}

				$values = [
					'report' => 'Staff',
					'type' => ucwords($type),
					'actualDate' => $date,
					'records' => []
				];

				$staffMembers = $this->user->staff;
				$staffMembers[] = $this->user;
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

							foreach ($staffMembers as $staff) {
								$values['records'][$staff->id][$year] = Invoice::where('user_id', $staff->id)
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
							$startDate = Date("{$year}-{$i}-01");
							$endDate = DateTime::createFromFormat('Y-m-d', $startDate)->format('Y-m-t');

							foreach ($staffMembers as $staff) {
								$values['records'][$staff->id][DateTime::createFromFormat('!m', $i)->format('F')] = Invoice::where('user_id', $staff->id)
									->whereBetween('created_at', [$startDate, $endDate])
									->count();
							}
						}
				}

				return Response::json([
						'success' => true,
						'data' => $values
				], 200);
		}

		/* Get Sales By Customer Report
			 Params
			 Header:
				- token: string (required)

			 Body:
			  -	timeframe: string (yearly | monhly | daily) (optional)
 			  -	date: date (yyyy-mm-dd) (optional)
		*/
		public function getSalesByCustomerReport(Request $request)
		{
				$errorMessage = '';

				$type = null;
				if($request->has('timeframe')) $type = $request->timeframe;

				$date = null;
				if($request->has('date')) $date = $request->date;

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
					'actualDate' => $date,
					'records' => []
				];

				$customers = $this->user->customers;
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

							foreach ($customers as $customer) {
								$values['records'][$customer->id][$year] = Invoice::where('customer_id', $customer->id)
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
							$startDate = Date("{$year}-{$i}-01");
							$endDate = DateTime::createFromFormat('Y-m-d', $startDate)->format('Y-m-t');

							foreach ($customers as $customer) {
								$values['records'][$customer->id][DateTime::createFromFormat('!m', $i)->format('F')] = Invoice::where('customer_id', $customer->id)
									->whereBetween('created_at', [$startDate, $endDate])
									->count();
							}
						}
				}

				return Response::json([
						'success' => true,
						'data' => $values
				], 200);
		}

		/* Get Revenue Trend Report
			 Params
			 Header:
				- token: string (required)
		*/
		public function getRevenueTrendReport(Request $request)
		{
				$values = [
					'reportTitle' => 'Revenue Trend',
					'records' => []
				];

				for ($m = 1; $m <= 12; $m++) {
					$values['records'][$m] = [
						'month' => DateTime::createFromFormat('!m', $m)->format('F')
					];
				}

				for ($i = 4; $i >= 0; $i--) {
					$year = date('Y', strtotime("-{$i} years"));

					foreach ($values['records'] as $month => $array) {
						$startDate = "{$year}-{$month}-01";
						$endDate = DateTime::createFromFormat('Y-m-d', $startDate)->format('Y-m-t');

						$values['records'][$month][$year] = number_format(
							Invoice::where('user_id', $this->user->id)
								->whereBetween('due_at', [$startDate, $endDate])
								->sum('total'),
							2
						);
					}
				}

				return Response::json([
						'success' => true,
						'history' => $values
				], 200);
		}

		/* Get Package Leaderboard Report
			 Params
			 Header:
				- token: string (required)
		*/
		public function getPackageLeaderboardReport(Request $request)
		{
				$values = [
					'reportTitle' => 'Package Leaderboard',
					'records' => []
				];

				$packages = Package::whereHas('group', function ($q) {
					$q->where('user_id', $this->user->id);
				})->get();

				foreach ($packages as $package) {
					$record = [
						'Package Name' => $package->name,
						'One-Off' => 'N/A',
						'Daily' => 'N/A',
						'Weekly' => 'N/A',
						'Fortnightly' => 'N/A',
						'Monthly' => 'N/A',
						'2 Months' => 'N/A',
						'3 Months' => 'N/A',
						'4 Months' => 'N/A',
						'5 Months' => 'N/A',
						'6 Months' => 'N/A',
						'7 Months' => 'N/A',
						'8 Months' => 'N/A',
						'9 Months' => 'N/A',
						'10 Months' => 'N/A',
						'11 Months' => 'N/A',
						'12 Months' => 'N/A',
						'24 Months' => 'N/A',
						'36 Months' => 'N/A'
					];

					foreach ($package->cycles as $cycle) {
						$record['cycle'][$cycle->cycle] = Order::where('cycle_id', $cycle->id)
							->where('package_id', $package->id)
							->count();
					}

					$values['records'][] = $record;
				}

				return Response::json([
						'success' => true,
						'package_leaderboard' => $values
				], 200);
		}

		/* Get Customer Receipt Report
			 Params
			 Header:
				- token: string (required)
		*/
		public function getCustomerReceiptReport(Request $request)
		{
				$values = [
					'reportTitle' => 'Customer Receipt Report',
					'records' => []
				];

				foreach ($this->user->invoices->where('status', Invoice::PAID) as $invoice) {
					$values['records'][] = [
						'Date' => $invoice->due_at,
						'Invoice #' => $invoice->invoice_number,
						'Customer' => $invoice->customer->name,
						'Gross' => $invoice->total,
						'Payment Type' => '',
						'Authorization ID' => ''
					];
				}

				return Response::json([
						'success' => true,
						'invoices' => $values
				], 200);
		}

		/* Get Package Customer Credit Report
			 Params
			 Header:
				- token: string (required)
		*/
		public function getCustomerCreditReport(Request $request)
		{
				$values = [
					'reportTitle' => 'Customer Credit Report',
					'records' => []
				];

				$customers = $this->user->customers;

				foreach ($customers as $customer) {
					$values['records'][] = [
						'Customer' => $customer->name,
						'Credit' => number_format($customer->getCredit()->value, 2)
					];
				}

				return Response::json([
						'success' => true,
						'credits' => $values
				], 200);
		}

		/* Get Customer Invoice Report
			 Params
			 Header:
				- token: string (required)
		*/
		public function getCustomerInvoiceReport(Request $request)
		{
				$values = [
					'reportTitle' => 'Customer Invoice Report',
					'records' => []
				];

				$invoices = $this->user->invoices;
				foreach ($invoices as $invoice) {
					$values['records'][] = [
						'Date' => $invoice->created_at,
						'Invoice' => $invoice->invoice_number,
						'Customer' => $invoice->customer->name,
						'Tax' => '0.00',
						'Net' => number_format($invoice->total, 2),
						'Gross' => number_format($invoice->total, 2),
						'Payment Type' => 'N/A'
					];
				}

				return Response::json([
						'success' => true,
						'invoices' => $values
				], 200);
		}

		/* Get Customer Debt Report
			 Params
			 Header:
				- token: string (required)
		*/
		public function getCustomerDebtReport(Request $request)
		{
				$values = [
					'reportTitle' => 'Debt Sheet',
					'records' => []
				];

				$customers = array_values($this->user->getCustomerIds()->all());

				$records = $this->user->invoices->where('status', Invoice::OVERDUE);
				foreach ($records as $record) {
					$values['records'][] = [
						'Name' => $record->name,
						'Phone' => $record->mailingContact->address->phone,
						'Email' => $record->email,
						'Overdue' => $record->invoices->where('status', Invoice::OVERDUE)->count(),
						'Total' => number_format($record->invoices->where('status', Invoice::OVERDUE)->sum('total'), 2)
					];
				}
				return Response::json([
						'success' => true,
						'debts' => $values
				], 200);
		}

}
