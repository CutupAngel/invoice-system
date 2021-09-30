<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PackageSetting extends Model
{
	protected $connection = 'site';
    protected $table = 'order_group_package_settings';
    protected $fillable = ['name', 'value'];
    protected $visible = ['name', 'value'];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
