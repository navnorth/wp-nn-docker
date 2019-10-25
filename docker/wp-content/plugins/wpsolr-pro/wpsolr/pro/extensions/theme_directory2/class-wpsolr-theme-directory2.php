<?php

namespace wpsolr\pro\extensions\theme_directory2;

use wpsolr\core\classes\engines\WPSOLR_AbstractSearchClient;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\ui\WPSOLR_Query;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\core\classes\WpSolrSchema;
use wpsolr\pro\extensions\geolocation\WPSOLR_Option_GeoLocation;

/**
 * Class WPSOLR_Theme_Directory2
 *
 * Manage Directory2 theme
 */
class WPSOLR_Theme_Directory2 extends WpSolrExtensions {

	const POST_TYPE_AI_ITEM = 'ait-item';

	const FIELD_ARRAY_AIT_ITEM_ITEM_DATA = '_ait-item_item-data';

	/**
	 * WPSOLR_Theme_Directory2 constructor.
	 */

	const FIELD_WPSOLR_GEOLOCATION = 'wpsolr_directory2_geolocation';
	const FIELD_SUBTITLE = 'subtitle';
	const FIELD_MAP_ADDRESS = 'address';
	const FIELD_MAP_LATITUDE = 'latitude';
	const FIELD_MAP_LONGITUDE = 'longitude';
	const FIELD_RATING_MAX = 'rating_max';
	const FIELD_RATING_MEAN = 'rating_mean';
	const FIELD_RATING_MEAN_ROUNDED = 'rating_mean_rounded';
	const FIELD_RATING_COUNT = 'rating_count';
	const FIELD_AIT_LATITUDE = 'ait-latitude';
	const FIELD_AIT_LONGITUDE = 'ait-longitude';

	const TAXONOMY_CATEGORIES = 'ait-items';
	const TAXONOMY_LOCATIONS = 'ait-locations';

	/**
	 * Url parameters
	 */
	const URL_PARAMETER_CATEGORY = 'category';
	const URL_PARAMETER_LOCATION = 'location';


	public function __construct() {

		$this->init_default_events();

		if ( is_admin() ) {
			// Activate geolocation type on indexing fields
			add_filter( WPSOLR_Events::WPSOLR_FILTER_SOLR_FIELD_TYPES, [
				WPSOLR_Option_GeoLocation::class,
				'wpsolr_filter_solr_field_types',
			], 10, 1 );

		} else {

			if ( $this->get_option_is_replace_search() ) {

				add_action( WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY, [
					$this,
					'wpsolr_action_query',
				], 10, 1 );
			}

		}

		add_filter( WPSOLR_Events::WPSOLR_FILTER_POST_CUSTOM_FIELDS, [
			$this,
			'filter_custom_fields',
		], 10, 2 );

	}

	//add_filter( 'ait_alter_search_query',

	/**
	 * @inheritdoc
	 */
	function get_option_is_replace_search() {
		return WPSOLR_Service_Container::getOption()->get_theme_directory2_is_replace_search();
	}

