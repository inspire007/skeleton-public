<?php

function two_digit_price($x)
{
	return $x < 10 ?  "0".$x : $x;
}

function cal_percentage_save_on_billing($x1, $x2, $month)
{
	return round( ( ($x1 * $month - $x2) / ($x1 * $month)) * 100 ) ;
}

function redirect_success( $msg )
{
	return redirect(LaravelLocalization::localizeUrl(route('success')))->withErrors( [$msg] );
}

function redirect_warning( $msg )
{
	return redirect(LaravelLocalization::localizeUrl(route('warning')))->withErrors( [$msg] );
}

function redirect_error( $msg )
{
	return redirect(LaravelLocalization::localizeUrl(route('error')))->withErrors( [$msg] );
}

?>