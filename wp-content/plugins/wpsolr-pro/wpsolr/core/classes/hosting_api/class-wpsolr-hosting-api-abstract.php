<?php
/**
 * Hosting API
 */

namespace wpsolr\core\classes\hosting_api;

use wpsolr\core\classes\engines\solarium\admin\WPSOLR_Solr_Admin_Api_Core;

abstract class WPSOLR_Hosting_Api_Abstract {

	const HOSTING_API_ID = 'Hosting API to be defined';

	const DATA_HOST_BY_REGION_ID = 'DATA_HOST_BY_REGION_ID';
	const DATA_PATH = 'DATA_PATH';
	const DATA_PORT = 'DATA_PORT';
	const DATA_SCHEME = 'DATA_SCHEME';
	const DATA_REGION_LABEL_BY_REGION_ID = 'DATA_REGION_LABEL_BY_REGION_ID';

	/**
	 * @return WPSOLR_Hosting_Api_Abstract[]
	 */
	static function get_hosting_apis() {
		return [
			new WPSOLR_Hosting_Api_None(),
			new WPSOLR_Hosting_Api_Opensolr(),
		];
	}

	/**
	 * @param string $hosting_api_id
	 *
	 * @return WPSOLR_Hosting_Api_Abstract
	 * @throws \Exception
	 */
	static function get_hosting_api_by_id( $hosting_api_id ) {
		$hosting_apis = self::get_hosting_apis();

		foreach ( $hosting_apis as $hosting_api ) {
			if ( $hosting_api_id === $hosting_api->get_id() ) {
				return $hosting_api;
			}
		}

		// Not found
		throw new \Exception( 'Hosting %s is undefined.', $hosting_api_id );
	}

	/**
	 * @return string
	 */
	public function get_id() {
		return static::HOSTING_API_ID;
	}

	/**
	 * @param string $hosting_api_id
	 *
	 * @return bool
	 * @throws \Exception
	 */
	static function get_is_show_email_by_id( $hosting_api_id ) {
		$hosting_api = self::get_hosting_api_by_id( $hosting_api_id );

		return $hosting_api->get_is_show_email( $hosting_api_id );

	}

	/**
	 *
	 * @param string $hosting_api_id
	 * @param array $config
	 * @param \Solarium\Client $search_engine_client
	 *
	 * @return WPSOLR_Solr_Admin_Api_Core
	 * @throws \Exception
	 */
	public static function new_solr_admin_api_by_id( $hosting_api_id, $config, $search_engine_client ) {

		$hosting_api = self::get_hosting_api_by_id( $hosting_api_id );

		return $hosting_api->new_solr_admin_api( $config, $search_engine_client );
	}

	/**
	 * @param string $host
	 *
	 * @return string
	 */
	public function get_host( $host ) {
		return $host;
	}


	/**
	 * @param string $label
	 * @param string $id
	 * @param string $default
	 *
	 * @return string
	 */
	public function get_data_by_id( $label, $id, $default ) {

		return $default;
	}

	/**
	 * @return string
	 */
	abstract public function get_label();

	/**
	 * @return string
	 */
	abstract public function get_url();

	/**
	 * @return array
	 */
	abstract public function get_credentials();

	/**
	 * @return array
	 */
	abstract public function get_search_engines();

	/**
	 * @param string $hosting_api_id
	 *
	 * @return bool
	 */
	abstract public function get_is_show_email( $hosting_api_id );

	/**
	 *
	 * @param array $config
	 * @param \Solarium\Client $search_engine_client
	 *
	 * @return WPSOLR_Solr_Admin_Api_Core
	 */
	protected function new_solr_admin_api( $config, $search_engine_client ) {

		return new WPSOLR_Solr_Admin_Api_Core( $config, $search_engine_client );
	}
}