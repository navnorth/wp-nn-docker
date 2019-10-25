<?php

namespace wpsolr\pro\extensions\scoring;

use wpsolr\core\classes\engines\WPSOLR_AbstractSearchClient;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WPSOLR_Option_Scoring
 * @package wpsolr\pro\extensions\scoring
 *
 * Manage Advanced scoring
 */
class WPSOLR_Option_Scoring extends WpSolrExtensions {

	const DECAY_FUNCTION_GAUSS = 'gauss';
	const DECAY_FUNCTION_EXP = 'exp';
	const DECAY_FUNCTION_LINEAR = 'linear';
	const DECAY_DATE_UNIT_DAY = 'unit_day';
	const DECAY_DATE_UNIT_KM = 'unit_km';
	const DECAY_DATE_UNIT_NONE = 'unit_none';

	// Decay functions definitions
	static $DECAY_FUNCTIONS = [
		self::DECAY_FUNCTION_GAUSS  => [ 'label' => 'Gauss' ],
		self::DECAY_FUNCTION_EXP    => [ 'label' => 'Exponential' ],
		self::DECAY_FUNCTION_LINEAR => [ 'label' => 'Linear' ],
	];

	/**
	 * Constructor
	 * Subscribe to actions/filters
	 **/
	function __construct() {

		add_action( WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY, [
			$this,
			'wpsolr_action_query',
		], 10, 1 );
	}

	/**
	 *
	 * Add a filter to remove empty coordinates from results.
	 *
	 * @param $parameters array
	 *
	 * @throws \Exception
	 */
	public function wpsolr_action_query( $parameters ) {

		// @var WPSOLR_Query $wpsolr_query
		$wpsolr_query = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_WPSOLR_QUERY ];


		if ( WPSOLR_Service_Container::getOption()->get_option_scoring_is_decay() ) {
			// Add decay functions to the search query
			/** @var WPSOLR_AbstractSearchClient $search_client_query */
			$search_client_query = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_CLIENT ];

			$formatted_decays = $this->format_decay_functions();
			$search_client_query->search_engine_client_add_decay_functions( $formatted_decays );
		}
	}


	/**
	 * Format decay field functions from the options
	 */
	private function format_decay_functions() {

		$results = [];

		foreach ( WPSOLR_Service_Container::getOption()->get_option_scoring_fields_decays() as $field_name ) {

			if ( WpSolrSchema::get_custom_field_is_date_type( $field_name ) ) {

				$unit   = self::DECAY_DATE_UNIT_DAY;
				$origin = WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_origin( $field_name, WPSOLR_Option::OPTION_SCORING_DECAY_ORIGIN_DATE_NOW );

			} elseif ( WpSolrSchema::get_custom_field_is_numeric_type( $field_name ) ) {

				$unit   = self::DECAY_DATE_UNIT_NONE;
				$origin = WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_origin( $field_name, WPSOLR_Option::OPTION_SCORING_DECAY_ORIGIN_ZERO );

			} else {

				throw new \Exception( sprintf( 'Field %s cannot have a decay scoring.', $field_name ) );
			}

			$results[] = [
				'function' => WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_function( $field_name, self::DECAY_FUNCTION_GAUSS ),
				'field'    => WpSolrSchema::replace_field_name_extension( $field_name ),
				'unit'     => $unit,
				'origin'   => $origin,
				'scale'    => WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_scale( $field_name ),
				'offset'   => WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_offset( $field_name ),
				'decay'    => WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_value( $field_name ),
			];
		}

		return $results;
	}

}
