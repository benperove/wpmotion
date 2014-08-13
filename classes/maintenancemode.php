<?php

/**
 *
 * Adapted from code-freeze by Kevin Davis
 *
 */
class MaintenanceMode {

	public function __construct()
	{
//		add_filter( 'login_message' , array( $this, 'mm_custom_login_message' ) );
//		add_action( 'admin_notices', array( $this, 'mm_effective_notice' ) );
//		add_action( 'admin_init', array( $this, 'mm_enter' ) );
//		add_action( 'admin_init', array( $this, 'mm_exit' ) );
//		add_action( 'admin_print_scripts', array( $this, 'mm_load_admin_head' ) );
//		add_action( 'plugins_loaded', array( $this, 'mm_close_comments' ) );
//		add_action( 'admin_head', array( $this, 'mm_remove_media_buttons' ) );
//		add_filter( 'tiny_mce_before_init', array( $this, 'mm_visedit_readonly' ) );
//		add_filter( 'tiny_mce_before_init', array( $this, 'mm_visedit_readonly_disabled' ) );
//		add_filter( 'post_row_actions', array( $this, 'mm_remove_row_actions', 10, 1 ) );
//		add_filter( 'page_row_actions', array( $this, 'mm_remove_row_actions', 10, 1 ) );
//		add_filter( 'user_row_actions', array( $this, 'mm_remove_row_actions', 10, 1 ) );
//		add_filter( 'tag_row_actions', array( $this, 'mm_remove_row_actions', 10, 1 ) );
//		add_filter( 'media_row_actions', array( $this, 'mm_remove_row_actions', 10, 1 ) );
//		add_filter( 'plugin_install_action_links', array( $this, 'mm_remove_row_actions', 10, 1 ) );
//		add_filter( 'theme_install_action_links', array( $this, 'mm_remove_row_actions', 10, 1 ) );
//		add_filter( 'plugin_action_links', array( $this, 'mm_plugin_action_links', 10, 2 ) );
//		if ( class_exists( 'bbPress' ) ) 
//		{
//			add_filter( 'bbp_current_user_can_access_create_reply_form', mm_close_bbp_comments );
//			add_filter( 'bbp_current_user_can_access_create_topic_form', mm_close_bbp_comments );		
//		}
	}
		
	/**
	 * Insert text onto login page
	 *
	 * @return  string Text to insert onto login page
	 */
	public function mm_custom_login_message() 
	{
		$message = '<p style="padding:10px;border: 2px solid red; margin-bottom: 10px;"><span style="color:red;font-weight:bold;">'.__('Maintenance Mode Notice', 'codefreeze' ).':</span><br/>'.__('This site is currently being migrated to a new location. Changes made here will not be reflected in the migrated site. To avoid lost work, please do not make any changes to the site until this message is removed.', 'codefreeze' ).'</p>';
		return $message;
	}
			
	/**
	 * Show notice on site pages when site disabled
	 *
	 * @return  void
	 */
	public function mm_effective_notice() 
	{
		echo '<div class="error"><p><strong>'.__('Maintenance Mode Enabled:', 'maintenancemode').'</strong> '.__('All changes will be discarded during site migration.', 'maintenancemode' ).'</p></div>';
	}
		
	/**
	 * Register javascript, disable quickpress widget, remove add/edit menu items
	 *
	 * @return  void
	 */
	public function mm_enter() 
	{
		// register js
		//wp_register_script( 'mm-js', plugins_url('wp-motion/assets/js/mm.js', __FILE__) );
		$this->mm_load_admin_head();
		//add_action( 'wp_head', array( $this, 'mm_load_admin_head' ) );
	//	do_action ('wp_head');
		//$this->mm_load_admin_head();

		// make localizable
	//	load_plugin_textdomain( 'codefreeze', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		// remove QuickPress widget
//		remove_meta_box('dashboard_quick_press', 'dashboard', 'normal');
		
		// remove menu items - doesn't work for all of them in admin_menu
//		mm_modify_menu();

		add_filter( 'login_message' , array( $this, 'mm_custom_login_message' ) );
	//	apply_filters('login_message');

		add_action( 'admin_notices', array( $this, 'mm_effective_notice' ) );
	//	do_action('admin_notices');
		
		//$this->mm_close_comments();
//		wp_register_style( 'mm-css', plugins_url('../assets/css/mm.css', __FILE__) );
//		wp_enqueue_style( 'mm-css' );
		add_action( 'plugins_loaded', array( $this, 'mm_close_comments' ) );
	//	do_action('plugins_loaded');
		//do_action( 'mm_close_comments' );	
	//	add_action( 'admin_head', 'mm_remove_media_buttons' );
//		$this->mm_remove_media_buttons();
	//	add_filter( 'tiny_mce_before_init', 'mm_visedit_readonly' );
//		$this->mm_visedit_readonly();

	//	add_filter( 'post_row_actions', 'mm_remove_row_actions', 10, 1 );
	//	add_filter( 'page_row_actions', 'mm_remove_row_actions', 10, 1 );
	//	add_filter( 'user_row_actions', 'mm_remove_row_actions', 10, 1 );
	//	add_filter( 'tag_row_actions', 'mm_remove_row_actions', 10, 1 );
	//	add_filter( 'media_row_actions', 'mm_remove_row_actions', 10, 1 );
	//	add_filter( 'plugin_install_action_links', 'mm_remove_row_actions', 10, 1 );
	//	add_filter( 'theme_install_action_links', 'mm_remove_row_actions', 10, 1 );
	//	add_filter( 'plugin_action_links', 'mm_plugin_action_links', 10, 2 );

	}
	
