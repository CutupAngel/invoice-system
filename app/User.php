<?php

namespace App;

use Auth;
use DB;

use App\Login_History;
use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Illuminate\Notifications\Notifiable as Notifiable;

class User extends Model implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, SoftDeletingTrait, Notifiable;

    const ADMIN = 0;
    const CLIENT = 1;
    const CUSTOMER = 2;
    const STAFF = 3;

    protected $connection = 'site';
    protected $table = 'users';
    protected $guarded = [];
    protected $hidden = ['password', 'remember_token'];

    private $settings = []; // For caching
    private $userContacts = []; // For caching
    private $lastLogin; // For caching
    private $currentLogin; // For caching;

    public function taxClass()
    {
        return $this->hasOne(\App\TaxClasses::class);
    }

    public function taxRates()
    {
        return $this->hasOne(TaxRates::class);
    }

    public function customers()
    {
        return $this->hasManyThrough(User::class, User_Link::class, 'parent_id', 'id', 'id')
            ->where('account_type', self::CUSTOMER)->groupBy('users.id');
    }

    public function getCustomerIds()
    {
        return $this->customers()->pluck('users.id');
    }

    /**
     * Relation package option
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packageOptions()
    {
        return $this->hasMany('App\Package_Options');
    }

    // This really should be done another way, but I'm tired and I want security.
    public function checkCanEdit($userId)
    {
        //super admin can edit all things
        if ($userId === 1) return true;

        $parentIds = User_Link::whereIn('user_id', [$this->getKey(), $userId])
            ->pluck('parent_id')
            ->toArray();

        if ($parentIds[0] !== $parentIds[1]) {
            throw new \Exception("User cannot modify this customer!");
        }
    }

    public function invoices($status = null)
    {
    		if ($status === null) {
    			return $this->hasMany(Invoice::class, ($this->isCustomer()) ? 'customer_id' : 'id');
    		}

    		return $this->hasMany(Invoice::class, 'customer_id')->where('status', $status);
    }

    public function orders()
    {
        if ($this->isCustomer()) {
            return $this->hasMany(Order::class, 'customer_id', 'id');
        }

        return $this->hasMany(Order::class, 'user_id', 'id');
    }

    public function order_options()
    {
        return $this->hasManyThrough('App\Order_Options', 'App\Order');
    }

    public function staff()
    {
        return $this->hasManyThrough('App\User', 'App\User_Link', 'parent_id', 'id')
            ->where('account_type', self::STAFF);
    }

    public function settings()
    {
        return $this->hasMany('App\User_Setting');
    }

    public function getSetting($var = false, $default = null)
    {
        if (empty($this->settings)) {
            $settings = DB::table('user_settings')
                ->where('user_id', $this->id)
                ->get(['name', 'value']);

            foreach ($settings as $setting) {
                $this->settings[$setting->name] = $setting->value;
            }
        }

        if (!empty($this->settings[$var])) {
            return $this->settings[$var];
        }

        return $default;
    }

    public function siteSettings($var = false, $default = false)
    {
        return $this->getSetting('site.' . $var, $default);
    }

    public function siteContacts()
    {
        if (empty($this->userContacts)) {
            $this->userContacts = DB::table('user_contacts')->where('user_id', $this->id)->get();
		}

        return $this->userContacts;
    }

    public function contacts()
    {
        return $this->hasMany(User_Contact::class);
    }

    public function hasAddress($type = 'any')
    {
        switch ($type) {
            case 'mailing':
                return count((array)$this->mailingContact) !== 0;
                break;
            case 'billing':
                return count((array)$this->billingContact) !== 0;
                break;
            case 'admin':
                return count((array)$this->adminContact) !== 0;
                break;
            case 'tech':
                return count((array)$this->techContact) !== 0;
                break;
            default:
                return count((array)$this->contacts) !== 0;
        }
    }

    public function getContact($type)
    {
        return $this->contacts()->type($type);
    }

    public function defaultContact()
    {
        return $this->hasOne(User_Contact::class)->where('type', User_Contact::CONTACT);
    }

    public function mailingContact()
    {
        return $this->hasOne(User_Contact::class)->where('type', User_Contact::MAILING);
    }

    public function billingContact()
    {
        return $this->hasMany(User_Contact::class)->where('type', User_Contact::BILLING);
    }

    public function adminContact()
    {
        return $this->hasOne(User_Contact::class)->where('type', User_Contact::ADMIN);
    }

    public function techContact()
    {
        return $this->hasOne(User_Contact::class)->where('type', User_Contact::TECH);
    }

    public function isAdmin()
    {
        return $this->account_type === self::ADMIN;
    }

    public function isClient()
    {
        return $this->account_type === self::CLIENT;
    }

    public function isCustomer()
    {
        return $this->account_type === self::CUSTOMER;
    }

    public function isStaff()
    {
        return $this->account_type === self::STAFF;
    }

    public function has2fa()
    {
        return $this->authEnabled === 1;
    }

    public function orderGroups()
    {
        return $this->hasMany('App\Order_Group');
    }

    public function currentLogin()
    {
        if (!isset($this->currentLogin)) {
            $username = $this->username;
            if ($this->isImpersonating()) {
                $username = $this->originalUser()->username;
            }

            $lastLogin = Login_History::where('failed', false)
                ->where('username', $username)
                ->whereNull('logout')
                ->orderBy('created_at', 'desc')
                ->first();

            $this->currentLogin = $lastLogin->id;
        }

        if ($this->isImpersonating()) {
            return $this->originalUser()->hasOne('App\Login_History', 'username', 'username')->where('id', $this->currentLogin);
        } else {
            return $this->hasOne('App\Login_History', 'username', 'username')->where('id', $this->currentLogin);
        }
    }

    public function lastLogin()
    {
        if (!isset($this->lastLogin)) {
            $username = $this->username;
            if ($this->isImpersonating()) {
                $username = $this->originalUser()->username;
            }

            $lastLogin = Login_History::where('failed', false)
                ->where('username', $username)
                ->whereNotNull('logout')
                ->orderBy('created_at', 'desc')
                ->first();

            $this->lastLogin = (!isset($lastLogin->created_at) ? date('Y-m-d h:m:S') : $lastLogin->created_at) ;
        }

        return $this->lastLogin;
    }

    public function getSpace()
    {
        $limit = 104857600; // 100mb

        $total = DB::table('order_group_package_files as files')
            ->join('order_group_packages as packages', 'files.package_id', '=', 'packages.id')
            ->join('order_groups as group', 'packages.group_id', '=', 'group.id')
            ->where('group.user_id', $this->id)
            ->sum('files.size');

        return ['limit' => $limit, 'used' => $total, 'free' => $limit - $total];
    }

	public function parentLink()
	{
		return $this->hasOne(User_Link::class, 'user_id');
	}

    public function parent()
    {
        if ($this->isStaff() || $this->isCustomer()) {
			return $this->hasManyThrough(User::class, User_Link::inverse(), 'user_id', 'id')
                ->where('account_type', self::CLIENT)
                ->limit(1);
        } else {
            return $this->hasMany(User::class, 'id', 'id')->where('id', $this->id);
        }
    }

    public function miscStorage()
    {
        return $this->hasMany(MiscStorage::class, 'user_id');
    }

    public function credit()
    {
        return $this->hasOne(MiscStorage::class)->where('name', 'account.credit');
    }

    public function getCredit()
    {
        if (!array_key_exists('credit', $this->relations)) {
            $this->load('credit');
        }

        if (! $credit = $this->getRelation('credit')) {
            $credit = new MiscStorage;
            $credit->name = 'account-credit';
            $credit->value = 0.00;
            $credit->user_id = $this->id;
        }

        return $credit;
    }

    public function originalUser()
    {
        if ($this->isImpersonating()) {
            return User::findOrFail(\Session::get('original_user'));
        }

        return $this;
    }

    public function setImpersonating($id)
    {
        \Session::put('original_user', $this->id);
        \Session::put('impersonate', $id);
    }

    public function stopImpersonating()
    {
        \Auth::onceUsingId(\Session::get('original_user'));
        \Session::forget('impersonate');
        \Session::forget('original_user');
    }

    public function isImpersonating()
    {
        return \Session::has('impersonate');
    }

	public function savedPaymentMethods($gateway = null)
	{
	    if($gateway != null){
	        return $this->hasMany(SavedPaymentMethods::class, 'user_id')->where('gateway_id',$gateway);
        }else{
	        return $this->hasMany(SavedPaymentMethods::class, 'user_id');
        }
	}

	public function getGprdDownloadData($user_id){
        return $this->makeHidden(['settings','stripeId','authSecret','deleted_at'])
            ->where('id',$user_id)->with('getLoginHistory')->get();
    }

    public function getLoginHistory(){
        return $this->hasMany(Login_History::class,'username','username');
    }

    public function transactions(){
        return $this->hasMany(Transactions::class,($this->isCustomer()) ? 'customer_id' : 'user_id','id');
    }

    public function integrations()
    {
        return $this->hasMany(Integration::class, 'user_id');
    }

    // uses regex that accepts any word character or hyphen in last name
    public function split_name() {
        $name = trim($this->name);
        $last_name = (strpos($this->name, ' ') === false) ? '' : preg_replace('#.*\s([\w-]*)$#', '$1', $this->name);
        $first_name = trim( preg_replace('#'.$last_name.'#', '', $this->name ) );
        return array($first_name, $last_name);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
}
