<?php

namespace App\Http\Controllers;

use App\Address;
use App\Countries;
use App\Invoice;
use App\MiscStorage;
use App\User;
use App\User_Contact;
use App\User_Link;
use App\Mail\GeneralEmail;
use Auth;
use Hash;
use Permissions;
use Mail;
use Illuminate\Http\Request;
use FraudLabsPro\Configuration as FraudConfiguration;
use FraudLabsPro\Order as FraudOrder;

class CustomerController extends Controller
{
	public function __construct()
	{
		if (!Permissions::has('customers')) {
			//throw new Permissions::$exception;
			return redirect(route('login'));
		}
	}

	public function index()
	{
		//$customers = Auth::User()->load('parent.customers')->parent()->first()->customers()->withTrashed()->get();
		//Jordan requested only customers who arent deleted be shown...
		if(Auth::User()->id === 1) {
            $customers = User::where('id', '<>', Auth::User()->id)
                ->where('account_type', User::CUSTOMER)
                ->get();
		} else {
            $customers = Auth::User()->load('parent.customers')->parent()->first()->customers()->get();
		}

		return view('Customers.customerList', [
			'customers' => $customers
		]);
	}

	public function show($id)
	{
	    try {
            $userAdmin = User::find(1);
            $customer = User::withTrashed()->findOrFail($id);
            $customer->checkCanEdit(auth()->id());

            if ($customer->mailingContact === null) {
                $customer->mailingContact = new User_Contact();
                $customer->mailingContact->address = new Address();
            }

            $credit = MiscStorage::where('name', 'account-credit')->where('user_id', $customer->id)->first();
            if(!$credit) {
                $credit = 0;
            } else {
                $credit = intval($credit->value);
            }

            $stats = [
                'paid' => $customer->invoices(Invoice::PAID)->count(),
                'due' => $customer->invoices(Invoice::UNPAID)->count(),
                'overdue' => $customer->invoices(Invoice::OVERDUE)->count(),
                'credit' => $credit
            ];

            $notes = MiscStorage::where('name', 'customer-notes-' . $customer->id)->get()->first();
            if (empty($notes)) {
                $notes = '';
            } else {
                $notes = $notes->value;
            }

            return view('Customers.viewCustomer', [
                'customer' => $customer,
                'notes' => $notes,
                'stats' => $stats,
                'userAdmin' => $userAdmin,
            ]);
        } catch (\Exception $e) {
	        abort(403);
        }
	}

	public function create()
	{
		$customer = new User();
		$customer->mailingContact = new User_contact();
		$customer->mailingContact->address = new Address();

		$countries = Countries::orderByRaw('case when id IN(222,223) then -1 else id end,id')->get();

		return view('Customers.createForm', [
			'customer' => $customer,
			'countries' => $countries,
			'notes' => '',
			'type' => 'Create',
			'credit' => $customer->getCredit()->value ?: 0
		]);
	}

	public function editCredit(Request $request, $id)
	{
		$this->validate($request, [
			'credit' => 'required|numeric'
		]);

		// $customer = User::findOrFail($id);
		// $customer->checkCanEdit(Auth::User()->id);
		// $customer->getCredit()->value = $request->input('credit');
		// $customer->getCredit()->save();

			$credit = MiscStorage::where('name', 'account-credit')->where('user_id',$id)->first();

			if(!$credit) {
				$credit = new MiscStorage;
				$value = $request->credit;
			} else {
				$value = $credit->value + $request->credit;
			}

			$credit->name = "account-credit";
			$credit->user_id = $id;
			$credit->value = $value;
			$credit->save();

		//return intval($credit->value);
		return redirect()->back()->with('status', trans('backend.cust-credit-added'));
	}

	public function merge(Request $request, $id)
	{
			$this->validate($request, [
				'account' => 'required|exists:users,id|not_in:'.$id
			], [
				'account.not_in' => 'You cannot merge the same accounts together.'
			]);

			$customer = User::findOrFail($id);
			$customer->checkCanEdit(Auth::User()->id);

			$newAccount = User::findOrFail($request->input('account'));
			$newAccount->checkCanEdit(Auth::User()->id);

			foreach ($customer->invoices as $invoice) {
				$invoice->customer_id = $newAccount->id;
				$invoice->save();
			}

			foreach ($customer->orders as $order) {
				$order->customer_id = $newAccount->id;
				$order->save();
			}

			foreach ($customer->contacts as $contact) {
				//$contact->user_id = $newAccount->id;
				//$contact->save();
			}

			foreach ($customer->orderGroups as $group) {
				$group->user_id = $newAccount->id;
				$group->save();
			}

				foreach ($customer->miscStorage as $storage) {
					if ($storage->name == 'account-credit') {
						$newAccount->getCredit()->value += floatval($storage->value);
						$newAccount->getCredit()->save();

						$storage->delete();
						continue;
					}

					$storage->user_id = $newAccount->id;
					$storage->save();
				}

			$customer->delete();

			return $newAccount->id;
	}

