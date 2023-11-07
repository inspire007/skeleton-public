<?php

namespace App\Library\Skeleton\Billing;

trait Billable
{	
	public function membership()
    {
        return $this->hasOne('App\Library\Skeleton\Models\UserMemberships')->withDefault([
			'plan_id' => 1,
			'is_active' => 1
		]);
    }
	
	public function invoices()
    {
        return $this->hasMany('App\Library\Skeleton\Models\Invoices');
    }
	
}