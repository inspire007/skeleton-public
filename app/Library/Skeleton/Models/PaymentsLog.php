<?php

namespace App\Library\Skeleton\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class PaymentsLog extends Model
{
    //
	protected $table = 'payments_log';
	
	public function isTrialEligible()
	{
		return (new PaymentsLog)->where('payment_id', $this->payment_id)->where('has_trial', 1)->first();
	}
	
}
