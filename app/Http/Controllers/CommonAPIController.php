<?php

namespace App\Http\Controllers;

use Artisan;

use Auth;
use Settings;
use Storage;

use App\APIToken;
use Illuminate\Http\Request;
use Symfony\Component\Console\Output\BufferedOutput;

class CommonAPIController extends Controller
{
	private $token;
	private $bypassToken = [
		'api/migrate'
	];

	public function __construct(Request $request)
	{
		// We need to disable the debug bar for API requests.
		// This is generally enabled in development enviroments and casuses
		// issues with API requests due to it's auto injection.
		\Debugbar::disable();

		if (!in_array($request->path(), $this->bypassToken)) {
			try {
				// Validate API token.
				$this->token = APIToken::where('token',$request->headers->get('token'))->first();
				if(empty($this->token))
				{
					throw new \Exception("Invalid token");
				}
				//if ($this->token->ip !== $request->ip()) {
				//	throw new \Exception("IP Mismatch: ".$this->token->ip.'|'.$request->ip());
				//}
			} catch (\Exception $e) {
				http_response_code(403);
				die($e);
			}
		}
	}

	public function installDatabase(Request $request)
	{
		ignore_user_abort(true); // Ignore connection close
		set_time_limit(0); // Do not termiate the process due to timelimit.
		Artisan::call('migrate:site', ['--site' => Config('app.site')->domain, '--force' => 'Y']);
		return 1;
	}

	public function createUser(Request $request)
	{
		if (is_null($this->token->user_id)) {
			if(empty($request->input('fullname')))
			{
				throw new \Exception("Missing fullname");
			}
			if(empty($request->input('username')))
			{
				throw new \Exception("Missing username");
			}
			try {
				$user = new \App\User();
				$user->id = 1;
				$user->name = $request->input('fullname');
				$user->username = $request->input('username');
				$user->password = bcrypt($request->input('password'));
				$user->email = $request->input('email');
				$user->save();

				return 1;
			} catch (\Exception $e) {
				return 'User or email already exists.';
			}
		}

		return 0;
	}

	public function setSetting(Request $request)
	{
		if (is_null($this->token->user_id)) {
			Auth::loginUsingId(1);
			Settings::set($request->input('settings'));
			Auth::logout();
			return 1;
		}

		return 0;
	}

	public function login(Request $request)
	{
		if (is_null($this->token->user_id)) {
			$token = \App\SSOToken::create([
				'token' => str_random(12),
				'user_id' => $request->input('user_id')
			]);

			return $token->token;
		}
	}
}
