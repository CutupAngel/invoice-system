<?php

namespace App\Packages\Invoices;

use DB;
use Mail;

use App\User;
use App\User_Contact;
use App\Currency;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceTotal;
use App\Http\Controllers\PdfTrait;

use Illuminate\Support\Collection;

class Provider
{
	use PdfTrait;

	private static $invoice;
	private static $items;
	private static $totals;

	public function __construct()
	{
		self::$invoice = new Collection();
		self::$items = new Collection();
		self::$totals = new Collection();
	}

	public function create($user_id, $customer_id, $due_at = false, $invoice_number = false, $status = false, $estimate = false)
	{
		$user = User::findOrFail($user_id);
		$customer = User::findOrFail($customer_id);
		$address = User_Contact::where('user_id',$user->id)->whereIn('type',[1,2])->orderBy('id','desc')->limit(1)->first();
		if(empty($address))
		{
			throw new \Exception("Customer does not have a mailing address|".count($customer->billingContact)."|".count($customer->mailingContact) );
		}
		$addressId = $address->address()->first()->id;

		if (!$due_at) {
			$due_at = date('Y-m-d');
		}

		if (!$invoice_number) {
			$invoice_number = DB::table('invoices')
				->where('user_id', $user->id)
				->max('invoice_number') + 1;

			if ($invoice_number < $user->getSetting('invoice.startNumber', 0)) {
				$invoice_number = $user->getSetting('invoice.startNumber', 0);
			}
		}

		if (!$status) {
			$status = Invoice::UNPAID;
		}

		$currency = $user->getSetting('site.defaultCurrency',4);

		self::$invoice = new Collection([
			'user_id' => $user->id,
			'customer_id' => $customer->id,
			'currency_id' => $currency,
			'address_id' => $addressId,
			'invoice_number' => $invoice_number,
			'status' => $status,
			'due_at' => date('Y-m-d h:m:s'),
			'estimate' => $estimate
		]);
		return self::$invoice;
	}

	public function addItem($item, $description, $taxclass, $price, $quantity)
	{
		self::$items->push([
			'item' => $item,
			'product' => 0,
			'description' => $description,
			'price' => $price,
			'quantity' => $quantity,
			'total' => $price * $quantity,
			'tax_class' => $taxclass
		]);
	}

	public function addTotal($item, $price, $taxclass)
	{
		self::$totals->push([
			'item' => $item,
			'price' => $price,
			'tax_class' => $taxclass
		]);
	}

	public function save()
	{
		if (self::$invoice->isEmpty()) {
			throw new \Exception("No invoice data has been set.");
		}

		// Add the total to the invoice collection.
		self::$invoice->put('total', self::$items->sum('total') + self::$totals->sum('price'));

		self::$invoice = Invoice::create(self::$invoice->toArray());

		if (self::$items->count()) {
			self::$invoice->items()->createMany(self::$items->toArray());
		}

		if (self::$totals->count()) {
			self::$invoice->totals()->createMany(self::$totals->toArray());
		}

		return self::$invoice;
	}

	public function sendEmail($invoice = false)
	{
		if (!$invoice) {
			$invoice = self::$invoice;
		}

		if (empty($invoice)) {
			throw new \Exception("Unable to detect which invoice to use.");
		}

		$user = $invoice->user;
		$customer = $invoice->customer;
		$invoice = $invoice;
		$subTotal = $invoice->total;
		$currId = $user->getSetting('site.defaultCurrency', 4);
		$validationHash = hash('sha256', $invoice->customer->id . $invoice->customer->email . $invoice->created_at . $invoice->customer->password . $invoice->id);

		$pdfFullPath = null;
		if ($invoice) {
			$pdfFullPath = $this->generatePdfFile($invoice, true);
		}

		Mail::send(
			!$invoice->estimate ? 'Invoices.newInvoiceEmail' : 'Invoices.newEstimateEmail',
			[
				'user' => $user,
				'customer' => $customer,
				'invoice' => $invoice,
				'currency' => Currency::findOrFail($currId),
				'subTotal' => $subTotal,
				'validationHash' => $validationHash
			],
			function ($m) use ($customer, $user, $invoice, $pdfFullPath) {
				$m->from($user->mailingContact->address->email, $user->mailingContact->address->contact_name);
				$m->to($customer->email, $customer->name);

				if (!is_null($pdfFullPath)) {
                    $m->attach($pdfFullPath);
                }

				$m->subject(!$invoice->estimate ? 'New Invoice!' : 'New Estimate!');
			}
		);

	}

	public function getHash($invoice)
	{
		$invoice = $invoice;
		return hash('sha256', $invoice->user->id . $invoice->user->email . $invoice->created_at . $invoice->user->password . $invoice->id);
	}
}
