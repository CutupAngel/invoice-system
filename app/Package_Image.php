<?php

namespace App;

use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;

class Package_Image extends Model
{
	protected $connection = 'site';
	protected $table = 'order_group_package_images';
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
	public function getPath($value)
    {
        return Storage::disk('minio')->url($value);
    }
}
