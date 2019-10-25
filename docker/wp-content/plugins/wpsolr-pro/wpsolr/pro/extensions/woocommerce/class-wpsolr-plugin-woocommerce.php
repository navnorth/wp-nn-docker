<?php

namespace wpsolr\pro\extensions\woocommerce;

use wpsolr\core\classes\engines\WPSOLR_AbstractIndexClient;
use wpsolr\core\classes\engines\WPSOLR_AbstractSearchClient;
use wpsolr\core\classes\extensions\localization\OptionLocalization;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\ui\WPSOLR_Data_Sort;
use wpsolr\core\classes\ui\WPSOLR_Query;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WPSOLR_Plugin_WooCommerce
 *
 * Manage WooCommerce plugin
 */
class WPSOLR_Plugin_WooCommerce extends WpSolrExtensions {

	// Polylang options
	const _OPTIONS_NAME = WPSOLR_Option::OPTION_EXTENSION_WOOCOMMERCE;

	// Product types
	const PRODUCT_TYPE_VARIABLE = 'variable';

	// Product category field
	const FIELD_PRODUCT_CAT_STR = 'product_cat_str';

	// Post type of orders
	const POST_TYPE_SHOP_ORDER = 'shop_order';

	// Product type
	const POST_TYPE_PRODUCT = 'product';

	// Order fields
	const FIELD_POST_DATE_DT = 'post_date_dt';
	const FIELD_ORDER_TOTAL_F = '_order_total_f';

	// WooCommerce url parameter 'orderby'
	const WOOCOMERCE_URL_PARAMETER_SORT_BY = 'orderby';

	const ORDER_STATUS_ALL = 'all';

	// Url product category pattern.
	// Ex: /anything => /anything
	// Ex: /anything/next1/ => next1
	// Ex: /anything/next1/next2 => next2
	const URL_PATTERN_PRODUCT_CATEGORY = '/.*\/([^\/]+)$/';

	// Custom field visibility used to filter catalog or search results
	const CUSTOM_FIELD_VISIBILITY_STR = '_visibility_str';
	const CUSTOM_FIELD_VISIBILITY = '_visibility';
	const VISIBILITY_CATALOG_AND_SEARCH = 'visible';
	const VISIBILITY_CATALOG = 'catalog';
	const VISIBILITY_SEARCH = 'search';
	/**
	 * Remove the parameters from the url.
	 * anything?something => anything
	 */
	const URL_PATTERN_NO_PARAMETERS = '/([^?]*)?.*/';
	/**
	 * Remove the ending slash
	 */
	const URL_PATTERN_NO_ENDING_SLASH = '/(.*)\/$/';
	/**
	 * Remove the ending /page/x.
	 * anything/page/2 => anything
	 */
	const URL_PATTERN_NO_PAGES = '/(.*)\/page\/.*/';

	const URL_PARAMETER_CUSTOMER_USER = '_customer_user';

	/**
	 * Remove the top level of the category facet hierarchy on category pages.
	 * top_cat =>
	 * top_cat->current_cat =>
	 * top_cat->current_cat->sub_cat => sub_cat
	 */
	const REGEX_SUB_CATEGORIES = '/%s->(.*)/';

	/*
	 * @var bool $is_replace_category_search
	 */
	protected $is_replace_category_search;

	/*
	 * @var string $product_category_name
	 */
	protected $product_category_name;

	/*
	 * @var string $product_category_id
	 */
	protected $product_category_id;


	/**
	 * Constructor.
	 */
	function __construct() {


		add_filter( WPSOLR_Events::WPSOLR_FILTER_EXTRA_URL_PARAMETERS, [
			$this,
			'filter_extra_url_parameters',
		], 10, 1 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_IS_PARSE_QUERY, [
			$this,
			'filter_is_parse_query',
		], 10, 1 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_POST_STATUSES_TO_INDEX, [
			$this,
			'filter_post_statuses_to_index',
		], 10, 2 );

		add_action( WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY, [
			$this,
			'wpsolr_action_query',
		], 10, 1 );

		add_action( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, [
			$this,
			'wpsolr_filter_is_replace_by_wpsolr_query',
		], 10, 1 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_SOLARIUM_DOCUMENT_FOR_UPDATE, [
			$this,
			'add_fields_to_document_for_update',
		], 10, 5 );

		add_action( WPSOLR_Events::WPSOLR_ACTION_URL_PARAMETERS, [
			$this,
			'wpsolr_filter_url_parameters',
		], 10, 2 );

		// Customize the WooCOmmerce sort list-box
		add_filter( 'woocommerce_default_catalog_orderby_options', [
			$this,
			'custom_woocommerce_catalog_orderby',
		], 10 );
		add_filter( 'woocommerce_catalog_orderby', [
			$this,
			'custom_woocommerce_catalog_orderby',
		], 10 );

		add_action( WPSOLR_Events::WPSOLR_FILTER_FACETS_TO_DISPLAY, [
			$this,
			'wpsolr_filter_facets_to_display',
		], 10, 1 );

		add_action( WPSOLR_Events::WPSOLR_FILTER_FACETS_CONTENT_TO_DISPLAY, [
			$this,
			'wpsolr_filter_facets_content_to_display',
		], 10, 1 );


		add_filter( WPSOLR_Events::WPSOLR_FILTER_INDEX_CUSTOM_FIELDS, [
			$this,
			'get_index_custom_fields',
		], 10, 2 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_FACET_ITEMS, [
			$this,
			'get_facet_items',
		], 10, 3 );

	}

