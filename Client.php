<?php

namespace App\Modules\Orders\Controllers;

use App\Modules\Controller;
use App\Order_Group;
use App\Package;
use App\Package_Cycle;
use App\Package_File;
use App\Package_Options;
use App\Package_Option_Values;
use App\Options_To_Packages;
use App\TaxClasses;

use Auth;
use Integrations;
use Permissions;
use Storage;
use Illuminate\Http\Request;
use Response;

use Validator;

class Client extends Controller
{
    /**
     * @var string
     */
    private $user = null;

    public function __construct()
    {
        if (!Permissions::has('orders')) {
            throw new Permissions::$exception;
        }

        $this->user = Auth::user();
    }

    public function getPackages()
    {
        $groups = Order_Group::where('visible', '!=', '2')->get();

        return view('Orders::packageList', [
            'groups' => $groups
        ]);
    }

    public function getOptionsList()
    {
        $options = $this->user->packageOptions()->orderBy('internal_name', 'asc')->get();

        return view('Orders::packageOptionList', [
            'options' => $options,
        ]);
    }

    public function getFieldTypes($type)
    {
        $types = [
            'option' => [
                '0' => 'Dropdown / Select Box',
                '1' => 'Text Input',
                '2' => 'Numeric Input',
                '3' => 'Choices / Radio Button',
                '4' => 'Checkbox',
                '5' => 'Toggle',
            ],
            'value' => [
                '1' => 'One-Off',
                '2' => 'Daily',
                '3' => 'Weekly',
                '4' => 'Fortnightly',
                '5' => 'Monthly',
                '6' => 'Every 2 Months',
                '7' => 'Every 3 Months',
                '8' => 'Every 4 Months',
                '9' => 'Every 5 Months',
                '10' => 'Every 6 Months',
                '11' => 'Every 7 Months',
                '12' => 'Every 8 Months',
                '13' => 'Every 9 Months',
                '14' => 'Every 10 Months',
                '15' => 'Every 11 Months',
                '16' => 'Every 12 Months',
                '17' => 'Every 24 Months',
                '18' => 'Every 36 Months',
            ],
        ];

        return isset($types[$type]) ? $types[$type] : [];
    }

    public function getPartialOptionView()
    {
        return [
            'option',
            'value',
        ];
    }

    public function getOptionDataJson($type, $optionId, $valueId = null)
    {
        if (sizeof($this->getPartialOptionView($type)) == 0 || !in_array($type, $this->getPartialOptionView($type))) {
            return;
        }

        $option = $this->user->packageOptions()->find($optionId);
        $value = $option ? $option->values()->find($valueId) : null;

        return view('Orders::partials.' . $type, [
            'option' => $option,
            'value' => $value,
            'types' => $this->getFieldTypes($type),
        ]);
    }

    public function rulesOption()
    {
        return [
            'display' => 'required|max:255',
            'internal' => 'required|max:255',
        ];
    }

    public function rulesValue()
    {
        return [
            'optionId' => 'required',
            'price' => 'required',
            'cycle' => 'required|numeric',
            'fee' => 'required|numeric',
        ];
    }

    public function getAttributeNames()
    {
        return [
            'optionId' => 'Option id',
            'display' => 'display name',
            'internal' => 'Quotes',
            'price' => 'Price',
            'cycle' => 'Cycle',
            'fee' => 'Setup fee',
        ];
    }

