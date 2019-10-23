<?php

namespace wpsolr\core\classes\admin\ui\ajax;

use wpsolr\core\classes\engines\configuration\builder\WPSOLR_Configuration_Builder_Factory;


/**
 * Retrieve content of a configuration (tokenize and filters)
 *
 * Class WPSOLR_Admin_UI_Ajax_Index_Configuration_Default_builder
 * @package wpsolr\core\classes\admin\ui\ajax
 */
class WPSOLR_Admin_UI_Ajax_Index_Configuration_Default_builder extends WPSOLR_Admin_UI_Ajax_Search {

	const PARAMETER_CONFIGURATION_ID = 'configuration_id';
	const PARAMETER_INDEX_UUID = 'index_uuid';
	const PARAMETER_OPTION_NAME = 'option_name';
	const PARAMETER_BUILDER_ID = 'builder_id';

	/**
	 * @inheritDoc
	 */
	public static function extract_parameters() {

		$parameters = array(
			self::PARAMETER_OPTION_NAME      => empty( $_GET[ self::PARAMETER_OPTION_NAME ] ) ? '' : $_GET[ self::PARAMETER_OPTION_NAME ],
			self::PARAMETER_INDEX_UUID       => empty( $_GET[ self::PARAMETER_INDEX_UUID ] ) ? '' : $_GET[ self::PARAMETER_INDEX_UUID ],
			self::PARAMETER_CONFIGURATION_ID => empty( $_GET[ self::PARAMETER_CONFIGURATION_ID ] ) ? '' : $_GET[ self::PARAMETER_CONFIGURATION_ID ],
			self::PARAMETER_BUILDER_ID       => empty( $_GET[ self::PARAMETER_BUILDER_ID ] ) ? '' : $_GET[ self::PARAMETER_BUILDER_ID ],
		);

		return $parameters;
	}

	/**
	 * @inheritDoc
	 */
	public static function execute_parameters( $parameters ) {


		$results = [];

		$results[] = [
			'id'    => 'noneed',
			'label' => WPSOLR_Configuration_Builder_Factory::build_form( $parameters[ self::PARAMETER_OPTION_NAME ],
				null, $parameters[ self::PARAMETER_INDEX_UUID ],
				$parameters[ self::PARAMETER_CONFIGURATION_ID ],
				$parameters[ self::PARAMETER_BUILDER_ID ]
			)
		];

		return $results;
	}

}