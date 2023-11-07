@extends("themes.$theme.layouts.app")

@section('content')
	<x-centered-one-column-mini-page>
		<h1 class="ui header">{{__('Confirm your password')}}</h1>
		@if($errors->any())
			@foreach ($errors->all() as $error)
				<x-alert type="negative">
					<x-slot name="title">{{__('Action failed')}}</x-slot>
					{{ $error }}
				</x-alert>
				@break
			@endforeach
		@endif
			<form  class="ui form" method="POST" action="{{ LaravelLocalization::localizeUrl(route('password.confirm')) }}">
				@csrf

				<div class="field">
					<label for="password">{{ __('Password') }}</label>

					<div class="ui left icon input">
						<i class="lock icon"></i>
						<input id="password" type="password" name="password" required autocomplete="current-password" placeholder="{{ __('Password') }}">
					</div>
				</div>

				<button type="submit" class="ui secondary button">
					{{ __('Confirm Password') }}
				</button>

				@if (Route::has('password.request'))
					<!--<a class="btn btn-link" href="{{ route('password.request') }}">
						{{ __('Forgot Your Password?') }}
					</a>-->
				@endif
					
			</form>
		<x-slot name="back"><x-go-back-home/></x-slot>
	</x-centered-one-column-mini-page>	
    
@endsection

