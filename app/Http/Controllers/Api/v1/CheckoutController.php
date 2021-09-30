<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'package_id' => 'required|integer|exists:site.order_group_packages,id',
            'cycle_id' => 'required|integer|exists:site.order_group_package_cycles,id',
            'callback_url' => 'required|url'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()->all()
            ]);
        }

        if ($request->filled('customer_id')) {
            $request->merge(['customer_id' => $request->input('customer_id')]);
        }

        $key = Str::uuid();
        Cache::put($key, $request->all(), now()->addHour());

        return response()->json([
            'success' => true,
            'message' => 'Successfully',
            'url' => route('checkout.form', ['key' => $key])
        ]);
    }
}