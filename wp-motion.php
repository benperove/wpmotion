<?php
/*
Plugin Name: WP Motion
Plugin URI: http://wpmotion.co
Description: A plugin/service that automates WordPress migrations to other hosting providers.
Author: Benjamin Perove
Version: 0.8.0
Author URI: http://benperove.com
*/

if ( ! defined( 'WPM_PLUGIN_SLUG' ) )
	define( 'WPM_PLUGIN_SLUG', basename( dirname( __FILE__ ) ) );

if ( ! defined( 'WPM_PLUGIN_PATH' ) )
	define( 'WPM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'WPM_PLUGIN_URL' ) )
	define( 'WPM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'WPM_REQUIRED_PHP_VERSION' ) )
	define( 'WPM_REQUIRED_PHP_VERSION', '5.2.4' );

//hook in activation/deactivation actions
register_activation_hook( WPM_PLUGIN_SLUG . '/wp-motion.php', 'wpm_activate' );
register_deactivation_hook( WPM_PLUGIN_SLUG . '/wp-motion.php', 'wpm_deactivate' );
register_uninstall_hook( WPM_PLUGIN_SLUG . '/uninstall.php', 'wpm_uninstall' );

//don't activate on anything less than PHP 5.2.4
if ( version_compare( phpversion(), WPM_REQUIRED_PHP_VERSION, '<' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	deactivate_plugins( __FILE__ );

	if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'activate' || $_GET['action'] == 'error_scrape' ) )
		die( sprintf( __( 'WP Motion requires PHP version %s or greater.', 'wpm' ), WPM_REQUIRED_PHP_VERSION ) );

}

//don't activate on old versions of wordpress
global $wp_version;

if ( version_compare( $wp_version, WPM_REQUIRED_WP_VERSION, '<' ) ) {

	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	deactivate_plugins( __FILE__ );

	if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'activate' || $_GET['action'] == 'error_scrape' ) )
		die( sprintf( __( 'WP Motion requires WordPress version %s or greater.', 'wpm' ), WPM_REQUIRED_WP_VERSION ) );

}

/**
 * require functions & classes, if they exist
 *
 * @return void
 */
foreach ( glob( WPM_PLUGIN_PATH . 'functions/*.php' ) as $filename )
{
	require_once($filename);
}

foreach ( glob( WPM_PLUGIN_PATH . 'classes/*.php' ) as $filename )
{
	require_once($filename);
}

global $wpm_plugin_version;
$wpm_plugin_version = $wp_version;


/**
 * add plugin to tools menu
 *
 * @return void
 */
function wpm_admin_actions() {

	add_management_page( 'WP Motion', 'WP Motion', 'manage_options', __FILE__, 'wpm_admin' );

}
add_action( 'admin_menu', 'wpm_admin_actions' );

//check to see if a shell command exists
function cmd_exists($cmd) {

	$return = shell_exec("command -v $cmd");
	return (empty($return) ? false : true);
	
} 
add_filter( 'cmd_exists', 'cmd_exists' );



/**
 * require functions & classes, if they exist
 *
 * @return void
 */
function wpm_migration_prep_js() {
?>
<script type="text/javascript" >
jQuery(document).ready(function($) {

	$('#loading')
		.bind('ajaxStart', function() {
	  		$(this).show();
	}).bind('ajaxComplete', function() {
	  		$(this).hide();
	});

	var data = {
		action: "migration_prep",
		whatever: 1234
	};

	$.post(ajaxurl, data, function(response) {
		var json = JSON.parse(response);
		var msg = "All systems are functional; proceeding with migration.";
		if (json.result == true) {
			$('.success-box.alert')
				.fadeIn(800)
				.delay(2000)
				.fadeOut(800);
			$('.msg')
				.html(msg)
				.fadeIn(800)
				.delay(2000)
				.fadeOut(800);
			location.reload();
		} else {
			console.log(json.err);
			$('.error-box.alert')
				.fadeIn(800)
				.delay(2000)
				.fadeOut(800);
			$('.msg')
				.html('FUCK!!!')
				.fadeIn(800)
				.delay(2000)
				.fadeOut(800);
		}
	});

});
</script>
<style>
	.alert { 
		padding: 19px 15px;
		display: none;
		color: #000; 
		position: relative; 
		font: 14px/20px Museo300Regular, Helvetica, Arial, sans-serif; 
	} 
	.alert .msg { 
		padding: 0 20px 0 40px; 
		display:none; 
	} 
	.alert p { 
		margin: 0; 
	} 
	.alert .toggle-alert { 
		position: absolute; 
		top: 7px; 
		right: 10px; 
		display: block; 
		text-indent: -10000px; 
		width: 20px; 
		height: 20px; 
		border-radius: 10px; 
		-webkit-box-shadow: inset 1px 1px 2px rgba(0, 0, 0, 0.1), 1px 1px 1px rgba(255, 255, 255, 0.1); 
		-moz-box-shadow: inset 1px 1px 2px rgba(0, 0, 0, 0.1), 1px 1px 1px rgba(255, 255, 255, 0.1); 
		box-shadow: inset 1px 1px 2px rgba(0, 0, 0, 0.1), 1px 1px 1px rgba(255, 255, 255, 0.1); 
		background: rgba(0, 0, 0, 0.08) url(../wp-content/plugins/wp-motion/assets/images/alert.png) no-repeat 6px 6px; 
	} 
	.info-box { 
		background: #2fa9f6 url(../wp-content/plugins/wp-motion/assets/images/info.png) no-repeat 14px 14px; 
	} 
	.success-box {	
		-moz-border-radius: 5px 5px 5px 5px;
		-webkit-border-radius: 5px;  
		border-radius: 5px 5px 5px 5px; 
		border: #7EB62E 2px solid; 
		background: #CCE0AF url(../wp-content/plugins/wp-motion/assets/images/success.png) no-repeat 14px 14px; 
  	} 
	.error-box { 
		background: #f64b2f url(../wp-content/plugins/wp-motion/assets/images/error.png) no-repeat 14px 14px; 
	} 
	.notice-box { 
		background: #f6ca2f url(../wp-content/plugins/wp-motion/assets/images/notice.png) no-repeat 14px 14px; 
	} 
	.download-box { 
		background: #a555ca url(../wp-content/plugins/wp-motion/assets/images/download.png) no-repeat 14px 14px; 
	}
</style>
<div id="loading"><img style="zoom: 0.5;" src="../wp-content/plugins/wp-motion/assets/images/ajax-loading.gif" /><p>Preparing for migration... please stand by. This process may take a few minutes, so avoid hitting the refresh button.</p></div>
<div class="success-box alert">
	<div class="msg"></div>
</div>
<div class="error-box alert">
	<div class="msg"></div>
</div>
<?php
}
add_action( 'wpm_migration_prep', 'wpm_migration_prep_js' );

