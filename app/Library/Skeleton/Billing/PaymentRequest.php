<?php

namespace App\Library\Skeleton\Billing;
use App\Library\Skeleton\Models\MembershipPlans;
use App\Library\Skeleton\Models\UserMemberships;
use App\Library\Skeleton\Models\Invoice;
use Carbon\Carbon;
use Config;

/**
 * PaymentRequest Model
 * Handles payment request
 * @package App\Library\Skeleton\Billing
 */
class PaymentRequest
{
	/**
     * @param UserMemberships Class | represents a UserMemberships class for current user plan
	 * @param MembershipPlans Class | represents details of current user-membership class
	 * @param MembershipPlans Class | represents details of requested user-membership class
	 * @param billing_interval int  | billing interval e.g 1|3|12
	 * @param is_recurring bool 	| whether the payment request is recurring or onetime
	 * @return Invoice Class 		| a model of invoice class
     */
	public function generate_invoice(UserMemberships $current_plan, MembershipPlans $current_plan_details, MembershipPlans $requested_plan_details, $billing_interval, $is_recurring)
	{
		$token = request()->input('uniqid');
		$origin = 'CART_'.$token;
		$invoice = Invoice::where('invoice_origin', $origin)->first();
		if(!$invoice){
			$invoice = new Invoice();
			$invoice->user_id = $current_plan->user_id;
			$invoice->plan_id = $requested_plan_details->id; 
			$invoice->billing_interval = $billing_interval;
			$invoice->currency = config('site.currency');
			$invoice->payment_due_date = Carbon::now()->addDays(2);
			$invoice->invoice_origin = $origin;
			
			$price_key = "price_$billing_interval";
			$price = $requested_plan_details->$price_key;
			$invoice->billing_price = $price;
			
			$trial_available = $current_plan->isTrialEligible();
			$invoice->trial_days = $trial_available ? $requested_plan_details->trial_duration : 0;
			
			/*
			 * Free plan
			 */
			if($current_plan->id == 1){
				$invoice->amount_due = $price;
				$invoice->invoice_action_require = 'CREATE';
			}
			/*
			 * Paid plans
			 */
			 /*
			 * Same plan requested
			 * We will invoice full amount and extend membership
			 */
			else if($current_plan_details->id == $requested_plan_details->id){
				$invoice->amount_due = $price;
				$invoice->invoice_action_require = 'EXTEND';
			}
			 /*
			 * Different plan requested
			 * We will invoice balance amount and upgrade/downgrade membership
			 */
			else{
				if($price > $current_plan_details->$price_key){
					$unused_balance = $current_plan->getBalance($current_plan_details);
					$invoice->membership_balance_used = $unused_balance;
					$invoice->amount_due = $price - $unused_balance;
					$invoice->invoice_action_require = 'UPGRADE';
				}
				else{
					$daysLeft = $current_plan->getDaysLeft();
					$invoice->amount_due = 0;
					$invoice->next_payment_date = Carbon::now()->addDays( $daysLeft );
					$invoice->invoice_action_require = 'DOWNGRADE';
					$invoice->membership_balance_days = $daysLeft;
					$invoice->trial_days = $daysLeft;
				}
			}
			
			$invoice->save();
		}
		return $invoice;
	}
	
}