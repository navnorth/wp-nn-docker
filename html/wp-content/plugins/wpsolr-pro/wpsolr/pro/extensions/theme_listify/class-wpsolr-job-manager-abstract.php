<?php

namespace wpsolr\pro\extensions\theme_listify;

use WP_Query;
use wpsolr\core\classes\engines\WPSOLR_AbstractIndexClient;
use wpsolr\core\classes\engines\WPSOLR_AbstractSearchClient;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\ui\WPSOLR_Query;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\core\classes\WpSolrSchema;
use wpsolr\pro\extensions\geolocation\WPSOLR_Option_GeoLocation;

/**
 * Class WPSOLR_Job_Manager_Abstract
 *
 * Common code managing Job manager. Used by Astoundify themes.
 */
abstract class WPSOLR_Job_Manager_Abstract extends WpSolrExtensions {

	const WPSOLR_GEOLOCATION = 'to be defined in themes'; // Concatenate lat and long fields

	const GEOLOCATION_LAT = 'geolocation_lat';
	const GEOLOCATION_LONG = 'geolocation_long';
	const GEOLOCATED = 'geolocated';
	const GEOLOCATION_CITY = 'geolocation_city';
	const GEOLOCATION_COUNTRY_LONG = 'geolocation_country_long';
	const GEOLOCATION_COUNTRY_SHORT = 'geolocation_country_short';
	const GEOLOCATION_FORMATTED_ADDRESS = 'geolocation_formatted_address';
	const GEOLOCATION_STATE_LONG = 'geolocation_state_long';
	const GEOLOCATION_STATE_SHORT = 'geolocation_state_short';
	const GEOLOCATION_STREET = 'geolocation_street';
	const GEOLOCATION_STREET_NUMBER = 'geolocation_street_number';
	const GEOLOCATION_POSTCODE = 'geolocation_postcode';
	const RATING = 'rating';
	const JOB_LOCATION = '_job_location';
	const COMPANY_NAME = '_company_name';
	const COMPANY_WEBSITE = '_company_website';
	const COMPANY_TAGLINE = '_company_tagline';
	const COMPANY_DESCRIPTION = '_company_description';

	const POST_TYPE_JOB_LISTING = 'job_listing';

	/** @var bool $listify_feature_listings_in_location_search */
	protected $listify_feature_listings_in_location_search = false;

	/** @var bool $before_get_job_listings */
	protected $before_get_job_listings = false;

	/** @var bool $is_ajax */
	protected $is_ajax_processing = false;

	/** @var  WPSOLR_Theme_Listify_Filter_By_Distance $filter_by_distance */
	protected $filter_by_distance;

	/** @var bool bool $is_caching */
	protected $is_caching;

	/** @var  array $search_listings_args */
	protected $search_listings_args;

	/**
	 * @inheritDoc
	 */
	public function __construct() {

		$this->init_default_events();

		if ( is_admin() ) {

			// Activate geolocation type on indexing fields
			add_filter( WPSOLR_Events::WPSOLR_FILTER_SOLR_FIELD_TYPES, [
				WPSOLR_Option_GeoLocation::class,
				'wpsolr_filter_solr_field_types',
			], 10, 1 );

			add_filter( WPSOLR_Events::WPSOLR_FILTER_FIELDS, [
				$this,
				'wpsolr_filter_add_fields',
			], 10, 4 );

			add_filter( WPSOLR_Events::WPSOLR_FILTER_SOLARIUM_DOCUMENT_FOR_UPDATE, [
				$this,
				'wpsolr_filter_solarium_document_for_update',
			], 10, 5 );

		}

		if ( ! is_admin() && $this->get_option_is_replace_search() ) {

			$this->is_caching = $this->get_option_is_caching();

			// Specific search events for a theme.
			$this->init_search_events();

			// Intercept filter before SQL in function get_job_listings()
			add_action( 'before_get_job_listings', [ $this, 'before_get_job_listings' ], 9, 2 );

			// Search parameters
			add_filter( 'job_manager_get_listings_args', [ $this, 'job_manager_get_listings_args' ], 9, 1 );

			add_filter( 'get_job_listings_cache_results', [ $this, 'get_job_listings_cache_results' ], 9, 1 );

			// Intercept get_products() in YITH_WCAS::ajax_search_products()
			add_filter( 'posts_pre_query', [ $this, 'query' ], 10, 2 );


			add_action( WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY, [
				$this,
				'wpsolr_action_query',
			], 10, 1 );

		}

	}

	/**
	 * Initialize events during the search.
	 *
	 * @return mixed
	 */
	abstract function init_search_events();

	/**
	 * Get option to replace search
	 *
	 * @return bool
	 */
	abstract function get_option_is_replace_search();

	/**
	 * Get option to cache results
	 *
	 * @return bool
	 */
	abstract function get_option_is_caching();

