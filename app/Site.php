<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Config;

class Site extends Model
{
    protected $connection = 'main';
    protected $primaryKey = 'domain';
    protected $table = 'billingserv_sites';
    public $incrementing = false;

    protected $casts = [
        'super' => 'boolean'
    ];

    public function setDatabase()
    {
      	Config::set('database.connections.site.host', $this->database_host);
      	Config::set('database.connections.site.database', $this->database_name);
      	Config::set('database.connections.site.username', $this->database_username);
      	Config::set('database.connections.site.password', $this->database_password);
      	Config::set('database.default', 'site');
        Config::set('app.site', $this);
    }

    public function activeSubscription()
    {
        return $this->hasOne(Subscription::class, 'id', 'active_subscription');
    }
}
