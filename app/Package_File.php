<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Package_File extends Model
{
	protected $connection = 'site';
	protected $table = 'order_group_package_files';
	protected $fillable = ['package_id', 'filename', 'path'];

	public function package()
	{
		return $this->belongsTo('App\Package');
	}

	public function getUrl($expires = null)
	{
		return Storage::disk('minio')->url($this->path);
	}

	public function getUrlDownload($expires = null)
	{
		return $this->path;
	}

    /**
     * @param $value
     * @return mixed
     */
	public function getPathAttribute($value)
    {
        return Storage::disk('minio')->url($value);
    }

	// public function getUrlIP($ip)
	// {
	// 	return Storage::disk('dreamobjects')->getAdapter()->getUrlIP($this->path, $ip);
	// }
}
