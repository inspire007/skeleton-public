<?php

namespace App\Library\Skeleton\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    //
	protected $table = 'invoices';
	
	protected $dates = [
        'payment_due_date',
		'payment_made_date',
		'next_payment_date',
		'invoice_period_start',
		'invoice_period_end',
		'next_reminder'
    ];
	
	public function user()
    {
        return $this->belongsToMany('App\User', 'id', 'user_id');
    }
	
	public function user_membership()
    {
        return $this->belongsToMany('App\Library\Skeleton\Models\UserMemberships', 'plan_id', 'plan_id');
    }
	
	public function membership_plan()
    {
        return $this->belongsToMany('App\Library\Skeleton\Models\MembershipPlans', 'plan_id', 'plan_id');
    }
	
	public function scopeLatest($query)
	{
		return $query->orderBy('created_at', 'DESC');
	}
	
	public function isPastDue()
	{
		return $this->invoice_status != 'PAID' && Carbon::now()->gt( $this->payment_due_date );
	}
	
	public function isPastGracePeriod()
	{
		return $this->invoice_status != 'PAID' && Carbon::now()->gt( $this->payment_due_date->addDays( config('billing.grace_period') ) );
	}

	public function isOnGracePeriod()
	{
		return $this->invoice_status != 'PAID' && Carbon::now()->gt( $this->payment_due_date ) && Carbon::now()->lt( $this->payment_due_date->addDays( config('billing.grace_period') ) );
	}

}
