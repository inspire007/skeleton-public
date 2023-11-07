@extends("themes.$theme.layouts.app")

@section('content')
<div class="ui container vertical sm_stripe">
	<x-alert type="icon positive">
		<x-slot name="title">{{__('Thank you for  the order!')}}</x-slot>
		<x-slot name="icon"><i class="icon check circle"></i></x-slot>
		{{__('Please wait for a moment while we validate and activate requested services. Please check your email for further instructions. If there is any error please contact us.') }}
	</x-alert>
	<p></p>
	<a href="{{ LaravelLocalization::localizeUrl(route('home')) }}">{{__('Go back to home')}}</a>
</div>
@endsection