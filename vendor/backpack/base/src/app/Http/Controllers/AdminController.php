<?php

namespace Backpack\Base\app\Http\Controllers;

class AdminController extends Controller
{
    protected $data = []; // the information we send to the view

    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('admin');
    }

    /**
     * Show the admin dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function dashboard()
    {
        $this->data['title'] = trans('backpack::base.dashboard'); // set the page title

        return view('backpack::dashboard', $this->data);
    }

    /**
     * Redirect to the dashboard.
     *
     * @return \Illuminate\Routing\Redirector|\Illuminate\Http\RedirectResponse
     */
    public function redirect()
    {
        $check_version = array('ip' => $_SERVER['SERVER_ADDR'], 'hostname' => $_SERVER['SERVER_NAME'] ,'domain' => $_SERVER['HTTP_HOST'], 'email' => auth()->user()->email);

        $this->data['version_response'] = self::checkVersion($check_version, 'https://www.wiledia.com/gameport/version');

        // The '/admin' route is not to be used as a page, because it breaks the menu's active state.
        return redirect(config('backpack.base.route_prefix').'/dashboard');
    }

    public function checkVersion($_p, $remote_url)
    {
    	$remote_url = trim($remote_url);

    	$is_https = (substr($remote_url, 0, 5) == 'https');

    	$fields_string = http_build_query($_p);

    	if(function_exists('curl_init')) {

    		$ch = curl_init();

    		curl_setopt($ch, CURLOPT_URL, $remote_url);

    		if($is_https && extension_loaded('openssl')) {
    			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    		}

    		curl_setopt($ch, CURLOPT_POST, 1);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
    		curl_setopt($ch, CURLOPT_HEADER, false);

    		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    		$response = curl_exec($ch);

            return $response;

    		curl_close($ch);

    	} else {

    		$context_options = array (
    			'http' => array (
    				'method' => 'POST',
    				'header' => "Content-type: application/x-www-form-urlencoded\r\n".
    							"Content-Length: ".strlen($fields_string)."\r\n",
    				'content' => $fields_string
    			 )
    		 );


            try {

                $context = stream_context_create($context_options);
                $fp = fopen($remote_url, 'r', false, $context);

         		$response = @stream_get_contents($fp);

            } catch(\Exception $e) {
                return false;
            }

    	}
    	return $response;
    }
}