	/**
	 * Unserialize and split some array fields.
	 *
	 * @param array $custom_fields
	 * @param int $post_id
	 *
	 * @return mixed
	 */
	public
	function filter_custom_fields(
		$custom_fields, $post_id
	) {

		if ( ! isset( $custom_fields ) ) {
			$custom_fields = [];
		}

		// Fields to split. Others can be added later.
		$array_fields = [
			self::FIELD_ARRAY_AIT_ITEM_ITEM_DATA => [
				''    => [ self::FIELD_SUBTITLE ],
				// ['subtitle']
				'map' => [ self::FIELD_MAP_ADDRESS, self::FIELD_MAP_LATITUDE, self::FIELD_MAP_LONGITUDE ],
				// ['map]['address']
			]
		];

		foreach ( $array_fields as $array_name => $array_defs ) {

			if ( ! empty( $custom_fields[ $array_name ] ) && is_array( $custom_fields[ $array_name ] ) ) {

				$array_subfields = $custom_fields[ $array_name ][0];

				$decode_value =
					is_serialized( $array_subfields )
						? unserialize( $array_subfields )
						: $array_subfields;

				if ( ! empty( $decode_value ) && is_array( $decode_value ) ) {

					foreach ( $array_defs as $sub_field => $field_serialized_names ) {

						foreach ( ( empty( $field_serialized_names ) ? [] : $field_serialized_names ) as $field_serialized_name ) {

							if (
								( empty( $sub_field ) && ! empty( $decode_value[ $field_serialized_name ] ) ) ||
								( ! empty( $sub_field ) && ! empty( $decode_value[ $sub_field ] ) && ! empty( $decode_value[ $sub_field ][ $field_serialized_name ] ) )
							) {
								$long_name = $this->get_serialized_field_name( self::FIELD_ARRAY_AIT_ITEM_ITEM_DATA, $field_serialized_name );

								// New field split from the array field
								$custom_fields[ $long_name ] = empty( $sub_field )
									? $decode_value[ $field_serialized_name ]
									: $decode_value[ $sub_field ][ $field_serialized_name ];
							}
						}

					}
				}
			}

		}


		// Geolocation is concatenated from lat,long
		$latitude  = $this->get_serialized_field_name( self::FIELD_ARRAY_AIT_ITEM_ITEM_DATA, self::FIELD_MAP_LATITUDE );
		$longitude = $this->get_serialized_field_name( self::FIELD_ARRAY_AIT_ITEM_ITEM_DATA, self::FIELD_MAP_LONGITUDE );
		if ( ! empty( $custom_fields[ $latitude ] ) && ! empty( $custom_fields[ $longitude ] ) ) {
			$custom_fields[ self::FIELD_WPSOLR_GEOLOCATION ] = sprintf( '%s,%s', $custom_fields[ $latitude ], $custom_fields[ $longitude ] );
		}


		return $custom_fields;
	}

