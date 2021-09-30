<?php


namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use App\Invoice;

class TransactionsController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * INVOICE STATUS
     * 0 - unpaid
     * 1 - paid
     * 2 - overdue
     * 3 - refunded
     * 4 - canceled
     */
    public function addPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id'     => "required|integer|min:0",
            'date_paid'      => "required|date",
            'transaction_id' => "required|string",
            'status'         => [
                'required',
                Rule::in([0, 1, 2, 3, 4])
            ],
            'amount'         => "required|numeric|min:0",
            'payment_method' => [
                'required',
                'integer',
                Rule::in([0, 1, 2, 3])
            ]
        ]);

        $invoice = Invoice::where('id', (int)$request->get('invoice_id'))->first();

        if (!$invoice) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($validator->getMessageBag());
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withInput($request->all())
                ->withErrors($validator->getMessageBag());
        }

        $formStatus = $request->get('status');
        $invoiceStatus = $formStatus;

        $transaction = [
            'transaction_id' => $request->get('transaction_id'),
            'created_at'     => Carbon::parse($request->get('date_paid')),
            'invoice_id'     => $invoice->id,
            'amount'         => $request->get('amount'),
            'status'         => 1,
            'currency_id'    => $invoice->currency_id,
            'payment_method' => (int)$request->get('payment_method'),
            'message'        => "",
            'customer_id'    => $invoice->customer_id,
            'user_id'        => $invoice->user_id,
            'json_response'  => "{}"
        ];

        # If the billing was not fully paid or not paid at all
        if ((int)$invoice->status === 0) {
            if ($formStatus === '0') {
                $amountSummary = (int)($invoice->amount - $request->get('amount'));
                if ($amountSummary === 0) {
                    $invoiceStatus = '0';
                } else if ($amountSummary < 0) {
                    $this->redirectWithError('Given amount exceed the amount needed to be paid');
                }
            }
        } else if ((int)$invoice->status === 1) {
            if ($formStatus === '0') {
                $this->redirectWithError('Billing already fully paid');
            }
        }

        DB::beginTransaction();

        try {
            DB::table('transactions')->insert($transaction);
            DB::table('invoices')
                ->where('id', $transaction['invoice_id'])
                ->update([
                    'status' => $invoiceStatus
                ]);

            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();

            $this->redirectWithError($exception->getMessage());
        }

        return redirect()->back()->with('status', 'Transaction detail added.');
    }

    private function redirectWithError($message)
    {
        return redirect()->back()
            ->withInput(request()->all())
            ->with('has_error', $message);
    }
}
