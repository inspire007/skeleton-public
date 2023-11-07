<?php

namespace App\Library\Skeleton\Models;

use Illuminate\Database\Eloquent\Model;
use DB;
use Auth;
use Carbon\Carbon;
use App\Library\Skeleton\Models\UserMemberships;
use App\Library\Skeleton\Models\PaymentsLog;



class MembershipPlans Extends Model
{
	protected $table = 'membership_plans';
	
	public function user()
    {
        return $this->belongsToMany('App\User', 'plan_id');
    }
	
	
	
	
	
	
	public static function validate_trial($email, $user_id = null, $ref_id = null)
	{	
		if(!$user_id && Auth::check())$user_id = Auth::user()->id;
		
		$exists = PaymentsLog::where('has_trial', 1);
		
		if($user_id){
			$exists->where(function($query) use($email, $user_id){
				$query->
					where('payment_id', $email)->
					orWhere('user_id', $user_id);
			});
		}
		else $exists->where('payment_id', $email);
		
		//if($ref_id)$exists->where('ref_id', '!=', $ref_id);
		
		$exists = $exists->get()->first();
		
		if($exists){
			return false;
		}
		return true;
	}

	public static function is_new_payment($plan_id, $user_id = null)
	{	
		if(!$user_id && Auth::check())$user_id = Auth::user()->id;
		else if(!$user_id)$user_id = 0;
		
		$exists = UserMemberships::where('plan_id', $plan_id)->where('user_id', $user_id)->get()->first();
		
		if($exists){
			return false;
		}
		return true;
	}
	
	public static function user_trial_used($user_id = null)
	{
		if(!$user_id && Auth::check())$user_id = Auth::user()->id;
		else if(!$user_id)return false;
		
		return UserMemberships::where('user_id', $user_id)->where('trial_used', 1)->get()->first();
	}
	
	public static function upgrade_membership($plan_id, $user_id, $duration_days = null, $trial_duration = null, $fallback = null, $gateway_data = array())
	{	
		$membership = UserMemberships::find($user_id);
		if(!$membership){
			$membership = new UserMemberships();
			$membership->user_id = $user_id;
		}
		
		if(!$duration_days && !$fallback){
			throw new \ErrorException(__('Duration days cannot be null when fallback is not requested.'));
		}
		
		if($fallback){
			$membership->next_plan_id = $plan_id;
		}
		else $membership->plan_id = $plan_id;
		
		if($trial_duration){
			if(!$fallback){
				$pid = empty($gateway_data['payment_id']) ? null : $gateway_data['payment_id'];
				$refid = empty($gateway_data['ref_id']) ? null : $gateway_data['ref_id'];
				$trial_eligible = self::validate_trial($pid, $user_id, $refid);
				if(!$trial_eligible){
					throw new \ErrorException(__('Trying to avail trial when not eligible.'));
				}
				
				$membership->trial_ends_at = now()->addDays($trial_duration)->toDateTimeString();
				$membership->trial_active = 1;
				$membership->trial_used = 1;
			}
		}
		else if(!$fallback) $membership->trial_active = 0;
		
		if(!empty($gateway_data)){
			if($gateway_data['billing_interval'] == 1 && $duration_days > 31){
				throw new \ErrorException(__('Invalid subscription duration day.'));
			}
			else if($gateway_data['billing_interval'] == 3 && $duration_days > 95){
				throw new \ErrorException(__('Invalid subscription duration day.'));
			}
			else if($gateway_data['billing_interval'] == 12 && $duration_days > 370){
				throw new \ErrorException(__('Invalid subscription duration day.'));
			}
			
			if(!in_array($gateway_data['billing_interval'], array(1, 3, 12))){
				throw new \ErrorException(__('Invalid subscription duration day.'));
			}
		}
		
		if($duration_days){
			$membership->plan_ends_at = now()->addDays($duration_days)->toDateTimeString();
			$membership->next_payment_date = now()->addDays($duration_days)->toDateTimeString();
		}
		
		$new = self::is_new_payment($plan_id, $user_id);
		if($new && $gateway_data){
			$membership->first_payment_date = now();
			$membership->first_payment_amount = $gateway_data['amount'];
		}
		
		if(!$fallback && $gateway_data){
			$membership->payment_amount = $gateway_data['next_amount']; //$gateway_data['amount'];
			$membership->payment_date = now();
			$membership->next_payment_amount = $gateway_data['next_amount'];
		}
		
		if(!empty($gateway_data)){
			$membership->billing_interval = $gateway_data['billing_interval'];
			$membership->payment_method = $gateway_data['payment_method'];
			$membership->payment_id = $gateway_data['payment_id'];
			$membership->payment_ref = $gateway_data['ref_id'];
		}
		
		$membership->save();
		
		return true;
	}
	
	public static function get_billing_price($plan, $billing_period = null, $billing_interval = null)
	{
		if($billing_period)
			return ($billing_period == 'monthly' ? $plan->monthly_price : ( $billing_period == 'quarterly' ? $plan->quarterly_price : $plan->yearly_price ));
		if($billing_interval)
			return ($billing_interval == 1 ? $plan->monthly_price : ( $billing_interval == 3 ? $plan->quarterly_price : $plan->yearly_price ));
	}
	
	public static function get_bililing_interval_from_period($billing_period)
	{
		return $billing_period == 'monthly' ? 1 : ($billing_period == 'quarterly' ? 3 : 12);
	}
	
	public static function get_bililing_period_from_interval($billing_interval)
	{
		return $billing_interval ==  1 ? 'monthly' : ($billing_interval == 3 ? 'quarterly' : 'yearly');
	}
	
}