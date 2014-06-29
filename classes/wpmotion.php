<?php

/*
classes accessed by instantiating the new class:
$wpmotion = new WPMotion();
$t = $wpmotion->json_request();
var_dump($t);

or applying the filter:
$t2 = apply_filters( 'json_request' );
var_dump($t2);
*/

class WPMotion {

	public function __construct()
	{
		add_filter( 'json_request', array( $this, 'json_request' ) );
	}

	public function json_request($uri, array $data2)
	{
		//setup the request
		$license_key = get_option( 'wpmotion_license_key' );	
		$callback    = 'WPMotion' . uniqid();
		$data        = array( 'callback' => $callback, 'license_key' => get_option( 'wpmotion_license_key' ) );
		$data        = array_merge($data, $data2);
		$json        = '?json=' . json_encode($data);
		$url         = 'https://wpmotion.internetmoving.co/main/'. $uri . $json;

		//make request	
		$response = wp_remote_get( $url, array( 'user-agent' => user_agent(), 'timeout' => 30 ) );

		//var_dump($response);

		//parse the response
		if ( $response['response']['code'] == 200 ) 
		{
			$json = preg_replace( "/^[" . $callback . "\[]+|[\]]$/x", "", $response['body'] );
			if ($json)
			{
				$data = json_decode( $json, true );
				return $data;
			}
			else
			{
				$wpm_error = 'There was a problem interpreting the request.';
				return $wpm_error;
			}
		}
		else
		{
			$code = $response['response']['code'];
			$message = $response['response']['message'];
			$wpm_error = 'Error ' . $code . ': ' . $message . '<br />There was a problem communicating with the migration server.';
			return $wpm_error;
		}
	}

	public function curl_upload($filename, $filepath)
	{
		$key        = get_option( 'wpmotion_license_key' );
		$header     = array( "License-Key: " . $key );
		$server_url = 'https://wpmotion.internetmoving.co/main/get_uploads2/';
		$filesize   = filesize($filepath);
		$fh         = fopen($filepath, 'r');
		$handle     = curl_init($server_url.$filename);
		$curlOptArr = array(
			CURLOPT_HTTPHEADER => $header,
			CURLOPT_PUT        => TRUE,
			CURLOPT_INFILESIZE => $filesize,
			CURLOPT_INFILE     => $fh
			);
		curl_setopt_array($handle, $curlOptArr);
		$ret = curl_exec($handle);
		$errRet = curl_error($handle);
		curl_close($handle);
		//var_dump($errRet);
		//var_dump($ret);
		return $ret;
	}

	public function curl_upload_fallback()
	{
		$url = 'https://wpmotion.internetmoving.co/main/get_uploads2/test.sql';
		$tmpFile = '/storage/html/internetmoving.co/wp-content/plugins/wp-motion/exports/test.sql';
		$data = file_get_contents($tmpFile);
		$params = array(
			'http' => array(
				'method' => 'PUT',
				//'header' => "Authorization: Basic " . base64_encode($this->ci->config->item('ws_login') . ':' . $this->ci->config->item('ws_passwd')) . "\r\nContent-type: text/xml\r\n",
				'content' => file_get_contents($tmpFile)
				)
				);
		$ctx = stream_context_create($params);
		$response = @file_get_contents($url, false, $ctx);
		return ($response == '');
	}


	public function check_dns()
  	{
		$url      = get_bloginfo( 'url' );
		$url      = preg_replace( "(https?://)", "", $url );
		$data     = array( 'url' => $url );

		$result   = $this->json_request( 'dns_check_ttl', $data );

		if ( $result['ttl'] )
		{
			$ttl = $result['ttl'];
			if ( $ttl < 600 )
			{
				update_option( 'wpmotion_state', '6' );
				return TRUE;				
			}
			else
			{
				return FALSE;
			}
		}
		elseif ( $result['ERROR'] )
		{
			$wpm_error = $result['reason'];
			return $wpm_error;
		}
		else
		{
			var_dump($result);
		}
		return FALSE;
  	}