	/**
	 * Get geolocation custom fields.
	 *
	 * @return array
	 */
	protected function get_geolocation_custom_fields() {

		return [
			static::WPSOLR_GEOLOCATION          => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WPSOLR_Option_GeoLocation::_SOLR_DYNAMIC_TYPE_LATITUDE_LONGITUDE,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_THROW_ERROR
			],
			self::GEOLOCATION_LAT               => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATION_LONG              => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATED                    => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATION_CITY              => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATION_COUNTRY_LONG      => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATION_COUNTRY_SHORT     => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATION_FORMATTED_ADDRESS => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATION_STATE_LONG        => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATION_STATE_SHORT       => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATION_STREET            => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATION_STREET_NUMBER     => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::GEOLOCATION_POSTCODE          => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
		];

	}

	/**
	 * Get job custom fields.
	 *
	 * @return array
	 */
	protected function get_job_custom_fields() {

		return [
			self::JOB_LOCATION        => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_TEXT,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::COMPANY_NAME        => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::COMPANY_WEBSITE     => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::COMPANY_TAGLINE     => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_TEXT,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::COMPANY_DESCRIPTION => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_JOB_LISTING ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_TEXT,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
		];

	}

	/**
	 * Create geolocation field content from Listify lat and long fields
	 *
	 * @param array $document_for_update
	 * @param $solr_indexing_options
	 * @param $post
	 * @param $attachment_body
	 * @param WPSOLR_AbstractIndexClient $search_engine_client
	 *
	 * @return array Document updated with fields
	 */
	function wpsolr_filter_solarium_document_for_update( array $document_for_update, $solr_indexing_options, $post, $attachment_body, WPSOLR_AbstractIndexClient $search_engine_client ) {

		if ( ! empty( $document_for_update[ self::GEOLOCATION_LAT . WpSolrSchema::_SOLR_DYNAMIC_TYPE_S ] )
		     && ! empty( $document_for_update[ self::GEOLOCATION_LONG . WpSolrSchema::_SOLR_DYNAMIC_TYPE_S ] )
		) {
			$document_for_update[ static::WPSOLR_GEOLOCATION . WPSOLR_Option_GeoLocation::_SOLR_DYNAMIC_TYPE_LATITUDE_LONGITUDE ] =
				sprintf( '%s,%s',
					$document_for_update[ self::GEOLOCATION_LAT . WpSolrSchema::_SOLR_DYNAMIC_TYPE_S ][0],
					$document_for_update[ self::GEOLOCATION_LONG . WpSolrSchema::_SOLR_DYNAMIC_TYPE_S ][0]
				);

		}

		return $document_for_update;
	}

	/**
	 * Add field post capabilities
	 *
	 * @param array $fields
	 * @param WPSOLR_Query $wpsolr_query
	 * @param WPSOLR_AbstractSearchClient $search_engine_client
	 *
	 * @return array
	 */
	public
	function wpsolr_filter_add_fields(
		$fields, WPSOLR_Query $wpsolr_query, WPSOLR_AbstractSearchClient $search_engine_client
	) {
		$fields[] = static::WPSOLR_GEOLOCATION . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING;

		return $fields;
	}

	/**
	 * @inheritdoc
	 */
	/*
	protected function get_default_sorts() {
		return [
			sprintf( '%s%s_%s', self::RATING, WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, WPSOLR_SearchSolariumClient::SORT_ASC ),
			sprintf( '%s%s_%s', self::RATING, WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, WPSOLR_SearchSolariumClient::SORT_DESC ),
		];
	}*/

	/**
	 * @inheritdoc
	 */
	protected function get_default_taxonomies() {
		return [ 'job_listing_region', 'job_listing_category', 'job_listing_type', 'job_listing_tag' ];
	}

	/**
	 * @inheritdoc
	 */
	protected function get_default_post_types() {
		return [ self::POST_TYPE_JOB_LISTING ];
	}

	/**
	 * @param array $query_args
	 * @param array $args
	 */
	public function listify_feature_listings_in_location_search( $is_true ) {

		$this->listify_feature_listings_in_location_search = true;

		$this->filter_by_distance = WPSOLR_Theme_Listify_Filter_By_Distance::wpsolr_replace_wpdb( $this );

		return $is_true;
	}

	/**
	 * @param array $query_args
	 * @param array $args
	 */
	public function before_get_job_listings( $query_args, $args ) {

		$this->before_get_job_listings = true;
	}

	/**
	 * @param $is_cache
	 *
	 * @return bool
	 */
	public function get_job_listings_cache_results( $is_cache ) {

		// Use Listify cache system, or not
		return $this->is_caching;
	}

	/**
	 * Store search parameters
	 *
	 * @param array args
	 *
	 * @return bool
	 */
	public function job_manager_get_listings_args( $args ) {

		// Store
		$this->search_listings_args = $args;

		// Do nothing
		return $args;
	}

	/**
	 * Stop WordPress performing a DB query for its main loop.
	 *
	 * As of WordPress 4.6, it is possible to bypass the main WP_Query entirely.
	 * This saves us one unnecessary database query! :)
	 *
	 * @since 2.7.0
	 *
	 * @param  null $retval Current return value for filter.
	 * @param  WP_Query $query Current WordPress query object.
	 *
	 * @return null|array
	 */
	function query( $retval, $query ) {

		if ( $this->is_ajax_processing ) {
			// Recurse call, stop now.
			return $retval;
		}

		if ( ! $this->before_get_job_listings ) {
			// This is not a Listify filter.
			return $retval;
		}

		// To prevent recursive infinite calls
		$this->is_ajax_processing = true;
		//remove_filter( 'posts_pre_query', [ $this, 'query' ] );


		$wpsolr_query = new WPSOLR_Query(); // Potential recurse here
		$wpsolr_query->wpsolr_set_wp_query( $query );
		$wpsolr_query->query['post_type'] = ! isset( $query->query['post_type'] ) ? self::POST_TYPE_JOB_LISTING : $query->query['post_type'];
		$wpsolr_query->query['s']         = ! isset( $this->search_listings_args['search_keywords'] ) ? '' : $this->search_listings_args['search_keywords'];
		$wpsolr_query->wpsolr_set_nb_results_by_page(
			! isset( $this->search_listings_args['posts_per_page'] ) ?
				WPSOLR_Service_Container::getOption()->get_search_max_nb_results_by_page()
				: $this->search_listings_args['posts_per_page']
		);
		$wpsolr_query->query_vars['paged'] = ( ! isset( $this->search_listings_args['offset'] ) ) ? '0' : 1 + (int) ( $this->search_listings_args['offset'] / intval( $this->search_listings_args['posts_per_page'] ) );
		$this->add_sort( $wpsolr_query );
		$products = $wpsolr_query->get_posts();

		// To prevent recursive infinite calls
		$this->is_ajax_processing = false;

		// Return $results, which prevents standard $wp_query to execute it's SQL.
		$post_ids = array_column( $products, 'ID' );

		$query->post_count    = $wpsolr_query->post_count;
		$query->found_posts   = $wpsolr_query->found_posts;
		$query->max_num_pages = $wpsolr_query->max_num_pages;

		return $post_ids;
	}


	/**
	 * @param WPSOLR_Query $wpsolr_query
	 */
	abstract protected function add_sort( $wpsolr_query );

	/**
	 *
	 * Add a filter on product post type.
	 *
	 * @param array $parameters
	 *
	 */
	public function wpsolr_action_query( $parameters ) {
		global $wpdb;

		/* @var WPSOLR_Query $wpsolr_query */
		$wpsolr_query = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_WPSOLR_QUERY ];
		/* @var WPSOLR_AbstractSearchClient $search_engine_client */
		$search_engine_client = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_CLIENT ];

		$wp_query = $wpsolr_query->wpsolr_get_wp_query();

		// post_type url parameter
		if ( ! empty( $wp_query->query['post_type'] ) ) {

			$search_engine_client->search_engine_client_add_filter_term( sprintf( 'WPSOLR_Theme_Listify type:%s', $wpsolr_query->query['post_type'] ), WpSolrSchema::_FIELD_NAME_TYPE, false, $wpsolr_query->query['post_type'] );
		}

		// taxonomy parameter
		if ( isset( $wp_query->query['tax_query'] ) && ! empty( $wp_query->query['tax_query'] ) ) {

			foreach ( $wp_query->query['tax_query'] as $tax_query ) {
				$field_name = $tax_query['taxonomy'] . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING;

				$terms = $tax_query['terms'];
				switch ( $tax_query['field'] ) {
					case 'term_id':
						$terms = get_terms( [
							'taxonomy' => $tax_query['taxonomy'],
							'include'  => $tax_query['terms'],
							'fields'   => 'names'
						] );
						break;

					case 'slug':
						$terms = get_terms( [
							'taxonomy' => $tax_query['taxonomy'],
							'slug'     => $tax_query['terms'],
							'fields'   => 'names'
						] );
						break;

				}

				$search_engine_client->search_engine_client_add_filter_in_terms(
					sprintf( 'WPSOLR_Theme_Listify taxonomy %s', $tax_query['taxonomy'] ),
					$field_name,
					$terms
				);
			}
		}

		// Add custom filters
		$this->add_custom_filters( $search_engine_client );

	}

	/**
	 * Add custom filters in children's code.
	 *
	 * @param WPSOLR_AbstractSearchClient $search_engine_client
	 *
	 */
	abstract function add_custom_filters( $search_engine_client );

}