	/**
	 *
	 * Add a filter on product post type.
	 *
	 * @param array $parameters
	 *
	 */
	public function wpsolr_action_query( $parameters ) {

		/* @var WPSOLR_Query $wpsolr_query */
		$wpsolr_query = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_WPSOLR_QUERY ];
		/* @var WPSOLR_AbstractSearchClient $search_engine_client */
		$search_engine_client = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_CLIENT ];

		if ( ! isset( $_SERVER['QUERY_STRING'] ) ) {
			// No url parameters
			return;
		}

		parse_str( $_SERVER['QUERY_STRING'], $url_parameters );
		if ( empty( $url_parameters ) ) {
			return;
		}

		$settings = aitOptions()->getOptionsByType( 'theme' );
		$settings = (object) $settings['items'];

		/**
		 * Count parameter
		 */
		$count = ! empty( $url_parameters['count'] ) ? $url_parameters['count'] : $settings->sortingDefaultCount;
		$wpsolr_query->wpsolr_set_nb_results_by_page( $count );
		$search_engine_client->search_engine_client_set_start( $wpsolr_query->get_start() );
		$search_engine_client->search_engine_client_set_rows( $wpsolr_query->get_nb_results_by_page() );

		/**
		 * Sort parameters
		 */
		$order_by = ! empty( $url_parameters['orderby'] ) ? $url_parameters['orderby'] : $settings->sortingDefaultOrderBy;
		$order    = ! empty( $url_parameters['order'] ) ? $url_parameters['order'] : $settings->sortingDefaultOrder;

		// Convert Directory2 sort to wpsolr sort
		switch ( sprintf( '%s-%s', $order_by, $order ) ) {
			case 'date-ASC':
				$search_engine_client->search_engine_client_add_sort( WpSolrSchema::_FIELD_NAME_DATE, WPSOLR_AbstractSearchClient::SORT_ASC, false );
				break;

			case 'date-DESC':
				$search_engine_client->search_engine_client_add_sort( WpSolrSchema::_FIELD_NAME_DATE, WPSOLR_AbstractSearchClient::SORT_DESC, false );
				break;

			case 'title-ASC':
				$search_engine_client->search_engine_client_add_sort( WpSolrSchema::_FIELD_NAME_TITLE_S, WPSOLR_AbstractSearchClient::SORT_ASC, false );
				break;

			case 'title-DESC':
				$search_engine_client->search_engine_client_add_sort( WpSolrSchema::_FIELD_NAME_TITLE_S, WPSOLR_AbstractSearchClient::SORT_DESC, false );
				break;

		}

		/**
		 * Add taxonomies filters (location and items)
		 */
		$taxonomies = [
			self::URL_PARAMETER_CATEGORY => self::TAXONOMY_CATEGORIES,
			self::URL_PARAMETER_LOCATION => self::TAXONOMY_LOCATIONS,
		];
		foreach ( $taxonomies as $url_parameter_name => $taxonomy_name ) {

			if ( ! empty( $url_parameters[ $url_parameter_name ] ) ) {

				$field_name = sprintf( WpSolrSchema::_FIELD_NAME_NON_FLAT_HIERARCHY, $taxonomy_name . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING );

				$terms = get_terms( [
					'taxonomy' => $taxonomy_name,
					'include'  => $url_parameters[ $url_parameter_name ],
					'fields'   => 'names'
				] );

				$search_engine_client->search_engine_client_add_filter_in_terms(
					sprintf( 'WPSOLR_Theme_Directory2 taxonomy %s', $taxonomy_name ),
					$field_name,
					$terms
				);
			}
		}

		/**
		 * Add the radius filter. Only active with the advanced search filter https://www.ait-themes.club/wordpress-plugins/advanced-search/
		 */
		if ( ! empty( $url_parameters['lat'] ) && ! empty( $url_parameters['lon'] ) and ! empty( $url_parameters['rad'] ) ) {

			$radius_units = ! empty( $url_parameters['runits'] ) ? $url_parameters['runits'] : 'km';
			$radius_value = ! empty( $url_parameters['rad'] ) ? $url_parameters['rad'] : 100;
			$radius_value = $radius_units == 'mi' ? $radius_value * 1.609344 : $radius_value;

			// Add the radius filter
			$search_engine_client->search_engine_client_add_filter_geolocation_distance(
				static::FIELD_WPSOLR_GEOLOCATION . WPSOLR_Option_GeoLocation::_SOLR_DYNAMIC_TYPE_LATITUDE_LONGITUDE,
				$url_parameters['lat'],
				$url_parameters['lon'],
				$radius_value );
		}

	}

	/**
	 * @inheritdoc
	 */
	protected function get_default_post_types() {
		return [ self::POST_TYPE_AI_ITEM ];
	}

	/**
	 * @inheritdoc
	 */
	protected function get_default_taxonomies() {
		return [ self::TAXONOMY_CATEGORIES, self::TAXONOMY_LOCATIONS ];
	}

	/**
	 * @inheritdoc
	 */
	protected function get_default_custom_fields() {

		return [
			static::FIELD_WPSOLR_GEOLOCATION                                                                  => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_AI_ITEM ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WPSOLR_Option_GeoLocation::_SOLR_DYNAMIC_TYPE_LATITUDE_LONGITUDE,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_THROW_ERROR
			],
			self::FIELD_RATING_MAX                                                                            => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_AI_ITEM ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_FLOAT,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::FIELD_RATING_MAX                                                                            => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_AI_ITEM ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_FLOAT,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::FIELD_RATING_MEAN                                                                           => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_AI_ITEM ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_FLOAT,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::FIELD_RATING_MEAN_ROUNDED                                                                   => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_AI_ITEM ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_INTEGER,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			self::FIELD_RATING_COUNT                                                                          => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_AI_ITEM ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_INTEGER,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			$this->get_serialized_field_name( self::FIELD_ARRAY_AIT_ITEM_ITEM_DATA, self::FIELD_SUBTITLE )    => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_AI_ITEM ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
			$this->get_serialized_field_name( self::FIELD_ARRAY_AIT_ITEM_ITEM_DATA, self::FIELD_MAP_ADDRESS ) => [
				self::_FIELD_POST_TYPES                                                   => [ self::POST_TYPE_AI_ITEM ],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
		];
	}

	/**
	 * Generate names for serialized fields from an aggregate field (array)
	 *
	 * @param string $aggregate_field_name
	 * @param string $short_name
	 *
	 * @return string
	 */
	protected function get_serialized_field_name( $aggregate_field_name, $short_name ) {

		return sprintf( 'wpsolr_%s_%s', $aggregate_field_name, $short_name );
	}


}