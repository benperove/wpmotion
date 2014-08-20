<?php
/*
Plugin Name: WP Motion
Plugin URI: https://wpmotion.co
Description: A plugin/service that automates WordPress migrations between hosting providers.
Author: Benjamin Perove
Version: 0.9.4
Author URI: http://benperove.com
*/

if ( ! defined( 'WPM_PLUGIN_SLUG' ) )
	define( 'WPM_PLUGIN_SLUG', basename( dirname( __FILE__ ) ) );

if ( ! defined( 'WPM_PLUGIN_PATH' ) )
	define( 'WPM_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

if ( ! defined( 'WPM_PLUGIN_URL' ) )
	define( 'WPM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! defined( 'WPM_PLUGIN_FILENAME' ) )
	define( 'WPM_PLUGIN_FILENAME', basename( __FILE__ ) );

if ( ! defined( 'WPM_PLUGIN_VERSION' ) )
	define( 'WPM_PLUGIN_VERSION', '0.9.4' );

if ( ! defined( 'WPM_REQUIRED_PHP_VERSION' ) )
	define( 'WPM_REQUIRED_PHP_VERSION', '5.2.4' );

define( 'WPM_REQUIRED_WP_VERSION', '3.2' );

//hook in activation/deactivation actions
register_activation_hook( WPM_PLUGIN_SLUG . '/' . WPM_PLUGIN_FILENAME, 'wpm_activate' );
register_deactivation_hook( WPM_PLUGIN_SLUG . '/' . WPM_PLUGIN_FILENAME, 'wpm_deactivate' );
register_uninstall_hook( WPM_PLUGIN_SLUG . '/uninstall.php', 'wpm_uninstall' );

//don't activate on anything less than php 5.2.4
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

//require functions, if they exist
foreach ( glob( WPM_PLUGIN_PATH . 'functions/*.php' ) as $filename ) {

	require_once( $filename );

}

//require classes, if they exist
foreach ( glob( WPM_PLUGIN_PATH . 'classes/*.php' ) as $filename ) {

	require_once( $filename );

}

global $wpm_plugin_version;
$wpm_plugin_version = WPM_PLUGIN_VERSION;

/**
 * add plugin to add main menu & create submenus
 *
 * @return void
 */
function wpm_admin_actions() {

	add_menu_page( 'WP Motion', 'WP Motion', 'manage_options', __FILE__, 'wpm_admin', 'dashicons-migrate' );
	add_submenu_page( __FILE__, 'WP Motion ', 'One Click Migration', 'manage_options', __FILE__, 'wpm_admin' );
	add_submenu_page( __FILE__, 'DNS', 'Business Class DNS', 'manage_options', 'dns', 'wpm_dns' );

}
add_action( 'admin_menu', 'wpm_admin_actions' );

/**
 * this action is called from state 4 to initiate the migration
 * the ajax callback name is migration_prep_callback()
 * upon successful completion of the callback, a success box is
 * displayed and the window is reloaded
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
		action: "migration_prep"
	};

	$.post(ajaxurl, data, function(response) {
		console.log(response);
		var obj = $.parseJSON(response);
		console.log(obj);
		var msg = "All systems are functional; proceeding with migration.";
		if (obj.result == true) {
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
				.html('Something went wrong!')
				.fadeIn(800)
				.delay(2000)
				.fadeOut(800);
		}
	});

});
</script>
<div id="loading"><img style="zoom: 0.5;" src="../wp-content/plugins/wp-motion/assets/images/ajax-loading.gif" /><p>Preparing for migration... please stand by.</p></div>
<div class="success-box alert">
	<div class="msg"></div>
</div>
<div class="error-box alert">
	<div class="msg"></div>
</div>
<?php
}
add_action( 'wpm_migration_prep', 'wpm_migration_prep_js' );

/**
 * callback for wpm_migration_prep() enables maintenance mode and 
 * initiates the migration filter wpm_do_migration()
 * 
 * @return string json which indicates { result:"true|false" }
 **/
function migration_prep_callback() {

	$value = false;
	$data  = apply_filters( 'wpm_do_migration', $value );
	echo json_encode($data);
	die(); //this is required to return a proper result

}
add_action( 'wp_ajax_migration_prep', 'migration_prep_callback' );

/**
 * kicks off the migration from migration_prep_callback() 
 * 
 * @return array result of migration request from server 
 **/
function wpm_do_migration() {

	$wpmotion = new wpmotion();
	$url      = get_bloginfo( 'url' );
	$url      = preg_replace( '(https?://)', '', $url );
	$data     = array( 'url' => $url );
	$result   = $wpmotion->wpm_json_request( 'do_migration_from_' . strtolower( get_option( 'wpmotion_sourcehost' ) ), $data );

	if ( $result['OK'] ) {
		update_option( 'wpmotion_maintenance_mode', '1' );
		update_option( 'wpmotion_state', '9' );
		$data = array( 'result' => TRUE );
		return $data;
	} elseif ( $result['ERROR'] ) {
		$err  = array_keys( $result, FALSE );
		$data = array( 'result' => FALSE, 'error' => $err );
		return $data;
	} else {
		var_dump( $result );
	}

}
add_filter( 'wpm_do_migration', 'wpm_do_migration' );

/**
 * hook into admin_init for plugin updates
 * 
 * @return void
 **/
function wpm_init() {

	$plugin_data = get_plugin_data( __FILE__ );

	//define plugin version
	define( 'WPM_VERSION', $plugin_data['Version'] );

	//show the update action
	if ( WPM_VERSION != get_option( 'wpmotion_plugin_version' ) )
		echo 'Please upgrade to the newest version of WP Motion.';

}
add_action( 'admin_init', 'wpm_init' );

/**
 * enter maintenance mode
 * 
 * @return void
 **/
function maintenance_mode_enter() {

	$mm = new MaintenanceMode;
	$mm->mm_enter();

}
add_action( 'mm_enter', 'maintenance_mode_enter' );

/**
 * exit maintenance mode
 * 
 * @return void
 **/
function maintenanace_mode_exit() {

	$mm = new MaintenanceMode;
	$mm->mm_exit();

}
add_action( 'mm_exit', 'maintenance_mode_exit' );

/**
 * determines if maintenance mode should be enabled or
 * disabled when the plugin loads
 * 
 * @return void
 **/
function maintenance_mode() {

	if ( get_option( 'wpmotion_maintenance_mode' ) )
	{
		$enabled = get_option( 'wpmotion_maintenance_mode' );
		if ($enabled == '1') {
			do_action( 'mm_enter' );
		} else {
			do_action( 'mm_exit' );
		}
	}
}
add_action( 'maintenance_mode', 'maintenance_mode' );
do_action( 'maintenance_mode' );

/**
 * enqueue admin scripts & styles
 * 
 * @return void
 **/
function wpm_admin_enqueue_scripts() {

	//load js
	//wp_enqueue_script( 'jquery-ui-core' ); //doesn't work; need to load it manually
	wp_register_script( 'jquery-ui', 'https://code.jquery.com/ui/1.11.0/jquery-ui.min.js' );
	wp_enqueue_script( 'jquery-ui' );
	wp_enqueue_script( 'jquery-effects-core' );
	wp_enqueue_script( 'jquery-effects-fade' );

	//load css
	wp_register_style( 'jquery_ui_css', plugins_url( WPM_PLUGIN_SLUG . '/assets/css/jquery-ui.min.css' ) );
	wp_enqueue_style( 'jquery_ui_css' );
	wp_register_style( 'jquery_ui_theme_css', plugins_url( WPM_PLUGIN_SLUG . '/assets/css/jquery-ui.theme.min.css' ) );
	wp_enqueue_style( 'jquery_ui_theme_css' );
	wp_register_style( 'wpm_css', plugins_url( WPM_PLUGIN_SLUG . '/assets/css/wpmotion.css' ) );
	wp_enqueue_style( 'wpm_css' );

	//load maintenance mode scripts
	if ( get_option( 'wpmotion_maintenance_mode' ) && get_option( 'wpmotion_maintenance_mode' ) == '1' ) {
		wp_enqueue_script( 'mm-js', plugins_url( WPM_PLUGIN_SLUG . '/assets/js/mm.js' ), array( 'jquery' ), FALSE, TRUE );
		wp_register_style( 'mm-css', plugins_url( WPM_PLUGIN_SLUG . '/assets/css/mm.css' ) );
		wp_enqueue_style( 'mm-css' );
	}

}
add_action( 'admin_enqueue_scripts', 'wpm_admin_enqueue_scripts' );

/**
 * placeholder for admin header
 * 
 * @return void
 **/
function wpm_admin_head() {

	//do stuff here

}
add_action( 'admin_head', 'wpm_admin_head' );

/**
 * main plugin functionality
 * 
 * @return void
 **/
function wpm_admin() {

	//setup globals
	global $wpdb;
	global $wpm_state;
	global $uid;
	global $ref;
	global $host_array;
	global $wpm_error;

	//setup vars for migration
	$wpm_state = get_option( 'wpmotion_state' );
	$uid       = get_current_user_id(); 
	$ref       = admin_url() . 'admin.php?page=' . WPM_PLUGIN_SLUG . '/' . WPM_PLUGIN_FILENAME;
	$domain    = str_replace( array( 'http://', 'https://' ), '', get_site_url() );
	$wpm_error = NULL;
	$wpmotion  = new wpmotion();

	//get license key from migration server & add to db
	//change state from 0 to 1
	if ( isset($_POST['get_started']) && $wpm_state == '0' ) {
		//make sure user has agreed to the terms
		if ( ! empty( $_POST['agree'] ) && $_POST['agree'] == 'true' ) {
			//gather required information
			$wpm_first_name = urlencode( get_user_meta( $uid, 'first_name', true ) );
			$wpm_last_name  = urlencode( get_user_meta( $uid, 'last_name', true ) );
			$admin_email    = urlencode( get_option( 'admin_email' ) );
			$domain         = urlencode( $domain );

			//setup the inital request
			//$wpmotion->wpm_json_request() requires a license key, so the first request is setup manually
			$callback = 'WPMotion' . uniqid();
			$data     = array( 'callback' => $callback, 'first_name' => $wpm_first_name, 'last_name' => $wpm_last_name, 'email' => $admin_email, 'url' => $domain );
			$json     = '?json=' . json_encode( $data );
			$url      = 'https://go.wpmotion.co/main/plugin_signup' . $json;

			//make request
			$result   = wp_remote_get( $url, array( 'user-agent' => wpm_user_agent(), ) );

			//get response	
			if ( $result['response']['code'] == 200 ) {
				$json = preg_replace("/^[" . $callback . "\[]+|[\]]$/x", "", $result['body']);
				if ( $json ) {
					$data = json_decode( $json, true );
				} else {
					$wpm_error = 'There was a problem interpreting the request.';
				}
			} else {
				$code      = $result['response']['code'];
				$message   = $result['response']['message'];
				$wpm_error = 'Error ' . $code . ': ' . $message . '<br />There was a problem communicating with the migration server.';
			}

			//process response
			if ( $data['OK'] ) {
				$hosts_export = var_export( $data['destination_hosts'], true );
				update_option( 'wpmotion_destination_hosts', $hosts_export );
				$wpm_license_key = $data['license_key'];
				add_option( 'wpmotion_license_key', $wpm_license_key );
				update_option( 'wpmotion_state', '1' );
				$wpm_state = get_option( 'wpmotion_state' );
			} else {
				$wpm_error = $data['reason'];
			}
		} else {
			$wpm_error = "To get started, please agree to the terms by checking the box.";
		} //end if - agree

	} //end if - get_started

	//desired host is selected
	//change state from 1 to 2
	if ( isset( $_POST['select_host'] ) && $wpm_state == '1' ) {
		//setup vars & make request
		$host   = $_POST['host'];
		$data   = array( 'selected_host' =>  $host );
		$result = $wpmotion->wpm_json_request( 'selected_host', $data );

		//process response
		if ( $result['OK'] ) {
			update_option( 'wpmotion_selected_host', $host );
			update_option( 'wpmotion_state', '2' );
			$wpm_state = get_option( 'wpmotion_state' );
		}  elseif ( $result['ERROR'] ) {
			$wpm_error = $result['reason'];
		} else {
			var_dump( $result );
		}
	//user is trying to change destination host
	} elseif ( ! empty( $_GET['change_selected_host'] ) && $_GET['change_selected_host'] == 'TRUE') {
		update_option( 'wpmotion_selected_host', NULL );
		update_option( 'wpmotion_state', '1' );
		$wpm_state = get_option( 'wpmotion_state' );
	}

	//create hosting account button pressed
	if ( isset( $_POST['wpm_host_signup'] ) && $wpm_state == '2' ) {
		//setup the request
		$license_key = get_option( 'wpmotion_license_key' );	
		$data        = array( 'license_key' => get_option( 'wpmotion_license_key' ) );

		//make request	
		$result      = $wpmotion->wpm_json_request( 'host_signup', $data );

		//process result
		if ( $result['OK'] ) {
			update_option( 'wpmotion_state', '3' );
			update_option( 'wpmotion_create_account_pressed', '1' );
			echo '<script>';
			echo 'window.location = "' . $result['signup_url'] . '";';
			echo '</script>';
		} else {
			$wpm_error = $result['reason'];
		}
	}

	//existing hosting account button pressed
	if ( isset( $_POST['wpm_existing_host'] ) && $wpm_state == '2' ) {
		update_option( 'wpmotion_state', '3' );
		$wpm_state = get_option( 'wpmotion_state' );
	}

	//validate credentials
	if ( isset( $_POST['submit_credentials'] ) && $wpm_state == '3' ) {
		//setup request vars
		$username = $_POST['username'];
		$password = $_POST['password'];
		$data     = array( 'username' => $username, 'password' => $password, 'host' => 'WP Engine', 'ref' => $ref, 'domain' => $domain );

		//make request
		$result   = $wpmotion->wpm_json_request( 'check_credentials', $data );

		//process response
		if ( $result['OK'] && isset($result['installs']) ) {
			if ( isset( $result['sourcehost'] ) ) {
				$sourcehost = $result['sourcehost'];
				update_option( 'wpmotion_sourcehost', $sourcehost );
			}
			$installs = $result['installs'];
			$userid   = $result['userid']; 
			update_option( 'wpmotion_installs', var_export( $installs, TRUE ) );
			update_option( 'wpmotion_state', '3.1' );
			$wpm_state = get_option( 'wpmotion_state' );
		} elseif ( $result['OK'] ) {
			update_option( 'wpmotion_state', '4' );
			$wpm_state = get_option( 'wpmotion_state' );
 		} elseif ( $result['ERROR'] ) {
			$wpm_error = $result['reason'];
		} else {
			var_dump( $result );
		}
	}

	if ( isset( $_POST['selected_install'] ) && $wpm_state == '3.1' ) {
		$selected_install = $_POST['install'];

		if ( $selected_install == 'add_new_install' ) {
			$url = "https://my.wpengine.com/installs#add_install_form";
			header( 'Location: ' . $url );
		} else {
			$data   = array( 'selected_install' => $selected_install );
			$result = $wpmotion->wpm_json_request( 'selected_install', $data );
		}

		if ( $result['OK'] ) {
			if ( get_option( 'wpmotion_sourcehost' ) ) {
				//add additional step for source host credentials
				update_option( 'wpmotion_state', '3.2' );
				$wpm_state = get_option( 'wpmotion_state' );
			} else {
				update_option( 'wpmotion_state', '4' );
				$wpm_state = get_option( 'wpmotion_state' );
			}
		} elseif ( $result['ERROR'] ) {
			$wpm_error = $result['reason'];
		} else {
			var_dump( $result );
		}
	} elseif ( $wpm_state == '3.1' ) {
		$installs2 = get_option( 'wpmotion_installs' );
		eval( "\$installs = $installs2;" );
	}

	//get credentials for source host
	if ( isset( $_POST['submit_credentials'] ) && $wpm_state == '3.2' ) {
		//setup request vars
		$username   = $_POST['username'];
		$password   = $_POST['password'];
		$sourcehost = $_POST['sourcehost'];
		$doc_root   = get_home_path();
		$data       = array( 'username' => $username, 'password' => $password, 'host' => $sourcehost, 'ref' => $ref, 'domain' => $domain, 'doc_root' => $doc_root );

		//make request
		$result     = $wpmotion->wpm_json_request( 'check_credentials', $data );

		//process request
		if ( $result['OK'] ) {
			update_option( 'wpmotion_state', '4' );
			$wpm_state = get_option( 'wpmotion_state' );
		}  elseif ( $result['ERROR'] ) {
			$wpm_error = $result['reason'];
		} else {
			var_dump( $result );
		}
	}

/*	if ( isset($_POST['verify_site']) && $wpm_state == '10' ) {
		$url    = get_bloginfo( 'url' );
		$url    = preg_replace( '(https?://)', '', $url );
		$data   = array( 'url' => $url );
		$result = $wpmotion->json_rquest( 'verify_site', $data );

		if ( $result['OK'] ) {
			$pct = $result ['percent_similar'];
		} elseif ( $result['ERROR'] ) {
			$wpm_error = $result['reason'];
		} else {
			var_dump( $result );
		}

	} */
	?>
	<div class="wrap">
		<style>
			p {width:570px;}
		</style>
		<!-- always display the plugin header -->
		<h1 style="letter-spacing:6px;">One Click Migration<span style="font-variant: small-caps; font-size: 70%; color:#0074a2;"></span></h1>
		<h3>Version <?php echo WPM_VERSION ?> by <a href="https://wpmotion.co">WP Motion</a></h3>
		<?php echo ( get_option( 'wpmotion_license_key' ) ) ? '<strong>Registered to</strong>: <span style="color:green">' . get_option( 'admin_email' ) . '</span><br />' : NULL ?>
		<?php echo ( get_option( 'wpmotion_license_key' ) ) ? '<strong>License key</strong>: &nbsp; &nbsp; <span style="color:green">' . get_option( 'wpmotion_license_key' ) . '</span><br />' : NULL ?>
		<?php echo ( get_option( 'wpmotion_selected_host' ) ) ? '<strong>Selected host</strong>: <span style="color:green">' . get_option( 'wpmotion_selected_host' ) . '</span>' : NULL ?>
		<?php echo ( get_option( 'wpmotion_state' ) == '2' ) ? ' [<a href="' . $ref . '&change_selected_host=TRUE">change</a>]<br />' : '<br /><br />' ?>
		<?php
			//state 0 - welcome screen & disclaimer
			if ( $wpm_state == '0' ) {
				echo '<p style="line-height:175%; position:relative; top:-20px;">WP Motion is a WordPress plugin that makes it easy to move your WordPress site between 
					hosting providers. WP Motion does all the heavy lifting and checks to make sure that everything is in place. WP Motion automates all aspects of the
					migration, including DNS changes. In no time at all, you will have completed a migration of your WordPress site to the next host. To get started,
					agree to the terms by checking the box, and click the blue "Get Started" button.</p>';

				$textarea  = "To make single click migration possible, WP Motion requires specific pieces of information in order to uniquely identify your site, and to ";
				$textarea .= "conduct the migration using strict levels of security.\n\n";
				$textarea .= "Privacy\n\nThis plugin uses an external migration server to facilitate an automatic migration. As such, the following pieces of information ";
				$textarea .= "will be shared with WP Motion:\n\n\tWordPress site URL\n\tAdmin email address\n\tSource host credentials\n\tDestination host credentials\n\n";
				$textarea .= "Security\n\nWhen your migration is taking place, all password information is salted and hashed using modern cryptographic algorithms (before ";
				$textarea .= "it ever hits the database). The key which is used to decrypt hashed passwords exists on an external server. Thus, for the duration of the ";
				$textarea .= "migration, your information is in good hands.\n\nFollowing the migration, your information will be completely purged from our servers & ";
				$textarea .= "databases. For increased protection, you may wish to set a temporary password for your hosting accounts prior to the migration, then change ";
				$textarea .= "back to your normal password when the migration is complete.";

				echo '<form action="" method="POST">';
				echo "<textarea readonly style='width:555px; height:150px; resize:none;'>$textarea</textarea><br />";
				echo '<input type="checkbox" name="agree" value="true" style="margin:10px 7px 12px 0;" >I understand and agree to share this information with WP Motion<br>';

				$response = wp_remote_get( 'https://go.wpmotion.co/main/ssl_check' );
				if ($response['response']['code'] == '200') {
					echo '<p style="color:green"><img src="https://go.wpmotion.co/public/assets/img/secure-connection.png" class="ssl_connection" />All migration activities are secured by 256-bit SSL encryption</p>';
				}

				echo '<input type="hidden" id="ref" name="ref" value="' . admin_url() . 'admin.php?page=' . WPM_PLUGIN_SLUG . '/' . WPM_PLUGIN_FILENAME . '">';
				echo '<input type="hidden" id="admin_email" name="admin_email" value="' . get_option( 'admin_email' ) . '">';
				echo '<input type="submit" name="get_started" value="Get Started With Your Migration" class="button-primary" />';
				echo '</form>';
				echo '<br />';
				echo '<div id="wpmotion-result"></div>';
				echo '<div id="wpmotion-error">' . ( isset( $wpm_error ) ? $wpm_error . ' <br />For immediate assistance, please call support at 1-866-386-4592' : '' ) . '</div>';
			}

			//state 1 - host selection
			if ( $wpm_state == '1' /* && get_option( 'wpmotion_license_key' ) */ ) {
				$destination_hosts2 = get_option( 'wpmotion_destination_hosts' );
				eval( "\$destination_hosts = $destination_hosts2;" );
				?>
				<p>To which host will you be migrating your WordPress site?</p>
				<table>
					<form action="" method="POST">
						<tr><td>Desired Host</td><td>
							<select name="host" style="width:232px">
							<?php
								//iterate through hosts array
								foreach ( $destination_hosts as $host => $enabled ) {
									//if array value is enabled, light up the option
									if ( $destination_hosts[$host] == 'enabled' ) {
										echo '<option value="' . $host . '">' . $host . '</option>';
									} else {
										echo '<option value="' . $host . '" ' . $destination_hosts[$host] . '>' . $host . '</option>';
									}
								}
							?>
							</select></td></tr>
						<tr><td><br /><input type="submit" name="select_host" value="Next" class="button-primary" /></td></tr>
					</form>
				</table>
				<br />
				<div id="wpmotion-result"></div>
				<div id="wpmotion-error"><?php echo ( isset( $wpm_error ) ? $wpm_error : '' ) ?></div>
			<?php
			}

			//state 2 - hostingaccount creation
			if ( $wpm_state == '2' && ! get_option( 'wpmotion_create_account_pressed' ) ) {
			?>
				<p>Migrating your site to <?php echo get_option( 'wpmotion_selected_host' ); ?> is easy!<br />Click one of the following buttons.</p>
				<?php
				//create an account button
				echo '<form action="" method="POST">';
				echo '<input type="submit" name="wpm_host_signup" value="NEW ACCOUNT&#010;Pre-pay for 1 year of ' . get_option( 'wpmotion_selected_host' ) . '&#010;hosting and get 2 months free!" class="button-primary" style="line-height:17px; height:79px; width:250px; white-space:pre;">';
				echo '</form>';
				echo '<br />';

				//already have an account button
				echo '<form action="" method="POST">';
				echo '<input type="submit" name="wpm_existing_host" value="EXISTING ACCOUNT" class="button-primary" style="width:250px;">';
				echo '</form>';
				echo '<br />';
				echo '<div id="wpmotion-error">' . ( isset( $wpm_error ) ? $wpm_error : '' ) . '</div>';
				?>

			<?php
			} /* elseif ( $wpm_state == '2' && get_option( 'wpmotion_create_account_pressed' ) == '1' ) {
	    			//create an account button
	    			echo '<form action="" method="POST">';
	    			echo '<input type="submit" name="wpm_host_confirm" value="Confirm your ' . get_option( 'wpmotion_selected_host' ) . ' account" class="button-primary" style="width:250px;">';
	    			echo '</form>';
	    			echo '<br />';
	    			echo '<div id="wpmotion-error">' . ( isset( $wpm_error ) ? $wpm_error : '' ) . '</div>';
			} */

			//state 3 - validate credentials
			if ( $wpm_state == '3' ) {
			?>
				<table>
				<tr><td>Enter your <?php echo get_option( 'wpmotion_selected_host' ); ?> login credentials</td></tr>
				<tr><td>
					<form action="" method="POST">
						<input type="text" name="username" value="Username" onfocus="if (this.value=='Username') this.value='';" ></td></tr>
						<tr><td><input type="password" name="password" value="Password" onfocus="if (this.value=='Password') this.value='';"></td></tr>
						<input type="hidden" name="ref" value="<?php echo admin_url() . 'admin.php?page=' . WPM_PLUGIN_SLUG . '/' . WPM_PLUGIN_FILENAME; ?>">
						<tr><td><input type="submit" name="submit_credentials" value="Next" class="button-primary" />
					</form></td></tr>
				</table>
				<p>Note: your passwords are safe with us! Passwords are hashed using modern cryptography algorithms and can easily be purged at the end of the migration.</p>
				<div id="wpmotion-result"></div>
				<div id="wpmotion-error"><?php echo ( isset( $wpm_error ) ? $wpm_error : '' ) ?></div>

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
				<div id="wpmotion-error"><?php echo ( isset( $wpm_error ) ? $wpm_error : '' ) ?></div>
			<?php
			}

			//state 3.2 - validate credentials for source host
			if ( $wpm_state == '3.2' ) {
				$sourcehost = get_option( 'wpmotion_sourcehost' );
				if ( ! empty( $sourcehost ) && $sourcehost == 'Bluehost' ) {
				?>
					<table>
					<tr><td>Enter your <?php echo get_option( 'wpmotion_sourcehost' ); ?> login credentials</td></tr>
					<tr><td>
						<form action="" method="POST">
							<input type="text" name="username" value="Username" onfocus="if (this.value=='Username') this.value='';" ></td></tr>
							<tr><td><input type="password" name="password" value="Password" onfocus="if (this.value=='Password') this.value='';"></td></tr>
							<input type="hidden" name="ref" value="<?php echo admin_url() . 'admin.php?page=' . WPM_PLUGIN_SLUG . '/' . WPM_PLUGIN_FILENAME; ?>">
							<input type="hidden" name="sourcehost" value="<?php echo get_option( 'wpmotion_sourcehost' ); ?>">
							<tr><td><input type="submit" name="submit_credentials" value="Next" class="button-primary" />
						</form></td></tr>
					</table>
					<p>Note: your passwords are safe with us! Passwords are hashed and salted using modern crypto algorithms and can be purged at the end of the migration.</p>
					<div id="wpmotion-result"></div>
					<div id="wpmotion-error"><?php echo ( isset( $wpm_error ) ? $wpm_error : '' ) ?></div>
				<?php
				} elseif ( ! empty( $sourcehost ) && $sourcehost == 'Cloudflare' ) {
					echo "<p>We've detected that you're using CloudFlare DNS. We're actively building support for CloudFlare, but it's not ready yet. However, we can still move your site! A support ticket has been autogenerated and one of the magic elves at WP Motion will be in touch with you ASAP.</p>";
					echo "<p>If you would prefer to speak with a human right now, dial 1-866-386-4592 (24/7) and we'll get you squared away!</p>";
				} else {
					echo "<p>Oops... your present host isn't supported just yet, but we can still move your site! A support ticket has been autogenerated and one of the magic elves at WP Motion will be in touch with you ASAP.</p>";
					echo "<p>If you would prefer to speak with a human right now, dial 1-866-386-4592 (24/7) and we'll get you squared away!</p>";
				}
			}

			//state 4 - kick off the migration
			if ( $wpm_state == '4' ) {
				do_action( 'wpm_migration_prep' );
			}

			//state 9 - show migration progress
			if ( $wpm_state == '9' ) {
				echo 'Migration to ' . get_option( 'wpmotion_selected_host' ) . ' is underway.<br /><br />';
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
							url: 'https://go.wpmotion.co/main/migration_status?license_key=' + key,
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
											var complete = "<?php update_option( 'wpmotion_maintenance_mode', '0' ); ?>";
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

							}, //end success
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

/*			if ( $wpm_state == '10' ) {
				echo "<p>Currently this site and the one @ WP Engine are $pct% similar.</p>";
				if ($pct < 50)
					echo "<p>Something didn't work properly.</p>";
				if ($pct > 95)
					echo "<p>You are free to switch DNS.</p>";
			} */

	?>
	</div> <!-- end .wrap -->
	<?php

} //end wpm_admin

/**
 * dns function
 * 
 * @return void 
 **/
function wpm_dns() {

?>
	<style>
		p {width:570px;}
	</style>
	<!-- always display the plugin header -->
	<h1 style="letter-spacing:6px;">Business Class DNS<span style="font-variant: small-caps; font-size: 70%; color:#0074a2;"></span></h1>
	<h3>Version <?php echo WPM_VERSION ?> by <a href="https://wpmotion.co">WP Motion</a></h3>
	<?php echo ( get_option( 'wpmotion_license_key' ) ) ? '<strong>Registered to</strong>: <span style="color:green">' . get_option( 'admin_email' ) . '</span><br />' : NULL ?>
	<?php echo ( get_option( 'wpmotion_license_key' ) ) ? '<strong>License key</strong>: &nbsp; &nbsp; <span style="color:green">' . get_option( 'wpmotion_license_key' ) . '</span><br />' : NULL ?>
	<p>"Simply the best DNS solution available for WordPress. Period."</p><p>To find out how you can benefit from our Business Class DNS, <a href="https://wpmotion.co/dns">visit our site</a>.</p>
<?php

}
add_action( 'wpm_dns', 'wpm_dns' );

/**
 * plugin activation
 * 
 * @return void 
 **/
function wpm_activate() {

	global $wpm_plugin_version;
	global $wpm_state;

	$wpm_state = 0; //initialize state 0
	add_option( 'wpmotion_plugin_version', $wpm_plugin_version );
	add_option( 'wpmotion_state', $wpm_state );

}
add_action( 'wpm_activate', 'wpm_activate' );

/**
 * plugin deactivation
 * 
 * @return void 
 **/
function wpm_deactivate() {

	update_option( 'wpmotion_maintenance_mode', '0' );

}
add_action( 'wpm_deactivate', 'wpm_deactivate' );
