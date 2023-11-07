@extends("themes.$theme.layouts.app")

@section('content')
	<x-centered-one-column-mini-page>
		<h1 class="ui header">{{__('Reset Password')}}</h1>
		@if (session('status'))
			<x-alert type="positive">
				<x-slot name="title">{{__('Action successful')}}</x-slot>
				{{ session('status') }}
			</x-alert>
		@endif
		@if($errors->any())
			@foreach ($errors->all() as $error)
				<x-alert type="negative">
					<x-slot name="title">{{__('Action failed')}}</x-slot>
					{{ $error }}
				</x-alert>
				@break
			@endforeach
		@endif
		<form class="ui form" method="POST" action="{{ LaravelLocalization::localizeUrl(route('password.email')) }}">
			@csrf

			<div class="field">
				<label for="email">{{ __('E-Mail Address') }}</label>
				<div class="ui left icon input">
					<i class="envelope icon"></i>
					<input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="{{ __('Email address') }}">
				</div>
			</div>

			<button type="submit" class="ui fluid secondary button">
				{{ __('Send Password Reset Link') }}
			</button>
			   
		</form>
		<x-slot name="back"><x-go-back-home/></x-slot>
	</x-centered-one-column-mini-page>
@endsection
