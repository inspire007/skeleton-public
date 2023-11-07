<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Support\Facades\Auth;
use \Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Google_Client;
use App\User; 
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

	public $response;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
		parent::__construct();
		$this->response = array('error' => '');
        $this->middleware('guest')->except('logout');
    }
	
	public function showLoginForm()
    {
		$data = array(
			'page_title' => __('Login'),
			'mini_page' => true
		);
        return view('themes.'.$this->theme.'.auth.login', $data);
    }
	
	public function logout()
    {
		Auth::logout();
		return redirect()->intended(LaravelLocalization::localizeUrl(route('home')));
	}
	
	public function social()
	{
		$email = null;
		$site = request()->post('site');
		
		if(!in_array($site, array('google', 'facebook'))){
			$this->response['error'] = __('Sorry! Invalid site');
			return $this->output();
		}
		
		$id_token = request()->post('id_token'); 
		
		if(empty($id_token)){
			$this->response['error'] = __('Invalid access token');
			return $this->output();
		}
		
		if($site == 'facebook'){
			if(!config('site.fb_login_enabled')){
				$this->response['error'] = __('Sorry! Facebook login is disabled');
				return $this->output();
			}
			else{
				$url = "https://graph.facebook.com/me";
				$client = new \GuzzleHttp\Client();
				$response = $client->request('GET', $url, ['query' => [
					'access_token' => $id_token, 
					'fields' => 'email,name',
				]]);
				$content = $response->getBody();
				$data = json_decode($content, true);
				if(empty($data['email'])){
					$this->response['error'] = __('Invalid Facebook login');
					return $this->output();
				}
				$email = $data['email'];
			}
		}
		
		if($site == 'google'){
			if(!config('site.google_login_enabled')){
				$this->response['error'] = __('Sorry! Google login is disabled');
				return $this->output();
			}
			else{
				$client = new Google_Client(['client_id' => config('site.google_api_key')]);
				$payload = $client->verifyIdToken($id_token);
				if($payload)$email = $payload['email'];
				else{
					$this->response['error'] = __('Invalid Google login');
					return $this->output();
				}
			}
		}
		
		if(empty($email)){
			$this->response['error'] = __('Invalid email address');
			return $this->output();
		}
		
		$existing_user = User::where('email', '=', $email)->first();
			
		if(empty($existing_user)) {
			if(!config('site.registration_enabled')){
				$this->response['error'] = __('Sorry! new user registration is disabled');
				return $this->output();
			}
			$uname = explode('@', $email);
			$uname = $uname[0];
			$user = new User();
			$user->password = Hash::make(uniqid().rand(111111111, 999999999).time());
			$user->email_verified_at = now();
			$user->email = $email;
			$user->name = $uname;
			$user->save();
			
			Auth::login($user, true);
			
			$this->response['email'] = $email;
			return $this->output();
		}
		
		Auth::login($existing_user, true);
		$this->response['email'] = $email;
		return $this->output();
		
	}
	
	public function output()
	{
		return response()->json($this->response);
	}
	
}
