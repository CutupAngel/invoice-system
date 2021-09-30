<?php

namespace App\Http\Controllers;

use Auth;
use Google2FA;
use Session;
use Validator;
use Mail;

use App\Address;
use App\SSOToken;
use App\User;
use App\User_Link;
use App\User_Contact;
use App\Countries;
use App\Mail\GeneralEmail;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\AuthenticatesAndRegistersUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class AuthController extends Controller
{
	use AuthenticatesUsers;

	protected $username = 'username';
	protected $redirectTo = '/';

	public function getLogin(Request $request)
	{
		if ($request->has('url')) {
			Session::put('url.intended', $request->input('url'));
		}
		return view('Auth.login');
	}

	public function postLogin(Request $request)
	{
		$this->validate($request, [
			'username' => 'required',
			'password' => 'required'
		]);

		if(!$this->site('modal')->activeSubscription)
		{
				return view('Common.subscriptionExpired');
		}

		if (Session::has('url.intended')) {
			$this->redirectTo = Session::get('url.intended');
		}

		$credentials = $request->only('username', 'password');

		if (Auth::attempt($credentials, $request->has('remember'))) {
			$user = Auth::User();
			$user->last_login = date('Y-m-d h:m:s');
			$user->save();

			return redirect()->intended($this->redirectPath());
		}

		return redirect('/auth/login')
			->withInput($request->only('username', 'remember'))
			->withErrors([
				'login' => $this->getFailedLoginMessage()
			]);
	}

	protected function getFailedLoginMessage()
	{
	    return trans('auth.failed');
	}

	public function get2fa()
	{
		return view('Auth.2fa');
	}

	public function post2fa(Request $request)
	{
		$secret = Auth::User()->authSecret;

		if (Google2FA::verifyKey($secret, $request->input('2fa'))) {
			$request->session()->push('2faAuthed', true);
			return redirect()->intended($this->redirectPath());
		}

		return redirect('/auth/2fa')
			->withErrors([
				'2fa' => 'Invalid Two-Factor Authenication Code.'
			]);
	}

	public function getRegister()
	{
			$countries = Countries::orderByRaw('case when id IN(222,223) then -1 else id end,id')->get();
			return view('Auth.register', compact('countries'));
	}

	/**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|regex:/^[a-zA-Z0-9_ -]+$/u|max:255',
						//'username' => 'regex:/^[a-zA-Z0-9_-]+$/u|max:255',
            'email' => 'required|email|max:255|unique:users',
            'password' => 'required|min:6|confirmed',
            'phone' => 'required|regex:/^[0-9+ -]+$/u',
            'address_1' => 'required|regex:/^[a-zA-Z0-9_ -,]+$/u',
            'city' => 'regex:/^[a-zA-Z0-9_ -]+$/u',
            'postal_code' => 'required|regex:/^[a-zA-Z0-9 ]+$/u',
            'confirm_terms' => 'required',
            //'g-recaptcha-response' => 'required|recaptcha',
        ]);
    }

	/**
 * Handle a registration request for the application.
 *
 * @param  \Illuminate\Http\Request  $request
 * @return \Illuminate\Http\Response
 */
	public function postRegister(Request $request)
	{
	    $validator = $this->validator($request->all());

	    if ($validator->fails()) {
					$errors = $validator->errors();
					return redirect()->back()
									->withInput($request->except(['password']))
									->withErrors($errors);
	    }

				$user = User::create([
	      "name"  => $request->name,
	      "username"  => $request->email,
	      "email"  => $request->email,
	      "password" => bcrypt($request->password),
				'account_type' => '2'
	   ]);

				 $userLink = User_Link::create([
				 "user_id"  => $user->id,
				 "parent_id"  => 1
			]);

					$address = Address::create([
				 "contact_name"  => $request->name,
				 "phone"  => $request->phone,
				 "email"  => $request->email,
				 "address_1"  => $request->address_1,
				 "city"  => $request->city,
				 "county_id"  => $request->county_id,
				 "postal_code"  => $request->postal_code,
				 "country_id"  => $request->country_id
			]);

					$userContact = User_Contact::create([
				 "user_id"  => $user->id,
				 "address_id"  => $address->id,
				 "type"  => '1'
			]);

			//send registered email
			$userFrom = User::find(1);
			$subject = $user->siteSettings('name') . ' Registered';
			$content = '-- Content Here --';
			$view = 'Auth.emails.register';
			Mail::to($user)->send(new GeneralEmail($userFrom, $subject, $content, $view));

	    return redirect('/auth/login')->with('status', trans('auth.register_success'));
	}

	public function getLogout(Request $request)
	{
		Auth::Logout();
		$request->session()->flush();

		return redirect('/auth/login');
	}

	public function tokenLogin($token)
	{
		// Somehow the user gets redirected back to this page after the token is used.
		// When this happens they get the unauthorized error.
		// So we'll just go ahead and bypass this call if they're already logged in.
		if (! Auth::guest()) {
			return redirect('/');
		}

		try {
			$token = SSOToken::findOrFail($token);
			if ($token->created_at->lte(Carbon::createFromTimestamp(strtotime('+5 minutes')))) {
				Auth::loginUsingId($token->user_id);
			}

			$token->delete();

			return redirect('/');
		} catch (\Exception $e) {
			return response('Unauthorized.', 401);
		}
	}
}
