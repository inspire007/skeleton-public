@extends("themes.$theme.layouts.app")

@section('content')
<div class="ui vertical stripe sm_stripe container">
	<div class="ui one column wide grid">
		<div class="column">
		
			<h1 class="ui header center aligned">{{__('Membership plans and pricing')}}</h1>
			
			@if($plans_count == 0)
				
				<div class="ui negative message">
					<div class="header">{{ __('Sorry! Nothing found') }}</div>
					<p>{{ __('Oops! No membership plans yet. Check back later') }}</p>
				</div>
				<p></p>
			@else
			
		    <section id="generic_price_table"> 
				<div class="ui stackable {{ $plans_count < 3 || $plans_count == 4 ? 'two' : 'three' }} column wide grid">
					@foreach($plans as $plan)
						<div class="column">
							<div class="generic_content {{ $plan->is_featured ? 'active' : '' }}">
								
								<div class="generic_head_price ">
								
									<div class="generic_head_content ">
									
										<div class="head_bg"></div>
										<div class="head">
											<span>{{{ $plan->plan_name }}}</span>
										</div>
										
									</div>
									
									@php 
										$dc = array(
											1 => array(floor($plan->price_1), two_digit_price($plan->price_1 - floor($plan->price_1)), __('/MON')),
											3 => array(floor($plan->price_3), two_digit_price($plan->price_3 - floor($plan->price_3)), __('/QUAR')),
											12 => array(floor($plan->price_12), two_digit_price($plan->price_12 - floor($plan->price_12)), __('/YEAR'))
										);
									@endphp
									
									<div class="generic_price_tag price_selected" data-config="{{ json_encode($dc) }}">    
										<span class="price">
											<span class="sign">{{ config('site.currency_symbol') }}</span>
											<span class="currency">{{ (floor($plan->price_1)) }}</span>
											<span class="cent">{{ two_digit_price($plan->price_1 - floor($plan->price_1)) }}</span>
											<span class="month">{{ __('/MON') }}</span>
										</span>
									</div>
								
								</div>                            
								
								<div class="generic_feature_list">
									<ul>
										<li style="background:lavender">
											  <div class="ui floating labeled icon dropdown billing_period_dropdown">
												  <i class="gift icon"></i>
												  <span class="text">{{__('Get Discounted Offers')}}</span>
												  <div class="menu">
													<div class="header">
													{{__('Choose billing period')}}
													</div>
													<div class="divider"></div>
													<div class="item" data-value="1">
													  <span class="description">0% {{__('OFF')}}</span>
													  <span class="text">{{__('Monthly')}}</span>
													</div>
													@if($plan->price_3)
													<div class="item" data-value="3">
													  <span class="description">{{ cal_percentage_save_on_billing($plan->price_1, $plan->price_3, 3) }}% {{__('OFF')}}</span>
													  <span class="text">{{__('Quarterly')}}</span>
													</div>
													@endif
													@if($plan->price_12)
													<div class="item" data-value="12">
													  <span class="description">{{ cal_percentage_save_on_billing($plan->price_1, $plan->price_12, 12) }}% {{__('OFF')}}</span>
													  <span class="text">{{__('Annually')}}</span>
													</div>
													@endif
												  </div>
												</div>
										</li>
										<li><span>{{ $plan->trial_duration ? $plan->trial_duration.' '.__('days') : __('No') }}</span> {{__('Trial')}}</li>
										<li><span>2GB</span> Bandwidth</li>
										<li><span>150GB</span> Storage</li>
										<li><span>12</span> Accounts</li>
										<li><span>7</span> Host Domain</li>
										<li><span>24/7</span> Support</li>
									</ul>
								</div>
								
								<form class="member_upgrade_form" method="post" action="{{ LaravelLocalization::localizeUrl(route('membership.request')) }}">
									@csrf
									<div class="generic_price_btn ">
										<button class="">Sign up</button>
									</div>								
									<input type="hidden" name="plan_id" value="{{$plan->id}}"/>
									<input type="hidden" name="billing_interval" value="1"/>
									<input type="hidden" name="uniqid" value="{{ uniqid() }}"/>
								</form>
								
							</div>
						</div>	

					@endforeach
					
			</section>  
			
			@endif
				<a href="{{ LaravelLocalization::localizeUrl(route('home')) }}">
					{{ __('Go back to home') }}
				</a>
				
		</div>
	</div>
</div>
@endsection