function migration_prep_callback() {
	update_option( 'wpmotion_maintenance_mode', '1' );
//	$wpmotion = new wpmotion();
//	$ttl      = $wpmotion->check_dns();
//	$archive  = $wpmotion->export_archive();
//	$upload   = $wpmotion->do_upload();
//	$result   = array('ttl' => $ttl, 'archive' => $archive, 'upload' => $upload);
//	if ($ttl == TRUE && $archive == TRUE && $upload == TRUE)
//	{
//		$data = array('result' => TRUE);
		$data = apply_filters( 'wpm_do_migration' );
//	}
//	else
//	{
//		$err = array_keys($result, FALSE);
//		$data = array('result' => FALSE, 'error' => $err);
//	}
	echo json_encode($data);

	die(); // this is required to return a proper result
}
add_action( 'wp_ajax_migration_prep', 'migration_prep_callback' );

function wpm_do_migration() {
	$wpmotion = new wpmotion();
	$url      = get_bloginfo( 'url' );
	$url      = preg_replace( "(https?://)", "", $url );
	$data     = array( 'url' => $url );
	
	$result   = $wpmotion->json_request( 'do_migration_from_' . strtolower( get_option( 'wpmotion_sourcehost' ) ), $data );
	$result['OK'] = TRUE;
	
	if ( $result['OK'] )
	{
		update_option( 'wpmotion_state', '9' );
		$data = array('result' => TRUE);
		return $data;
	}
	elseif ( $result['ERROR'] )
	{
		$err = array_keys($result, FALSE);
		$data = array('result' => FALSE, 'error' => $err);
		return $data;
	}
	else
	{
		var_dump($result);
	}

}
add_filter( 'wpm_do_migration', 'wpm_do_migration' );

function wpm_init() {

	$plugin_data = get_plugin_data( __FILE__ );

	//define plugin version
	define( 'WPM_VERSION', $plugin_data['Version'] );

	//fire the update action
	if ( WPM_VERSION != get_option( 'wpmotion_plugin_version' ) )
		echo 'Plugin out-of-date. Please update.';

}
add_action( 'admin_init', 'wpm_init' );

function wpm_admin_styles() {

	wp_register_style( 'jquery_ui_css', plugins_url( 'wp-motion/assets/css/jquery-ui-1.10.4.custom.css' ) );
	wp_enqueue_style( 'jquery_ui_css' );
	wp_register_style( 'wpm_css', plugins_url( 'wp-motion/assets/css/wp-motion.css' ) );
	wp_enqueue_style( 'wpm_css' );
	//wp_register_style( 'wp-polls', 'http://internetmoving.co/wp-content/plugins/wp-polls/polls-css.css?ver=2.63' );
	//wp_enqueue_style( 'wp-polls' );
}
add_action( 'set_admin_styles', 'wpm_admin_styles' );

function maintenance_mode_enter() {

	$mm = new MaintenanceMode;
	$mm->mm_enter();

}
add_action( 'mm_enter', 'maintenance_mode_enter' );

function maintenanace_mode_exit() {

	$mm = new MaintenanceMode;
	$mm->mm_exit();

}
add_action( 'mm_exit', 'maintenance_mode_exit' );

function maintenance_mode() {

	if ( get_option( 'wpmotion_maintenance_mode') )
	{
		$enabled = get_option( 'wpmotion_maintenance_mode' );
		if ($enabled == '1')
		{
			do_action( 'mm_enter' );
		}
		else
		{
			do_action( 'mm_exit' );
		}
	}
}
add_action( 'maintenance_mode', 'maintenance_mode' );
do_action( 'maintenance_mode' );

