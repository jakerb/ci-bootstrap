<?php

	function get_user_avatar($email) {
		return "https://www.gravatar.com/avatar/" . md5( strtolower( trim( $email ) ) ) . "?s=300";
	}

	function get_user_name($parts = false) {
		$ci =& get_instance();

		return $ci->ion_auth->get_user_full_name($parts);
	}

	function get_user_id() {
		$ci =& get_instance();

		$user_id = $ci->ion_auth->get_user_id();
		return $user_id ? $user_id : false;
	}

	function is_user_logged_in() {
		$ci = & get_instance();

		return $ci->ion_auth->logged_in();
	}

	function is_user_admin($user_id = false) {
		$ci = & get_instance();

		return $ci->ion_auth->is_admin($user_id);
	}


	function get_user_email() {
		$ci =& get_instance();

		return $ci->ion_auth->get_user_email();
	}