<?php
/**
 * Hosting API for none
 */

namespace wpsolr\core\classes\hosting_api;

class WPSOLR_Hosting_Api_None extends WPSOLR_Hosting_Api_Abstract {

	const HOSTING_API_ID = '';

	public function get_label() {
		return 'None';
	}

	public function get_url() {
		return '';
	}

	public function get_credentials() {
		return [];
	}

	public function get_search_engines() {
		return [];
	}

	public function get_is_show_email( $hosting_api_id ) {
		return false;
	}
}