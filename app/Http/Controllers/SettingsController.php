<?php

namespace App\Http\Controllers;

use Auth;
use Settings;
use DB;
use Google2FA;
use Input;
use Mail;
use Permissions;
use Response;
use Illuminate\Support\Facades\Storage;
use Redirect;
use Invoices;
use Validator;

use App\Address;
use App\Invoice;
use App\Transactions;
use App\Countries;
use App\Counties;
use App\User;
use App\User_Contact;
use App\TaxZones;
use App\TaxZoneCounties;
use App\TaxClasses;
use App\TaxRates;
use App\Plan;
use App\Plan_Cycle;
use App\Site;
use App\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * SettingsController constructor.
     */
	public function __construct()
	{
		$user = Auth::User();
		if (!Permissions::has('settings') && $user && !$user->isCustomer()) {
			//throw new Permissions::$exception;
			return redirect(route('login'));
		}
	}

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
	public function getMyAccountForm(Request $request)
	{
		$user = $request->user();
		return view('Settings.myAccountForm', compact('user'));
	}

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
	public function postMyAccountForm(Request $request)
	{
		$user = $request->user();

		if ($request->ajax() && $request->has('action')) {
			switch ($request->input('action')) {
				case 'get2fa':
					if (empty($user->authSecret)) {
						$user->authSecret = Google2FA::generateSecretKey();
						$user->save();
					}
					return Response::json([
						'QR' => Google2FA::getQRCodeInline($user->username, $user->email, $user->authSecret)
					]);
					break;
				case 'verify2fa':
					$codeValid = Google2FA::verifyKey($user->authSecret, $request->input('2faverify'));
					if ($request->has('2faverify') && $codeValid) {
						$user->authEnabled = true;
						$user->save();

						session()->put('2faAuthed',1);

						return Response::json([
							'valid' => true
						]);
					} else {
						return Response::json([
							'valid' => false
						]);
					}
					break;
				case 'remove2fa':
					$user->authEnabled = false;
					$user->authSecret = false;
					$user->save();

					return Response::json([
						'success' => true
					]);
					break;
				default:
					return Response::json([
						'fucked'=>true
					]);
			}
		} else {
		    if (!$request->filled('password')) {
		        $request->request->remove('password');
		        $request->request->remove('password_confirmation');
            }

			$this->validate($request, [
				'username' => 'required',
				'password' => 'sometimes|confirmed',
                'site-name' => 'required'
			]);

			$user->username = $request->input('username');
			if ($request->filled('password')) {
				$user->password = bcrypt($request->input('password'));
			}

			$user->save();

			if (!$user->siteSettings('name')) {
				DB::table('user_settings')->insert([
					'name' => 'site.name',
					'value' => $request->input('site-name'),
					'user_id' => $user->id
				]);
			} else {
				DB::table('user_settings')
					->where('user_id', $user->id)
					->where('name', 'site.name')
					->update(['value' => $request->input('site-name')]);
			}

			if ($request->hasFile('site-logo') && $request->file('site-logo')->isValid()) {
				$filename = "clientLogoFiles/".Str::random(20) ."-". time()."-". $user->id ."-logo." . $request->file('site-logo')->getClientOriginalExtension();

				Storage::disk('minio')->put($filename, file_get_contents(Input::file('site-logo')), 'public');

				if (Storage::disk('minio')->has($filename)) {
					if (!$user->siteSettings('logo')) {
						DB::table('user_settings')->insert([
							'name' => 'site.logo',
							'value' => $filename,
							'user_id' => $user->id
						]);
					} else {
						DB::table('user_settings')
							->where('user_id', $user->id)
							->where('name', 'site.logo')
							->update(['value' => $filename]);
					}
				}
			}


			return redirect('/settings/my-account');
		}
	}

	public function getMyAccountFormCustomer()
	{
		$user = Auth::User();

		return view('Settings.myAccountForm', [
			'user' => $user
		]);
	}

	public function postMyAccountFormCustomer(Request $request)
	{
		$user = Auth::User();

		if ($request->ajax() && $request->has('action')) {
			switch ($request->input('action')) {
				case 'get2fa':
					if (empty($user->authSecret)) {
						$user->authSecret = Google2FA::generateSecretKey();
						$user->save();
					}

					return Response::json([
						'QR' => Google2FA::getQRCodeInline('BillingServ', $user->username, $user->authSecret, 200)
					]);
					break;
				case 'verify2fa':
					$codeValid = Google2FA::verifyKey($user->authSecret, $request->input('2faverify'));
					if ($request->has('2faverify') && $codeValid) {
						$user->authEnabled = true;
						$user->save();

						return Response::json([
							'valid' => true
						]);
					} else {
						return Response::json([
							'valid' => false
						]);
					}
					break;
				case 'remove2fa':
					$user->authEnabled = false;
					$user->authSecret = false;
					$user->save();

					return Response::json([
						'success' => true
					]);
					break;
				default:
					return Response::json([
						false
					]);
			}
		} else {
            if (!$request->filled('password')) {
                $request->request->remove('password');
                $request->request->remove('password_confirmation');
            }

			$this->validate($request, [
                'username' => 'required',
                'password' => 'sometimes|confirmed',
			]);

			$user->username = $request->input('username');
			if ($request->has('password')) {
				$user->password = bcrypt($request->input('password'));
			}

			$user->save();

			return redirect('/settings/myaccount');
		}
	}

	private function generate_options ($arr, $selected_id)
	{
		$html = '';
		foreach ($arr as $k => $v) {
			$selected = ($v->id == $selected_id) ? ' selected' : '';
			$html .= "<option {$selected} value='{$v->id}'>{$v->name}</option>";
		}
		return $html;
	}

	public function getMyAccount()
	{
		$user = Auth::User();

		$data = [
			'user' => $user,
			'contacts' => [],
			'countries' => Countries::orderByRaw('case when id IN(222,223) then -1 else id end,id')->get()
		];

		foreach ($user->contacts as $contacts) {
			$data['contacts'][$contacts->type] = $contacts->address;
			$data['counties'][$contacts->type] = DB::table('main.counties')
						->select('id', 'name')
						->where('country_id', $contacts->address['country_id'])
						->get();
		}

		for ($i = 0; $i <= 4; $i++) {
			if (!isset($data['contacts'][$i])) {
				$data['contacts'][$i] = new Address;
			}
		}

		return view('Settings.myAccount', $data);
	}

	public function postMyAccount(Request $request)
	{
		$user = Auth::User();

		if ($request->ajax() && $request->has('action')) {
			switch ($request->input('action')) {
				case 'contacts':
					$rules = array(
						'business_name'	=> 'required|regex:/^[a-zA-Z0-9_ -]+$/u',
						'contact_name'	=> 'required|regex:/^[a-zA-Z0-9_ -]+$/u',
						'phone'			=> 'required|regex:/^[0-9+ -]+$/u',
						'email'			=> 'required|email',
						'address_1'		=> 'required',
						'city'			=> 'required|regex:/^[a-zA-Z0-9_ -]+$/u',
						'county_id'		=> 'required|exists:main.counties,id',
						'country_id'	=> 'required|exists:main.countries,id',
						'address_type'	=> 'required',
						'postal_code'	=> 'required|regex:/^[a-zA-Z0-9 ]+$/u'
					);
					$validator = Validator::make(Input::all(), $rules);
					if ($validator->fails())
					{
					    return Response::json(array(
					        'success' => false,
					        'errors' => $validator->getMessageBag()->toArray()
					    ), 200);
					}

                    $contact = User_Contact::where([ ['user_id','=', $user->id], ['type', '=', $request->address_type] ])->first();
					$address = null;
					if (!empty($contact)) {
                        if(!empty($request->address_id)){
                           $address = Address::where('id',$request->address_id)->first();
                        }else{
                            $address = new Address();
                        }
					}
					try {
						if (!$contact) {
							$address = new Address();

							//CONTACT
							$contact = new User_Contact;
							$contact->type = $request->input('address_type');
							$contact->user_id = Auth::User()->id;

							//MAILING
							$contactMailing = new User_Contact;
							$contactMailing->type = 2;
							$contactMailing->user_id = Auth::User()->id;

							//BILLING
							$contactBilling = new User_Contact;
							$contactBilling->type = 3;
							$contactBilling->user_id = Auth::User()->id;

							//ADMIN
							$contactAdmin = new User_Contact;
							$contactAdmin->type = 4;
							$contactAdmin->user_id = Auth::User()->id;

							//TECH
							$contactTech = new User_Contact;
							$contactTech->type = 5;
							$contactTech->user_id = Auth::User()->id;
						}

						$address->business_name	= $request->input('business_name');
						$address->contact_name	= $request->input('contact_name');
						$address->phone			= $request->input('phone');
						$address->fax			= $request->input('fax');
						$address->email			= $request->input('email');
						$address->address_1		= $request->input('address_1');
						$address->address_2		= $request->input('address_2');
						$address->address_3		= $request->input('address_3');
						$address->address_4		= $request->input('address_4');
						$address->city			= $request->input('city');
						$address->county_id		= $request->input('county_id');
						$address->country_id	= $request->input('country_id');
						$address->postal_code	= $request->input('postal_code');
						$address->save();

						//CONTACT
						if(isset($contact))
						{
							$contact->address_id = $address->id;
							$contact->save();
						}

						//MAILING
						if(isset($contactMailing))
						{
							$contactMailing->address_id = $address->id;
							$contactMailing->save();
						}

						//BILLING
						if(isset($contactBilling))
						{
							$contactBilling->address_id = $address->id;
							$contactBilling->save();
						}

						//ADMIN
						if(isset($contactAdmin))
						{
							$contactAdmin->address_id = $address->id;
							$contactAdmin->save();
						}

						//TECH
						if(isset($contactTech))
						{
							$contactTech->address_id = $address->id;
							$contactTech->save();
						}

					} catch (\Exeption $ex) {
						// error
					}

					return Response::json([
						'success' => true
					]);

					break;
				case 'get_region':
					$data = Counties::where('country_id', $request->input('country'))->get();

					$return = ['options' => $this->generate_options($data, '')];
					return Response::json($return);
				break;
				case '6':
					break;
			}
		}
	}

	public function getMyAccountCustomer()
	{
		$user = Auth::User();

		$data = [
			'user' => $user,
			'contacts' => [],
			'countries' => Countries::orderByRaw('case when id IN(222,223) then -1 else id end,id')->get()
		];

		foreach ($user->contacts as $contacts) {
			$data['contacts'][$contacts->type] = $contacts->address;
			$data['counties'][$contacts->type] = DB::table('main.counties')
						->select('id', 'name')
						->where('country_id', $contacts->address['country_id'])
						->get();
		}

		for ($i = 0; $i <= 4; $i++) {
			if (!isset($data['contacts'][$i])) {
				$data['contacts'][$i] = new Address;
			}
		}

		return view('Settings.myAccount', $data);
	}

	public function postMyAccountCustomer(Request $request)
	{
		$user = Auth::User();

		if ($request->ajax() && $request->has('action') && $user) {
			switch ($request->input('action')) {
				case 'contacts':
					$this->validate($request, [
						'business_name'	=> 'required',
						'contact_name'	=> 'required',
						'phone'			=> 'required',
						'email'			=> 'required|email',
						'address_1'		=> 'required',
						'city'			=> 'required',
						'county_id'		=> 'required|exists:main.counties,id',
						'country_id'	=> 'required|exists:main.countries,id',
						'address_type'	=> 'required',
						'postal_code'	=> 'required'
					]);

					$contact = $user->contacts($request->input('address_type'))->first();

					$address = null;
					if ($contact) {
						$address = Address::find($request->get('address_id'));
					}

					if (!$contact) {
						$address = new Address();

						//CONTACT
						$contact = new User_Contact;
						$contact->type = $request->input('address_type');
						$contact->user_id = Auth::User()->id;

						//MAILING
						$contactMailing = new User_Contact;
						$contactMailing->type = 1;
						$contactMailing->user_id = Auth::User()->id;

						//BILLING
						$contactBilling = new User_Contact;
						$contactBilling->type = 2;
						$contactBilling->user_id = Auth::User()->id;

						//ADMIN
						$contactAdmin = new User_Contact;
						$contactAdmin->type = 3;
						$contactAdmin->user_id = Auth::User()->id;

						//TECH
						$contactTech = new User_Contact;
						$contactTech->type = 4;
						$contactTech->user_id = Auth::User()->id;
					}

					$address->business_name	= $request->input('business_name');
					$address->contact_name	= $request->input('contact_name');
					$address->phone			= $request->input('phone');
					$address->fax			= $request->input('fax');
					$address->email			= $request->input('email');
					$address->address_1		= $request->input('address_1');
					$address->address_2		= $request->input('address_2');
					$address->address_3		= $request->input('address_3');
					$address->address_4		= $request->input('address_4');
					$address->city			= $request->input('city');
					$address->county_id		= $request->input('county_id');
					$address->country_id	= $request->input('country_id');
					$address->postal_code	= $request->input('postal_code');
					$address->save();

					//CONTACT
					$contact->address_id = $address->id;
					$contact->save();

					//MAILING
					$contactMailing->address_id = $address->id;
					$contactMailing->save();

					//BILLING
					$contactBilling->address_id = $address->id;
					$contactBilling->save();

					//ADMIN
					$contactAdmin->address_id = $address->id;
					$contactAdmin->save();

					//TECH
					$contactTech->address_id = $address->id;
					$contactTech->save();

					return Response::json(['success' => true]);
					break;
				case 'get_region':
					$data = Counties::where('country_id', $request->input('country'))->get();

					$return = ['options' => $this->generate_options($data, '')];
					return Response::json($return);
				break;
				case '6':
					break;
			}
		}
	}

	public function postGetRegionsOfCountry(Request $request)
	{
		if ($request->ajax() && $request->has('country')) {
			$counties = Counties::where('country_id', $request->input('country'))->get();
			$html = '<div class="col-md-12" id="countiesOf'.$request->input('country').'">';
			foreach($counties as $k=>$v)
			{
				$html = $html . '<div class="col-md-4"><input type="checkbox" name="selectedCounties[]" value="' . $v->id .'"/> <span>' . $v->name . '</span></div>';
			}
			return $html.'</div>';
		}
		else
		{
			abort(404);
		}
	}

	public function postGetSavedClass(Request $request)
	{
		if ($request->ajax() && $request->has('class')) {
			$user = Auth::User();
			$classId = $request->input('class');

			$zones = [];
			$className = '';
			$class = TaxClasses::select('id', 'name', 'default')->where('id', $classId)->where('user_id','=',$user->id)->first();
			$zones = DB::table('taxZones')->select('taxZones.id', 'taxZones.name', 'taxRates.rate')->leftJoin('taxRates',function ($join) use ($classId){
				$join->on('taxZones.id','=','taxRates.zone_id')->where('taxRates.class_id','=',$classId);
			})->where('taxZones.user_id', $user->id)->get();

			$html = '';
			foreach($zones as $zone)
			{
				$classDefault = '';
				$value = '';
				if(isset($class) && !empty($class))
				{
					$className = $class->name;
					$classDefault = $class->default;
				}
				if(is_object($zone) && property_exists($zone,'rate'))
				{
					$value = $zone->rate;
				}
				$html = $html . '<tr><td>'.$zone->name.'</td><td><input type="text" name="rate['.$zone->id.']" value="'.$value.'"/></td></tr>';
			}
			return Response::json(['html'=>$html,'name'=>$className,'default' => $classDefault]);
		}
		else
		{
			abort(404);
		}
	}

	public function postDeleteZone(Request $request)
	{
		if($request->ajax() && $request->has('zoneId') && is_numeric($request->input('zoneId')))
		{
			$user = Auth::User();
			TaxZones::where('id','=',$request->input('zoneId'))->where('user_id','=',$user->id)->delete();
			return $this->getUpdatedTaxData();
		}
		abort(500);
	}

	public function postDeleteClass(Request $request)
	{
		if($request->ajax() && $request->has('classId') && is_numeric($request->input('classId')))
		{
			$user = Auth::User();
			TaxClasses::where('id','=',$request->input('classId'))->where('user_id','=',$user->id)->delete();
			return $this->getUpdatedTaxData();
		}
		abort(500);
	}

	public function postSaveClass(Request $request)
	{
		if($request->ajax() && $request->has('classId') && $request->has('className') && $request->has('rate') && is_numeric($request->input('classId')))
		{
			$user = Auth::User();
			$classId = $request->input('classId');

			if($classId < 1)
			{
				$class = new TaxClasses();
			}
			else
			{
				$class = TaxClasses::where('id','=',$classId)->where('user_id','=',$user->id)->first();
				TaxRates::where('class_id','=',$class->id)->delete();
			}
			if($request->input('classDefault'))
			{
				TaxClasses::where('default',1)->where('user_id',$user->id)->update(['default' => 0]);
			}
			$class->user_id = $user->id;
			$class->name = $request->input('className');
			$class->default = $request->input('classDefault');
			$class->save();

			foreach($request->input('rate') as $k=>$v)
			{
				$rate = new TaxRates();
				$rate->zone_id = $k;
				$rate->class_id = $class->id;
				$rate->rate = $v;
				$rate->save();
			}

			return $this->getUpdatedTaxData();
		}
		else
		{
			abort(500);
		}
	}

	public function postSaveZone(Request $request)
	{
		if(!$request->ajax())
		{
			abort(500);
		}
		if(!$request->has('zoneId') || !is_numeric($request->input('zoneId')))
		{
			abort(500);
		}
		if(!$request->has('zoneName'))
		{
			abort(500);
		}
		if(!$request->has('selectedCounties'))
		{
			abort(500);
		}
		$zoneId = $request->input('zoneId');

		$user = Auth::User();

		if($zoneId < 1)
		{
			$zone = new TaxZones();
		}
		else
		{
			$zone = TaxZones::where('id','=',$zoneId)->where('user_id','=',$user->id)->first();
			TaxZoneCounties::where('zone_id','=',$zone->id)->delete();
		}
		$zone->user_id = $user->id;
		$zone->name = $request->input('zoneName');
		$zone->save();

		foreach($request->input('selectedCounties') as $k=>$v)
		{
			$TaxZoneCounty = new TaxZoneCounties();
			$TaxZoneCounty->zone_id = $zone->id;
			$TaxZoneCounty->county_id = $v;
			$TaxZoneCounty->save();
		}

		return $this->getUpdatedTaxData();
}

	public function postGetSavedZone(Request $request)
	{
		if ($request->ajax() && $request->has('zone')) {
			$user = Auth::User();
			if($request->input('zone') > 0)
			{
				$zone = TaxZones::where('id','=',$request->input('zone'))->where('user_id', $user->id)->first();
				$zoneId = $zone->id;
				$zoneName = $zone->name;
			}
			else
			{
				$zoneId = 0;
				$zoneName = '';
			}
			$taxCounties = DB::table('main.counties')->select('counties.id', 'counties.name','counties.country_id','taxZoneCounties.id as checked')->leftJoin('taxZoneCounties',function($join) use ($zoneId){
				$join->on('counties.id','=','taxZoneCounties.county_id')->where('taxZoneCounties.zone_id','=',$zoneId);
			})->where('counties.country_id','=',222)->get();
			$countries = [222];

			$html = '';
			foreach($countries as $k=>$v)
			{
				$html2 = '';
				$checkedCount = 0;
				$display = 'display:none;';
				if($v == 222)
				{
					//$display = 'display:block;';
				}
				$html2 = $html2 . '<div id="countiesOf'.$v.'" class="col-md-12"><table>';
				$x = 0;
				foreach($taxCounties as $k2=>$v2)
				{
					$checked = ' ';
					if(!empty($v2->checked))
					{
						$checked = ' checked ';
						$checkedCount = $checkedCount + 1;
					}

					if($x == 0)
					{
							$html2 = $html2 . '<tr>';
					}
					$html2 = $html2 . '<td width="200px"><input type="checkbox"'.$checked.'name="selectedCounties[]" class="col-md-2" value="' . $v2->id .'">' . $v2->name . '</input></td>';
					if($x == 1)
					{
							$html2 = $html2 . '</tr>';
					}

					if($x >= 1)
					{
							$x = 0;
					}
					else
					{
							$x++;
					}
				}
				$html2 = $html2 . '</table></div>';
				//if($checkedCount > 0 || $v)
				//{
					$html = $html . $html2;
				//}
			}
			return Response::json(['html'=>$html,'name'=>$zoneName]);
		}
		else
		{
			abort(404);
		}
	}

	private function getUpdatedTaxData()
	{
		$user = Auth::User();
		/*$zones = TaxZones::with('zoneCounties')->where('user_id', $user->id)->get();
		$arrZones = [];
		foreach($zones as $zone)
		{
			$arrZones[$zone->id] = [
				'id'=>$zone->id,
				'name'=>$zone->name,
				'counties'=>$zone->zoneCounties
			];

		}
		print_r($arrZones);
		die();
		*/

		$classes = TaxClasses::select('id', 'name', 'default')->where('user_id', $user->id)->get();
		$zones = TaxZones::select('id', 'name')->where('user_id', $user->id)->get();

		$zonesHtml = '';
		foreach($zones as $k=>$v)
		{
			$zonesHtml = $zonesHtml . '						<tr>
							<td>'.$v->name.'</td>
							<td class="tools text-center">
								<button onclick="editTaxZone(this);" class="frmZoneBtnEdit btn btn-default" data-target="'.$v->id.'">
									<i class="fa fa-pencil-square-o"></i> Edit
								</button>
								<button onclick="deleteTaxZone(this);" data-target="'.$v->id.'" class="frmZoneBtnDelete btn btn-default">
									<i class="fa fa-trash-o"></i> Delete
								</button>
							</td>
						</tr>';
		}

		$classesHtml = '';
		foreach($classes as $k=>$v)
		{
			$default = '';
			if($v->default)
			{
				$default = 'Default';
			}
			$classesHtml = $classesHtml . '						<tr>
							<td>'.$v->name.'</td>
							<td class="tools text-center">
								<button onclick="editTaxClass(this);" class="frmClassBtnEdit btn btn-default" data-target="'.$v->id.'">
									<i class="fa fa-pencil-square-o"></i>Edit
								</button>
								<button onclick="deleteTaxClass(this);" data-target="'.$v->id.'" class="frmClassBtnDelete btn btn-default">
									<i class="fa fa-trash-o"></i> Delete
								</button>
							</td>
							<td>'.$default.'</td>
						</tr>';
		}
		$data = [
			'classes'=>$classesHtml,
			'zones'=>$zonesHtml
		];
		return $data;
	}

    public function getTaxRates()
	{
		return view('Settings.taxratesForm');
	}
    public function getTaxClasses()
	{
		return view('Settings.taxclassesForm');
	}
    public function getTaxZones()
	{
		$user = Auth::User();
		$data['zones'] = TaxZones::select('id', 'name')->where('user_id', $user->id)->get();
		$data['classes'] = TaxClasses::select('id', 'name', 'default')->where('user_id', $user->id)->get();
		$data['countries'] = Countries::orderByRaw('case when id IN(222,223) then -1 else id end,id')->get();

		return view('Settings.taxzonesForm',$data);
	}
    public function getFrontEndTheme()
    {
        return view('Settings.frontendtheme');
    }
    public function getInvoicesTheme()
    {
        return view('Settings.invoicestheme');
    }
    public function getDesignSettings()
    {
		$user = Auth::User();
		$customCSS = '';
		if(!empty($user->siteSettings('customCSS')))
		{
			$temp = file_get_contents(config('app.CDN').$user->siteSettings('customCSS'));
			if(!empty($temp))
			{
				$customCSS = $temp;
			}
		}
		$data = [
			'customHeader'=>$user->siteSettings('headerHTML'),
			'customFooter'=>$user->siteSettings('footerHTML'),
			'customCSS'=>$customCSS,
			'invoiceColor'=>$user->siteSettings('invoiceColor')
		];
        return view('Settings.designSettings',$data);
    }
    public function postDesignSettings(Request $request)
    {
		if($request->ajax())
		{
			$user = Auth::User();

			if ($request->has('invoiceColor')) {
				DB::table('user_settings')->where('name','=','site.invoiceColor')->where('user_id','=',$user->id)->delete();

				DB::table('user_settings')->insert([
					'name' => 'site.invoiceColor',
					'value' => $request->input('invoiceColor'),
					'user_id' => $user->id
				]);
			}

			if ($request->has('headerHTML')) {
				$headerHTML = $this->filterHtml($request->input('headerHTML'));
				//basic filter
				//$headerHTML = str_replace(['</textarea>','<script','javascript:','onclick='],'',$headerHTML);

				DB::table('user_settings')->where('name','=','site.headerHTML')->where('user_id','=',$user->id)->delete();

				DB::table('user_settings')->insert([
					'name' => 'site.headerHTML',
					'value' => $headerHTML,
					'user_id' => $user->id
				]);
			}
			else{
				DB::table('user_settings')->where('name','=','site.headerHTML')->where('user_id','=',$user->id)->delete();
			}

			if ($request->has('footerHTML')) {
				$footerHTML = $this->filterHtml($request->input('footerHTML'));
				//basic filter
				//$footerHTML = str_replace(['</textarea>','<script','javascript:','onclick='],'',$footerHTML);

				DB::table('user_settings')->where('name','=','site.footerHTML')->where('user_id','=',$user->id)->delete();

				DB::table('user_settings')->insert([
					'name' => 'site.footerHTML',
					'value' => $footerHTML,
					'user_id' => $user->id
				]);
			}
			else{
				DB::table('user_settings')->where('name','=','site.footerHTML')->where('user_id','=',$user->id)->delete();
			}

			if ($request->has('customCSS')) {
				$customCSS = $request->input('customCSS');

				//create hash, create css file using hash as name, store filename in customCSS, upload file to cdn
				$filename = "clientCustomCSS/".str_random(20) ."-". time()."-". $user->id .".css";

				Storage::disk('minio')->put($filename, $customCSS, 'public');

				if (Storage::disk('minio')->has($filename)) {
					DB::table('user_settings')->where('name','=','site.customCSS')->where('user_id','=',$user->id)->delete();
					DB::table('user_settings')->insert([
						'name' => 'site.customCSS',
						'value' => $filename,
						'user_id' => $user->id
					]);
				}
			}
			else{
				DB::table('user_settings')->where('name','=','site.customCSS')->where('user_id','=',$user->id)->delete();
			}
			return Response::json(['success' => true]);
		}
    }

	//removes scripts from html
	private function filterHtml($html)
	{
		try{
			$d = new \DOMDocument();
			$d->preserveWhiteSpace = false;
			libxml_use_internal_errors(true);
			$d->loadHTML($html);
			libxml_use_internal_errors(false);
			$xpath = new \DOMXPath($d);

			$scripts = $xpath->query('//script');

			$domElemsToRemove = array();
			if(!empty($scripts))
			{
				foreach ($scripts as $node) {
					 $node->parentNode->removeChild($node);
				}
			}

			$events = [
				'onblur',
				'onchange',
				'oncontextmenu',
				'onfocus',
				'oninput',
				'oninvalid',
				'onreset',
				'onsearch',
				'onselect',
				'onsubmit',
				'onclick',
				'ondblclick',
				'ondrag',
				'ondragend',
				'ondragenter',
				'ondragleave',
				'ondragover',
				'ondragstart',
				'ondrop',
				'onmousedown',
				'onmousemove',
				'onmouseout',
				'onmouseover',
				'onmouseup',
				'onmousewheel',
				'onscroll',
				'onwheel',
				'oncopy',
				'oncut',
				'onpaste',
				'onabort',
				'oncanplay',
				'oncanplaythrough',
				'oncuechange',
				'ondurationchange',
				'onemptied',
				'onended',
				'onerror',
				'onloadeddata',
				'onloadedmetadata',
				'onloadstart',
				'onpause',
				'onplay',
				'onplaying',
				'onprogress',
				'onratechange',
				'onseeked',
				'onseeking',
				'onstalled',
				'onsuspend',
				'ontimeupdate',
				'onvolumechange',
				'onwaiting',
				'onerror',
				'onshow',
				'ontoggle',
				'onkeydown',
				'onkeyup',
				'onkeypress',
				'onwaiting'
			];

			$onAttributes = $xpath->query("//*/@*[starts-with(name(), 'on')]");
			foreach ($onAttributes as $onAttribute) {
				$onAttribute->ownerElement->removeAttributeNode($onAttribute);
			}
			$html = $d->saveHTML();
			$html = preg_replace('/href="javascript:[^"]+"/', '', $html);
			$html = explode('<body>',$html);
			if(isset($html[1]))
			{
				$html = explode('</body>',$html[1]);
			}
			else
			{
				$html = explode('</body>',$html[0]);
			}
			return $html[0];
		}catch(Exception $e)
		{
			return '';
		}
	}

	public function postChangePlan(Request $request)
	{
		$this->validate($request, [
			'plan' => 'required|exists:main.billingserv_plans,id'
		]);

		$plan = $request->input('plan');
		$planInfo = Plan::where('id', $plan)->first();
		$planCycle = Plan_Cycle::where('plan_id', $plan)->first();
		$price = $planCycle->price;

		$currentSubscription = Subscription::where(array('site_id' => config('app.site')->id, 'status' => Subscription::SETUP))->first();

		if ($currentSubscription) {
			if ($currentSubscription->plan_id == $plan) {
				return Redirect::back()->with(['status' => 'You are already subscribed to this plan']);
			}

			if (!empty($currentSubscription->newplan) && $currentSubscription->newplan != 0) {
				Subscription::where('id', $currentSubscription->id)->update(array('newplan' => $plan));
				return Redirect::back()->with(['status' => 'Plan will be changed at the end of this billing period']);
			}

			$currentCycle = Plan_Cycle::where('plan_id', $currentSubscription->plan_id)->first();

			if ($price > $currentCycle->price) { // Plan is being upgraded
				// Charge difference
				$dates = [
					1 => '',
		            2 => '+1 day',
		            3 => '+1 week',
		            4 => '+2 weeks',
		            5 => '+1 month',
		            6 => '+2 months',
		            7 => '+3 months',
		            8 => '+4 months',
		            9 => '+5 months',
		            10 => '+6 months',
		            11 => '+7 months',
		            12 => '+8 months',
		            13 => '+9 months',
		            14 => '+10 months',
		            15 => '+11 months',
		            16 => '+1 year',
		            17 => '+2 years',
		            18 => '+3 years'
		        ];
		        $days = [
		        	1 => 0,
		        	2 => 1,
		        	3 => 7,
		        	4 => 7 * 2,
		        	5 => 30,
		        	6 => 30 * 2,
		        	7 => 30 * 3,
		        	8 => 30 * 4,
		        	9 => 30 * 5,
		        	10 => 30 * 6,
		        	11 => 30 * 7,
		        	12 => 30 * 8,
		        	13 => 30 * 9,
		        	14 => 30 * 10,
		        	15 => 30 * 11,
		        	16 => 365,
		        	17 => 365 * 2,
		        	18 => 365 * 3
		        ];

		        $subscriptionStart = strtotime($currentSubscription->last_invoice);
				$subscriptionEnd = strtotime($dates[$currentCycle->cycle], $subscriptionStart);

				$differenceTime = time() - $subscriptionEnd;
				$differenceDays = floor($differenceTime/(60*60*24));

				$daysLeft = $days[$currentCycle->cycle] - $differenceDays;
				$billingLeft = $currentCycle->price * $daysLeft;

				Subscription::where(array('site_id' => Config('app.site')->id, 'status' => Subscription::SETUP))->update(array('status' => Subscription::UPGRADED));
			} else { // Plan is being downgraded
				// Keep on the same plan, but downgrade on end
				$currentSubscription->newplan = $plan;
				$currentSubscription->save();

				return Redirect::back()->with(['status' => 'Plan was changed successfully']);
			}
		} else {
			$subscription = new Subscription;
			$subscription->site_id = self::site('modal')->id;
			$subscription->plan_id = $plan;
			$subscription->cycle_id = Plan_Cycle::where('plan_id', $plan)->first()->id;
			$subscription->status = Subscription::SETUP;
			$subscription->last_invoice = date('Y-m-d h:m:s');
			$subscription->save();

			$thisSite = Config('app.site');

			$billingServ = Site::where('super', true)->first();
			$billingServ->setDatabase();

			try {
				$user = User::where('username', $this->site->id)->firstOrFail();
			} catch (\Exception $e) {
				$user = new User;
				$user->username = $thisSite->id;
				$user->save();
			}

			Invoices::create(1, $user->id, date('Y-m-d h:m:s'));
            Invoices::addItem($planInfo->name, '', '', $planCycle->price, 1);
            $invoice = Invoices::save();
            $hash = Invoices::getHash();

            die('test');
            return Redirect::to($billingServ->domain . '/invoices/'.$invoice->id.'/pay/'.$hash);
		}
	}

	public function postGDPRDownload(Request $request) {

        $user = new User();
        $user = $user->getGprdDownloadData(Auth::User()->id)->toArray();

        $addresses = new Address();
        $addresses = $addresses->getGprdDownloadData(Auth::User()->id)->toArray();

        $invoices = new Invoice();
        $invoices = $invoices->getGprdDownloadData(Auth::User()->id)->toArray();

        $transactions = new Transactions();
        $transactions = $transactions->getGprdDownloadData(Auth::User()->id)->toArray();

        $user = array_add($user[0],'addresses',$addresses);
        $user = array_add($user,'invoices',$invoices);
        $user = array_add($user,'transactions',$transactions);

        $data = json_encode($user,JSON_PRETTY_PRINT);

        Storage::put('GDPR_Information_Download.json', $data, 'public');
        return response()->download(storage_path('app/GDPR_Information_Download.json'))->deleteFileAfterSend(true);
    }

		function generateApiKey()
		{
				$user = Auth::User();

				$user->sandbox_api_key = 'sk_' . self::generateKeyUnique('sandbox');
				$user->live_api_key = 'live_' . self::generateKeyUnique('live');
				$user->save();

				return Response::json([
																'success' => true,
																'sandbox_api_key' => $user->sandbox_api_key,
																'live_api_key' => $user->live_api_key
															]);
		}

		function generateKeyUnique($type)
		{
				$exist = true;
				$newKey = '';
				while($exist)
				{
						$newKey = str_random(20);
						if($type == 'sandbox')
						{
								$keyExist = User::where('sandbox_api_key', 'sk_' . $newKey)->first();
								if(!$keyExist)
								{
										$exist = false;
										break;
								}
						}
						else if($type == 'live')
						{
								$keyExist = User::where('live_api_key', 'live_' . $newKey)->first();
								if(!$keyExist)
								{
										$exist = false;
										break;
								}
						}
				}

				return $newKey;
		}
}
