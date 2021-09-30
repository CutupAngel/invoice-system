<?php

namespace App\Webhooks\PYMT;

use Log;
use App\Modules\Controller;
use Illuminate\Http\Request;
use App\Invoice;
use App\Transactions;

class PYMTController extends \App\Webhooks\Webhook
{
	
	function handleCallback(Request $request, $invoiceId)
	{
		if($request->has('order.custom') && is_numeric($invoiceId))
		{
			$invoice = Invoice::findOrFail($invoiceId);
			$token = $this->user->getSetting('pymtpro.token');
			$secret = $this->user->getSetting('pymtpro.secret');
			
			$invoiceTotal = round($this->invoice->total + $this->invoice->total * Settings::get('invoice.exchangerate') / 100 * 100) / 100;
			
			if(session()->get('receipt.transaction_id'))
			{
				$transaction = Transactions::findOrFail(session()->get('receipt.transaction_id'));
			}
			else
			{
				$transaction = new Transactions();
			}
			$transaction->transaction_id = $request->input('order.connected') . '-'. $invoice->invoice_number . '-' . time();
			$transaction->invoice_id = $invoiceId;
			$transaction->user_id = 1;
			$transaction->customer_id = $invoice->customer_id;
			$transaction->gateway_id = 'pymtpro';
			$transaction->amount = $request->input('order.total.cents') / 100;
			$transaction->payment_method = 3;
			$transaction->message = '';
			$transaction->currency_id = $invoice->currency_id;
			
			$custom = hash('sha256',$invoice->customer->id . $invoice->customer->email . $invoice->created_at . $invoice->customer->password . $invoice->id);
			if($custom === $request->input('order.custom') && $request->input('order.total.cents') == ($invoiceTotal * 100) && $request->input('order.total.currency_iso') === 'usd')
			{
				$transaction->status = Invoice::PAID;
			}
			$transaction->json_response = json_encode($_POST,1);
			$transaction->save();
		}
	}
}