	public function edit($id)
	{
		try {
			$customer = User::withTrashed()->findOrFail($id);
			$customer->checkCanEdit(Auth::User()->id);

			if ($customer->mailingContact === null) {
				$customer->mailingContact = new User_contact();
				$customer->mailingContact->address = new Address();
			}

			$notes = MiscStorage::where('name', 'customer-notes-' . $customer->id)->get()->first();
			if (empty($notes)) {
				$notes = '';
			} else {
				$notes = $notes->value;
			}

			$credit = MiscStorage::where('name', 'account-credit')->where('user_id', $customer->id)->first();
			if(!$credit) {
				$credit = 0;
			} else {
				$credit = intval($credit->value);
			}

			$countries = Countries::all();
			return view('Customers.createForm', [
				'customer' => $customer,
				'countries' => $countries,
				'type' => 'Edit',
				'notes' => $notes,
				'credit' => $credit
				// 'credit' => $customer->getCredit()->value ?: 0
			]);
		} catch (\Exception $e) {
			return back();
		}
	}

	public function impersonate($user_id)
	{
		$user = User::findOrFail($user_id);
		$user->checkCanEdit(Auth::User()->id);

		Auth::User()->setImpersonating($user->id);
		return redirect('/');
	}

	public function update(Request $request, $id)
	{
		try {
			$user = User::withTrashed()->findOrFail($id);
			$user->checkCanEdit(Auth::User()->id);

			$this->validate($request, [
				'name'         => 'required',
				//'email'        => 'required|email|unique:users,email,'.$user->id,
				'address_1'    => 'required',
				'city'         => 'required',
				'county'       => 'required|exists:main.counties,id',
				'country'      => 'required|exists:main.countries,id',
				'postal_code'  => 'required',
				'phone'        => 'required',
				'username'     => 'required|email|unique:users,email,'.$user->id,
				'website'      => 'url'
			]);


			$user->name = $request->input('name');
			$user->email = $request->input('username');
			$user->username = $request->input('username');
			if ($request->has('password')) {
				$user->password = Hash::make($request->input('password'));
			}

			if ($user->mailingContact !== null) {
				$user->mailingContact->address->business_name = ($request->input('business_name')) ? $request->input('business_name') : '';
				$user->mailingContact->address->address_1 = $request->input('address_1');
				$user->mailingContact->address->address_2 = $request->input('address_2');
				$user->mailingContact->address->city = $request->input('city');
				$user->mailingContact->address->county_id = $request->input('county');
				$user->mailingContact->address->country_id = $request->input('country');
				$user->mailingContact->address->postal_code = $request->input('postal_code');
				$user->mailingContact->address->phone = $request->input('phone');
				$user->mailingContact->address->fax = $request->input('fax');
				$user->mailingContact->address->website = $request->input('website');
				$user->mailingContact->address->email = $user->email;
			} else {
				$address = new Address();
				$address->business_name = ($request->input('business_name')) ? $request->input('business_name') : '';
				$address->address_1 = $request->input('address_1');
				$address->address_2 = $request->input('address_2');
				$address->city = $request->input('city');
				$address->county_id = $request->input('county');
				$address->country_id = $request->input('country');
				$address->postal_code = $request->input('postal_code');
				$address->phone = $request->input('phone');
				$address->fax = $request->input('fax');
				$address->website = $request->input('website');
				$address->email = $user->email;
				$address->save();

				$contact = new User_Contact();
				$contact->address_id = $address->id;
				$contact->user_id = $user->id;
				$contact->type = User_Contact::MAILING;
				$contact->save();
			}

			$user->push();

			$vat = $user->settings()->where('name', 'billing.vat')->first();
			if ($request->has('vat')) {
				if (empty($vat)) {
					$user->settings()->create([
						'name' => 'billing.vat',
						'value' => true
					]);
				} else {
					$vat->value = true;
					$vat->save();
				}
			} elseif (!empty($vat)) {
				$vat->value = false;
				$vat->save();
			}

			$notes = MiscStorage::where('name', 'customer-notes-' . $user->id)->get();
			if ($notes->count() === 0) {
				$notes = new MiscStorage;
				$notes->name = "customer-notes-{$user->id}";
				$notes->user_id = Auth::User()->id;
			} else {
				$notes = $notes->first();
			}

			$credit = MiscStorage::where('name', 'account-credit')->where('user_id', $user->id)->first();

			if(!$credit) {
				$credit = new MiscStorage;
				$value = 0;
			} else {
				$value = $credit->value + $request->credit;
			}

			$credit->name = "account-credit";
			$credit->user_id = $user->id;
			$credit->value = $value;
			$credit->save();

			return redirect("/customers/{$id}");
		} catch (\Exception $e) {
			if (get_class($e) === 'Illuminate\Foundation\Validation\ValidationException') {
				throw $e;
			}
			return back()->withInput()->withErrors([$e->getMessage()]);
		}
	}