  	//needs some work
  	public function export_archive()
	{

		$export = new wpmotionexport();
	// -----------------------------------------------------------------

		//$export->get_archive_filepath();
		//$export->get_archive_filename();
		//$export->set_archive_filename( $filename );
		//$t3 = $export->get_database_dump_filepath();
		//$t2 = $export->get_database_dump_filename();
		//$export->set_database_dump_filename();
		//var_dump($t3);
		$t4 = $export->get_root();
		//var_dump($t4);
		//$export->set_root( $path );
		//$path = $export->get_path();
		$path = $t4.'/wp-content/plugins/wp-motion/exports';
		$export->set_path( $path );
		//$export->get_archive_method();
		//$export->get_mysqldump_method();
		//$type = $export->get_type();
		//$export->set_type( $type );
		//$path = $export->get_mysqldump_command_path();
		//$export->set_mysqldump_command_path( $path );
		//$path = $export->get_zip_command_path();
		//$export->set_zip_command_path( $path );
		//$export->backup();
		$t = $export->dump_database();
		//var_dump($t);
		//$t = $export->mysqldump();
		//var_dump($t);
		//$export->mysqldump_fallback();
		$t = $export->archive();
		//var_dump($t);
		
		//$t = $export->get_archive_filepath();
		//$t = $export->get_archive_filename();
		//$export->zip();
		//$export->zip_archive();
		//$export->pcl_zip();
		$t = $export->verify_mysqldump();
		//var_dump($t);
		//var_dump($t);
		$t = $export->verify_archive();
		//var_dump($t);
		//$export->get_files();
		//$export->get_included_files();
		//$export->get_included_file_count();
		//$export->get_excluded_files();
		//$export->get_excluded_file_count();
		//$export->get_unreadable_files();
		//$export->get_unreadable_file_count();
		//$excludes = $export->get_excludes();
		//$context = $export->set_excludes( $excludes, $append = false )
		//$export->exclude_string( $context = 'zip' )
		//$export->get_errors( $context = null )
		//$export->error( $context, $error )
		//$export->get_warnings( $context = null )
		//$export->error_handler( $type )

	// -----------------------------------------------------------------

		if ( $t )
		{
			update_option( 'wpmotion_state', '7' );
			return TRUE;
		}
		else
		{
			var_dump($t);
		}
		return FALSE;
	}

	public function do_upload()
	{
		$exports       = plugin_dir_path( __FILE__ ) . '../exports/';
		$files         = scandir( $exports, SCANDIR_SORT_DESCENDING);
		$filename      = $files[0];
		$filepath      = $exports.$files[0];
		$export_result = $this->curl_upload($filename, $filepath);

		if ( $export_result )
		{
			update_option( 'wpmotion_state', '8' );	
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	public function do_migration()
	{
		$url      = get_bloginfo( 'url' );
		$url      = preg_replace( "(https?://)", "", $url );
		$data     = array( 'url' => $url );
		$result   = $this->json_request( 'do_migration', $data );
		//var_dump($result);
		if ( $result['OK'] )
		{
			update_option( 'wpmotion_state', '9' ); //should be 6 when export_files is done
			$wpm_state = get_option( 'wpmotion_state' );
		}
		elseif ( $result['ERROR'] )
		{
			$wpm_error = $result['reason'];
		}
		else
		{
			var_dump($result);
		}
	}








































	public function get_access_token()
	{
		//setup the request
		$url         = 'https://wpmotion2.internetmoving.co/oauth/access_token';
		$post_data   = 'grant_type=client_credentials&client_id=wpmotion&client_secret=123456';
		//make request	
		$response = wp_remote_post( $url, array( 'sslverify' => FALSE, 'body' => $post_data ) );

		//parse the response
		if ( $response['response']['code'] == 200 ) 
		{
			$data = json_decode( $response['body'], true );
			return $data['access_token'];
		}

//		$code = $response['response']['code'];
//		$message = $response['response']['message'];
//		$wpm_error = 'Error ' . $code . ': ' . $message . '<br />There was a problem communicating with the migration server.';
//		return $wpm_error;
	
	}

	public function api_request($uri, array $data2)
	{

		$uri         = 'secure-endpoint';
		$url         = 'https://wpmotion2.internetmoving.co/'.$uri;
		$data 	   = array( 'access_token' => get_option( 'wpmotion_access_token' ) );
		
		//make request	
		$response = wp_remote_post( $url, array( 'sslverify' => FALSE, 'body' => $post_data ) );		
		
		//need to get a new access token
		if ( $response['response']['code'] == 401 && $response['response']['body']['error_message'] == 'Access token is not valid' )
		{
			$access_token = $this->get_access_token();
			update_option( 'wpmotion_access_token', $access_token );
		}
		
	}


}
