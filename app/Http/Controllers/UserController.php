<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{	
    public function dashboard()
	{
		$data = array(
			'page_title' => __('Dashboard'),
			'dashboard_page' => true,
			'mini_page' => true
		);
        return view('themes.'.$this->theme.'.dashboard', $data);
	}
}
