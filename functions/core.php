<?php

	/**
	 * set the user-agent string
	 *
	 * @return string user-agent string
	 */
	function wpm_user_agent() {
		$user_agent = 'WP Motion/' . get_option( 'wpmotion_plugin_version' ) . '; https://wpmotion.co';
		return $user_agent;
	}

	// -----------------------------------------------------------------
