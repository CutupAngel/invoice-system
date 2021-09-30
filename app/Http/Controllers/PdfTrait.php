<?php

namespace App\Http\Controllers;

use PDF;
use File;
use App\Currency;
use DB;

trait PdfTrait
{
	public function generatePdfFile($invoice, $isSend = false)
	{
        $filename = DB::table('user_settings')
            ->where('user_id', $invoice->user_id)
            ->where('name', 'site.logo')
            ->first();

        $path = config('app.CDN');

        $logo = '';
        if ($filename) {
            $data = file_get_contents($path . $filename->value);
            $type = pathinfo($data, PATHINFO_EXTENSION);
            $logo = 'data:image/png;base64,' . base64_encode($data);
        }

        $pdf = PDF::setOptions([
            'images' => true
        ])->loadView('Invoices.viewInvoicePdf', [
            'invoice' => $invoice,
            'currency' => Currency::findOrFail($invoice->currency_id),
            'logo' => $logo,
        ])->setPaper('a4', 'portrait');

        $pdfPath = storage_path('pdf/' . $invoice->id);
        $pdfFileName = 'invoice.pdf';

        if (!File::isDirectory($pdfPath)) {
            File::makeDirectory($pdfPath, 0777, true, true);
        }

        $pdf->save($pdfPath . '/' . $pdfFileName);

        if ($isSend) {
            return $pdfPath . '/' . $pdfFileName;
        }

        return $pdf->stream();
	}
}
