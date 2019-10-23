<?php

namespace wpsolr\pro\extensions\theme_listify;

use wpsolr\core\classes\engines\solarium\WPSOLR_SearchSolariumClient;
use wpsolr\core\classes\extensions\localization\OptionLocalization;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\ui\WPSOLR_Data_Sort;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WpSolrSchema;
use wpsolr\pro\extensions\geolocation\WPSOLR_Option_GeoLocation;

/**
 * Class WPSOLR_Theme_Listify
 *
 * Manage Listify theme
 */
class WPSOLR_Theme_Listify extends WPSOLR_Job_Manager_Abstract {

	const WPSOLR_GEOLOCATION = 'wpsolr_listify_geolocation';

	/**
	 * @inheritdoc
	 */
	function get_option_is_replace_search() {
		return WPSOLR_Service_Container::getOption()->get_theme_listify_is_replace_search();
	}

	/**
	 * @inheritdoc
	 */
	function get_option_is_caching() {
		return WPSOLR_Service_Container::getOption()->get_theme_listify_is_caching();
	}

	/**
	 * @inheritdoc
	 */
	function get_option_is_replace_sort_options() {
		return WPSOLR_Service_Container::getOption()->get_theme_listify_is_replace_sort_options();
	}

	/**
	 * @inheritdoc
	 */
	protected function get_default_custom_fields() {

		$results = $this->get_geolocation_custom_fields();

		// Add ratings
		$results[ self::RATING ] = [
			self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
			WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_FLOAT,
			WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD,
		];

		return $results;
	}

	/**
	 * @inheritdoc
	 */

	function init_search_events() {

		add_filter( 'listify_filters_sort_by', [ $this, 'listify_filters_sort_by' ], 9, 1 );

		// Intercept filter before geolocation SQL in method Listify_WP_Job_Manager_Map::geolocation_search()
		add_filter( 'listify_feature_listings_in_location_search', [
			$this,
			'listify_feature_listings_in_location_search'
		], 9 );

	}

	/**
	 * @inherit
	 */
	function add_custom_filters( $search_engine_client ) {

		// Add geo distance filter
		// Test search_location to fix a Listify bug (does not clear lat and long when the location is cleared)
		if ( ! empty( $this->search_listings_args['search_location'] ) && is_object( $this->filter_by_distance ) ) {
			$search_engine_client->search_engine_client_add_filter_geolocation_distance(
				static::WPSOLR_GEOLOCATION . WPSOLR_Option_GeoLocation::_SOLR_DYNAMIC_TYPE_LATITUDE_LONGITUDE,
				$this->filter_by_distance->wpsolr_get_latitude(),
				$this->filter_by_distance->wpsolr_get_longitude(),
				$this->filter_by_distance->wpsolr_get_radius()
			);
		}

	}

	/**
	 * Replace Listify sort options with WPSOLR's
	 *
	 * Array
	 * (
	 *  [date-desc] => Le plus r&eacute;cent en premier
	 *  [date-asc] => Le plus ancien en premier
	 *  [random] => Random
	 *  [rating-desc] => Highest Rating
	 *  [rating-asc] => Lowest Rating
	 * )
	 *
	 *
	 * @param array $options
	 *
	 * @return mixed
	 */
	public function listify_filters_sort_by( $options ) {

		if ( ! $this->get_option_is_replace_sort_options() ) {
			// Use standard sort items.
			return $options;
		}

		$results = [];

		// Retrieve WPSOLR sort fields, with their translations.
		$sorts = WPSOLR_Data_Sort::get_data(
			WPSOLR_Service_Container::getOption()->get_sortby_items_as_array(),
			WPSOLR_Service_Container::getOption()->get_sortby_items_labels(),
			WPSOLR_Service_Container::get_query()->get_wpsolr_sort(),
			OptionLocalization::get_options()
		);

		if ( ! empty( $sorts ) && ! empty( $sorts['items'] ) ) {
			foreach ( $sorts['items'] as $sort_item ) {
				$results[ $sort_item['id'] ] = $sort_item['name'];
			}
		}

		return $results;
	}

	/**
	 * @inherit
	 */
	protected function add_sort( $wpsolr_query ) {

		/**
		 * Add sort
		 */

		$listify_search_sort = '';
		if ( isset( $_REQUEST['form_data'] ) ) {
			wp_parse_str( wp_unslash( $_REQUEST['form_data'] ), $params );

			if ( isset( $params['search_sort'] ) ) {
				$listify_search_sort = $params['search_sort'];
			}
		}

		if ( ! $this->get_option_is_replace_sort_options() ) {

			$wpsolr_sort = '';
			if ( ! empty( $listify_search_sort ) ) {
				// Convert Listify sort to wpsolr sort

				switch ( $listify_search_sort ) {
					case 'date-asc':
						$wpsolr_sort = WPSOLR_SearchSolariumClient::SORT_CODE_BY_DATE_ASC;
						break;

					case 'date-desc':
						$wpsolr_sort = WPSOLR_SearchSolariumClient::SORT_CODE_BY_DATE_DESC;
						break;

					case 'random':
						$wpsolr_sort = WPSOLR_SearchSolariumClient::SORT_CODE_BY_DATE_ASC;
						break;

					case 'rating-desc':
						$wpsolr_sort = sprintf( '%s%s_%s', self::RATING, WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, WPSOLR_SearchSolariumClient::SORT_DESC );
						break;

					case 'rating-asc':
						$wpsolr_sort = sprintf( '%s%s_%s', self::RATING, WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, WPSOLR_SearchSolariumClient::SORT_ASC );
						break;

					default:
						// Relevancy first
						break;

				}
			}

		} else {

			// Plain wpsolr sort
			$wpsolr_sort = $listify_search_sort;
		}

		if ( ! empty( $wpsolr_sort ) ) {
			$wpsolr_query->set_wpsolr_sort( $wpsolr_sort );
		}

	}

}