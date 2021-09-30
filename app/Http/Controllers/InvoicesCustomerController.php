<?php

namespace App\Http\Controllers;

use Auth;
use Cart;
use Route;

use App\Currency;
use App\User;
use App\Invoice;
use App\Order;
use App\Order_Options;
use Illuminate\Http\Request;

class InvoicesCustomerController extends Controller
{
	private $invoiceTypes = [
		'unpaid',
		'overdue',
		'paid',
		'refunded',
		'canceled',
		'estimates'
	];

	public function index($type = 'all')
	{
		if (!in_array($type, $this->invoiceTypes)) {
			$type = 'all';
		}

		Route::current()->setUri("admin/invoices/view/{$type}");

		$invoices = [];
		if ($type === 'all') {
			$invoices = Invoice::where('customer_id', Auth::User()->id)->where('estimate',false)->get();
		} elseif($type === 'estimates'){
			$invoices = Invoice::where('customer_id', Auth::User()->id)->where('estimate',true)->get();
		} else {
			$invoices = Invoice::where('customer_id', Auth::User()->id)
				->where('status', constant('App\Invoice::' . strtoupper($type)))
				->where('estimate',false)
				->get();
		}

		$data = [
			'type' => $type,
			'invoices' => $invoices
		];

		return view('Invoices.customerInvoicesList', $data);
	}

	public function show($id)
	{
			$invoice = Invoice::findOrFail($id);
			if ($invoice->customer_id !== Auth::User()->id) {
				throw new \Exception('Not the customer of this invoice.');
			}

			$totalOptionCost = 0;
			$invoice->subtotal = 0.00;
			if ($invoice->items !== null) {
				foreach ($invoice->items as $item) {
					$totalItemCost = 0;
					$invoice->subtotal += ($item->price * $item->quantity);
				}
			}

			//Calculate Options
			$options = $this->getOrderOptions($invoice);

			for($x = 0; $x < count($options); $x++) {
					$option = \App\Package_Options::where('id', $options[$x]['option_id'])
																					->first();
					if($option->type == 2) {
						$totalItemCost += ((int)$options[$x]['value'] * $options[$x]['price']) + $options[$x]['fee'];
					}
					else {
						$totalItemCost += $options[$x]['price'] + $options[$x]['fee'];
					}
			}
			$totalOptionCost += $totalItemCost;
			$invoice->subtotal += $totalOptionCost;

			return view('Invoices.viewInvoice', [
																						'invoice' => $invoice,
																						'user' => Auth::User(),
																						'default_currency' => Controller::setDefaultCurrency(),
																						'currency' => Currency::findOrFail($invoice->currency_id),
																						'options' => $options
																					]);
	}

    public function pay($id, $authhash = null)
    {
        if (!empty($authhash)) {
            $invoice = $this->validateAuthHash($id, $authhash);
            if ($invoice) {
                $subTotal = 0;
                $order = $invoice->order;
                session()->forget('cart');
                Cart::destroy();
                foreach ($invoice->items as $item) {
                    $cart = Cart::add(
                        $item->id,
                        $item->item,
                        $item->quantity,
                        $order->cycle->price,
                        [
                            'fee'      => $order->cycle->fee,
                            'cycle_id' => $order->cycle_id,
                            'options'  => $this->getOrderOptions($invoice),
                        ]
                    );
                    $subTotal = $subTotal + ($item->quantity * $item->price);
                }

                session()->put('cart.mode', 'invoice');
                session()->put('cart.inv', $invoice->id);
                session()->put('cart.subTotal', $subTotal);
                session()->put('cart.tokenAuthed', true);
                session()->put('cart.tokenUser', $invoice->customer->id);

                return redirect('checkout');
            }
        } elseif (Auth::User()) {
            $invoice = Invoice::findOrFail($id);
            if ($invoice->customer_id == Auth::User()->id || $invoice->user_id == Auth::User()->id) {
                $subTotal = 0;
                session()->forget('cart');
                Cart::destroy();

                $order = $invoice->order;

                $invoicePrice = $invoice->total; //$order ? $order->cycle->price : $invoice->total;

                foreach ($invoice->items as $item) {
                    $subTotal = $invoicePrice; //$subTotal + ($item->quantity * $invoicePrice);
                    $cart = Cart::add(
                        $item->id,
                        $item->item,
                        $item->quantity,
                        $invoicePrice,
                        [
                            'fee'      => $order ? $order->cycle->fee : 0,
                            'cycle_id' => $order ? $order->cycle_id : null,
                            'options'  => $order ? $this->getOrderOptions($invoice) : null,
                        ]
                    );
                }
                session()->put('cart.mode', 'invoice');
                session()->put('cart.inv', $invoice->id);
                session()->put('cart.subTotal', $subTotal);

                return redirect('checkout');
            }
        }

        throw new \Exception('Not authorized to view this invoice.');
    }

	private function validateAuthHash($invoiceId, $hash)
	{
		$invoice = Invoice::findOrFail($invoiceId);

		$validationHash = hash('sha256',$invoice->user->id . $invoice->user->email . $invoice->created_at . $invoice->user->password . $invoice->id);

		if ($hash === $validationHash) {
			return $invoice;
		}

		return false;
	}

	private function getOrderOptions($invoice)
	{
			$orderOptions = Order_Options::where('order_id', $invoice->order_id)
																		->orderBy('id', 'asc')
																		->get();

			$options = [];
			foreach ($orderOptions as $key => $value) {
				$optionsItem['id'] = $value->option_value->id;
				$optionsItem['option_id'] = $value->option_value->option_id;
				$optionsItem['display_name'] = $value->option_value->display_name;
				$optionsItem['price'] = $value->amount;
				$optionsItem['fee'] = $value->option_value->fee;
				$optionsItem['cycle_type'] = $value->cycle_type;
				$optionsItem['value'] = $value->value;

				$options[] = $optionsItem;
			}
			return $options;
	}
}