	/**
	 * Load javascript on all admin pages
	 *
	 * @return  void
	 */
	public function mm_load_admin_head() 
	{
//		wp_enqueue_script( 'mm-js' );
//		wp_enqueue_script( 'mm-js', plugins_url('wp-motion/assets/js/mm.js'), array('jquery'), FALSE, TRUE );
	}

	/**
	 * Close comments and trackbacks while activated
	 *
	 * @return  void
	 */
	public function mm_close_comments() 
	{
		add_filter( 'the_posts', 'mm_set_comment_status_closed' );
		add_filter( 'comments_open', 'mm_close_the_comments', 10, 2 );
		add_filter( 'pings_open', 'mm_close_the_comments', 10, 2 );
		/**
		 * Close comments and trackbacks while activated
		 *
		 * @return  array Array of posts with comments closed
		 */

		function mm_set_comment_status_closed ( $posts ) 
		{
			if ( ! empty( $posts ) && is_singular() ) {
				$posts[0]->comment_status = 'closed';
				$posts[0]->post_status = 'closed';
			}
			return $posts;
		}
		
		/**
		 * Close comments and trackbacks while activated
		 *
		 * @return  $open
		 */
		function mm_close_the_comments ( $open, $post_id ) 
		{
			// if not open, than back
			if ( ! $open )
				return $open;
				$post = get_post( $post_id );
			if ( $post -> post_type ) // all post types
				return FALSE;
			return $open;
		}
	}

	/**
	 * Remove media upload button(s)
	 *
	 * @return  void
	 */
	public function mm_remove_media_buttons() 
	{
		remove_action( 'media_buttons', 'media_buttons' );
	}
	
	/**
	 * Set visual editor as "read only"
	 *
	 * @return  array Array of arguments to send to editor
	 */
	public function mm_visedit_readonly() 
	{
		// suppress php warning in core when editor is read only
		error_reporting(0);
		return $args['readonly'] = 1;
	}
	
	/**
	 * Remove invalid action links
	 *
	 * @return  array Modified array of action links
	 */
	public function mm_remove_row_actions($actions) 
	{
		unset( $actions['trash'] );
		unset( $actions['delete'] );
		
		// no normal filter action for this (install plugin row)
		foreach ($actions as $k => $v) {
			if (strpos($v, 'class="install-now') ) {
				unset ($actions[$k]);
			}
		}
		
		return $actions;
	}
	
	/**
	 * Remove add/edit menu items
	 *
	 * @return  void
	 */
	public function mm_modify_menu() 
	{
		global $submenu;
		unset($submenu['edit.php?post_type=page'][10]); // Page > Add New
		remove_submenu_page('edit.php', 'post-new.php');
		remove_submenu_page('sites.php', 'site-new.php');
		remove_submenu_page('upload.php', 'media-new.php');
		remove_submenu_page('link-manager.php', 'link-add.php');
		remove_submenu_page('themes.php', 'theme-editor.php');
		remove_submenu_page('themes.php', 'customize.php');
		remove_submenu_page('themes.php', 'theme-install.php');
		remove_submenu_page('plugins.php', 'plugin-editor.php');
		remove_submenu_page('plugins.php', 'plugin-install.php');
		remove_submenu_page('users.php', 'user-new.php');
		remove_submenu_page('tools.php', 'import.php');
		remove_submenu_page('update-core.php', 'upgrade.php');
	}
	
	/**
	 * Remove Activation/Deactivation/Edit links for all plugins but this one
	 *
	 * @return  array Modified array of action links for plugins
	 */
	public function mm_plugin_action_links($links, $file) 
	{
		$this_plugin = plugin_basename(__FILE__);
		
		unset($links['edit']);
		
		if ($file !== $this_plugin) {
			return array(); // prevents PHP warning from any plugins that have modified the action links
		}
		return $links;
	}
	
	/**
	 * Remove topic replies and new topics from bbPress
	 *
	 * @note	props to theZedt
	 * @return  void
	 */
	public function mm_close_bbp_comments() 
	{
		return false;
	}

	// -----------------------------------------------------------------------------

	public function mm_exit()
	{
		remove_filter( 'login_message' , 'mm_custom_login_message' );
		remove_action( 'admin_notices', 'mm_effective_notice' );
		remove_filter( 'tiny_mce_before_init', 'mm_visedit_readonly' );
		$this->mm_open_comments();
		wp_deregister_script( 'mm-js' );
	}

	public function mm_open_comments()
	{

		remove_filter( 'the_posts', 'mm_set_comment_status_closed' );
		remove_filter( 'comments_open', 'mm_close_the_comments' );
		remove_filter( 'pings_open', 'mm_close_the_comments' );
	}

	public function mm_visedit_readonly_disabled() 
	{
		// suppress php warning in core when editor is read only
		error_reporting(0);
		return $args['readonly'] = 0;
	}


}