function wpm_enqueue_admin_scripts() {

	//wp_register_script( 'jq', 'https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js' );
	//wp_enqueue_script( 'jq' );
	wp_deregister_script( 'jquery-ui' );
	wp_register_script( 'jquery-ui', 'https://code.jquery.com/ui/1.10.4/jquery-ui.js' );
	wp_enqueue_script( 'jquery-ui' );
	wp_deregister_script( 'jquery' );
	wp_register_script( 'jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js');


	//wp_enqueue_script( 'mm-js', plugins_url('/wp-motion/assets/js/mm.js'), array('jquery'), FALSE, FALSE );

	//wp_enqueue_script('jquery');
	//wp_register_script( 'wp-polls', 'https://internetmoving.co/wp-content/plugins/wp-polls/polls-js.js?ver=2.63' );
	//wp_enqueue_script( 'wp-polls' );
	//wp_register_script( 'wp-polls-dev', 'https://internetmoving.co/wp-content/plugins/wp-polls/polls-js.dev.js' );
	//wp_enqueue_script( 'wp-polls-dev' );
	//wp_register_script( 'plupload', 'https://internetmoving.co/wp-content/plugins/wp-motion/assets/js/plupload.full.min.js' );
	//wp_enqueue_script( 'plupload' );
}
add_action( 'enqueue_admin_scripts', 'wpm_enqueue_admin_scripts' );

function wpm_admin_head() {

	do_action( 'set_admin_styles' );
	do_action( 'enqueue_admin_scripts' );

}
add_action( 'admin_head', 'wpm_admin_head' );