	/*
	 * Constructor
	 * Subscribe to actions
	 */

	/**
	 * Factory
	 *
	 * @return WPSOLR_Plugin_WooCommerce
	 */
	static function create() {

		return new self();
	}

	/**
	 * Return all woo commerce attributes names (slugs)
	 *
	 * @param string[] $custom_fields
	 * @param string $model_type
	 *
	 * @return array
	 */
	public function get_index_custom_fields( $custom_fields, $model_type ) {

		if ( self::POST_TYPE_PRODUCT === $model_type ) {

			if ( ! isset( $custom_fields ) ) {
				$custom_fields = [];
			}

			/* Attributes are now taxonomies. No need to add them to custom fields selection. */
			foreach ( $custom_fields as $key => $custom_field_name ) {

				// Remove custom fields which are attributes.
				if ( $this->get_container()->get_service_wpsolr()->starts_with( $custom_field_name, 'attribute_pa_' ) ) {
					unset( $custom_fields[ $key ] );
				}
			}

			// Remove visibility. It's added automatically.
			$key = array_search( self::CUSTOM_FIELD_VISIBILITY, $custom_fields, true );
			if ( false !== $key ) {
				unset( $custom_fields[ $key ] );
			}
		}

		return $custom_fields;
	}

	/**
	 * Return all woo commerce attribute values
	 * @return array
	 */
	public function get_facet_items( $attributes_values, $field_name, $facet_name ) {

		if ( ! isset( $field_name ) ) {
			return $attributes_values;
		}

		if ( in_array( sprintf( 'pa_%s', $field_name ), wc_get_attribute_taxonomy_names(), true ) ) {
			foreach ( get_terms( [ 'taxonomy' => sprintf( 'pa_%s', $field_name ), 'fields' => 'names' ] ) as $term ) {
				array_push( $attributes_values, $term );
			};
		}

		return $attributes_values;
	}

	/**
	 * Return all woo commerce attributes
	 * @return array
	 */
	public function get_attribute_taxonomies() {

		// Standard woo function
		return wc_get_attribute_taxonomies();
	}


	/**
	 * @return bool
	 */
	public function get_is_category_search() {

		if ( isset( $this->is_replace_category_search ) ) {
			// Use cached value.
			return $this->is_replace_category_search;
		}

		$this->is_replace_category_search = ( WPSOLR_Service_Container::getOption()->get_option_plugin_woocommerce_is_replace_product_category_search() && $this->is_product_category_url() );

		return $this->is_replace_category_search;
	}

	/**
	 * Extract product category from url.
	 * Must be done because is_product_category() does not work at this early stage.
	 *
	 * @return bool
	 */
	public function is_product_category_url() {

		if ( is_admin() || ! is_main_query() ) {
			return false;
		}

		$url = $_SERVER['REQUEST_URI'];

		if ( false !== strpos( $url, '.php' ) ) {
			// Ajax or cron
			return false;
		}

		// Remove url parameters
		$url = preg_replace( self::URL_PATTERN_NO_PARAMETERS, '$1', $url );
		// Remove url ending /
		$url = preg_replace( self::URL_PATTERN_NO_ENDING_SLASH, '$1', $url );
		// Remove ending /page/xxx
		$url = preg_replace( self::URL_PATTERN_NO_PAGES, '$1', $url );

		// Is it a shop ?
		if ( preg_match( '/shop\/?$/', $url, $output_array ) ) {
			return true;
		}

		// Extract product category
		$product_category_slug = preg_replace( self::URL_PATTERN_PRODUCT_CATEGORY, '$1', $url );

		if ( empty( $product_category_slug ) || ( $product_category_slug === $url ) ) {
			return false;
		}

		$product_category = get_term_by( 'slug', $product_category_slug, 'product_cat' );
		if ( $product_category ) {
			$this->product_category_name = $product_category->name;
			$this->product_category_id   = $product_category->term_id;

			return true;
		}

		return false;
	}

