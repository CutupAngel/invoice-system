<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes as SoftDeletingTrait;
use Illuminate\Support\Facades\Auth;

class Invoice extends Model
{
	use SoftDeletingTrait;

	const UNPAID   = '0';
	const PAID     = '1';
	const OVERDUE  = '2';
	const REFUNDED = '3';
	const CANCELED = '4';
	const PENDING = '5';

	protected $attributes = [
		'status' => self::UNPAID,
		'estimate' => false
	];

	protected $hidden = [
		'id',
		'updated_at',
		'deleted_at'
	];

	protected $fillable = [
		'id',
		'user_id',
		'customer_id',
		'address_id',
		'currency_id',
		'order_id',
		'invoice_number',
		'total',
		'status',
		'due_at',
		'tax_exempt',
		'credit',
        'suspend_count'
	];

	protected $casts = [
		'total' => 'float',
		'estimate' => 'boolean'
	];

	private $types = [
		'0' => 'Unpaid',
		'1' => 'Paid',
		'2' => 'Overdue',
		'3' => 'Refunded',
		'4' => 'Canceled'
	];

	protected $connection = 'site';

	public function order()
	{
		return $this->hasOne(Order::class, 'id', 'order_id');
	}

	public function transactions()
	{
		return $this->hasMany(Transactions::class);
	}

	public function items()
	{
		return $this->hasMany(InvoiceItem::class);
	}

	public function currency()
	{
		return $this->hasOne(Currency::class,'id','currency_id');
	}

	public function totals()
	{
		return $this->hasMany(InvoiceTotal::class);
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id', 'id');
	}

	public function customer()
	{
		return $this->belongsTo(User::class, 'customer_id', 'id');
	}

	public function address()
	{
		return $this->hasOne(Address::class, 'id', 'address_id');
	}

	public function status()
	{
		// Empty statuses will cause errors.
		try {
			return $this->types[$this->status];
		} catch (\Exception $e) {
			return $this->types[self::UNPAID];
		}
	}

  public function getGprdDownloadData($user_id)
	{
      return $this->makeHidden(['deleted_at','estimate','tax_exempt'])
          ->where('customer_id',$user_id)->with('items','totals')
          ->get();
  }

  	public static function getStatusConst($status)
	{
		return constant('self::' . strtoupper($status));
	}
}
