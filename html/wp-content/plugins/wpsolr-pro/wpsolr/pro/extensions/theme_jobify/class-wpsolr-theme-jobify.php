<?php

namespace wpsolr\pro\extensions\theme_jobify;

use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\pro\extensions\theme_listify\WPSOLR_Job_Manager_Abstract;

/**
 * Class WPSOLR_Theme_Jobify
 *
 * Manage Jobify theme
 */
class WPSOLR_Theme_Jobify extends WPSOLR_Job_Manager_Abstract {

	/**
	 * @inheritdoc
	 */
	function get_option_is_replace_search() {
		return WPSOLR_Service_Container::getOption()->get_theme_jobify_is_replace_search();
	}

	/**
	 * @inheritdoc
	 */
	function get_option_is_caching() {
		return WPSOLR_Service_Container::getOption()->get_theme_jobify_is_caching();
	}

	/**
	 * @inheritdoc
	 */
	protected function get_default_custom_fields() {

		return $this->get_job_custom_fields();
	}

	/**
	 * Add a simple location to the search query
	 *
	 * @param array $extra_field_queries
	 */
	public function wpsolr_filter_query_add_extra_field_queries( $extra_field_queries ) {

		if ( ! empty( $this->search_listings_args['search_location'] ) && ! is_object( $this->filter_by_distance ) ) {
			$extra_field_queries[ self::JOB_LOCATION ] = $this->search_listings_args['search_location'];
		}

		return $extra_field_queries;
	}

	/**
	 * @inheritdoc
	 */
	function init_search_events() {

		add_filter( WPSOLR_Events::WPSOLR_FILTER_QUERY_ADD_EXTRA_FIELD_QUERIES, [
			$this,
			'wpsolr_filter_query_add_extra_field_queries',
		], 10, 1 );

	}

	/**
	 * @inherit
	 */
	function add_custom_filters( $search_engine_client ) {
		// Nothing. Job types are alreadymanaged by the taxonomies
	}

	/**
	 * @inheritdoc
	 */
	protected function add_sort( $wpsolr_query ) {
		// No sort.
	}
}