	/**
	 *
	 * Replace WP query by a WPSOLR query when the current WP Query is an order type query.
	 *
	 * @param bool $is_replace_by_wpsolr_query
	 *
	 * @return bool
	 */
	public function wpsolr_filter_is_replace_by_wpsolr_query( $is_replace_by_wpsolr_query ) {
		global $wp_query;

		// A category page
		if ( ( $this->get_is_category_search() )
		     && WPSOLR_Service_Container::getOption()->get_search_is_replace_default_wp_search()
		     && WPSOLR_Service_Container::getOption()->get_search_is_use_current_theme_search_template()
		) {
			return true;
		}

		if ( is_admin() && WPSOLR_Service_Container::getOption()->get_option_plugin_woocommerce_is_replace_admin_orders_search() ) {

			// ) && ! empty( $_REQUEST['s']
			if ( ( false !== strpos( $_SERVER['REQUEST_URI'], '/wp-admin/edit.php' ) ) && ! empty( $_REQUEST['post_type'] ) && ( self::POST_TYPE_SHOP_ORDER === $_REQUEST['post_type'] ) ) {
				// This is an order query, in the admin.
				return true;
			}
		}

		return $is_replace_by_wpsolr_query;
	}


	/**
	 * Add extra parameters to SEO redirect url.
	 *
	 * @param array $url_parameters
	 *
	 * @return array
	 */
	public function filter_extra_url_parameters( $url_parameters = [] ) {

		// Required by themes to show results as product results.
		$url_parameters['post_type'] = self::POST_TYPE_PRODUCT;

		return $url_parameters;
	}

	/**
	 * Do not execute parse_query() on wpsolr_query for orders. Too slow when a lot of orders metas are there.
	 *
	 * @param $true
	 *
	 * @return bool
	 */
	public function filter_is_parse_query( $true ) {

		if ( is_admin() && WPSOLR_Service_Container::getOption()->get_option_plugin_woocommerce_is_replace_admin_orders_search() ) {

			if ( ( false !== strpos( $_SERVER['REQUEST_URI'], '/wp-admin/edit.php' ) ) && ! empty( $_REQUEST['post_type'] ) && ( self::POST_TYPE_SHOP_ORDER === $_REQUEST['post_type'] ) ) {
				// This is an order query, in the admin. Do not execute parse_query(), as it is deadly slow (heavy joins on metas in shop_order_search_custom_fields()).
				return false;
			}
		}

		return $true;
	}