	public function store(Request $request)
	{
		$this->validate($request, [
			'name'         => 'required',
			//'email'        => 'required|email|unique:users,email',
			'address_1'    => 'required',
			'city'         => 'required',
			'county'       => 'required|exists:main.counties,id',
			'country'      => 'required|exists:main.countries,id',
			'postal_code'  => 'required',
			'phone'        => 'required',
			'username'     => 'required|email|unique:users,email',
			'password'     => 'required',
			'website'      => 'url',
			'credit'       => 'numeric'
		]);

		$user = User::create([
			'name' => $request->input('name'),
			'email' => $request->input('username'),
			'username' => $request->input('username'),
			'password' => Hash::make($request->input('password')),
			'account_type' => User::CUSTOMER,
		]);

		$address = Address::create([
			'business_name' => ($request->input('business_name')) ? $request->input('business_name') : '',
			'address_1' => $request->input('address_1'),
			'address_2' => $request->input('address_2'),
			'city' => $request->input('city'),
			'county_id' => $request->input('county'),
			'country_id' => $request->input('country'),
			'postal_code' => $request->input('postal_code'),
			'phone' => $request->input('phone'),
			'fax' => $request->input('fax'),
			'website' => $request->input('website'),
			'email' => $user->email,
		]);

		if (!$address) {
			abort('Not created address');
		}

		$contact = User_Contact::create([
			'address_id' => $address->id,
			'user_id' => $user->id,
			'type' => User_Contact::MAILING,
		]);

		$link = User_Link::create([
			'user_id' => $user->id,
			'parent_id' => Auth::User()->id,
		]);

		if ($request->has('comment')) {
			$notes = MiscStorage::create([
				'name' => "customer-notes-{$user->id}",
				'user_id' => Auth::User()->id,
				'value' => $request->input('comment'),
			]);
		}

		if ($request->has('credit')) {
			$notes = MiscStorage::create([
				'name' => "account-credit",
				'user_id' => $user->id,
				'value' => $request->input('credit'),
			]);
		}

		$userFrom = Auth::User();
		$subject = 'Customer Account Created';
		$content = '--customer data--';
		$view = 'Customers.mail.customerCreateEmail';
		Mail::to($user)->send(new GeneralEmail($userFrom, $subject, $content, $view));

		return redirect('/customers');
	}

	public function restore($id)
	{
		$customer = User::where('id', $id)->withTrashed()->first();
		$customer->checkCanEdit(Auth::User()->id);
		$customer->restore();

		return 1;
	}

	public function destroy($id)
	{
			$customer = User::findOrFail($id);
			$customer->checkCanEdit(Auth::User()->id);

			//delete address
			foreach($customer->contacts as $customer_contact) {
					$customer_contact->address->forceDelete();
			}

			$customer_name = $customer->name;
			$customer->forceDelete();

			return redirect()->back()->with(['status' => $customer_name . ' deleted successfully.']);
	}

	public function setFraudLabsStatus($customers, $status)
	{
        // Configures FraudLabs Pro API key
        $userAdmin = User::find(1);
        $customer = User::find($customers);

        if ($userAdmin->getSetting('integration.fraudlabs')) {
            $orderStatus = 'APPROVE';
            if($status == 'APPROVE') $orderStatus = FraudOrder::APPROVE;
            else if($status == 'REJECT') $orderStatus = FraudOrder::REJECT;
            else if($status == 'REJECT_BLACKLIST') $orderStatus = FraudOrder::REJECT_BLACKLIST;

            if (!empty($customer->fraudlabs_json)) {
                FraudConfiguration::apiKey($userAdmin->getSetting('fraudlabs.apiKey'));

                $fraudlabsResponse = $customer->fraudlabs_json;
                $fraudlabsResponse = json_decode($fraudlabsResponse);

                // Order details
                $orderDetails = [
                    'id'		=> $fraudlabsResponse->fraudlabspro_id,
                    'status'	=> $orderStatus,
                    'note'		=> 'This customer made a valid purchase before.',
                ];

                FraudOrder::feedback($orderDetails);
            }


            $customer->fraudlabs_status = $status;
            $customer->save();

            return redirect()
                ->back()
                ->with('status', 'FraudLabsPro status set "' . $status . '" successfully');
        } else {
            return redirect()
                ->back()
                ->with('error', 'Integration is not enabled');
        }
	}

	public function saveNote(Request $request)
	{
			$notes = MiscStorage::where('name', 'customer-notes-' . $request->customer_id)->first();
			if (!$notes)
			{
				$notes = new MiscStorage();
			}
			$notes->name = "customer-notes-{$request->customer_id}";
			$notes->value = $request->note;
			$notes->user_id = Auth::User()->id;
			$notes->save();

			return response()->json([
				'success' => true,
				'status' => trans('backend.cb-customerssavenotessuccess')
			], 200);
	}
}
