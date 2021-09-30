<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Artisan;
use DateTime;
use DB;
use App\Site;
use App\Address;
use App\User_Contact;
use App\Countries;
use App\Invoice;
use App\Order;
use App\MiscStorage;
Use App\User;
use App\Currency;

class DashboardController extends Controller
{
	public function dashboard()
	{
		switch (Auth::User()->account_type) {
			case 0: //admin
			case 1: //client
			case 3: //staff
				return $this->clientDashboard();
				break;
			case 2: //customer
				return $this->customerDashboard();
				break;
			default:
				return abort(404);
		}
	}

	public function clientDashboard()
	{
		$recentDate = date('Y-m-d', strtotime('-1 week'));

		$top = [
			'orders' => DB::table('invoices')
							->where('status', Invoice::UNPAID)
							->where('created_at', '>=', $recentDate)
							->where('user_id', Auth::User()->id)
							->whereNotNull('order_id')
							->count('invoices.id'),
			'users' => DB::table('users as u1')
							->join('user_link as ul', 'ul.parent_id', '=', 'u1.id')
							->join('users as u2', 'ul.user_id', '=', 'u2.id')
							->where('u2.account_type', User::CUSTOMER)
							->where('u2.created_at', '>=', $recentDate)
							->where('u1.id', Auth::User()->id)
							->count('u2.id')
		];

		$todoList = Auth::User()->miscStorage->where('name', 'todo');
		if ($todoList->count()) {
			$todoList = $todoList->first()->value;
		} else {
			$todoList = [];
		}

		$revenueChart = [];
		for ($i = 12; $i >= 0; $i--) {
			$startDate = Date("Y-m-01", strtotime("-{$i} Months"));
			$endDate = DateTime::createFromFormat('Y-m-d', $startDate)->format('Y-m-t');

			$row = ['y' => $startDate];
			$row['completed'] = number_format(Invoice::where('user_id', Auth::User()->id)
				->whereBetween('created_at', [$startDate, $endDate])
				->where('status', Invoice::PAID)
				->sum('total'), 2, '.', '');

			$row['refunded'] = number_format(Invoice::where('user_id', Auth::User()->id)
				->whereBetween('created_at', [$startDate, $endDate])
				->where('status', Invoice::REFUNDED)
				->sum('total'), 2, '.', '');

			$revenueChart[] = $row;
		}

		$contact = User_Contact::where('user_id', Auth::User()->id)
						->where('type', '0')
						->first();
		$foundAddress = false;
		$address = null;

		if (!empty($contact)) {
			$address = Address::findOrFail($contact->address->id);

			if (!empty($address->city) && !empty($address->country_id) && !empty($address->postal_code)) {
				$foundAddress = true;
			}
		} else {
			$address = new Address();
		}

		$activeSubscription = $this->site('modal')->activeSubscription;
		$subscriptionAlerts = [];

		if ($activeSubscription && $activeSubscription->plan->invoices !== 0) {
			$invoiceCount = \App\Invoice::count();
			if ($invoiceCount >= ($activeSubscription->plan->invoices * 90 / 100)) {
				if ($invoiceCount = $activeSubscription->plan->invoices) {
					$subscriptionAlerts['invoice'] = [
						'class' => 'alert-danger',
						'text' => 'You have used 100% of your invoice quota for your subscription plan.'
					];
				} else {
					$subscriptionAlerts['invoice'] = [
						'class' => 'alert-warning',
						'text' => 'You have used 90% of your invoice quota for your subscription plan.'
					];
				}
			}
		}

		if ($activeSubscription && $activeSubscription->plan->clients !== 0) {
			$customerCount = \App\User::where('account_type', \App\User::CUSTOMER)->count();
			if ($customerCount >= ($activeSubscription->plan->clients * 90 / 100)) {
				if ($customerCount = $activeSubscription->plan->clients) {
					$subscriptionAlerts['customer'] = [
						'class' => 'alert-danger',
						'text' => 'You have used 100% of your customer quota for your subscription plan.'
					];
				} else {
					$subscriptionAlerts['customer'] = [
						'class' => 'alert-warning',
						'text' => 'You have used 90% of your customer quota for your subscription plan.'
					];
				}
			}
		}

		if ($activeSubscription && $activeSubscription->plan->staff !== 0) {
			$staffCount = \App\User::where('account_type', \App\User::STAFF)->count();
			if ($staffCount >= ($activeSubscription->plan->staff * 90 / 100)) {
				if ($staffCount = $activeSubscription->plan->staff) {
					$subscriptionAlerts['staff'] = [
						'class' => 'alert-danger',
						'text' => 'You have used 100% of your staff quota for your subscription plan.'
					];
				} else {
					$subscriptionAlerts['staff'] = [
						'class' => 'alert-warning',
						'text' => 'You have used 90% of your staff quota for your subscription plan.'
					];
				}
			}
		}

		$canEditDesign = false;
		if($activeSubscription && $activeSubscription->plan_id != '1')
		{
			$canEditDesign = true;
		}

		$invoices_due = DB::table('invoices')->where('status', Invoice::UNPAID)->count();
		$invoices_overdue = DB::table('invoices')->where('status', Invoice::OVERDUE)->count();
		$customers = DB::table('users')->where('account_type', USER::CUSTOMER)->count();
		$monthly_income = DB::table('invoices')
												->selectRaw('sum(total) as totalMonthly')
												->where('status', '1')
												->whereRaw('MONTH(updated_at) = MONTH(CURRENT_DATE())')
												->first();
		$yearly_income = DB::table('invoices')
												->selectRaw('sum(total) as totalYearly')
												->where('status', '1')
												->whereRaw('YEAR(updated_at) = YEAR(CURRENT_DATE())')
												->first();
		$support = DB::table('support_tickets')->where('status', 'open')->count();

		return view('Dashboard.clientDashboard', [
			'top' => $top,
			'revenueChart' => $revenueChart,
			'todo' => $todoList,
			'countries' => Countries::get(),
			'foundAddress' => $foundAddress,
			'address' => $address,
			'subscriptionAlerts' => $subscriptionAlerts,
			'invoices_due' => $invoices_due,
			'invoices_overdue' => $invoices_overdue,
			'customers' => $customers,
			'monthly_income' => $monthly_income->totalMonthly ?: 0,
			'yearly_income' => $yearly_income->totalYearly ?: 0,
			'currency' => Controller::setCurrency(),
			'support' => $support
		]);
	}