	/**
	 *
	 * Add a filter on order post type.
	 *
	 * @param array $parameters
	 *
	 */
	public function wpsolr_action_query( $parameters ) {

		/* @var WPSOLR_Query $wpsolr_query */
		$wpsolr_query = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_WPSOLR_QUERY ];
		/* @var mixed $search_engine_query */
		$search_engine_query = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_QUERY ];
		/* @var WPSOLR_AbstractSearchClient $search_engine_client */
		$search_engine_client = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_CLIENT ];

		// WooCommerce 3.3.x fix - Else no results shown in /shop
		$wpsolr_query->set( "wc_query", true );

		// post_type url parameter
		if ( ! empty( $wpsolr_query->query['post_type'] ) ) {

			$search_engine_client->search_engine_client_add_filter_term( sprintf( 'woocommerce type:%s', $wpsolr_query->query['post_type'] ), WpSolrSchema::_FIELD_NAME_TYPE, false, $wpsolr_query->query['post_type'] );
		}

		if ( is_admin() && WPSOLR_Service_Container::getOption()->get_option_plugin_woocommerce_is_replace_admin_orders_search() ) {
			if ( ! empty( $wpsolr_query->query['post_type'] ) && ( self::POST_TYPE_SHOP_ORDER === $wpsolr_query->query['post_type'] ) ) {

				// sort by
				$wpsolr_order_by_mapping_fields = [
					'ID'          => 'PID',
					'date'        => self::FIELD_POST_DATE_DT,
					'order_total' => self::FIELD_ORDER_TOTAL_F,
				];
				$original_order_by              = ! empty( $_GET['orderby'] ) ? $_GET['orderby'] : 'post_date';
				$orderby                        = ! empty( $wpsolr_order_by_mapping_fields[ $original_order_by ] ) ? $wpsolr_order_by_mapping_fields[ $original_order_by ] : self::FIELD_POST_DATE_DT;
				$order                          = ( empty( $_GET['order'] ) || ( 'desc' === $_GET['order'] ) ) ? WpSolrSchema::SORT_DESC : WpSolrSchema::SORT_ASC;
				$search_engine_client->search_engine_client_add_sort( $orderby, $order, WPSOLR_Service_Container::getOption()->get_sortby_is_multivalue() );

				// Filter by order status
				$order_status = ! empty( $_GET['post_status'] ) ? $_GET['post_status'] : '';
				if ( ! empty( $order_status ) && ( self::ORDER_STATUS_ALL !== $order_status ) ) {
					$search_engine_client->search_engine_client_add_filter_term( 'post_status', WpSolrSchema::_FIELD_NAME_STATUS_S, false, $order_status );
				}

				// Filter by customer id
				$customer_id = ! empty( $_GET[ self::URL_PARAMETER_CUSTOMER_USER ] ) ? $_GET[ self::URL_PARAMETER_CUSTOMER_USER ] : '';
				if ( ! empty( $customer_id ) ) {
					$search_engine_client->search_engine_client_add_filter_term(
						self::URL_PARAMETER_CUSTOMER_USER, self::URL_PARAMETER_CUSTOMER_USER . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, false, $customer_id
					);
				}
			}
		} elseif ( is_search() && WPSOLR_Service_Container::getOption()->get_option_plugin_woocommerce_is_replace_admin_orders_search() ) {
			// search page on front-end, filter out orders from results.

			$search_engine_client->search_engine_client_add_filter_not_in_terms(
				sprintf( '-type:%s', self::POST_TYPE_SHOP_ORDER ), WpSolrSchema::_FIELD_NAME_TYPE, [ self::POST_TYPE_SHOP_ORDER ]
			);
		}

		// Add category filter on category pages
		if ( $this->get_is_category_search() && ! empty( $this->product_category_name ) ) {

			$filter_query_field_name = $search_engine_client->get_facet_hierarchy_name( WpSolrSchema::_FIELD_NAME_NON_FLAT_HIERARCHY, self::FIELD_PRODUCT_CAT_STR );

			$search_engine_client->search_engine_client_add_filter_term(
				sprintf( 'woocommerce %s:"%s"', $filter_query_field_name, $this->product_category_name ), $filter_query_field_name, false, $this->product_category_name
			);

			// Add filter on _visibility: empty or search/catalog or catalog
			$search_engine_client->search_engine_client_add_filter_empty_or_in_terms(
				'woocommerce filter category visibility',
				self::CUSTOM_FIELD_VISIBILITY_STR,
				[ self::VISIBILITY_CATALOG_AND_SEARCH, self::VISIBILITY_CATALOG ]
			);


		} else {

			// Add filter on _visibility: empty or search/catalog or search
			$search_engine_client->search_engine_client_add_filter_empty_or_in_terms(
				'woocommerce filter search visibility',
				self::CUSTOM_FIELD_VISIBILITY_STR,
				[ self::VISIBILITY_CATALOG_AND_SEARCH, self::VISIBILITY_SEARCH ]
			);

		}

	}


	/**
	 * Return post status valid for orders
	 *
	 * @param string[] $post_statuses
	 * @param string $post_type
	 *
	 * @return string[]
	 */
	public function filter_post_statuses_to_index( array $post_statuses, $post_type ) {

		if ( self::POST_TYPE_SHOP_ORDER === $post_type ) {
			// Add order statuses to indexable statuses
			return array_merge( $post_statuses, array_keys( wc_get_order_statuses() ) );
		}

		// Default statuses.
		return $post_statuses;
	}

	/**
	 * Returns a single product attribute.
	 * We use this instead of standard wc get_attribute(), because it return a string whatever the attribute type (text or select):
	 * color_array => 'red, green'
	 * brand_text => 'Texas, CO.'
	 *
	 * But we then need to create an array with explode:
	 * color_array => array('red, green') // ok
	 * brand_text => array('Texas, CO.') // wrong, brand is split in 2 by the comma
	 *
	 * @param \WC_Product $product
	 * @param array $attribute
	 *
	 * @return array
	 * @internal param array $attr
	 */
	public function get_attribute( $product, $attribute ) {

		if ( isset( $attribute['is_taxonomy'] ) && $attribute['is_taxonomy'] ) {

			return wc_get_product_terms( $product->get_id(), $attribute['name'], [ 'fields' => 'names' ] );

		} else {

			return explode( '|', $attribute['value'] );
		}

	}

	/**
	 * Add fields to a document
	 *
	 * @param array $document_for_update
	 * @param $solr_indexing_options
	 * @param $post
	 * @param $attachment_body
	 * @param WPSOLR_AbstractIndexClient $search_engine_client
	 *
	 * @return array Document updated with fields
	 */
	function add_fields_to_document_for_update( array $document_for_update, $solr_indexing_options, $post, $attachment_body, WPSOLR_AbstractIndexClient $search_engine_client ) {

		if ( self::POST_TYPE_SHOP_ORDER === $post->post_type ) {

			// add order post_date for sorting
			if ( ! empty( $post->post_date ) ) {
				$field_name                         = self::FIELD_POST_DATE_DT;
				$document_for_update[ $field_name ] = $search_engine_client->search_engine_client_format_date( $post->post_date );

			}
		} else {

			// Add visibility.
			$product_visibility = get_post_custom_values( self::CUSTOM_FIELD_VISIBILITY, $post->ID );
			if ( ! empty( $product_visibility ) ) {
				$field_name                         = self::CUSTOM_FIELD_VISIBILITY_STR;
				$document_for_update[ $field_name ] = $product_visibility;
			}
		}

		return $document_for_update;
	}

	/**
	 * Replace WooCommerce sort list with WPSOLR sort list
	 *
	 * @param array $sortby
	 *
	 * @return array
	 */
	function custom_woocommerce_catalog_orderby( $sortby ) {

		if ( ! WPSOLR_Service_Container::getOption()->get_option_plugin_woocommerce_is_replace_sort_items() ) {
			// Use standard WooCommerce sort items.
			return $sortby;
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
	 * Map WooCommerce url orderby parameters with  WPSOLR's
	 *
	 * @param WPSOLR_Query $wpsolr_query
	 * @param array $url_parameters
	 *
	 */
	public
	function wpsolr_filter_url_parameters(
		WPSOLR_Query $wpsolr_query, $url_parameters
	) {

		if ( WPSOLR_Service_Container::getOption()->get_option_plugin_woocommerce_is_replace_sort_items() ) {
			// Get WooCommerce order by value from url, or use the default one set in settings->products->display.

			$order_by_value = isset( $url_parameters[ self::WOOCOMERCE_URL_PARAMETER_SORT_BY ] )
				? wc_clean( $url_parameters[ self::WOOCOMERCE_URL_PARAMETER_SORT_BY ] )
				: apply_filters( 'woocommerce_default_catalog_orderby', WPSOLR_Service_Container::getOption()->get_option( true, 'woocommerce_default_catalog_orderby' ) );

			if ( ! empty( $order_by_value ) ) {
				$wpsolr_query->set_wpsolr_sort( $order_by_value );
			}
		}
	}


	/**
	 * Remove product category of facets to display if we are on a category page.
	 *
	 * @param array $facets_to_display ['type', 'categories', 'product_cat_str']
	 *
	 * @return array
	 */
	public function wpsolr_filter_facets_to_display( array $facets_to_display ) {

		if ( $this->get_is_category_search() ) {
			$index = array_search( self::FIELD_PRODUCT_CAT_STR, $facets_to_display, true );
			if ( false !== $index ) {
				//unset( $facets_to_display[ $index ] );
			}
		}

		return $facets_to_display;
	}

	/**
	 * Remove the top level of the category facet hierarchy on category pages.
	 * top_cat =>
	 * top_cat->current_cat =>
	 * top_cat->current_cat->sub_cat => sub_cat
	 *
	 * @param array $facets_content
	 *
	 * @return array
	 */
	public function wpsolr_filter_facets_content_to_display( array $facets_content ) {

		if ( empty( $facets_content ) ) {
			return [];
		}

		if ( $this->get_is_category_search() && ! empty( $this->product_category_name ) && ! empty( $facets_content[ self::FIELD_PRODUCT_CAT_STR ] ) ) {

			foreach ( $facets_content as $facet_name => &$facet ) {

				if ( self::FIELD_PRODUCT_CAT_STR === $facet_name ) {

					foreach ( $facet['values'] as $index => &$facet_value ) {
						$value_without_top_level_hierarchy = preg_replace( sprintf( self::REGEX_SUB_CATEGORIES, preg_quote( $this->product_category_name, '/' ) ), '$1', $facet_value['value'] );

						if ( $facet_value['value'] !== $value_without_top_level_hierarchy ) {

							$facet_value['value'] = $value_without_top_level_hierarchy;
						} else {

							unset( $facet['values'][ $index ] );
						}
					}
				}
			}
		}

		return $facets_content;
	}
}