    public function validatorErrors($request, $validator)
    {
        if (\Request::ajax()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->getMessageBag()->toArray()
            ], 400);
        }

        return redirect()->back()->withErrors($validator)->withInput($request->all());
    }

    public function validateModal($request)
    {
        $type = $request->get('type');

        if (!in_array($type, $this->getPartialOptionView($type))) {
            return false;
        }

        $rules = 'rules' . ucfirst($type);

        return Validator::make($request->all(), $this->$rules(), [], $this->getAttributeNames());
    }

    public function postSaveOptionData(Request $request)
    {
        $error = [];
        if (!$this->user || !$request->has('type')) {
            abort(404);
        }

        switch ($request->get('type')) {
            case 'option':

                /**
                 * Validate process option
                 */
                if ($validator = $this->validateModal($request)) {
                    if ($validator === false) {
                        return null;
                    }

                    if ($validator->fails()) {
                        return $this->validatorErrors($request, $validator);
                    }
                }

                if ($optionId = $request->get('optionId')) {
                    $option = Package_Options::find($optionId);

                    if ($option) {
                        $option->update([
                            'display_name' => $request->get('display'),
                            'internal_name' => $request->get('internal'),
                            'required' => $request->get('required'),
                            'type' => $request->get('option_type'),
                            'user_id' => $this->user->id,
                        ]);

                        return Response::json([
                            'status' => 1,
                            'id' => $option->id,
                        ]);
                    }
                }

                try {
                    $option = Package_Options::create([
                        'display_name' => $request->get('display'),
                        'internal_name' => $request->get('internal'),
                        'required' => $request->get('required'),
                        'type' => $request->get('option_type'),
                        'user_id' => self::siteModal()->id,
                        'display_name' => $request->get('display'),
                    ]);

                    if ($option) {
                        return Response::json([
                            'status' => 1,
                            'id' => $option->id,
                        ]);
                    }
                } catch (Exception $e) {
                    $error[] = [
                        'message'=>'Failed to save option...',
                        'inputs'=>[]
                    ];
                }

                return Response::json([
                    'status' => 0,
                    'id' => [
                        'message'=>'Not created option',
                        'inputs'=>[]
                    ],
                ]);

                break;
            case 'value':

                /**
                 * Validate process option value
                 */
                if ($validator = $this->validateModal($request)) {
                    if ($validator === false) {
                        return null;
                    }

                    if ($validator->fails()) {
                        return $this->validatorErrors($request, $validator);
                    }
                }
                
                if ($optionId = $request->get('optionId')) {
                    $option = $this->user->packageOptions()->find($optionId);

                    if ($option) {
                        $value = $option->values()->find($request->get('valueId'));

                        if ($value) {
                            $value->update([
                                'display_name' => $request->get('display'),
                                'cycle_type' => $request->get('cycle'),
                                'price' => $request->get('price'),
                                'fee' => $request->get('fee'),
                            ]);

                            return Response::json([
                                'status'=>1,
                                'id'=>$value->id
                            ]);
                        }

                        try {
                            $value = Package_Option_Values::create([
                                'display_name' => $request->get('display'),
                                'cycle_type' => $request->get('cycle'),
                                'price' => $request->get('price'),
                                'fee' => $request->get('fee'),
                                'option_id' => $option->id,
                            ]);

                            if ($value) {
                                return Response::json([
                                    'status'=>1,
                                    'id'=>$value->id
                                ]);
                            }
                        } catch (Exception $e) {
                            $error[] = [
                                'message'=>'Failed to save option value...',
                                'inputs'=>[]
                            ];
                        }
                    }
                }

                break;
        }

        return Response::json([
            'status' => 0,
            'errors' => $error
        ]);
    }

    public function postDeleteOptionData(Request $request)
    {
        if (!$request->has('type') || !$request->has('id')) {
            abort(500);
        }

        switch ($request->get('type'))
        {
            case 'option':
                $option = Package_Options::findOrFail($request->get('id'));
                if ($option->user_id != self::siteModal()->id) {
                    abort(500);
                }
                $option->delete();
                break;
            case 'value':
                $value = Package_Option_Values::findOrFail($request->get('id'));
                if ($value->option()->user_id != self::siteModal()->id) {
                    abort(500);
                }
                $value->delete();
                break;
        }

        return Response::json([
            'status'=>1,
            'options'=>Package_Options::where('user_id', self::siteModal()->id)->get()->toArray()
        ]);
    }

    public function getGroup($id = 'new')
    {
        if ($id === 'new') {
            $group = new Order_Group();
            $type = 'Create';
        } else {
            try {
                $group = Order_Group::findOrFail($id);
                $group->permissionCheck(Auth::User()->id);
				$group->url = urldecode($group->url);
                $type = 'Edit';
            } catch (\Exception $e) {
                return redirect('/orders')->withErrors(['The selected group does not exist.']);
            }
        }

        return view('Orders::groupForm', ['siteURL' => $this->site('url'), 'group' => $group, 'type' => $type]);
    }

    public function saveGroup(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required',
            'type' => 'required'
        ]);

        if ($id !== 'new') {
            try {
                $group = Order_Group::findOrFail($id);
                $group->permissionCheck(Auth::User()->id);
            } catch (\Exception $e) {
                return redirect('/orders')->withErrors(['The selected group does not exist.']);
            }
        } else {
            $group = new Order_Group();
            $group->user_id = Auth::User()->id;
        }

        $group->name = $request->input('name');
        $group->description = $request->input('description');
        $group->url = urlencode($request->has('link') ? $request->input('link') : $request->input('name'));
        $group->type = $request->input('type');
        $group->save();

        return redirect('/orders')->with(['status' => "{$request->input('name')} was saved successfully."]);
    }

    public function getPackage($groupId, $packageId)
    {
        try {
            $group = Order_Group::findOrFail($groupId);
            $group->permissionCheck(Auth::User()->id);
			$group->url = urldecode($group->url);
        } catch (\Exception $e) {
            return redirect('/orders')->withErrors(['The selected group does not exist.']);
        }

        $type = ($packageId === 'new' ? 'Create' : 'Edit');
        if ($packageId === 'new') {
            $package = new Package();
        } else {
            $package = Package::findOrFail($packageId);
			$package->url = urldecode($package->url);
        }

        $integrations = Integrations::getAvailableIntegrations();

		$options = Package_Options::where('user_id', self::siteModal()->id)->get();

        $ids = $package->link->lists('option_id');
        $selectedOptions = Package_Options::where('user_id', self::siteModal()->id)->whereIn('id', $ids)->get();

		$taxclasses = TaxClasses::select('id', 'name', 'default')->where('user_id', self::siteModal()->id)->get();

        #disabled domains for this release
		return view('Orders::packageForm', [
            'group'        => $group,
            'type'         => $type,
            'package'      => $package,
            'options'      => $options,
            'taxclasses'      => $taxclasses ,
            'integrations' => $integrations->filter(function($item) { return $item['type'] != 'DomainRegistarIntegration'; }),
            //'domainIntegrationEnabled' => ! $integrations->where('type', 'DomainRegistarIntegration')->isEmpty()
            'domainIntegrationEnabled' => 0,
            'selectedOptions' => $selectedOptions,
        ]);
    }

    public function savePackage(Request $request, $groupId, $packageId = 'new')
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        try {
            $group = Order_Group::findOrFail($groupId);
            $group->permissionCheck(Auth::User()->id);
        } catch (\Exception $e) {
            return redirect('/orders')->withErrors(['Unable to create a package for the selected group.']);
        }

        $errors = [];

        if ($packageId !== 'new') {
            $package = Package::findOrFail($packageId);
        } else {
            $package = new Package();
        }

        $package->group_id = $group->id;
        $package->name = $request->input('name');
        $package->description = $request->input('description');
        $package->tax = $request->input('tax');
        $package->prorate = $request->input('prorate') == 'Y';
        $package->url = urlencode($request->input('url'));
        $package->trial = $request->input('trial');
        $package->save();

        if (!$request->has('keep')) {
            foreach ($package->files as $file) {
                $file->delete();
            }
        } else {
            $fileIds = [];
            foreach ($request->input('keep') as $id) {
                $fileIds[] = $id;
            }

            $files = Package_File::where('package_id', $package->id)->whereNotIn('id', $fileIds)->get();
            foreach ($files as $file) {
                $file->delete();
            }
        }

        if ($request->hasFile('files')) {
            $currentSpace = Auth::User()->getSpace();

            foreach ($request->file('files') as $file) {
                if (!$file->isValid()) {
                    $errors[] = "File: {$file->getClientOriginalName()} failed to upload. Reason: {$file->getErrorMessage()}";
                    continue;
                }

                if ($currentSpace['free'] - $file->getSize() < 0) {
                    $errors[] = "File: {$file->getClientOriginalName()} failed to upload. Reason: Not enough space to store file.";
                    continue;
                }

                $filepath = "clientProductFiles/" . Auth::User()->id . '/' . $file->getClientOriginalName();

                Storage::disk('minio')->put($filepath, file_get_contents($file->getRealPath()));

                if (Storage::disk('minio')->has($filepath)) {
                    $dbFile = new Package_File();
                    $dbFile->package_id = $package->id;
                    $dbFile->filename = $file->getClientOriginalName();
                    $dbFile->path = $filepath;
                    $dbFile->size = $file->getSize();
                    $dbFile->mime = $file->getMimeType();
                    $dbFile->save();
                } else {
                    $errors[] = "File: {$file->getClientOriginalName()} failed to upload.";
                }
            }
        }

        $cycles = $package->cycles;
        $savedCycles = [];
        foreach ($request->input('cycle.id') as $i => $id) {
            if ($id == 'new') {
                $cycle = new Package_Cycle();
            } else {
                if ($cycles->contains('id', $id)) {
                    $cycle = Package_Cycle::findOrFail($id);
                } else {
                    continue;
                }
            }

            $cycle->package_id = $package->id;
            $cycle->price = $request->input('cycle.price')[$i];
            $cycle->fee = $request->input('cycle.setup')[$i] ?: 0.00;
            $cycle->cycle = $request->input('cycle.cycle')[$i];
            $cycle->save();

            $savedCycles[] = $cycle->id;
        }

        foreach ($cycles as $cycle) {
            if (in_array($cycle->id, $savedCycles)) {
                continue;
            }

            $cycle->delete();
        }

		if($request->has('options'))
		{
			Options_To_Packages::where('package_id','=',$package->id)->delete();
			foreach($request->get('options') as $k=>$v)
			{
				$link = new Options_To_Packages();
				$link->package_id = $package->id;
				$link->option_id = $v;
				$link->save();
			}
		}

        if ($request->has('integration')) {
            $package->integration = $request->input('integration');
            Integrations::get($request->input('integration'), 'savePackageDetails', [$request, $package]);
        }

        #disabled domains for this release
		//if ($request->input('domainIntegration') == '1') {
        //    $package->domainIntegration = true;
       // } else {
            $package->domainIntegration = false;
        //}


        $package->save();

        return redirect('/orders')->with(['success' => 'Package has been saved successfully!'])->withErrors($errors);
    }

    public function getIntegrationForm(Request $request, $integration)
    {
        return Integrations::get($integration, 'getPackageForm', [$request]);
    }

    public function delete($groupId, $packageId = -1)
    {
        if ($packageId === -1) {
            $delete = Order_Group::findOrFail($groupId);
            $delete->permissionCheck(Auth::User()->id);
            $delete->delete();
        } else {
            $delete = Package::findOrFail($packageId);
            if ($delete->group->id !== intval($groupId)) {
                throw new \Exception("Invalid package selected.");
            }
            $delete->group->permissionCheck(Auth::User()->id);
            $delete->delete();
        }

        return redirect('/orders')->with(['success' => '{$delete->name} was successfully deleted.']);
    }

    public function toggle($groupId)
    {
        $group = Order_Group::findOrFail($groupId);
        $group->permissionCheck(Auth::User()->id);
        if ($group->visible === '0') {
            $group->visible = '1';
        } elseif ($group->visible == '1') {
            $group->visible = '0';
        }
        $group->save();

        return $group->visible;
    }
}
