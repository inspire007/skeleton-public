<?php

namespace App\Library\Skeleton\PaymentGateways\PayPal;

use Auth;
use Session;
use DB;
use LaravelLocalization;
use PayPal\Api\ChargeModel;
use PayPal\Api\Currency;
use PayPal\Api\MerchantPreferences;
use PayPal\Api\PaymentDefinition;
use PayPal\Api\Plan;
use PayPal\Api\Patch;
use PayPal\Api\PatchRequest;
use PayPal\Common\PayPalModel;
use PayPal\Api\Agreement;
use PayPal\Api\AgreementDetails;
use PayPal\Api\AgreementStateDescriptor;
use PayPal\Api\Payer;
use PayPal\Api\PayerInfo;
use PayPal\Api\ShippingAddress;
use PayPal\Api\Webhook;
use PayPal\Api\WebhookEventType;
use App\Library\Skeleton\MembershipPlans;
		
class PayPal
{
	public function __construct()
	{
		
	}
	
	public function index()
	{
		$theme = config('site.theme');
		if(!config('payment-gateways.PayPal.is_enabled'))abort(401);
		
		$plan_id = request()->input('plan_id');
		$plan = MembershipPlans::get_plan_details($plan_id);
		$billing_period = request()->input('billing_period');
		
		if($billing_period == 'monthly')$bill_interval = 1;
		else if($billing_period == 'quarterly')$bill_interval = 3;
		else $bill_interval = 12;
		
		if(!$plan)abort(401);
		
		$amount = $billing_period == 'monthly' ? $plan->monthly_price : ( $billing_period == 'quarterly' ? $plan->quarterly_price : $plan->annual_price );
		$trial = $plan->trial_duration;
		$is_recurring = (request()->input('payment_cycle') == 'recurring' ? 'recurring' : 'onetime') ;
		
		$apiContext = $this->create_api_context();
		
		$paypal_plan = $this->create_plan($plan, $amount, $bill_interval, $is_recurring, $trial);
		
		//create plan
		try {
    
			$cPlan = $paypal_plan->create($apiContext);

			try {

				$patch = new Patch();

				$value = new PayPalModel('{"state":"ACTIVE"}');
				$patch->setOp('replace')
					->setPath('/')
					->setValue($value);

				$patchRequest = new PatchRequest();
				$patchRequest->addPatch($patch);

				$cPlan->update($patchRequest, $apiContext);
				$cPlan = Plan::get($cPlan->getId(), $apiContext);

			} catch (\Exception $ex) {
				return $this->display_error(__('Failed to create paypal plan. Please contact site administrators. ').$ex->getCode().'|'.$ex->getData());
			}

		} catch (\Exception $ex) {
			return $this->display_error(__('Failed to create paypal plan. Please contact site administrators. ').$ex->getCode().'|'.$ex->getData());
		}
		
		$webhook = new Webhook();
		$webhook->setUrl('https://8975881d389c.ngrok.io/projects/skeleton/public/payment/ipn/paypal/');
		//LaravelLocalization::localizeUrl(route('paypal_ipn_request'))));
		
		$webhookEventTypes = array();
		$webhookEventTypes[] = new WebhookEventType(
			'{
				"name":"BILLING.SUBSCRIPTION.CANCELLED"
			}'
		);
		$webhookEventTypes[] = new WebhookEventType(
			'{
				"name":"BILLING.SUBSCRIPTION.RE-ACTIVATED"
			}'
		);
		$webhookEventTypes[] = new WebhookEventType(
			'{
				"name":"BILLING.SUBSCRIPTION.SUSPENDED"
			}'
		);
		
		$webhookEventTypes[] = new WebhookEventType(
			'{
				"name":"PAYMENT.SALE.COMPLETED"
			}'
		);
		
		$webhook->setEventTypes($webhookEventTypes);
		try {
			$webhook->create($apiContext);
		} 
		catch (\Exception $ex) {
			//$error = __('Failed to create webhooks. Please contact site administrators. ').$ex->getCode().'|'.$ex->getData();
			//return view('themes.'.$theme.'.payments.error', array('error' => $error) );
		}
		
		$agreement = new Agreement();

		$agreement->setName(__('Membership purchase: ').$plan->plan_name)
			->setDescription( sprintf(__('%.02f %s billed each %d month(s) as %s payment with %d day(s) trial'), $amount, env('SITE_CURRENCY'), $bill_interval, $is_recurring, $trial) )
			->setStartDate(date('c', time() + 600));

		$p_plan = new Plan();

		$p_plan->setId($cPlan->getId());
		$agreement->setPlan($p_plan);

		$payer = new Payer();
		$payer->setPaymentMethod('paypal');
		$agreement->setPayer($payer);

		try {
			$agreement = $agreement->create($apiContext);
			$approvalUrl = $agreement->getApprovalLink();
			
			Session::put('payment_init', $plan->id);
			return redirect($approvalUrl);

		} catch (\Exception $ex) {
			return $this->display_error(__('Failed to create paypal approval url. Please contact site administrators. ').$ex->getCode().'|'.$ex->getData());
		}
		
	}
	
	protected function create_plan($data, $amount, $bill_interval, $is_recurring, $trial)
	{
		if($is_recurring == 'recurring'){
			$plan_type = 'infinite';
			$cycle = 0;
		}
		else{
			$plan_type = 'fixed';
			$cycle = 1;
		}
			
		$trial_used = MembershipPlans::user_trial_used();
		
		$plan = new Plan();
		$plan->setName(env('APP_NAME').'-'.$data->plan_name.'-'.$data->id)
			->setDescription(env('APP_NAME').'-'.$data->plan_name.'-'.$data->id)
			->setType($plan_type);


		$paymentDefs = array();
		$paymentDefinition = new PaymentDefinition();

		
		$paymentDefinition->setName('Regular Payments')
			->setType('REGULAR')
			->setFrequency('Month')
			->setFrequencyInterval($bill_interval)
			->setCycles($cycle)
			->setAmount(new Currency(array('value' => $amount, 'currency' => env('SITE_CURRENCY'))));

		/*
		$chargeModel = new ChargeModel();
		$chargeModel->setType('SHIPPING')
			->setAmount(new Currency(array('value' => 0, 'currency' => 'USD')));

		$paymentDefinition->setChargeModels(array($chargeModel));
		*/
		
		$paymentDefs[] = $paymentDefinition;
		
		if($trial && !$trial_used){
			$trialPaymentDefinition = new PaymentDefinition();
			$trialPaymentDefinition->setName('One time trial')
				->setType('TRIAL')
				->setFrequency('Day')
				->setFrequencyInterval($trial)
				->setCycles("1")
				->setAmount(new Currency(array('value' => 0, 'currency' => env('SITE_CURRENCY'))));
			$paymentDefs[] = $trialPaymentDefinition;
		}
		
		$merchantPreferences = new MerchantPreferences();

		$merchantPreferences->setReturnUrl(LaravelLocalization::localizeUrl(route('paypal_approval_redir_url')))
			->setCancelUrl(LaravelLocalization::localizeUrl(route('payment_cancel')))
			->setNotifyUrl('https://0fd29829884d.ngrok.io/projects/skeleton/public/payment/ipn/paypal/')//LaravelLocalization::localizeUrl(route('paypal_ipn_request')))
			->setAutoBillAmount("yes")
			->setInitialFailAmountAction("CONTINUE")
			->setMaxFailAttempts("0");

		$plan->setPaymentDefinitions($paymentDefs);
		$plan->setMerchantPreferences($merchantPreferences);
		
		return $plan;
	}
	
	protected function create_api_context()
	{
		$apiContext = new \PayPal\Rest\ApiContext(
		  new \PayPal\Auth\OAuthTokenCredential(
			env('PAYPAL_API_KEY'),
			env('PAYPAL_API_SECRET')
		  )
		);
		
		$apiContext->setConfig(
			  array(
				'mode' => env('PAYPAL_MODE') == 'sandbox' ? 'sandbox' : 'live',
				'log.LogEnabled' => true,
				'log.FileName' => __DIR__.'/PayPal.log',
				'log.LogLevel' => 'DEBUG',
			  )
		);
		
		return $apiContext;
	}
	
	public function subscription_approved()
	{
		$theme = env('SITE_THEME');
		$error = '';
		
		if(!Session::has('payment_init'))abort(401);
		$plan_id = Session::get('payment_init');
		$plan = MembershipPlans::get_plan_details($plan_id);
		
		if(!$plan)abort(401);
		Session::forget('payment_init');
		
		$trial_used = MembershipPlans::user_trial_used();
		$apiContext = $this->create_api_context();
		
		if (!empty(request()->input('token'))) {
			$token = request()->input('token');
			$agreement = new Agreement();
			$data = array();
			$membership = array();
			
			$membership['payment_method'] = 'PayPal';
			$data['plan_id'] = $membership['plan_id'] = $plan_id;
			$data['payment_method'] = 'PayPal';
			$data['txn_status'] = 'FAIL';
			$data['created_at'] = now();
			
			try {
				$agreement->execute($token, $apiContext);
				$agreementId = $agreement->getId();
				$agreement = Agreement::get($agreementId, $apiContext);
				
				$payer = $agreement->getPayer();
				$payerInfo = $payer->getPayerInfo();
				$email = $payerInfo->getEmail();
				$data['payment_id'] = $email;
				$membership['payment_id'] = $email;
				$membership['payment_ref'] = $data['ref_id'] = $agreementId;
				
				$trial_eligible = MembershipPlans::validate_trial($email);
				$new_payment = MembershipPlans::is_new_payment($plan_id);
				
				if($trial_eligible && !$trial_used && $plan->trial_duration ){
					$data['has_trial'] = 1;
					$membership['trial_used'] = 1;
					$membership['trial_active'] = 1; 
					$membership['trial_ends_at'] = now()->addDays($plan->trial_duration);
				}
				
				$p_plan = $agreement->getPlan();
				$paymentDefs = $p_plan->getPaymentDefinitions();
				$is_recurring = 1;
				
				/*
				echo '<pre>';
				print_r($paymentDefs);
				print_r($p_plan);
				*/
				
				foreach($paymentDefs as $pd){
					$type = $pd->getType();
					$duration = $pd->getFrequencyInterval();
					
					if($type == 'TRIAL')$data['has_trial'] = 1;
					if($type == 'TRIAL' && ( !$trial_eligible || !$plan->trial_duration || $trial_used ) ){
						$data['reason'] = 'Attempt to avail trial when not eligible';
						$this->log_payment($data);
						$this->cancel_plan($agreement, $apiContext);
						return $this->display_error(__('Sorry! You are not eligible for trial.'));
					}
					else if($type == 'TRIAL' && $duration != $plan->trial_duration){
						$data['reason'] = 'Attempt to avail wrong trial length';
						$this->log_payment($data);
						$this->cancel_plan($agreement, $apiContext);
						return $this->display_error(__('Sorry! Wrong trial length.'));
					}
					else if($type == 'REGULAR'){
						$amount = $pd->getAmount();
						$bill_interval = $pd->getFrequencyInterval();
						$is_recurring = ($pd->getCycles() ? 0 : 1);
					}
				}
				
				$to_pay = ($bill_interval == 1 ? $plan->monthly_price : ($bill_interval == 3 ? $plan->quarterly_price : $plan->annual_price));
				
				$paid_currency = $amount->getCurrency();
				$paid_amount = $amount->getValue();
				$data['amount'] = $paid_amount;
				$data['currency'] = $paid_currency;
				
				if($paid_amount != $to_pay || $paid_currency != config('site.currency') ) {
					$this->cancel_plan($agreement, $apiContext);
					$data['reason'] = 'Paid amount or currency mismatch';
					$this->log_payment($data);
					return $this->display_error(__('Sorry! Failed to validate payment.'));
				}
				
				if($new_payment){
					$membership['first_payment_date'] = now();
					$membership['first_payment_amount'] = $to_pay;
				}

				$membership['next_payment_date'] = now()->addDays($bill_interval * 30);
				$membership['next_payment_amount'] = $to_pay;
				$membership['payment_currency'] = config('SITE_CURRENCY');
				if(!$is_recurring)$membership['plan_ends_at']  = now()->addDays($bill_interval*30);
				
				$data['txn_status'] = 'SUCCESS';
				$data['user_id'] = Auth::user()->id;
				
				$this->log_payment($data);
				$this->update_user_membership($membership);
				
				return view('themes.'.$theme.'.payments.thankyou');
			} 
			catch (\Exception $ex) {
				return $this->display_error(__('Failed to validate paypal token. Please contact site administrators.'));
			}
		}
		else{
			return $this->display_error(__('Error processing order. Please contact site administrators. '));
		}
	}
	
	protected function cancel_plan($createdAgreement, $apiContext)
	{
		$agreementStateDescriptor = new AgreementStateDescriptor();
		$agreementStateDescriptor->setNote("Suspending the agreement");
		
		try {
			$createdAgreement->suspend($agreementStateDescriptor, $apiContext);
			return true;
		}
		catch(\Exception $ex){
			return false;
		}
	}
	
	protected function log_payment($data)
	{
		DB::table('payments_log')->insert(
			$data
		);
	}
	
	protected function update_user_membership($data)
	{
		if(!empty($data['user_id']))unset($data['user_id']);
		DB::table('user_membership_plans')->updateOrInsert(
			['user_id' => Auth::user()->id],
			$data
		);
	}
	
	protected function display_error($error)
	{
		$theme = config('site.theme');
		return view('themes.'.$theme.'.payments.error', array('error' => $error) );
	}
	
	public function ipn()
	{
		$d = request()->all();
		file_put_contents(__DIR__.'/ipn.txt', print_r($d, true));
		file_put_contents(__DIR__.'/ipn2.txt', print_r($_POST, true));
	}
	
}