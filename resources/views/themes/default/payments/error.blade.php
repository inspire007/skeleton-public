@extends("themes.$theme.layouts.app")

@section('content')
<div class="ui container vertical sm_stripe">
	<x-alert type="icon negative">
		<x-slot name="title">{{__('Error processing order!')}}</x-slot>
		<x-slot name="icon"><i class="icon times circle"></i></x-slot>
		{{ $error }}
	</x-alert>
	<p></p>
	
	@if(!empty($warning))
	<x-alert type="icon warning">
		<x-slot name="title">{{__('Attention!')}}</x-slot>
		<x-slot name="icon"><i class="icon exclamation circle"></i></x-slot>
		{{ $warning }}
	</x-alert>	
	<p></p>
	@endif
	
	<a href="{{ url()->previous() }}">{{__('Go back to previous page')}}</a>
</div>
@endsection