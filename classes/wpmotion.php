<?php
/**
 * classes/wpmotion.php
 */

/**
 * WPMotion class definition
 */
class WPMotion {

	/**
	 * class constructor
	 */
	public function __construct()	{
		add_filter( 'json_request', array( $this, 'json_request' ) );
	}

	// -----------------------------------------------------------------

	/**
	 * responsible for plugin-to-server communication
	 *
	 * @param string $uri URI of requested server resource
	 * @param mixed[] $data2 an array of data to pass to the server
	 * @return string server response in json format or error string
	 */
	public function json_request( $uri, array $data2 ) {
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

	/**
	 * initiates the migration - NOT IN USE
	 *
	 * @return void
	 */
/*	public function do_migration() {
		$url      = get_bloginfo( 'url' );
		$url      = preg_replace( "(https?://)", "", $url );
		$data     = array( 'url' => $url );
		$result   = $this->json_request( 'do_migration', $data );
		if ( $result['OK'] ) {
			update_option( 'wpmotion_state', '9' );
			$wpm_state = get_option( 'wpmotion_state' );
		} elseif ( $result['ERROR'] ) {
			$wpm_error = $result['reason'];
		} else {
			var_dump( $result );
		}
	} */

	// -----------------------------------------------------------------


}
