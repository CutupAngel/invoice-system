<?php

namespace App\Http\Controllers;

use Auth;
use Permissions;
use Illuminate\Http\Request;

use App\Http\Controllers\MarketingController;

use App\Discount;

class DiscountsController extends MarketingController
{
	protected $type;

	public function index(Request $request)
	{
		if ($request->ajax()) {

			$today = strtotime(date('Y-m-d h:m:s'));

			$return = [];
			foreach (Discount::where('user_id', Auth::User()->id)->where('type', $this->type)->get() as $data) {
				$status = 'Inactive';
				if (strtotime($data->start) <= $today
					&& ($data->end == '0000-00-00' || $today <= strtotime($data->end))) {
					$status = 'Active';
				}

				$return[] = [
					'DT_RowId' => $data->id,
					'value' => $data->value,
					'discount' => $data->discount,
					'start' => $data->start,
					'end' => ($data->end == '0000-00-00' ? 'Indefinite' : $data->end),
					'status' => $status,
					'code' => $data->value
				];
			}

			return json_encode(['data' => $return]);
		} else {
			if($this->type == Discount::CODE)
			{
				return view('Marketing.discountcodesForm');
			}
			else
			{
				return view('Marketing.fixeddiscountsForm');
			}
		}
	}

	public function show($id)
	{
		$data = Discount::findOrFail($id);
		if ($data->user_id != Auth::User()->id) {
			throw new Exception('Unauthorized');
		}

		return $data->toJson();
	}

	public function update(Request $request, $id)
	{
		if(trim($request->value) == '')
		{
				return trans('backend.marketing-discount-code-empty-error');
		}

		if($this->type == Discount::CODE)
		{
				if($request->input('discount') > 100 || $request->input('discount') < 0)
				{
						return trans('backend.inv-totalingorder-discountpercentage-error');
				}
		}
		else
		{
				if($request->input('discount') < 0 || $request->input('discount') > $request->value)
				{
						return trans('backend.inv-totalingorder-discountfixed-error');
				}
		}

		$startDate = explode('-', $request->start);
		if($startDate[0] > 9999)
		{
				return trans('backend.marketing-discount-start-date-error');
		}

		$endDate = explode('-', $request->end);
		if($endDate[0] > 9999)
		{
				return trans('backend.marketing-discount-end-date-error');
		}

		if ($id === '-1') {
			$discount = new Discount;
			$discount->user_id = Auth::User()->id;
			$discount->type = strip_tags($this->type);
		} else {
			$discount = Discount::findOrFail($id);
		}

		if ($discount->user_id !== Auth::User()->id) {
			return 'Unauthorized.';
		}

		$value = strip_tags($request->value);
		$discount->value = $value;
		$discount->discount = strip_tags($request->input('discount'));
		$discount->start = strip_tags($request->input('start'));
		$discount->end = strip_tags(($request->has('indefinite') ? '0000-00-00' : $request->input('end')));

		$discount->save();

		return $discount->id;
	}

	public function destroy($id)
	{
		$discount = Discount::findOrFail($id);
		if ($discount->user_id !== Auth::User()->id) {
			throw new Exception('Unauthorized.');
		}

		return (string) $discount->delete();
	}
}
