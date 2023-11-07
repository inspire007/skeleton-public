@extends("themes.$theme.layouts.app")
@section('content')
<x-full-page-message> 
	<x-slot name="type">
        {{ $type }}
    </x-slot>
	@if($errors->any())
		@foreach ($errors->all() as $error)
			<div>{{ $error }}</div>
			@break;
		@endforeach
	@else
		<div>{{ __('Unknown error occurred!') }}</div>
	@endif
</x-full-page-message>
@endsection