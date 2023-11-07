<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class SetLocale extends Controller
{
    //
	public function setlang($lang)
	{
		if( in_array( $lang, LaravelLocalization::getSupportedLanguagesKeys() ) ) {
			\Session::put('locale', $lang);
			$url = url()->previous();
			return redirect(LaravelLocalization::localizeUrl($url));
		}
		
		return redirect()->back();
	}
}
