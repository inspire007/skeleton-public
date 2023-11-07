@php $theme = config('site.theme') @endphp
@include("themes.$theme.layouts.header")
@include("themes.$theme.layouts.footer")

<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ !empty($page_title) ? $page_title . ' | ' : '' }}{{ config('app.name') }}</title>
    <meta name="description" content="{{ config('site.description') }}">
    <meta name="author" content="{{ config('site.author') }}">
    <meta name="keywords" content="{{ config('site.meta_keywords') }}">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.4.1/semantic.min.css" type="text/css"/>
    <link rel="stylesheet" href="{{ url("assets/$theme/css/site.css") }}"/>
	<script>
		var user_dashboard_url = '{{LaravelLocalization::localizeUrl(route('user_dashboard'))}}';
		var current_route = '{{Route::currentRouteName()}}'; 
		var GOO_API_KEY = '{{config('site.google_app_id')}}'; 
		var FB_API_KEY = '{{config('site.fb_app_id')}}';
		var social_login_url = '{{LaravelLocalization::localizeUrl(route('social_login'))}}';
		var payment_zero_url = '{{LaravelLocalization::localizeUrl(route('membership.zero'))}}';
		var js_locale = {};
		js_locale.error = '{{__('Error')}}';
		js_locale.success = '{{__('Success')}}';
		js_locale.expand = '{{__('Expand')}}';
		js_locale.collapse = '{{__('Collapse')}}';
		js_locale.choose_payment_method = '{{__('Please choose a payment method')}}';
	</script>
</head>
<body id="root" class="pushable">
	@if(empty($mini_page))
		@yield('sidebar')
	@endif
	<div class="pusher {{ !empty($mini_page) ? 'mini_page' : '' }}">
		@yield('topmenu')
		@if(empty($mini_page))
			@yield('nav')
		@endif
		@yield('content')
		@if(empty($mini_page))
			@yield('footer')
		@endif
    </div>
	
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<script src="https://cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/semantic-ui/2.3.3/semantic.min.js"></script>
	<script src="{{ url("assets/$theme/js/site.js") }}?v=1"></script>
	
</body>
</html>
