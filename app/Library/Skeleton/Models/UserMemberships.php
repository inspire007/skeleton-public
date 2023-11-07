<?php

namespace App\Library\Skeleton\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use App;

/**
 * User Membership Model
 * Maps database table user_membership_plans
 * @package App\Library\Skeleton
 */
class UserMemberships extends Model
{
	protected $table = 'user_membership_plans';
	
	/**
     * @var array
	 * Declaring dates make it easy for us to apply Carbon operations on them
     */
	protected $dates = [
        'trial_ends_at',
		'plan_ends_at',
		'plan_starts_at',
		'first_payment_date',
		'next_plan_start_from',
		'latest_invoice_date'
    ];
	
	
	/**
     * @return an instance of App\User
	 * Each column in this database table only belongs to one user
     */
	public function user()
    {
        return $this->belongsTo('App\User');
    }
	
	/**
     * @return an instance of App\Library\Skeleton\Models\MembershipPlans
	 * Each record on this table will be originated from one membership-plan from the plans table
     */
	public function plan()
	{
		return $this->hasOne('App\Library\Skeleton\Models\MembershipPlans', 'id', 'plan_id');
	}
	
	/**
     * @return an instance of App\Library\Skeleton\Models\Invoices
	 * Each user-membership will have multiple invoices every month due to billing or plan swaps
     */
	public function invoices()
    {
        return $this->hasMany('App\Library\Skeleton\Models\Invoices');
    }
	
	/**
     * @return an instance of App\Library\Skeleton\Models\Invoices
	 * Relates to the latest invoice created for this user-membership plan
     */
	public function latest_invoice()
    {
        return $this->hasOne('App\Library\Skeleton\Models\Invoices', 'id', 'latest_invoice');
    }
	
	/**
	 * @param App\Library\Skeleton\Models\MembershipPlans instance represents users current membership plan details
     * @return null|float 
	 * unused balance from current membership plan till date
     */
	public function getBalance(App\Library\Skeleton\Models\MembershipPlans $plan)
	{
		if(!$this->is_active)return null;
		if(!$plan->price_1)return 0;
		
		$price_row = 'price_'.$this->billing_interval;
		
		$cost_per_day = $plan->$price_row / ($this->billing_interval * 30);
		$consumed_days = Carbon::now()->diffInDays($this->plan_starts_at);
		$consumed_amount = $consumed_days * $cost_per_day;
		
		return round($plan->$price_row - $consumed_amount, 2);
	}
	
	/**
     * @return null|int 
	 * returns days left till membership expiry
     */
	public function getDaysLeft()
	{
		if(!$this->valid())return null;
		if($this->onGracePeriod())return 0;
		
		return Carbon::now()->diffInDays( $this->plan_ends_at );
	}
	
	/**
	 * @param App\Library\Skeleton\Models\MembershipPlans instance represents the current membership plan details
	 * @param App\Library\Skeleton\Models\MembershipPlans instance represents the membership plan details that was requested
     * @return null|float 
	 * Calculate invoice amount for membership plan ==> current or new plan
	 * If plan is unchanged then same amount invoice is generated. Then after payment plan will be extended
	 * If new plan has a higher price then we calculate the unused balance from current membership and grace this amount from new plan price
	 * If new plan has a lower price we invoice user zero amount right now and set this plan to be actived after the expiry of current plan
     */
	public function calculateInvoiceAmount(App\Library\Skeleton\Models\MembershipPlans $current_plan, App\Library\Skeleton\Models\MembershipPlans $new_plan)
	{
		$price_row = 'price_'.$this->billing_interval;
		
		if($current_plan->id == $new_plan->id)return $new_plan->$price_row;
		
		if($new_plan->$price_row > $current->$price_row){
			return $new_plan->$price_row - $this->getBalance( $current_plan );
		}
		return 0;
	}
	
	/**
     * @return null|int 
	 * returns days left till trial expiry
     */
	public function getTrialLeft()
	{
		if(!$this->valid())return null;
		if(!$this->onTrial())return null;
		if($this->onGracePeriod())return 0;
	
		return Carbon::now()->diffInDays( $this->trial_ends_at );
	}
	
