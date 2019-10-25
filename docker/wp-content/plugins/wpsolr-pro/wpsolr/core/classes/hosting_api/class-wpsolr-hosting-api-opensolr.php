<?php
/**
 * Hosting API for https://opensolr.com
 */

namespace wpsolr\core\classes\hosting_api;

use wpsolr\core\classes\engines\solarium\admin\WPSOLR_Solr_Admin_Api_Opensolr;
use wpsolr\core\classes\engines\WPSOLR_AbstractEngineClient;

class WPSOLR_Hosting_Api_Opensolr extends WPSOLR_Hosting_Api_Abstract {

	const HOSTING_API_ID = 'opensolr';


	/**
	 * @param string $label
	 * @param string $id
	 * @param array $parameters
	 * @param mixed $default
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function get_data_by_id( $label, $id, $default, $parameters = [] ) {

		switch ( $label ) {
			case self::DATA_HOST_BY_REGION_ID:

				$result = '';
				break;

			case self::DATA_PORT:

				$result = '443';
				break;

			case self::DATA_PATH:

				$result = sprintf( '/solr/%s', $id );
				break;

			case self::DATA_SCHEME:

				$result = 'https';
				break;

			case self::DATA_REGION_LABEL_BY_REGION_ID:

				$hosting_admin_api = new WPSOLR_Solr_Admin_Api_Opensolr( [
					'extra_parameters' => [
						'index_email'   => $parameters['email'],
						'index_api_key' => $parameters['api_key']
					]
				], null );

				$regions = $hosting_admin_api->get_environments();

				if ( ! ( $key = array_search( $id, array_column( $regions, 'id' ) ) ) ) {
					throw new \Exception( sprintf( 'Unknown region %s', $id ) );
				}

				$result = $regions[ $key ]['label'];
				break;

			default:
				throw new \Exception( sprintf( 'Unknown label %s', $label ) );
				break;
		}

		return $result;
	}

	public function get_label() {
		return 'Opensolr';
	}

	public function get_url() {
		return 'https://opensolr.com';
	}

	public function get_credentials() {
		return [
			[ 'id' => 'email', 'label' => 'E-mail', 'type' => 'edit' ],
			[ 'id' => 'api_key', 'label' => 'API key', 'type' => 'password' ],
		];
	}

	public function get_search_engines() {
		return [ WPSOLR_AbstractEngineClient::ENGINE_SOLR ];
	}

	public function get_is_show_email( $hosting_api_id ) {
		return true;
	}

	protected function new_solr_admin_api( $extra_parameters, $search_engine_client ) {

		return new WPSOLR_Solr_Admin_Api_Opensolr( $extra_parameters, $search_engine_client );
	}

	public function get_host( $host ) {
		return '443';
	}
}