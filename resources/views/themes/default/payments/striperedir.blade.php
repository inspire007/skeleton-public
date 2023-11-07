@extends("themes.$theme.layouts.app")

@section('content')
<style type="text/css">
.top_menu{display:none}
</style>
<div class="ui container loading_div">
  <div class="ui active inverted dimmer">
    <div class="ui text loader">{{__('Redirecting...')}}</div>
  </div>
  <p></p>
</div>
<div class="ui container vertical sm_stripe error_div" style="display:none">
	<div class="ui negative message">
	  <div class="header">
	  {{__('Error processing order!')}}
	  </div>
	  <p class="error_msg"></p>
	</div>
	<p></p>
	<a href="{{ LaravelLocalization::localizeUrl(route('home')) }}">{{__('Go back to home')}}</a>
</div>
<script src="https://js.stripe.com/v3/"></script>
<script>
var stripe = Stripe('{{ config("payment-gateways.Stripe.api_key") }}');
stripe.redirectToCheckout({
    sessionId: '{{ $session_id }}'
  }).then(function (result) {
	  document.getElementsByClassName('loading_div')[0].style.display = 'none';
	  document.getElementsByClassName('error_msg')[0].innerHTML =  result.error.message;
	  document.getElementsByClassName('error_div')[0].style.display = 'block';
  });
</script>
@endsection