@extends('yivic-rest-api::layouts/wp-main')

@section('content')
	<div class="container">
		<h1><?php echo 'Setup Page'; ?></h1>
		<div class="message-content">
			{!! $message !!}
		</div>
	</div>
@endsection