	/**
     * @return bool 
	 * returns if the user is on trial
     */
	public function onTrial()
	{
		return $this->is_active && $this->trial_active && Carbon::now()->lt($this->trial_ends_at); 
	}
	
	/**
     * @return bool 
	 * returns if the user is on grace period
     */
	public function onGracePeriod()
	{
		return Carbon::now()->gt($this->plan_ends_at) && Carbon::now()->lt( $this->plan_ends_at->addDays( config('billing.grace_period') ) ); 
	}
	
	/**
     * @return bool 
	 * returns if the user-membership is valid //should be is_active and current time less than plan_ends_at
     */
	public function valid()
	{
		return $this->is_active &&  Carbon::now()->lt($this->plan_ends_at);
	}
	
	/**
     * @return int 
	 * returns time in seconds till last invoice was created
     */
	public function lastInvoiceAgo()
	{
		return Carbon::now()->diff($this->latest_invoice_date);
	}
	
	/**
     * @return bool 
	 * returns if current user-membership is trial eligible
	 * Doesn't check if current user used trial on another payment method or his payment method was used to take trial on another account. 
	 * So check them from other Models.
     */
	public function isTrialEligible()
	{
		return !$this->trial_used && !$this->trial_active;
	}
	
	/**
     * @return an instance of App\Library\Skeleton\Models\UserMemberships 
	 * @param $days int represents how many days plan is active
	 * Starts plan from current date and returns the same Model
     */
	public function startPlan($days)
	{
		$this->is_active = 1;
		$this->plan_starts_at = Carbon::now();
		$this->plan_ends_at = Carbon::now()->addDays($days);
		
		return $this;
	}
	
	/**
     * @return an instance of App\Library\Skeleton\Models\UserMemberships 
	 * @param $days int represents how many days plan is active
	 * Extends plan for $days from end date and returns the same Model
     */
	public function extendPlan($days)
	{
		$this->is_active = 1;
		$this->plan_ends_at = $this->plan_ends_at->addDays($days);
		
		return $this;
	}
	
	/**
     * @return an instance of App\Library\Skeleton\Models\UserMemberships 
	 * Sets payment method and returns the same Model
	 * @param $method_name string name of the payment method e.g. Stripe|PayPal|Braintree
	 * @param $payment_id string payment gateway identifier for the user usually user email used for checkout
	 * @param $payment_gateway_ref string reference id for payment gateway usually subscription or billing agreement id. This is used later for subscription cancellation 
     */
	 
	 
	 
	 
	///////////////////////////WORK
	 ///////////////////////////CREATE A PAYMENT GATEWAY CLASS AND ASSIGN THEM THESE VARIABLES
	 
	public function setPaymentMethod( $method_name, $payment_id, $payment_gateway_ref )
	{
		$this->payment_method = $method_name;
		
		return $this;
	}
	
	/**
     * @return an instance of App\Library\Skeleton\Models\UserMemberships 
	 * @param $days int represents how many days trial is active
	 * Starts trial and returns the same Model
     */
	public function startTrial($days)
	{
		$this->trial_active = 1;
		$this->trial_used = 1;
		$this->plan_ends_at 
			= $this->trial_ends_at 
			= Carbon::now()->addDays( $days );
		
		return $this;
	}
	
	/**
     * @return an instance of App\Library\Skeleton\Models\UserMemberships 
	 * Removes trial and returns the same Model
     */
	public function removeTrial()
	{
		$this->trial_active = 0;
		$this->trial_ends_at = Carbon::now();
		
		return $this;
	}
	
	/**
     * @return an instance of App\Library\Skeleton\Models\UserMemberships 
	 * Cancels current user plan
	 * Unsubscribes from payment gateway
	 */
	public function cancelPlan()
	{
		try{
			$paymentGateway = (new PaymentGateway($this->payment_method))->make();
		}
		catch(\Exception $e){
			throw new \ErrorException('Payment gateway not found');
		}
		
		try{
			$paymentGateway->cancel_subscription($this->payment_gateway_ref);
		}
		catch(\Exception $e){
			throw new \ErrorException('Failed to cancel plan');
		}
			
		$this->is_active = 0;
		$this->plan_ends_at = Carbon::now();
		$this->trial_active = 0;
		$this->trial_ends_at = null;
		
		return $this;
	}
	
}
