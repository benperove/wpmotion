<?php

/**
 * adapted from code-freeze by Kevin Davis
 */
class MaintenanceMode {

	public function __construct() {


	}

	// -----------------------------------------------------------------------------

	/**
	 * insert text onto login page
	 *
	 * @return  string Text to insert onto login page
	 */
	public function mm_custom_login_message() {

		$message = '<p style="padding:10px;border: 2px solid red; margin-bottom: 10px;"><span style="color:red;font-weight:bold;">' . __( 'Maintenance Mode Notice', 'codefreeze' ) . ':</span><br/>' . __( 'This site is currently being migrated to a new location. Changes made here will not be reflected in the migrated site. To avoid lost work, please do not make any changes to the site until this message is removed.', 'codefreeze' ) . '</p>';
		return $message;

	}

	// -----------------------------------------------------------------------------

	/**
	 * show notice on site pages when site disabled
	 *
	 * @return  void
	 */
	public function mm_effective_notice() {

		echo '<div class="error"><p><strong>' . __( 'Maintenance Mode Enabled:', 'maintenancemode' ) . '</strong> ' . __( 'All changes will be discarded during site migration.', 'maintenancemode' ) . '</p></div>';

	}

	// -----------------------------------------------------------------------------

	/**
	 * register javascript, disable quickpress widget, remove add/edit menu items
	 *
	 * @return  void
	 */
	public function mm_enter() {

		$this->mm_load_admin_head();
		add_filter( 'login_message' , array( $this, 'mm_custom_login_message' ) );
		add_action( 'admin_notices', array( $this, 'mm_effective_notice' ) );
		add_action( 'plugins_loaded', array( $this, 'mm_close_comments' ) );

	}

	// -----------------------------------------------------------------------------

	/**
	 * close comments and trackbacks while activated
	 *
	 * @return  void
	 */
	public function mm_close_comments() {

		add_filter( 'the_posts', 'mm_set_comment_status_closed' );
		add_filter( 'comments_open', 'mm_close_the_comments', 10, 2 );
		add_filter( 'pings_open', 'mm_close_the_comments', 10, 2 );

		/**
		 * close comments and trackbacks while activated
		 *
		 * @return  array array of posts with comments closed
		 */
		function mm_set_comment_status_closed ( $posts ) {

			if ( ! empty( $posts ) && is_singular() ) {
				$posts[0]->comment_status = 'closed';
				$posts[0]->post_status    = 'closed';
			}
			return $posts;

		}

		/**
		 * close comments and trackbacks while activated
		 *
		 * @return  $open
		 */
		function mm_close_the_comments ( $open, $post_id ) {
			//if not open, than back
			if ( ! $open )
				return $open;
				$post = get_post( $post_id );
			if ( $post -> post_type ) // all post types
				return FALSE;
			return $open;

		}

	}

	// -----------------------------------------------------------------------------

	/**
	 * remove media upload button(s)
	 *
	 * @return  void
	 */
	public function mm_remove_media_buttons() {

		remove_action( 'media_buttons', 'media_buttons' );

	}

	// -----------------------------------------------------------------------------

	/**
	 * set visual editor as "read only"
	 *
	 * @return  array Array of arguments to send to editor
	 */
	public function mm_visedit_readonly() {
		// suppress php warning in core when editor is read only
		error_reporting(0);
		return $args['readonly'] = 1;

	}

	// -----------------------------------------------------------------------------

	/**
	 * remove invalid action links
	 *
	 * @return  array Modified array of action links
	 */
	public function mm_remove_row_actions( $actions ) {

		unset( $actions['trash'] );
		unset( $actions['delete'] );

		// no normal filter action for this (install plugin row)
		foreach ( $actions as $k => $v ) {
			if ( strpos( $v, 'class="install-now' ) ) {
				unset( $actions[$k] );
			}
		}
		
		return $actions;

	}

	// -----------------------------------------------------------------------------	

	/**
	 * remove add/edit menu items
	 *
	 * @return  void
	 */
	public function mm_modify_menu() {

		global $submenu;
		unset( $submenu['edit.php?post_type=page'][10] ); //page > add new
		remove_submenu_page( 'edit.php', 'post-new.php' );
		remove_submenu_page( 'sites.php', 'site-new.php' );
		remove_submenu_page( 'upload.php', 'media-new.php' );
		remove_submenu_page( 'link-manager.php', 'link-add.php' );
		remove_submenu_page( 'themes.php', 'theme-editor.php' );
		remove_submenu_page( 'themes.php', 'customize.php' );
		remove_submenu_page( 'themes.php', 'theme-install.php' );
		remove_submenu_page( 'plugins.php', 'plugin-editor.php' );
		remove_submenu_page( 'plugins.php', 'plugin-install.php' );
		remove_submenu_page( 'users.php', 'user-new.php' );
		remove_submenu_page( 'tools.php', 'import.php' );
		remove_submenu_page( 'update-core.php', 'upgrade.php' );

	}

	// -----------------------------------------------------------------------------

	/**
	 * remove activation/deactivation/edit links for all plugins but this one
	 *
	 * @return array modified array of action links for plugins
	 */
	public function mm_plugin_action_links( $links, $file ) {

		$this_plugin = plugin_basename( __FILE__ );

		unset( $links['edit'] );

		if ( $file !== $this_plugin ) {
			return array(); // prevents PHP warning from any plugins that have modified the action links
		}
		return $links;

	}

	// -----------------------------------------------------------------------------

	/**
	 * remove topic replies and new topics from bbpress
	 *
	 * @note props to thezedt
	 * @return void
	 */
	public function mm_close_bbp_comments() {

		return false;

	}

	// -----------------------------------------------------------------------------

	/**
	 * exit maintenance mode
	 *
	 * @return void
	 */
	public function mm_exit() {

		remove_filter( 'login_message' , 'mm_custom_login_message' );
		remove_action( 'admin_notices', 'mm_effective_notice' );
		remove_filter( 'tiny_mce_before_init', 'mm_visedit_readonly' );
		$this->mm_open_comments();
		wp_deregister_script( 'mm-js' );

	}

	// -----------------------------------------------------------------------------

	/**
	 * reopen comments
	 *
	 * @return void
	 */
	public function mm_open_comments() {

		remove_filter( 'the_posts', 'mm_set_comment_status_closed' );
		remove_filter( 'comments_open', 'mm_close_the_comments' );
		remove_filter( 'pings_open', 'mm_close_the_comments' );

	}

	// -----------------------------------------------------------------------------

	/**
	 * reenable visual editor
	 *
	 * @return void
	 */
	public function mm_visedit_readonly_disabled() {

		//suppress php warning in core when editor is read only
		error_reporting(0);
		return $args['readonly'] = 0;

	}


}
