@extends("themes.$theme.layouts.app")

@section('content')
<div class="ui container vertical sm_stripe">
	<x-alert type="negative">
		<x-slot name="title">{{__('Order cancelled!')}}</x-slot>
		{{__('The order has been cancelled. You can always order again if you change your mind. If there was any error please contact us.') }}
	</x-alert>
	<p></p>
	<a href="{{ LaravelLocalization::localizeUrl(route('home')) }}">{{__('Go back to home')}}</a>
</div>
@endsection