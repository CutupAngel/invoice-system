<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Auth;
use Mail;
use Permissions;

use App\User;
use App\User_Link;
use App\User_Setting;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Password;

class StaffController extends Controller
{
	public function __construct()
	{
		// Staff members can only be managed by the parent account.
		$this->middleware('isClient');
	}

	public function index()
	{
		$staff = Auth::User()->staff;

		return view('Settings.staffListing', ['staff' => $staff]);
	}

	public function show($id)
	{
		return redirect("/settings/staff/{$id}/edit");
	}

	public function create()
	{
		return view('Settings.staffForm', [
			'user' => new User(),
			'permission' => [
				'packages' => true,
				'customers' => true,
				'invoices' => true,
				'marketing' => true,
				'reports' => true,
				'support' => true,
				'settings' => true
			]
		]);
	}

    /**
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
	public function store(Request $request)
	{
		$this->validate($request, [
			'name'     => 'required',
			'username' => 'required|unique:users',
			'email'    => 'required|unique:users|email',
			'password' => 'confirmed'
		]);

		$sendEmail = false;
		if ($request->filled('password')) {
		    $password = $request->input('password');
        } else {
		    $password = Str::random(10);
		    $sendEmail = true;
        }

        $request->merge([
            'password' => bcrypt($password),
            'account_type' => User::STAFF
        ]);

        $user = User::create($request->only(['name', 'username', 'email', 'password', 'account_type']));
        User_Link::create([
            'parent_id' => auth()->id(),
            'user_id' => $user->id
        ]);

		if ($request->has('permission')) {
			foreach ($request->input('permission') as $permission => $value) {
			    User_Setting::create([
			        'user_id' => $user->id,
                    'name' => "permission.{$permission}",
                    'value' => $value
                ]);
			}
		}

		if ($sendEmail) {
            $response = Password::sendResetLink($request->only('email'));
            if ($response == Password::RESET_LINK_SENT) {
                return redirect('/settings/staff')
                    ->with('status', trans($response));
            }

            return redirect('/settings/staff')
                ->with('error', trans($response));
        }

		return redirect('/settings/staff');
	}

	public function edit($id)
	{

		try {
			$user = User::findOrFail($id);
			$userLink = User_Link::where('user_id',$user->id)->first();
			$parent = User::findOrFail($userLink->parent_id);

			$values = [
				'user' => $user,
				'permission' => [
					'packages' => false,
					'customers' => false,
					'invoices' => false,
					'marketing' => false,
					'reports' => false,
					'support' => false,
					'settings' => false
				]
			];

			$permissions = User_Setting::where('user_id', $user->id)->where('name', 'LIKE', 'permission.%')->get();
			foreach ($permissions as $permission) {
				array_set($values, $permission->name, $permission->value);
			}

			return view('Settings.staffForm', $values);
		} catch (\Exception $e) {
			return Redirect('/settings/staff')->withErrors('You have selected an invalid user.');
		}
	}

	public function update($id, Request $request)
	{
		$this->validate($request, [
			'email' => 'unique:users,email,'.$id,
			'username' => 'unique:users,username,'.$id,
			'password' => 'confirmed'
		]);

		try {

			$user = User::findOrFail($id);
			$userLink = User_Link::where('user_id',$user->id)->first();
			$parent = User::findOrFail($userLink->parent_id);

			$user->name = $request->input('name');
			$user->email = $request->input('email');
			$user->username = $request->input('username');

			if ($request->has('password')) {
				$user->password = bcrypt($request->input('password'));
			}

			$permissions = User_Setting::where('user_id', $user->id)->where('name', 'LIKE', 'permission.%')->delete();
			foreach ($request->input('permission') as $permission => $value) {
				$user->settings()->create([
					'user_id' => $user->id,
					'name' => "permission.{$permission}",
					'value' => $value
				]);
			}

			$user->save();

			return redirect('/settings/staff');
		} catch (\Exception $e) {
			return back()->withErrors('An unexpected error occurred');
		}
	}

	public function destroy($id)
	{
		try {
			$user = User::findOrFail($id);
			$userLink = User_Link::where('user_id',$user->id)->first();
			$parent = User::findOrFail($userLink->parent_id);

			if ($parent->id == auth()->id()  && $user->account_type === 3) {
				$user->forceDelete();

				return 1;
			}
		} catch (\Exception $e) {

		}

		return 0;
	}
}
