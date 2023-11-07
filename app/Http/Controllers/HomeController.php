<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Library\Skeleton\Models\PaymentsLog;
use App\Library\Skeleton\Billing\PaymentGateway;
use Session;

class HomeController extends Controller
{
    public function index()
    {
		$data = array(
			'page_title' => __('Home'),
		);
        return view('themes.'.$this->theme.'.home', $data);
    }
	
	public function about()
	{
		$data = array(
			'page_title' => __('About'),
		);
        return view('themes.'.$this->theme.'.about', $data);
	}
	
	public function features()
	{
		$data = array(
			'page_title' => __('Features'),
		);
        return view('themes.'.$this->theme.'.features', $data);
	}
	
	public function pricing()
	{
		$plans = DB::table('membership_plans')->where('is_active', 1)->get();
		
		$data = array(
			'page_title' => __('Pricing'),
			'plans' => $plans,
			'mini_page' => true,
			'plans_count' => $plans->count()
		);
        return view('themes.'.$this->theme.'.pricing', $data);
	}
	
	public function support()
	{
		$data = array(
			'page_title' => __('Support'),
		);
        return view('themes.'.$this->theme.'.support', $data);
	}
	
	public function contact()
	{
		$data = array(
			'page_title' => __('Contact'),
		);
        return view('themes.'.$this->theme.'.contact', $data);
	}
	
	public function sitemap()
	{
		$data = array(
			'page_title' => __('Contact'),
		);
        return view('themes.'.$this->theme.'.sitemap', $data);
	}
	
	public function success()
	{
		if(!Session::get('errors'))abort(404);
		$data = array(
			'page_title' => __('Success'),
			'mini_page' => true,
			'type' => 'success'
		);
        return view('themes.'.$this->theme.'.message', $data);
	}
	
	public function warning()
	{
		if(!Session::get('errors'))abort(404);
		$data = array(
			'page_title' => __('Attention'),
			'mini_page' => true,
			'type' => 'warning'
		);
        return view('themes.'.$this->theme.'.message', $data);
	}
	
	public function error()
	{
		if(!Session::get('errors'))abort(404);
		$data = array(
			'page_title' => __('Error'),
			'mini_page' => true,
			'type' => 'error'
		);
        return view('themes.'.$this->theme.'.message', $data);
	}
}