function wpm_admin() {

	//setup globals
	global $wpdb;
	global $wpm_state;
	global $uid;
	global $ref;
//	global $exports;
	global $host_array;
	global $wpm_error;
	//global $wpmotion;
	//global $export;
//	global $ouput_buffer;

	//define globals
/*	$host_array = array(
		'WP Engine'      => 'disabled',
		'DreamHost'      => 'disabled',
		'HostGator'      => 'disabled',
		'BlueHost'       => 'disabled',
		'Godaddy'        => 'disabled',
		'Linode'         => 'disabled',
		'A Small Orange' => 'disabled',
		'Fat Cow'		  => 'disabled',
		'1&1'		  => 'disabled',
		'Digital Ocean'  => 'disabled',
		'Rackspace'      => 'disabled'
		);
*/
	$wpm_state       = get_option( 'wpmotion_state' );
	$uid             = get_current_user_id(); 
	$ref             = admin_url() . 'tools.php?page=wp-motion/wp-motion.php';
	$domain          = str_replace(array('http://', 'https://'), '', get_site_url());
	//$exports       = plugin_dir_path( __FILE__ ) . 'exports/';
	$wpm_error       = NULL;
	$wpmotion        = new wpmotion();
	//$export        = new wpmotionexport();
/*
	//include support for selected host
	if ( get_option( 'wpmotion_selected_host' ) )
	{
		$host = get_option( 'wpmotion_selected_host' );
		$host = strtolower( str_replace( ' ', '', $host ) );
		include( plugin_dir_path( __FILE__ ) . 'hosts/' . $host . '.php' );	
	}
*/
	//get license key from migration server & add to db
	//change state from 0 to 1
	if ( isset($_POST['get_started']) && $wpm_state == '0' ) {
		$wpm_first_name = urlencode(get_user_meta($uid, 'first_name', true));
		$wpm_last_name  = urlencode(get_user_meta($uid, 'last_name', true));
		$admin_email    = urlencode(get_option( 'admin_email' ));
		$domain		 = urlencode($domain);

		//setup the inital request		
		$callback = 'WPMotion' . uniqid();
		$data     = array( 'callback' => $callback, 'first_name' => $wpm_first_name, 'last_name' => $wpm_last_name, 'email' => $admin_email, 'url' => $domain );
		$json     = '?json=' . json_encode($data);
		$url      = 'https://wpmotion.internetmoving.co/main/plugin_signup'.$json;

		//make request	
		$response = wp_remote_get($url, array( 'user-agent' => user_agent(), ));

		//get response	
		if ( $response['response']['code'] == 200 ) 
		{
			$json = preg_replace("/^[" . $callback . "\[]+|[\]]$/x", "", $response['body']);
			if ($json)
			{
				$data = json_decode( $json, true );
			}
			else
			{
				$wpm_error = 'There was a problem interpreting the request.';
			}
		}
		else
		{
			$code      = $response['response']['code'];
			$message   = $response['response']['message'];
			$wpm_error = 'Error ' . $code . ': ' . $message . '<br />There was a problem communicating with the migration server.';		
		}

		//process response
		if ( $data['OK'] )
		{
			$hosts_export = var_export($data['destination_hosts'], true);
			update_option( 'wpmotion_destination_hosts', $hosts_export );
			$wpm_license_key = $data['license_key'];
			add_option( 'wpmotion_license_key', $wpm_license_key );
			update_option( 'wpmotion_state', '1' );
			$wpm_state = get_option( 'wpmotion_state' );
		}
		else
		{
			$wpm_error = $data['reason'];
		}

	}

	//desired host is selected
	//change state from 1 to 2
	if ( isset( $_POST['select_host'] ) && $wpm_state == '1' ) {

		$host   = $_POST['host'];
		$data   = array( 'selected_host' => $host );
		$result = $wpmotion->json_request( 'selected_host', $data );
		
		if ( $result['OK'] )
		{
			update_option( 'wpmotion_selected_host', $host );		
			update_option( 'wpmotion_state', '2' );
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

	elseif ( $_GET['change_selected_host'] == 'TRUE')
	{
		update_option( 'wpmotion_selected_host', NULL );
		update_option( 'wpmotion_state', '1' );
		$wpm_state = get_option( 'wpmotion_state' );
	}
	
	//create hosting account button pressed 
	if ( isset( $_POST['wpm_host_signup'] ) && $wpm_state == '2' )
	{
		//setup the request
		$license_key = get_option( 'wpmotion_license_key' );	
		$callback    = 'WPMotion' . uniqid();
		$data        = array( 'callback' => $callback, 'license_key' => get_option( 'wpmotion_license_key' ) );
		$json        = '?json=' . json_encode($data);
		$url         = 'https://wpmotion.internetmoving.co/main/host_signup' . $json;
	
		//make request	
		$response = wp_remote_get( $url, array( 'user-agent' => user_agent(), ) );

		//parse the response
		if ( $response['response']['code'] == 200 ) 
		{
			$json = preg_replace( "/^[" . $callback . "\[]+|[\]]$/x", "", $response['body'] );
			if ($json)
			{
				$data = json_decode( $json, true );
			}
			else
			{
				$wpm_error = 'There was a problem interpreting the request.';
			}
		}
		else
		{
			$code = $response['response']['code'];
			$message = $response['response']['message'];
			$wpm_error = 'Error ' . $code . ': ' . $message . '<br />There was a problem communicating with the migration server.';
		}

		if ( $data['OK'] )
		{
			update_option( 'wpmotion_create_account_pressed', '1' );
			header( 'Location: ' . $data['signup_url'] );
		}
		else
		{
			$wpm_error = $data['reason'];
		}

	}

	//confirm account creation
	if ( isset( $_POST['wpm_host_confirm'] ) && $wpm_state == '2' )
	{
		$data   = array( 'selected_host' => $host ); //not sure why i need this here
		$result = $wpmotion->json_request( 'check_host_signup', $data );
		
		if ( $result['OK'] )
		{
			update_option( 'wpmotion_state', '3' );
			$wpm_state = get_option( 'wpmotion-state' );
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

	//existing hosting account button pressed
	if ( isset( $_POST['wpm_existing_host'] ) && $wpm_state == '2' ) 
	{
		$payment_url = 'https://internetmoving.co/payment-wp-motion?license_key=' . get_option( 'wpmotion_license_key' ) . '&email=' . get_option( 'admin_email' ) . '&url=' . $domain . '&ref='.urlencode($ref);
		?>
		<script>
			window.location = '<?php echo $payment_url; ?>';
		</script>
		<?php
		update_option( 'wpmotion_state', '3' );
		$wpm_state = get_option( 'wpmotion_state' );
	}

	//validate credentials
	if ( isset( $_POST['submit_credentials'] ) && $wpm_state == '3' )
	{
		$username = $_POST['username'];
		$password = $_POST['password'];
		$data     = array( 'username' => $username, 'password' => $password, 'host' => 'WP Engine', 'ref' => $ref, 'domain' => $domain );
		$result   = $wpmotion->json_request('check_credentials', $data);
	
		if ( $result['OK'] && isset($result['installs']) )
		{	
			if ( isset( $result['sourcehost'] ) )
			{
				$sourcehost = $result['sourcehost'];
				update_option( 'wpmotion_sourcehost', $sourcehost );
			}
			$installs = $result['installs'];
			$userid   = $result['userid'];
			update_option( 'wpmotion_installs', var_export( $installs, TRUE ) );
			update_option( 'wpmotion_state', '3.1' );
			$wpm_state = get_option( 'wpmotion_state' );
		}
		elseif ( $result['OK'] )
		{
			update_option( 'wpmotion_state', '4' );
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

	if ( isset( $_POST['selected_install'] ) && $wpm_state == '3.1' )
	{
		$selected_install = $_POST['install'];

		if ( $selected_install == 'add_new_install' )
		{
			$url = "https://my.wpengine.com/installs#add_install_form";
			header( 'Location: ' . $url );
		}
		else
		{
			$data     = array( 'selected_install' => $selected_install );
			$result   = $wpmotion->json_request( 'selected_install', $data );			
		}

		if ( $result['OK'] )
		{
			if ( get_option( 'wpmotion_sourcehost' ) )
			{
				//add additional step for source host credentials
				update_option( 'wpmotion_state', '3.2' );
				$wpm_state = get_option( 'wpmotion_state' );
			}
			else
			{
				update_option( 'wpmotion_state', '4' );
				$wpm_state = get_option( 'wpmotion_state' );						
			}
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
	elseif ( $wpm_state == '3.1' )
	{
		$installs2 = get_option( 'wpmotion_installs' );
		eval("\$installs = $installs2;");
	}

	if ( isset( $_POST['submit_credentials'] ) && $wpm_state == '3.2' )
	{
		$username   = $_POST['username'];
		$password   = $_POST['password'];
		$sourcehost = $_POST['sourcehost'];
		$doc_root   = get_home_path();
		$data       = array( 'username' => $username, 'password' => $password, 'host' => $sourcehost, 'ref' => $ref, 'domain' => $domain, 'doc_root' => $doc_root );
		$result     = $wpmotion->json_request('check_credentials', $data);

		if ( $result['OK'] )
		{	
			update_option( 'wpmotion_state', '4' );
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

	//credentials have been validated and environment has been checked
	//changing from state 4 to state 5
	//moved to core
//	if ( ( isset( $_POST['environment_check_complete'] ) ) && $wpm_state == '4' ) {
	
//		update_option( 'wpmotion_state', '5' );
//		$wpm_state = get_option( 'wpmotion_state' );

//	}

	//check dns
	//moved to core
/*	if ( isset( $_POST['check_dns'] ) && $wpm_state == '5' ) {

		$url      = get_bloginfo( 'url' );
		$url      = preg_replace( "(https?://)", "", $url );
		$data     = array( 'url' => $url );

		$result   = $wpmotion->json_request( 'dns_check_ttl', $data );

		if ( $result['ttl'] )
		{
			$ttl = $result['ttl'];
			if ( $ttl < 600 )
			{
				update_option( 'wpmotion_state', '6' );
				$wpm_state = get_option( 'wpmotion_state' );
			}
		}
		elseif ( $result['ERROR'] )
		{
			$wpm_error = $result['reason'];
		}
		else
		{
			var_dump($result);
		}

	} */

	//export files
	//moved to core
/*	if ( isset( $_POST['export_files'] ) && $wpm_state == '6' )
	{

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
			$wpm_state = get_option( 'wpmotion_state' );
		}
		else
		{
			var_dump($t);
		}

	} */

	//upload files to migration server
	//moved to core
/*	if ( isset( $_POST['upload_files'] ) && $wpm_state == '7' )
	{
		$files         = scandir( $exports, SCANDIR_SORT_DESCENDING);
		$filename      = $files[0];
		$filepath      = $exports.$files[0];
		$export_result = curl_upload($filename, $filepath);

		if ( $export_result )
		{
			update_option( 'wpmotion_state', '8' );
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
	} */

//	if ( $wpm_state == '8' )
//	{
//		$wpm_state = get_option( 'wpmotion_state' );
//	}

	//moved to plugin function
/*	if ( isset($_POST['do_migration']) && $wpm_state == '8' )
	{		
	
		$url      = get_bloginfo( 'url' );
		$url      = preg_replace( "(https?://)", "", $url );
		$data     = array( 'url' => $url );
		$result   = $wpmotion->json_request( 'do_migration', $data );
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

	} */

	if ( isset($_POST['verify_site']) && $wpm_state == '10' )
	{		
		$url      = get_bloginfo( 'url' );
		$url      = preg_replace( "(https?://)", "", $url );
		$data     = array( 'url' => $url );
		$result   = $wpmotion->json_request( 'verify_site', $data );

		if ( $result['OK'] )
		{
			$pct = $result['percent_similar'];
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

	?>

	<div class="wrap">
	<script>
	function migration_server_request(url) {

		var ref   = document.getElementById('ref').value;
		var email = document.getElementById('admin_email').value;
		//var url = 'https://wpmotion.internetmoving.co/main/plugin_signup2?license_key= &callback=?';

		$.ajax({
			url: url,
			jsonp: "callback",
			dataType: "jsonp",
			data: { format: "json" },
		    success: function(res) {
		    	//alert('license key: '+res.license_key);
		    	//console.log(res);
		    	$("#wpmotion-result").html(' '+res.primary);
/*
			    	$.ajax({
			    		type: "post",
			    		//url: "options-general.php?page=wp-motion/wp-motion.php",
			    		url: "http://internetmoving.co/testpost.php",
			    		data: { JSON.stringify(res) }
			    		//success: function(data){
	               		//console.log('SUCCESS: '+data);
	               	//}
	               	//error: function(data){
	               	//	console.log('ERROR: '+data);
	               		//$("#bp-result").html(res);
	               	//}
	 				               
	           	});
*/
		    },
		    error: function(res) {
		    		console.log(res);
		    }
		});

	}
	</script>

		<style>
			p {width:570px;}
		</style>
		<!-- always display the plugin header -->
		<h1 style="letter-spacing:6px;">WP Motion<span style="font-variant: small-caps; font-size: 70%; color:#0074a2;"> ⇨ Seamless WordPress Migration</span></h1>
		<h3>version <?php echo WPM_VERSION ?> by <a href="http://internetmoving.co">internetmoving.co</a></h3>
		<?php echo ( get_option( 'wpmotion_license_key' ) ) ? '<strong>Registered to</strong>: <span style="color:green">' . get_option( 'admin_email' ) . '</span><br />' : NULL ?>
		<?php echo ( get_option( 'wpmotion_license_key' ) ) ? '<strong>License key</strong>: &nbsp; &nbsp; <span style="color:green">' . get_option( 'wpmotion_license_key' ) . '</span><br />' : NULL ?>
		<?php echo ( get_option( 'wpmotion_selected_host' ) ) ? '<strong>Selected host</strong>: <span style="color:green">' . get_option( 'wpmotion_selected_host' ) . '</span>' : NULL ?>
		<?php echo ( get_option( 'wpmotion_state' ) == '2' ) ? ' [<a href="' . $ref . '&change_selected_host=TRUE">change</a>]<br />' : '<br /><br />' ?>
		<?php //echo ( get_option( 'wpmotion_state' ) == '0' ) ? '' : '<br />' ?>
		<?php
			//state 0 - welcome screen & disclaimer
			if ( $wpm_state == '0' ) {
				echo '<p style="line-height:175%; position:relative; top:-20px;">WP Motion is a WordPress plugin that makes it easy for you to move your WordPress site to another 
					hosting company. Regardless as to where your site is presently hosted, WP Motion does all the heavy lifting and checks to make sure 
					that everything is in place prior to switchover. In no time at all, you will have completed a migration of your WordPress site to the host of your choice.</p>';

					$response = wp_remote_get( 'https://wpmotion.internetmoving.co/main/ssl_check' );
					if ($response['response']['code'] == '200')
					{
						echo '<p style="color:green"><img src="http://internetmoving.co/wp-content/plugins/wp-motion/assets/images/secure-connection.png" class="ssl_connection" />All migration activities are secured by 256-bit SSL encryption</p>';
					}

				echo '<form action="" method="POST">';
				//echo '<form action="" method="post" onsubmit="javascript:migration_server_request(); return false;">';
				//echo '<input type="hidden" name="ref" value="<?php $admin_url = admin_url(); echo $admin_url."/options-general.php?page=click2call/click2call.php"; ">'; //removed end php braces after ;
				//$admin_url = admin_url();
				echo '<input type="hidden" id="ref" name="ref" value="' . admin_url() . 'tools.php?page=wp-motion/wp-motion.php">';
				echo '<input type="hidden" id="admin_email" name="admin_email" value="'.get_option( 'admin_email' ).'">';
				echo '<input type="submit" name="get_started" value="Get Started With Your Migration" class="button-primary" />';
				echo '</form>';
				echo '<br />';
				echo '<div id="wpmotion-result"></div>';
				echo '<div id="wpmotion-error">' . (isset($wpm_error) ? $wpm_error . ' <br />Please contact support at 1-866-386-4592' : '') . '</div>';
				
			}

			//state 1 - host selection
			if ( $wpm_state == '1' /* && get_option( 'wpmotion_license_key' ) */ ) {
				$destination_hosts2 = get_option( 'wpmotion_destination_hosts' );
				eval("\$destination_hosts = $destination_hosts2;");
				?>
				<p>To which host will you be migrating your WordPress site?</p>
				<table>
					<form action="" method="POST">
						<tr><td>Desired Host</td><td>
							<select name="host" style="width:232px">
							<?php
								//iterate through hosts array
								foreach ( $destination_hosts as $host => $enabled )
								{
									//if array value is enabled, light up the option
									if ( $destination_hosts[$host] == 'enabled' )
									{
										echo '<option value="'.$host.'">'.$host.'</option>';
									}
									else
									{
										echo '<option value="'.$host.'" '.$destination_hosts[$host].'>'.$host.'</option>';
									}						
								}
							?>
							</select></td></tr>
						<tr><td><br /><input type="submit" name="select_host" value="Next" class="button-primary" /></td></tr>
					</form>
				</table>
				<br />
				<div id="wpmotion-result"></div>
				<div id="wpmotion-error"><?php echo (isset($wpm_error) ? $wpm_error : '' ) ?></div>
			<?php
			} 

			//state 2 - hosting account creation
			if ( $wpm_state == '2' && ! get_option( 'wpmotion_create_account_pressed' ) ) {
			?>
				<p>Migrating your site to <?php echo get_option( 'wpmotion_selected_host' ); ?> is easy!<br />Click one of the following buttons.</p>
	    			<?php
	    			//create an account button
	    			echo '<form action="" method="POST">';
	    			echo '<input type="submit" name="wpm_host_signup" value="Create an Account with ' . get_option( 'wpmotion_selected_host' ) . '" class="button-primary" style="width:250px;">';
	    			echo '</form>';
	    			echo '<br />';

	    			//already have an account button
	    			echo '<form action="" method="POST">';
	    			echo '<input type="submit" name="wpm_existing_host" value="I already have an account" class="button-primary" style="width:250px;">';
	    			echo '</form>';
	    			echo '<br />';
	    			echo '<div id="wpmotion-error">' . (isset($wpm_error) ? $wpm_error : '') . '</div>';
	    			?>

			<?php
			}
			elseif ( $wpm_state == '2' && get_option( 'wpmotion_create_account_pressed' ) == '1' )
			{
	    			//create an account button
	    			echo '<form action="" method="POST">';
	    			echo '<input type="submit" name="wpm_host_confirm" value="Confirm your ' . get_option( 'wpmotion_selected_host' ) . ' account" class="button-primary" style="width:250px;">';
	    			echo '</form>';
	    			echo '<br />';
	    			echo '<div id="wpmotion-error">' . (isset($wpm_error) ? $wpm_error : '') . '</div>';				
			}

			//state 3 - validate credentials
			if ( $wpm_state == '3' ) {
			?>
				<table>
				<tr><td>Enter your <?php echo get_option( 'wpmotion_selected_host' ); ?> login credentials</td></tr>
				<tr><td>
					<form action="" method="POST">
						<input type="text" name="username" value="Username" onfocus="if (this.value=='Username') this.value='';" ></td></tr>
						<tr><td><input type="password" name="password" value="Password" onfocus="if (this.value=='Password') this.value='';"></td></tr>
						<input type="hidden" name="ref" value="<?php echo admin_url() . 'tools.php?page=wp-motion/wp-motion.php'; ?>">
						<tr><td><input type="submit" name="submit_credentials" value="Next" class="button-primary" />
					</form></td></tr>
				</table>
				<p>Note: your passwords are safe with us! Passwords are hashed using modern cryptography algorithms and can easily be purged at the end of the migration.</p>
				<div id="wpmotion-result"></div>
				<div id="wpmotion-error"><?php echo (isset($wpm_error) ? $wpm_error : '' ) ?></div>

			<?php
			}

			//state 3.1 - select wpengine install
			if ( $wpm_state == '3.1' ) {
			?>
				<table>
				<tr><td>Choose the install you would like to use</td></tr>
				<tr><td>
					<form action="" method="POST">
						<select name="install" style="width:232px">
						<?php					
						foreach ( $installs as $install )
						{
							echo '<option value="'.$install.'">'.$install.'</option>';
						}
						echo '<option value="add_new_install">Add new install</option>';
						?>				
						</select></td></tr>
						<tr><td><input type="submit" name="selected_install" value="Select" class="button-primary" />
					</form></td></tr>
				</table>			
				<div id="wpmotion-result"></div>
				<div id="wpmotion-error"><?php echo (isset($wpm_error) ? $wpm_error : '' ) ?></div>
			<?php
			}

			//state 3.2 - validate credentials for source host
			if ( $wpm_state == '3.2' ) {
			?>
				<table>
				<tr><td>Enter your <?php echo get_option( 'wpmotion_sourcehost' ); ?> login credentials</td></tr>
				<tr><td>
					<form action="" method="POST">
						<input type="text" name="username" value="Username" onfocus="if (this.value=='Username') this.value='';" ></td></tr>
						<tr><td><input type="password" name="password" value="Password" onfocus="if (this.value=='Password') this.value='';"></td></tr>
						<input type="hidden" name="ref" value="<?php echo admin_url() . 'tools.php?page=wp-motion/wp-motion.php'; ?>">
						<input type="hidden" name="sourcehost" value="<?php echo get_option( 'wpmotion_sourcehost' ); ?>">
						<tr><td><input type="submit" name="submit_credentials" value="Next" class="button-primary" />
					</form></td></tr>
				</table>
				<p>Note: your passwords are safe with us! Passwords are hashed and salted using modern crypto algorithms and can be purged at the end of the migration.</p>
				<div id="wpmotion-result"></div>
				<div id="wpmotion-error"><?php echo (isset($wpm_error) ? $wpm_error : '' ) ?></div>

			<?php
			}

			if ( $wpm_state == '4' )
			{
				do_action( 'wpm_migration_prep' );
			}

//			if ( $wpm_state == '4' ) {

//				echo '<p>Next step is to evaluate the current WordPress Environment.</p>';
/*
	    			//check environment button
	    			echo '<form action="" method="POST">';
	    			echo '<input type="submit" name="check_environment" value="Proceed" class="button-primary" style="width:250px;">';
	    			echo '</form>';
	    			echo '<br />';
	    			echo '<div id="wpmotion-error">' . (isset($wpm_error) ? $wpm_error : '') . '</div>';				
*/


				/*	
				//setup commands array
				$commands = array( //1st param - command exists, 2nd permissions to run
					'ssh'       => array( FALSE, FALSE ),
					'scp'       => array( FALSE, FALSE ),
					'sftp'      => array( FALSE, FALSE ),
					'rsync'     => array( FALSE, FALSE ),
					'tar'       => array( FALSE, FALSE ),
					'mysqldump' => array( FALSE, FALSE )
					);

				//setup environment array
				$env = array();
				
				//if command exists, set param1 to true
				foreach ( $commands as $cmd => $val ) {
					$result = apply_filters( 'cmd_exists', $cmd );
					if ( $result ) {
						$commands[$cmd][0] = TRUE;
					}
				}		

				//begin table
				echo '<table>';

				//given existing commands
				foreach ( $commands as $cmd => $val ) {
					echo '<tr><td>';
					if ( $commands[$cmd][0] ) {
						echo strtoupper( $cmd ) . '</td><td><span style="color:green">OK</span></td></tr>';
						//check to make sure we have permissions to run
						$t = shell_exec( $cmd . ' 2>&1' );
						if ( ! empty( $t ) ) {
							$commands[$cmd][1] = TRUE;
						}
					} else { //command not found
						echo strtoupper( $cmd ) . '</td><td><span style="color:red">N/A</span></td></tr>';
					}
				}

		  		//get memory limit
		  		$php_memory_limit = ini_get( 'memory_limit' );
		  		$php_memory_limit = ( int ) $php_memory_limit;
		  		$env['php_memory_limit'] = floatval( $php_memory_limit ); //append to array
		  		echo '<tr><td>PHP memory limit</td><td>' . $php_memory_limit . 'M</td></tr>';

				//get memory usage
				echo '<tr><td>PHP memory usage</td><td>';
				$mem_usage = memory_get_usage( true ); 
				if ( $mem_usage < 1024 ) {
					echo $mem_usage . "b</td></tr>";
				} elseif ( $mem_usage < 1048576 ) {
		  			$usage = round( $mem_usage/1024, 0 );
		  			echo $usage . "K</td></tr>"; 
		  		} else {
						$usage = round( $mem_usage/1048576, 0 );
						$env['php_memory_usage'] = $usage; //append to array
		  			echo $usage . "M</td></tr>";
		  		}

		  		//calculate free memory
		  		$php_memory_free = $php_memory_limit-$usage;
		  		$env['php_memory_free'] = $php_memory_free; //append to array
		  		echo '<tr><td>PHP memory free</td><td>' . round( $php_memory_free, 0 ) . 'M</td></tr>';

		  		//get free space
		  		$free_space = disk_free_space('/');
		  		$df = $free_space/1048576;
		  		$env['disk_free'] = (int) $df;
		  		echo '<tr><td>Free space</td><td>' . round( $df, 0 ) . 'M</td></tr>';

		  		//get webserver
		  		$server = $_SERVER['SERVER_SOFTWARE'];
		  		$env['webserver'] = $server;
		  		echo '<tr><td>Webserver</td><td>' . $server . '</td></tr>';

		  		//get webserver user
		  		$user = shell_exec('whoami');
		  		$env['webserver_user'] = $user;
		  		echo '<tr><td>Webserver user</td><td>' . $user . '</td></tr>';

		  		//end table
				echo '</table>';

				//export arrays and save to db
				$commands_export = var_export($commands, true);
				$env_export      = var_export($env, true);
		  		update_option( 'wpmotion_commands', $commands_export );
		  		update_option( 'wpmotion_environment', $env_export );

		  		if ($php_memory_free > 10 && $df > 256)
		  		{
		  			echo '<p>Everything looks good!</p>';
		  			echo '<form action="" method="POST">';
		  			echo '<input type="submit" name="environment_check_complete" value="Proceed to next step »" class="button-primary">';
		  			echo '</form>';
		  		}
		  		*/

//		  	}
/*
			if ( $wpm_state == '5' ) {

				if ( ! $_POST['check_dns'] )
				{
					echo '<p>Here is where we check to make sure that the TTL of your A record has been lowered to an acceptible value.</p>';
					echo '<form action="" method="POST">';
		  			echo '<input type="submit" name="check_dns" value="Check DNS TTL »" class="button-primary">';
		  			echo '</form>';
				}
				else
				{
					echo "<p>TTL for A record @ $url is $ttl, which is too high.</p>";				
					echo '<form action="" method="POST">';
		  			echo '<input type="submit" name="check_dns" value="Check DNS TTL again »" class="button-primary">';
		  			echo '</form>';
				}
			}

			if ( $wpm_state == '6' )
			{
				echo "<p>Now we're going to export your static WordPress files and database into an archived file.</p>";
				echo '<form action="" method="POST">';
	  			echo '<input type="submit" name="export_files" value="Export site »" class="button-primary">';
	  			echo '</form>';
			}

			if ( $wpm_state == '7' )
			{
				echo '<p>Click the Upload button to initiate the file transfer of the export archive to the migration server.</p>';
				echo '<form action="" method="POST">';
	  			echo '<input type="submit" name="upload_files" value="Upload export »" class="button-primary">';
	  			echo '</form>';
			}
*/
//			if ( $wpm_state == '8' )
//			{
//				do_action( 'wpm_do_migration' );
//				echo '<p>WP Motion will now migrate your WordPress site to '.get_option( "wpmotion_selected_host" ).'. Hit the button to proceed.</p>';
//				echo '<form action="" method="POST">';
//	  			echo '<input type="submit" name="do_migration" value="Migrate »" class="button-primary">';
//	  			echo '</form>';

		//		echo "<script>location.reload();</script>";
//			}

//			if ( $wpm_state == '9' ) {
//				$test = $wpmotion->get_access_token();
//			}

			if ( $wpm_state == '9' )
			{
				echo 'Migration to ' . get_option('wpmotion_selected_host' ) . ' is underway.<br /><br />';
				?>
				<style>
				.ui-progressbar {
					position: relative;
				}
				.progress-label, .progress-substatus {
					position: absolute;
					left: 50%;
					top: 4px;
					font-weight: bold;
					text-shadow: 1px 1px 0 #fff;
 				}
				</style>
				<script>
				(function poll() {
				    setTimeout(function () {
				    	var key = '<?php echo get_option( 'wpmotion_license_key' ); ?>';
				        jQuery.ajax({
				            type: 'GET',
				            dataType: 'json',
				            url: 'https://wpmotion.internetmoving.co/main/migration_status?license_key='+key,
				            success: function (data) {
				            		var test            = data.pct.trim();
				            		var progressbar     = jQuery( "#migrationstatus" ),
				            		    progressbar2    = jQuery( "#migration-substatus" ),
				            		    migrationdetail = jQuery( "#migrationdetail" ),
      							    progresslabel   = jQuery( ".progress-label" ),
      							    progresslabel2  = jQuery( ".progress-substatus" );

      							    migrationdetail.html( "<p>" + data.stat + "</p>" );

								progressbar.progressbar({
								 	value: parseInt( data.pct ),
								 	change: function() {
       									progresslabel.text( data.pct + "%" );
     								},
      								complete: function() {
        									progresslabel.text( "Complete!" );
        									document.getElementById( "proceed" ).style.display = "";
        									var complete = "<?php update_option( 'wpmotion_maintenance_mode', '1' ); ?>";
     								}
								});

								if ( typeof(data.substatus) != "undefined" ) {
									document.getElementById( "migration-substatus" ).style.display = "";
									progressbar2.progressbar({
									 	value: parseInt( data.substatus ),
									 	change: function() {
	       									progresslabel2.text( data.substatus + "%" );
	     								},
	      								complete: function() {
	        									progresslabel2.text( "Done." );
	     								}
									});																		
								} else {
									document.getElementById( "migration-substatus" ).style.display = "none";
								}

				            },
				            complete: poll
				        });
				    }, 10000);
				})();
				</script>
				<?php
				echo '<div id="migrationstatus"><div class="progress-label"></div></div>';
				echo '<p><div id="migration-substatus"><div class="progress-substatus"></div></div></p>';
				echo '<div id="migrationdetail"></div>';

				//echo '<br /><div id="proceed" style="display:none;"><form action="" method="POST">';
	  			//echo '<input type="submit" name="verify_site" value="Verify new site »" class="button-primary">';
	  			//echo '</form></div>';	
			}

			if ( $wpm_state == '10' )
			{
				echo "<p>Currently this site and the one @ WP Engine are $pct% similar.</p>";
				if ($pct < 50)
					echo "<p>Something didn't work properly.</p>";
				if ($pct > 95)
					echo "<p>You are free to switch DNS.</p>";
			}

		?>

	</div> <!-- end .wrap -->

	<?php

} //end function wp_admin

function wpm_activate() {

	global $wpm_plugin_version;
	global $wpm_state;

	$wpm_state  = 0; //initialize state 0
	add_option( 'wpmotion_plugin_version', $wpm_plugin_version );
	add_option( 'wpmotion_state', $wpm_state );

}
add_action( 'wpm_activate', 'wpm_activate' );