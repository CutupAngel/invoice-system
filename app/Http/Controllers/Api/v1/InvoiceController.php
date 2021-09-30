<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;

use App\Address;
use App\Currency;
use App\Counties;
use App\Countries;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceTotal;
use App\MiscStorage;
use App\Order;
use App\User;
use App\User_Contact;
use App\User_Link;
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
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;


class InvoiceController extends Controller
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

		/* Create Quote
			 Params
			 Header:
				- token: string (required)

				Body:
				- duedate: date (mm-dd-yyyy)(required)
				- customer_id: integer (required)
				- record[item]][]: string (optional)
				- record[description]][]: string (optional)
				- record[price]][]: double (optional)
				- record[quantity]][]: integer (optional)
				- record[tax_class]][]: integer (optional)
				- record[tax]][]: double (optional)
				- record[comments]: string (optional)
		*/
		public function createQuote(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('duedate'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Due date is required.';
				}
				if(!$request->has('customer_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID is required.';
				}

				if($request->has('customer_id') && !is_numeric($request->customer_id))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID is not valid format.';
				}

				$customer = User::find($request->customer_id);
				if(!$customer)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID is invalid.';
				}

				if($request->has('duedate'))
				{
						$duedate = $request->duedate;
						$dateExploded = explode("-", $duedate);
						if(count($dateExploded) != 3)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Duedate is not valid format.';
						}
						else
						{
								$day = $dateExploded[1];
								$month = $dateExploded[0];
								$year = $dateExploded[2];
						}

						if(!checkdate($month, $day, $year))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Duedate is not a valid date.';
						}
				}

				if ($request->has('record'))
				{
						$validRecord = true;
						foreach ($request->record as $k => $value)
						{
								$errorPos = $k;
								if($k == 'item')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
								}
								if($k == 'price')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_double(floatval($val)))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
								if($k == 'quantity')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_numeric($val))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
								if($k == 'tax_class')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_double($val))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
								if($k == 'tax')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_double($val))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
						}

						if(!$validRecord)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Record: ' . $errorPos . ' is not a valid input.';
						}
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_number = DB::table('invoices')
					->where('user_id', $this->user->id)
					->max('invoice_number') + 1;

				if ($invoice_number < $this->user->getSetting('invoice.startNumber', 0))
				{
						$invoice_number = $this->user->getSetting('invoice.startNumber', 0);
				}

				$customer_id = $customer->id;
				$comments = $request->comments;
				$duedate = $year . '-' . $month . '-' . $day;

				$invoice = new Invoice();
				$invoice->user_id = $this->user->id;
				$invoice->customer_id = $customer->id;
				$invoice->address_id = $customer->mailingContact->getAddress()->id;
				$invoice->currency_id = $currency = $this->user->getSetting('site.defaultCurrency',4);
				$invoice->invoice_number = $invoice_number;
				$invoice->status = Invoice::UNPAID;
				$invoice->due_at = date('Y-m-d', strtotime($duedate));
				$invoice->estimate = 1;
				$invoice->comments = $comments;
				$invoice->api_type = $this->api_type;
				$invoice->save();

				$tax = 0;
				$total = 0;
				$subtotalItem = 0;
				$subtotalPrice = 0;
				$subtotalTaxClass = 0;
				$subtotalTax = 0;

				if ($request->has('record'))
				{
						$countRecordItem = count($request->record['item']);

						for($x = 0 ; $x < $countRecordItem ; $x++)
						{
								$item = new InvoiceItem();
								$item->invoice_id = $invoice->id;
								$item->item = $request->record['item'][$x];
								$item->description = $request->record['description'][$x];
								$item->price = $request->record['price'][$x];
								$item->quantity = $request->record['quantity'][$x];
								$item->tax_class = @$request->record['tax_class'][$x];
								$item->tax = @$request->record['tax'][$x];
								$item->save();


								if($item->tax != '')
									$tax = $tax + $item->tax;
								if($item->price != '' && $item->quantity)
									$total = $total + ($item->price * $item->quantity);

								$subtotalItem++;
								$subtotalPrice =+ $total;
								$subtotalTaxClass++;
								$subtotalTax =+ $tax;
						}
				}

				$invoiceTotal = new InvoiceTotal();
				$invoiceTotal->invoice_id = $invoice->id;
				$invoiceTotal->item = $subtotalItem;
				$invoiceTotal->price = $subtotalPrice;
				$invoiceTotal->tax_class = $subtotalTaxClass;
				$invoiceTotal->tax = $subtotalTax;
				$invoiceTotal->save();

				$invoice->total = $total;
				$invoice->tax = $tax;
				$invoice->save();

				Invoices::sendEmail($invoice);

				return Response::json([
						'success' => true,
						'message' => 'Quote created successfully',
						'invoice_id' => $invoice->id
				], 200);
		}

		/* Accept Quote
			 Params
			 Header:
				- token: string (required)

				Body:
				- invoice_id: integer (required)
		*/
		public function acceptQuote(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('invoice_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Invoice ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_id = $request->invoice_id;

				if($this->api_type == 'sandbox')
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->where('estimate', 1)
															->first();
				else
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->where('estimate', 1)
															->first();

				if(!$invoice)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Quote ID: ' . $invoice_id . ' not found.'
																	],
																	400);
				}

				$invoice->estimate = 0;
				$invoice->save();

				Invoices::sendEmail($invoice);

				return Response::json([
						'success' => true,
						'message' => 'Quote ID: ' . $invoice->id . ' accepted successfully',
						'id' => $invoice->id
				], 200);
		}

		/* Delete Quote
			 Params
			 Header:
				- token: string (required)

				Body:
				- invoice_id: integer (required)
		*/
		public function deleteQuote(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('invoice_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Invoice ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_id = $request->invoice_id;

				if($this->api_type == 'sandbox')
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('estimate', 1)
															->where('api_type', $this->api_type)
															->first();
				else
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('estimate', 1)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$invoice)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Quote ID: ' . $invoice_id . ' not found.'
																	],
																	400);
				}

				$invoice->delete();

				return Response::json([
						'success' => true,
						'message' => 'Quote ID: ' . $invoice->id . ' deleted successfully'
				], 200);
		}

		/* Lists Invoice
			 Params
			 Header:
			 	- token: string (required)

			 Body:
			  - status: string (required) (UNPAID | PAID | OVERDUE | REFUNDED | CANCELED | PENDING | PENDING | ALL)
		*/
		public function listsInvoice(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('status'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Status is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$status = $request->status;
				if($status == 'UNPAID' || $status == 'PAID' || $status == 'OVERDUE' || $status == 'REFUNDED' || $status == 'CANCELED' || $status == 'UNPAID' || $status == 'PENDING' || $status == 'ALL')
				{
						if($this->api_type == 'sandbox')
						{
								$invoices = Invoice::where('user_id', $this->user->id)
																		->where('api_type', $this->api_type);

								if($status <> 'ALL')
								{
										$invoices = $invoices->where('status', constant('App\Invoice::' . strtoupper($status)));
								}
								$invoices = $invoices->orderBy('id')->get();
						}
						else
						{
								$invoices = Invoice::where('user_id', $this->user->id)
																		->where(function($query) {
																				$query->whereNull('api_type')
																							->orWhere('api_type', $this->api_type);
																		});

								if($status <> 'ALL')
								{
										$invoices = $invoices->where('status', constant('App\Invoice::' . strtoupper($status)));
								}
								$invoices = $invoices->orderBy('id')->get();
						}

						$invoiceArr = [];
						foreach($invoices as $invoice)
						{
								$invoiceObj = new \stdClass();
								$invoiceObj->id = $invoice->id;
								$invoiceObj->user = $invoice->user;
								$invoiceObj->customer = $invoice->customer;
								$invoiceObj->currency = $invoice->currency;
								$invoiceObj->order = $invoice->order;
								$invoiceObj->address = $invoice->address;
								$invoiceObj->invoice_number = $invoice->invoice_number;
								$invoiceObj->total = $invoice->total;
								$invoiceObj->credit = $invoice->credit;
								$invoiceObj->status = $invoice->status;
								$invoiceObj->due_at = $invoice->due_at;
								$invoiceObj->last_reminder = $invoice->last_reminder;
								$invoiceObj->created_at = $invoice->created_at;
								$invoiceObj->updated_at = $invoice->updated_at;
								$invoiceObj->deleted_at = $invoice->deleted_at;
								$invoiceObj->estimate = $invoice->estimate;
								$invoiceObj->tax_exempt = $invoice->tax_exempt;
								$invoiceObj->tax = $invoice->tax;
								$invoiceObj->comments = $invoice->comments;
								$invoiceObj->api_type = $invoice->api_type;
								$invoiceArr[] = $invoiceObj;
						}

						return Response::json([
								'success' => true,
								'invoices' => $invoiceArr
						], 200);
				}
				else
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Status is not valid.'
																	],
																	400);
				}
		}

		/* Create Invoice
			 Params
			 Header:
				- token: string (required)

				Body:
				- duedate: date (mm-dd-yyyy)(required)
				- customer_id: integer (required)
				- record[item]][]: string (optional)
				- record[description]][]: string (optional)
				- record[price]][]: double (optional)
				- record[quantity]][]: integer (optional)
				- record[tax_class]][]: integer (optional)
				- record[tax]][]: double (optional)
				- record[comments]: string (optional)
		*/
		public function createInvoice(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('duedate'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Due date is required.';
				}
				if(!$request->has('customer_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID is required.';
				}

				if($request->has('customer_id') && !is_numeric($request->customer_id))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID is not valid format.';
				}

				$customer = User::find($request->customer_id);
				if(!$customer)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID is invalid.';
				}

				if($request->has('duedate'))
				{
						$duedate = $request->duedate;
						$dateExploded = explode("-", $duedate);
						if(count($dateExploded) != 3)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Dudate is not valid format.';
						}
						else
						{
								$day = $dateExploded[1];
								$month = $dateExploded[0];
								$year = $dateExploded[2];
						}

						if(!checkdate($month, $day, $year))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Duedate is not a valid date.';
						}
				}

				if ($request->has('record'))
				{
						$validRecord = true;
						foreach ($request->record as $k => $value)
						{
								$errorPos = $k;
								if($k == 'item')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
								}
								if($k == 'price')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_double(floatval($val)))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
								if($k == 'quantity')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_numeric($val))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
								if($k == 'tax_class')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_double($val))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
								if($k == 'tax')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_double($val))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
						}

						if(!$validRecord)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Record: ' . $errorPos . ' is not a valid input.';
						}
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_number = DB::table('invoices')
					->where('user_id', $this->user->id)
					->max('invoice_number') + 1;

				if ($invoice_number < $this->user->getSetting('invoice.startNumber', 0))
				{
						$invoice_number = $this->user->getSetting('invoice.startNumber', 0);
				}

				$customer_id = $customer->id;
				$comments = $request->comments;
				$duedate = $year . '-' . $month . '-' . $day;

				$invoice = new Invoice();
				$invoice->user_id = $this->user->id;
				$invoice->customer_id = $customer->id;
				$invoice->address_id = $customer->mailingContact->getAddress()->id;
				$invoice->currency_id = $currency = $this->user->getSetting('site.defaultCurrency',4);
				$invoice->invoice_number = $invoice_number;
				$invoice->status = Invoice::UNPAID;
				$invoice->due_at = date('Y-m-d', strtotime($duedate));
				$invoice->estimate = 0;
				$invoice->comments = $comments;
				$invoice->api_type = $this->api_type;
				$invoice->save();

				$tax = 0;
				$total = 0;
				$subtotalItem = 0;
				$subtotalPrice = 0;
				$subtotalTaxClass = 0;
				$subtotalTax = 0;

				if ($request->has('record'))
				{
						$countRecordItem = count($request->record['item']);

						for($x = 0 ; $x < $countRecordItem ; $x++)
						{
								$item = new InvoiceItem();
								$item->invoice_id = $invoice->id;
								$item->item = $request->record['item'][$x];
								$item->description = $request->record['description'][$x];
								$item->price = $request->record['price'][$x];
								$item->quantity = $request->record['quantity'][$x];
								$item->tax_class = @$request->record['tax_class'][$x];
								$item->tax = @$request->record['tax'][$x];
								$item->save();


								if($item->tax != '')
									$tax = $tax + $item->tax;
								if($item->price != '' && $item->quantity)
									$total = $total + ($item->price * $item->quantity);

								$subtotalItem++;
								$subtotalPrice =+ $total;
								$subtotalTaxClass++;
								$subtotalTax =+ $tax;
						}
				}

				$invoiceTotal = new InvoiceTotal();
				$invoiceTotal->invoice_id = $invoice->id;
				$invoiceTotal->item = $subtotalItem;
				$invoiceTotal->price = $subtotalPrice;
				$invoiceTotal->tax_class = $subtotalTaxClass;
				$invoiceTotal->tax = $subtotalTax;
				$invoiceTotal->save();

				$invoice->total = $total;
				$invoice->tax = $tax;
				$invoice->save();

				Invoices::sendEmail($invoice);

				return Response::json([
						'success' => true,
						'message' => 'Invoice created successfully',
						'invoice_id' => $invoice->id
				], 200);
		}

		/* Delete Invoice
			 Params
			 Header:
				- token: string (required)

				Body:
				- invoice_id: integer (required)
		*/
		public function deleteInvoice(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('invoice_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Invoice ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_id = $request->invoice_id;

				if($this->api_type == 'sandbox')
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('estimate', 0)
															->where('api_type', $this->api_type)
															->first();
				else
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('estimate', 0)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$invoice)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Invoice ID: ' . $invoice_id . ' not found.'
																	],
																	400);
				}

				$invoice->delete();

				return Response::json([
						'success' => true,
						'message' => 'Invoice ID: ' . $invoice->id . ' deleted successfully'
				], 200);
		}

		/* Update Invoice
			 Params
			 Header:
				- token: string (required)

				Body:
				- invoice_id: integer (required)
				- duedate: date (mm-dd-yyyy)(required)
				- customer_id: integer (required)
				- record[item]][]: string (optional)
				- record[description]][]: string (optional)
				- record[price]][]: double (optional)
				- record[quantity]][]: integer (optional)
				- record[tax_class]][]: integer (optional)
				- record[tax]][]: double (optional)
				- record[comments]: string (optional)
		*/
		public function updateInvoice(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('invoice_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Invoice ID is required.';
				}
				if(!$request->has('duedate'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Due date is required.';
				}
				if(!$request->has('customer_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID is required.';
				}

				if($request->has('customer_id') && !is_numeric($request->customer_id))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID is not valid format.';
				}

				$customer = User::find($request->customer_id);
				if(!$customer)
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Customer ID is invalid.';
				}

				if($request->has('duedate'))
				{
						$duedate = $request->duedate;
						$dateExploded = explode("-", $duedate);
						if(count($dateExploded) != 3)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Dudate is not valid format.';
						}
						else
						{
								$day = $dateExploded[1];
								$month = $dateExploded[0];
								$year = $dateExploded[2];
						}

						if(!checkdate($month, $day, $year))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Duedate is not a valid date.';
						}
				}

				if ($request->has('record'))
				{
						$validRecord = true;
						foreach ($request->record as $k => $value)
						{
								$errorPos = $k;
								if($k == 'item')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
								}
								if($k == 'price')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_double(floatval($val)))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
								if($k == 'quantity')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_numeric($val))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
								if($k == 'tax_class')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_double($val))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
								if($k == 'tax')
								{
										if(!is_array($value))
										{
												$validRecord = false;
												break;
										}
										else
										{
												foreach($value as $val)
												{
														if(!is_double($val))
														{
																$validRecord = false;
																break;
														}
												}
										}
								}
						}

						if(!$validRecord)
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Record: ' . $errorPos . ' is not a valid input.';
						}
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$customer_id = $customer->id;
				$comments = $request->comments;
				$invoice_id = $request->invoice_id;

				$invoice = Invoice::find($invoice_id);
				if(!$invoice)
				{
					return Response::json([
																		'success' => false,
																		'errors' => 'Invoice ID: ' . $invoice_id . ' not found.'
																],
																400);
				}

				$duedate = $year . '-' . $month . '-' . $day;
				
				$invoice->user_id = $this->user->id;
				$invoice->customer_id = $customer->id;
				$invoice->address_id = $customer->mailingContact->getAddress()->id;
				$invoice->currency_id = $currency = $this->user->getSetting('site.defaultCurrency',4);
				$invoice->status = Invoice::UNPAID;
				$invoice->due_at = date('Y-m-d', strtotime($duedate));
				$invoice->estimate = 0;
				$invoice->comments = $comments;
				$invoice->api_type = $this->api_type;
				$invoice->save();

				$tax = 0;
				$total = 0;
				$subtotalItem = 0;
				$subtotalPrice = 0;
				$subtotalTaxClass = 0;
				$subtotalTax = 0;

				if ($request->has('record'))
				{
						$countRecordItem = count($request->record['item']);

						InvoiceItem::where('invoice_id', $invoice->id)->forceDelete();
						for($x = 0 ; $x < $countRecordItem ; $x++)
						{
								$item = new InvoiceItem();
								$item->invoice_id = $invoice->id;
								$item->item = $request->record['item'][$x];
								$item->description = $request->record['description'][$x];
								$item->price = $request->record['price'][$x];
								$item->quantity = $request->record['quantity'][$x];
								$item->tax_class = @$request->record['tax_class'][$x];
								$item->tax = @$request->record['tax'][$x];
								$item->save();


								if($item->tax != '')
									$tax = $tax + $item->tax;
								if($item->price != '' && $item->quantity)
									$total = $total + ($item->price * $item->quantity);

								$subtotalItem++;
								$subtotalPrice =+ $total;
								$subtotalTaxClass++;
								$subtotalTax =+ $tax;
						}
				}

				$invoiceTotal = InvoiceTotal::where('invoice_id', $invoice->id)->first();
				if(!$invoiceTotal) $invoiceTotal = new InvoiceTotal();
				$invoiceTotal->invoice_id = $invoice->id;
				$invoiceTotal->item = $subtotalItem;
				$invoiceTotal->price = $subtotalPrice;
				$invoiceTotal->tax_class = $subtotalTaxClass;
				$invoiceTotal->tax = $subtotalTax;
				$invoiceTotal->save();

				$invoice->total = $total;
				$invoice->tax = $tax;
				$invoice->save();

				Invoices::sendEmail($invoice);

				return Response::json([
						'success' => true,
						'message' => 'Invoice updated successfully',
						'invoice_id' => $invoice->id
				], 200);
		}

		/* Send Invoice
			 Params
			 Header:
				- token: string (required)

				Body:
				- invoice_id: integer (required)
		*/
		public function sendInvoice(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('invoice_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Invoice ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_id = $request->invoice_id;

				if($this->api_type == 'sandbox')
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('estimate', 0)
															->where('api_type', $this->api_type)
															->first();
				else
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('estimate', 0)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$invoice)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Invoice ID: ' . $invoice_id . ' not found.'
																	],
																	400);
				}

				Invoices::sendEmail($invoice);

				return Response::json([
						'success' => true,
						'message' => 'Invoice ID: ' . $invoice->id . ' sent successfully'
				], 200);
		}

		/* Send Invoice Reminder
			 Params
			 Header:
				- token: string (required)

				Body:
				- invoice_id: integer (required)
		*/
		public function sendInvoiceReminder(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('invoice_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Invoice ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_id = $request->invoice_id;

				if($this->api_type == 'sandbox')
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('estimate', 0)
															->where('api_type', $this->api_type)
															->first();
				else
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('estimate', 0)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$invoice)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Invoice ID: ' . $invoice_id . ' not found.'
																	],
																	400);
				}

				$daysBetweenReminders = $this->user->getSetting('invoice.reminders') ?: 0;

				$addTimeServer = strtotime('+2 hour');
				$due = new \DateTime($invoice->due_at);
				$now = new \DateTime(date('Y-m-d H:m:s', $addTimeServer));
				$daysLate = $due->diff($now)->days;

				$reminderNumber = 0;
				if($daysBetweenReminders > 0)
				{
						if($daysLate % $daysBetweenReminders == 0) {
							$reminderNumber = floor($daysLate / $daysBetweenReminders);
						}
				}

				$userFromEmail = $invoice->user->mailingContact->address->email;
				$userFromName = $invoice->user->mailingContact->address->contact_name;
				$subject= $this->user->getSetting('site.name') . ' - Payment is Due';
				$view = 'Invoices.invoiceReminderEmail';
				$content = [
											'user' => $invoice->user,
											'customer' => $invoice->customer,
											'invoice' => $invoice,
											'currency' => Currency::findOrFail($this->user->getSetting('site.defaultCurrency', 4)),
											'validationHash' => Invoices::getHash($invoice),
											'subTotal' => $invoice->total,
											'reminder' => $reminderNumber,
											'address' => Address::findOrFail($invoice->address_id)
									];
				Mail::to($invoice->customer)->send(new InvoiceEmail($userFromEmail, $userFromName, $subject, $content, $view));

				$invoice->last_reminder = now();
				$invoice->save();

				return Response::json([
						'success' => true,
						'message' => 'Invoice ID: ' . $invoice->id . ' sent successfully'
				], 200);
		}

		/* Capture Payment Invoice
			 Params
			 Header:
				- token: string (required)

				Body:
				- invoice_id: integer (required)
		*/
		public function capturePaymentInvoice(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('invoice_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Invoice ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_id = $request->invoice_id;

				if($this->api_type == 'sandbox')
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('estimate', 0)
															->where('api_type', $this->api_type)
															->first();
				else
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('estimate', 0)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$invoice)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Invoice ID: ' . $invoice_id . ' not found.'
																	],
																	400);
				}

				if($invoice->status == Invoice::UNPAID)
				{
						$getOrder = Order::find($invoice->order_id);

						if($getOrder && $getOrder->trial_order == 1) {
							return Response::json([
																				'success' => false,
																				'errors' => 'Invoice ID: ' . $invoice_id . ' is in trial period.'
																		],
																		400);
						}

						$daysBetweenReminders = Settings::get('invoice.reminders',0);
						$remindersBeforeSuspend = Settings::get('invoice.reminders4suspend',0);
						$lateFeePercentage = Settings::get('invoice.lateFees',0);
						$lateFeeTaxClass = Settings::get('invoice.lateFeesTax',0);
						$addTimeServer = strtotime('+2 hour');

						$due = new DateTime($invoice->due_at);
						$now = new DateTime(date('Y-m-d H:m:s', $addTimeServer));
						$daysLate = $due->diff($now)->days;
						$secondsLate = $now->getTimestamp() - $due->getTimestamp();

						$autoChargeRetry = [0,1,2,4]; //0 days late (first charge attempt), 1 day late, 3 days late, 7 days late, for a total of 3 retries

						$AttemptCharge = false;

						if($secondsLate > 0) $AttemptCharge = true;

						$sendReminderEmail = false;
						$reminderNumber = 0;
						if($daysBetweenReminders > 0)
						{
								if($daysLate % $daysBetweenReminders == 0) {
									//if dayslate is a multiple of daysbetweenreminders
									$sendReminderEmail = true;
									$reminderNumber = floor($daysLate / $daysBetweenReminders);
								}
						}

						$chargedLateFee = 0;
						$taxTotal = 0;
						if($daysLate === 1 && !empty($lateFeePercentage))
						{
							foreach($invoice->totals() as $total) {
								if($total->item === 'Tax') {
									$taxTotalId = $total->id;
									$taxTotal = $total->price;
									break;
								}
							}

							$subTotal = $invoice->total - $taxTotal;
							$lateFeeTotal = $lateFeePercentage / 100 * $subTotal;
							$newSubTotal = $subTotal + $lateFeeTotal;
							$newTaxTotal = $taxTotal;

							if(!empty($lateFeeTaxClass)) {
								$rate = TaxRates::join('taxZones','taxRates.zone_id','=','taxZones.id')
								->join('taxZoneCounties','taxZoneCounties.zone_id','=','taxZones.id')
								->where('taxZones.user_id','=',$user)
								->where('taxClasses.id','=',$lateFeeTaxClass)
								->where('taxZoneCounties.county_id','=',Address::where('id',$invoice->address_id)->first()->county_id);

								if(!empty($arrRates)) {
									$newTaxTotal = $rate / 100 * $subTotal;
								}

							}
							$newGrandTotal = $newSubTotal + $newTaxTotal;
							$lateFee = new InvoiceTotal();
							$lateFee->invoice_id = $invoice->id;
							$lateFee->item = '%'.$lateFeePercentage.' Late Fee';
							$lateFee->price = $lateFeeTotal;
							$lateFee->save();
							$chargedLateFee = true;
							if(!empty($newTaxTotal)) {
								$tax = InvoiceTotal::findOfFail($taxTotalId);
								$tax->price = $newTaxTotal;
								$tax->save();
							}
							$invoice->total = $newGrandTotal;
							$invoice->save();
						}

						$sendBillingFailed = false;
						$sendReceiptEmail = false;
						if($AttemptCharge) {
							$gateway = new PaymentController();
							$gateway->invoice = $invoice;
							$gateway->customer = User::findOrFail($invoice->customer_id);
							$gateway->currency = Currency::findOrFail($invoice->currency_id);
							$gateway->user = User::findOrFail($invoice->user_id);
							$paymentMethods = $gateway->getAvailablePaymentMethods();

							if(!empty($paymentMethods)) {
								if($paymentMethods['card']) {
									$savedMethod = $gateway->getUsersSavedPaymentMethodByType(0);
								} elseif($paymentMethods['stripe']) {
									$savedMethod = $gateway->getUsersSavedPaymentMethodByType(2, $invoice->customer->stripeId);
								} elseif($paymentMethods['bank']) {
									$savedMethod = $gateway->getUsersSavedPaymentMethodByType(1);
								}

								if(!empty($savedMethod)) {
									Controller::setInvoiceMode($invoice->id);
									$cart = Controller::formatCartData();
									$gateway->fixedDiscount = $cart['totalDiscounts'];
									$gateway->savedPaymentMethod = $savedMethod;

									if($paymentMethods['stripe'])
									{
											$gateway->billingAddress = Address::findOrFail($user->mailingContact->address_id);
											$gateway->paymentMethod['type'] = '0';

											$paymentIntent = '';
											foreach($savedMethod->data as $pm_data)
											{
													try {
														$paymentIntent = \Stripe\PaymentIntent::create([
															'amount' => $cart['subTotal'] * 100,
															'currency' => $invoice->currency->short_name,
															'customer' => $invoice->customer->stripeId,
															'payment_method' => $pm_data->id,
															'off_session' => true,
															'confirm' => true,
														]);
														break;
													} catch (\Stripe\Exception\CardException $e) {
														// Error code will be authentication_required if authentication is needed
														echo 'Error code is:' . $e->getError()->code;
														$payment_intent_id = $e->getError()->payment_intent->id;
														$payment_intent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
													}
										 }

											//create request
											if($paymentIntent != '')
											{
													$request = new Request([
																										'transaction_json' => json_encode($paymentIntent),
																										'transaction_id' => $paymentIntent->id,
																										'transaction_status' => $paymentIntent->status
																								]);

													$status = $gateway->pay($request);
											}
									}
									else
									{
											$gateway->billingAddress = Address::findOrFail($savedMethod->billing_address_id);
											$gateway->paymentMethod['type'] = '0';
											$status = $gateway->pay($savedMethod);
									}
								}
							}

							$sendReceiptEmail = false;
							$sendBillingFailed = true;
							if(!empty($status[0])) {
								$sendReceiptEmail = true;
								$sendBillingFailed = false;
								$invoice->status = Invoice::PAID;
								$invoice->save();
							}

						}

						if($sendBillingFailed) {
							$user = $this->user;
							$userFromEmail = $invoice->user->mailingContact->address->email;
							$userFromName = $invoice->user->mailingContact->address->contact_name;
							$subject= $user->getSetting('site.name') . ' - Billing Failed';
							$view = 'Invoices.invoicePaymentError';
							$content = [
														'user' => $this->user,
														'customer' => $invoice->customer,
														'invoice' => $invoice,
														'currency' => Currency::findOrFail($user->getSetting('site.defaultCurrency', 4)),
														'validationHash' => Invoices::getHash($invoice),
														'subTotal' => $invoice->total,
														'reminder' => $reminderNumber,
														'address' => Address::findOrFail($invoice->address_id),
														'tax' => $taxTotal
												];
							Mail::to($invoice->customer)->send(new InvoiceEmail($userFromEmail, $userFromName, $subject, $content, $view));
						}

						if($sendReceiptEmail) {
							$user = $this->user;
							$userFromEmail = $invoice->user->mailingContact->address->email;
							$userFromName = $invoice->user->mailingContact->address->contact_name;
							$subject= $user->getSetting('site.name') . ' - Payment Receipt';
							$view = 'Invoices.invoiceReceiptEmail';
							$content = [
														'user' => $invoice->user,
														'customer' => $invoice->customer,
														'invoice' => $invoice,
														'currency' => Currency::findOrFail($user->getSetting('site.defaultCurrency', 4)),
														'validationHash' => Invoices::getHash($invoice),
														'subTotal' => $invoice->total,
														'reminder' => $reminderNumber,
														'address' => Address::findOrFail($invoice->address_id),
														'tax' => $taxTotal
												];
							Mail::to($invoice->customer)->send(new InvoiceEmail($userFromEmail, $userFromName, $subject, $content, $view));
						}
					}

					return Response::json([
							'success' => true,
							'message' => 'Invoice ID: ' . $invoice->id . ' captured payment successfully'
					], 200);
		}

		/* Capture Payment Invoice
			 Params
			 Header:
				- token: string (required)

				Body:
				- invoice_id: integer (required)
		*/
		public function getPaymentMethodInvoice(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('invoice_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Invoice ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_id = $request->invoice_id;

				if($this->api_type == 'sandbox')
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$invoice)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Invoice ID: ' . $invoice_id . ' not found.'
																	],
																	400);
				}

				$gateway = new PaymentController();
				$gateway->invoice = $invoice;
				$gateway->customer = User::findOrFail($invoice->customer_id);
				$gateway->currency = Currency::findOrFail($invoice->currency_id);
				$gateway->user = User::findOrFail($invoice->user_id);
				$paymentMethods = $gateway->getAvailablePaymentMethods();

				return Response::json([
																	'success' => false,
																	'invoice_id' => $invoice->id,
																	'payment_methods' => $paymentMethods
															],
															200);
		}

		/* Get Transactions
			 Params
			 Header:
				- token: string (required)

				Body:
				- invoice_id: integer (required)
		*/
		public function getTransactionsInvoice(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('invoice_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Invoice ID is required.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_id = $request->invoice_id;

				if($this->api_type == 'sandbox')
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$invoice)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Invoice ID: ' . $invoice_id . ' not found.'
																	],
																	400);
				}

				$transactions = Transactions::where('invoice_id', $invoice->id)->get();

				return Response::json([
																	'success' => false,
																	'invoice_id' => $invoice->id,
																	'transactions' => $transactions
															],
															200);
		}

		/* Update Transaction
			 Params
			 Header:
				- token: string (required)

				Body:
				- invoice_id: integer (required)
				- transaction_id: string (required)
				- gateway_id: string (optional)
				- amount: double (required)
				- status: 0 = unpaid | 1 = paid (required)
				- message: string (optional)
				- json_response: json string (optional)
				- transaction_key: string (optional)
		*/
		public function updateTransactionInvoice(Request $request)
		{
				$errorMessage = '';
				if(!$request->has('invoice_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Invoice ID is required.';
				}

				if(!$request->has('transaction_id'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Transaction ID is required.';
				}

				if(!$request->has('amount'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Amount is required.';
				}

				if($request->has('amount'))
				{
						if(!is_double(floatval($request->has('amount'))))
						{
								if($errorMessage != '') $errorMessage .= ', ';
								$errorMessage .= 'Amount is not valid format.';
						}
				}

				if(!$request->has('status'))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Status is required.';
				}

				if($request->has('status') && !is_numeric($request->status))
				{
						if($errorMessage != '') $errorMessage .= ', ';
						$errorMessage .= 'Status is not valid format.';
				}

				if($errorMessage != '')
				{
						return Response::json([
																			'success' => false,
																			'errors' => $errorMessage
																	],
																	401);
				}

				$invoice_id = $request->invoice_id;

				if($this->api_type == 'sandbox')
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where('api_type', $this->api_type)
															->first();
				else
					$invoice = Invoice::where('id', $invoice_id)
															->where('user_id', $this->user->id)
															->where(function($query) {
																	$query->whereNull('api_type')
																				->orWhere('api_type', $this->api_type);
															})
															->first();

				if(!$invoice)
				{
						return Response::json([
																			'success' => false,
																			'errors' => 'Invoice ID: ' . $invoice_id . ' not found.'
																	],
																	400);
				}

				$transaction_id = $request->transaction_id;
				$user_id = $this->user->id;
				$customer_id = $invoice->customer_id;

				$gateway_id = '';
				if($request->has('gateway_id'))
				$gateway_id = $request->gateway_id;

				$currency_id = $this->user->getSetting('site.defaultCurrency',4);
				$amount = $request->amount;
				$status = $request->status;
				$json_response = '';
				if($request->has('json_response'))
				{
						$json_response = $request->json_response;
				}

				$transaction_key = '';
				if($request->has('$transaction_key'))
				{
						$transaction_key = $request->$transaction_key;
				}

				$transaction = Transactions::where('invoice_id', $invoice->id)->first();
				if(!$transaction) $transaction = new Transactions();
				$transaction->transaction_id = $transaction_id;
				$transaction->user_id = $user_id;
				$transaction->customer_id = $customer_id;
				$transaction->gateway_id = $gateway_id;
				$transaction->currency_id = $currency_id;
				$transaction->amount = $amount;
				$transaction->status = $status;
				$transaction->json_response = $json_response;
				$transaction->transaction_key = $transaction_key;
				$transaction->save();

				return Response::json([
																	'success' => true,
																	'message' => 'Transaction with Invoice ID: ' . $invoice->id . ' updated successfully'
															],
															200);
		}
}
