<?php
/**
 * classes/wpmotion.php
 */

/**
 * wpmotion class definition
 */
class WPMotion {

	/**
	 * class constructor
	 */
	public function __construct()	{
		add_filter( 'wpm_json_request', array( $this, 'wpm_json_request' ) );
	}

	// -----------------------------------------------------------------

	/**
	 * responsible for plugin-to-server communication
	 *
	 * @param string $uri uri of requested server resource
	 * @param mixed[] $data2 an array of data to pass to the server
	 * @return string server response in json format or error string
	 */
	public function wpm_json_request( $uri, array $data2 ) {
		//setup the request
		$license_key = get_option( 'wpmotion_license_key' );
		$callback    = 'WPMotion' . uniqid();
		$data        = array( 'callback' => $callback, 'license_key' => get_option( 'wpmotion_license_key' ) );
		$data        = array_merge( $data, $data2 );
		$json        = '?json=' . json_encode( $data );
		$url         = 'https://go.wpmotion.co/main/' . $uri . $json;

		//make request	
		$response = wp_remote_get( $url, array( 'user-agent' => user_agent(), 'timeout' => 30 ) );

		//parse the response
		if ( $response['response']['code'] == 200 ) {
			$json = preg_replace( "/^[" . $callback . "\[]+|[\]]$/x", "", $response['body'] );
			if ( $json ) {
				$data = json_decode( $json, true );
				return $data;
			} else {
				$wpm_error = 'There was a problem interpreting the request.';
				return $wpm_error;
			}
		} else {
			$code      = $response['response']['code'];
			$message   = $response['response']['message'];
			$wpm_error = 'Error ' . $code . ': ' . $message . '<br />There was a problem communicating with the migration server.';
			return $wpm_error;
		}
	}

	// -----------------------------------------------------------------


}