	public function customerDashboard()
	{
		$counts = [];

		$invoices = Invoice::where('customer_id', Auth::User()->id)->get();
		$orders = Order::where('customer_id', Auth::User()->id)->get();
		$counts['invoices'] = Invoice::where('customer_id', Auth::User()->id)->where('status', Invoice::UNPAID)->count();
		$counts['overdueInvoices'] = Invoice::where('customer_id', Auth::User()->id)->where('status', Invoice::OVERDUE)->count();
		$counts['services'] = Order::where('customer_id', Auth::User()->id)->count();

		return view('Dashboard.customerDashboard', [
			'count' => $counts,
			'invoices' => $invoices,
			'orders' => $orders
		]);
	}

	public function updateTodolist(Request $request)
	{
		$this->validate($request, [
			'id' => 'required',
			'title' => 'required',
			'date' => 'date'
		]);

		try {
			$id = $request->input('id');

			$todoList = MiscStorage::findOrFail(Auth::User()->id . '.todo');
			$list = $todoList->value;
			$list[$id] = [
				'title' => $request->input('title'),
				'duedate' => $request->input('date'),
				'checked' => $request->input('checked') === "true"
			];
			$todoList->value = $list;

			$todoList->save();

			return '1';
		} catch (\Exception $e) {
			return '0';
		}
	}

	public function addTodolist(Request $request)
	{
		$this->validate($request, [
			'title' => 'required',
			'date' => 'date'
		]);

		try {
			$todoList = Auth::User()->miscStorage->where('name', 'todo')->first();
			$list = $todoList->value;
		} catch (\Exception $e) {
			$todoList = new MiscStorage;
			$todoList->name = 'todo';
			$todoList->user_id = Auth::User()->id;
			$list = [];
		}

		$list[] = [
			'title' => $request->input('title'),
			'duedate' => $request->input('date'),
			'checked' => $request->has('checked')
		];

		$todoList->value = $list;

		$todoList->save();

		return count($todoList->value) - 1;
	}

	public function deleteTodolist(Request $request)
	{
		$this->validate($request, [
			'id' => 'required'
		]);

		try {
			$id = $request->input('id');

			$todoList = MiscStorage::findOrFail(Auth::User()->id . '.todo');
			$list = $todoList->value;
			unset($list[$id]);
			$todoList->value = $list;

			$todoList->save();

			return '1';
		} catch (\Exception $e) {
			return '0';
		}
	}

	public function stopImpersonating(Request $request)
	{
			Auth::User()->stopImpersonating();
			return redirect('/');
	}

}
