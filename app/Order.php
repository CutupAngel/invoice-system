<?php

namespace App;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
	protected $connection = 'site';
	protected $table = 'orders';
	use SoftDeletes;

	// Statuses
	const RECENT = '0'; // Recent is used as NEW is a keyword.
	const PENDING = '1';
	const SETUP = '2';
	const SHIPPED = '3'; // For mailed items.
	const CANCELLED = '4';
	const RETURNED = '5'; // For mailed items.
	const TERMINATED = '6';
	const SUSPENDED = '7';
	const ERROR = '8';

	protected $casts = [
		'domainIntegration' => 'boolean'
	];

	protected $hidden = ['id', 'created_at', 'updated_at', 'deleted_at'];

	protected $fillable = [
		'user_id',
		'customer_id',
		'package_id',
		'cycle_id',
		'status',
		'last_invoice',
		'price',
		'currency_id',
		'integration',
		'domainIntegration',
		'trial_order',
		'trial_expire_date',
		'trial_expire_time'
	];

    /**
     * @param Builder $builder
     * @param User $user
     * @return Builder
     */
	public function scopeGetByUser(Builder $builder, User $user)
    {
        $field = $user->isCustomer() ? 'customer_id' : 'user_id';
        return $builder
            ->with('package')
            ->where($field, $user->id)
            ->orderBy('id');
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeTrialExpires(Builder $builder)
    {
        $now = now();
        return $builder
            ->where('trial_order', 1)
            ->where('trial_expire_date', $now->toDateString())
            ->where('trial_expire_time', '<=', $now->format('H:i:s'));
    }

    /**
     * @return void
     */
    public function resetTrial()
    {
        $this->forceFill([
            'trial_order' => 0,
            'trial_expire_date' => null,
            'trial_expire_time' => null,
        ])->save();
    }

	public function user()
	{
		return $this->belongsTo(User::class);
	}

	public function customer()
	{
		return $this->belongsTo(User::class, 'customer_id', 'id');
	}

	public function package()
	{
		return $this->belongsTo(Package::class);
	}

	public function cycle()
	{
		return $this->belongsTo(Package_Cycle::class);
	}

	public function settings()
	{
		return $this->hasMany(Order_Settings::class, 'order_id', 'id');
	}

	public function invoices()
	{
		return $this->hasMany(Invoice::class, 'order_id');
	}

	public function currency()
	{
		return $this->hasOne(Currency::class,'id','currency_id');
	}

    /**
     * @return mixed
     */
	public function getStatusTextAttribute()
    {
        switch ($this->status) {
            case self::RECENT:
                return 'Recent';
            case self::PENDING:
                return 'Pending';
            case self::SETUP:
                return 'Setup';
            case self::SHIPPED:
                return 'Shipped';
            case self::CANCELLED:
                return 'Canceled';
            case self::RETURNED:
                return 'Returned';
            case self::TERMINATED:
                return 'Terminated';
            case self::SUSPENDED:
                return 'Suspended';
            default:
                return 'Error';
        }
    }
}
