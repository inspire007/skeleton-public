@php $theme = config('site.theme')@endphp
@extends("themes.$theme.layouts.app")

@section('content')
<div class="ui vertical stripe container">
	<div class="ui four column grid">
		<div class=" column " style="border-color:red">AAAAAAAAAAAAA
		</div>
		<div class="column " style="border:red">BBBBBBBBBBBBBBBBB
		</div>
		<div class=" column " style="border:red">CCCCCCCCCCCCCCCCCCCCCCC
		</div>
		
		<div class=" column " style="border:red">DDDDDDDDDDDDDDDDD
		</div>
	</div>
</div>
@endsection