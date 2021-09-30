<?php

namespace App\Webhooks\Paypal;

use Log;
use App\Modules\Controller;
use App\Invoice;
use App\User_Setting;
use App\Transactions;
use Illuminate\Http\Request;

class PaypalController extends \App\Webhooks\Webhook
{
	function handleTaxRequest(Request $request)
	{
		
	}
	
	function handleIPN(Request $request, $invoiceId)
	{
		Log::info(print_r($_REQUEST,1));
		$txn = $request->get('txn');
		if(!empty($invoiceId) && !empty($txn))
		{
			//copy and pasted from paypals website, Cry Me A River.
			
			$raw_post_data = file_get_contents('php://input');
			$raw_post_array = explode('&', $raw_post_data);
			$req = 'cmd=_notify-validate';
			if (function_exists('get_magic_quotes_gpc')) {
				$get_magic_quotes_exists = true;
			}
			
			$myPost = array();
			foreach ($raw_post_array as $keyval)
			{
				$keyval = explode ('=', $keyval);
				if(count($keyval) == 2)
				{
					$myPost[$keyval[0]] = urldecode($keyval[1]);
				}
			}
			
			foreach ($myPost as $key => $value)
			{
				if ($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) {
					$value = urlencode(stripslashes($value));
				} else
				{
					$value = urlencode($value);
				}
				$req .= "&$key=$value";
			}
			
			$paypalTestmode = self::siteModal()->getSetting('paypalstandard.testmode',0);
			$paypalUrl = 'https://ipnpb.sandbox.paypal.com/cgi-bin/webscr';
			if($paypalTestmode == 0)
			{
				$paypalUrl = 'https://ipnpb.paypal.com/cgi-bin/webscr';
			}
			
			$ch = curl_init($paypalUrl);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close'));
			if ( !($res = curl_exec($ch)) )
			{
				throw new Exception("Got " . curl_error($ch) . " when processing IPN data - invoiceId:" . $invoiceId);
				curl_close($ch);
				exit;
			}
			curl_close($ch);
			
			if (strcmp ($res, "VERIFIED") == 0)
			{
				$invoice = Invoice::findOrFail($invoiceId);
				$transaction = Transactions::findOrFail($txn);
				$paypalEmail = self::siteModal()->getSetting('paypalstandard.email');
				if(strtolower($paypalEmail) !== strtolower($_POST['receiver_email']))
				{
					Log::info('Paypal IPN Receiver doesnt Match - '.$_POST['receiver_email'].':'.$paypalEmail.' - InvoiceId:'.$invoiceId);
					die();
				}
				
				$conversion = $invoice->user->getSetting('invoice.exchangerate') / 100 + 1;
				$invoiceTotal = round($invoice->total * $conversion * 100) / 100;
				if(floatval($invoiceTotal) != floatval($_POST['mc_gross']))
				{
					Log::info('Paypal IPN Totals dont Match - '.floatval($_POST['mc_gross']).':'.floatval($invoiceTotal).' - InvoiceId:'.$invoiceId);
					die();
				}
				
				if(isset($_POST['test_ipn']) && intval($_POST['test_ipn']) !== intval($paypalTestmode))
				{
					Log::info('Paypal IPN Test Modes dont Match - '.$_POST['test_ipn'].':'.$paypalTestmode.' - InvoiceId:'.$invoiceId);
					die();
				}
				
				$arrStatusDict = [
					'Denied' => Invoice::UNPAID,
					'Expired' => Invoice::UNPAID,
					'Failed' => Invoice::UNPAID,
					'Voided' => Invoice::UNPAID,
					'Canceled_Reversal' => Invoice::PAID,
					'Completed' => Invoice::PAID,
					'Refunded' => Invoice::REFUNDED,
					'Reversed' => Invoice::REFUNDED,
					'Pending' => Invoice::PENDING,
					'Processed' => Invoice::PENDING
				];
				
				$tranStatus = $arrStatusDict[$_POST['payment_status']];

				$transaction->transaction_id = $_POST['txn_id'];
				$transaction->invoice_id = $invoiceId;
				$transaction->user_id = 1;
				$transaction->customer_id = $invoice->customer_id;
				$transaction->gateway_id = 'paypalstandard';
				$transaction->amount = $_POST['mc_gross'];
				$transaction->payment_method = 2;
				$transaction->status = $tranStatus;
				$transaction->json_response = json_encode($_POST,1);
				$transaction->message = '';
				$transaction->currency_id = $invoice->currency_id;
				$transaction->save();

				$transTotal = Transactions::where('status',1)->where('invoice_id',$invoiceId)->sum('amount');
				
				if(floatval($transTotal) >= floatval($invoice->total)) {
					$invoice->status = Invoice::PAID;
					$invoice->save();
				}
			}
			else if (strcmp ($res, "INVALID") == 0)
			{
				// IPN invalid, log for manual investigation
				Log::info("Invalid PayPal IPN - invoiceId:" . $invoiceId);
			}
		}
		die();
	}
}