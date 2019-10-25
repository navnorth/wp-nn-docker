<?php

namespace wpsolr\pro\extensions\acf;

use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\metabox\WPSOLR_Metabox;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;

/**
 * Class WPSOLR_Plugin_Acf
 * @package wpsolr\pro\extensions\acf
 *
 * Manage Advanced Custom Fields (ACF) plugin
 * @link https://wordpress.org/plugins/advanced-custom-fields/
 */
class WPSOLR_Plugin_Acf extends WpSolrExtensions {

	// Prefix of ACF fields
	const FIELD_PREFIX = '_';

	// Polylang options
	const _OPTIONS_NAME = WPSOLR_Option::OPTION_EXTENSION_ACF;

	// acf fields indexed by name.
	private $_fields;

	// Options
	private $_options;

	/** @var bool */
	private $_is_index_all_file_fields;

	// ACF types
	const ACF_TYPE_GOOGLE_MAP = 'google_map';

	/**
	 * ACF field types 'Layout'
	 */
	const ACF_FIELD_TYPE_LAYOUT_REPEATER = 'repeater';
	const ACF_FIELD_TYPE_LAYOUT_FLEXIBLE_CONTENT = 'flexible_content';
	const ACF_FIELD_TYPE_LAYOUT_TAB = 'tab';
	const ACF_FIELD_TYPE_LAYOUT_CLONE = 'clone';
	const ACF_FIELD_TYPE_LAYOUT_MESSAGE = 'message';

	/**
	 * ACF field types 'Basic'
	 */
	const ACF_FIELD_TYPE_BASIC_TEXT = 'text';
	const ACF_FIELD_TYPE_BASIC_TEXTAREA = 'textarea';
	const ACF_FIELD_TYPE_BASIC_NUMBER = 'number';
	const ACF_FIELD_TYPE_BASIC_PASSWORD = 'password';
	const ACF_FIELD_TYPE_BASIC_URL = 'url';
	const ACF_FIELD_TYPE_BASIC_EMAIL = 'email';

	/**
	 * ACF field types 'Choice'
	 */
	const ACF_FIELD_TYPE_CHOICE_TRUE_FALSE = 'true_false';
	const ACF_FIELD_TYPE_CHOICE_SELECT = 'select';
	const ACF_FIELD_TYPE_CHOICE_CHECKBOX = 'checkbox';
	const ACF_FIELD_TYPE_CHOICE_RADIOBOX = 'radio';

	/**
	 * ACF field types 'File'
	 */
	const ACF_FIELD_TYPE_CONTENT_FILE = 'file';
	const ACF_FIELD_TYPE_CONTENT_FILE_URL = 'url';
	const ACF_FIELD_TYPE_CONTENT_FILE_ID = 'id';
	const ACF_FIELD_TYPE_CONTENT_FILE_ARRAY = 'array';
	const ACF_FIELD_TYPE_CONTENT_WYSIWYG = 'wysiwyg';

	/**
	 * ACF field types 'Relationship'
	 */
	const ACF_FIELD_TYPE_RELATIONSHIP_POST_OBJECT = 'post_object';
	const ACF_FIELD_TYPE_RELATIONSHIP_PAGE_LINK = 'page_link';
	const ACF_FIELD_TYPE_RELATIONSHIP_RELATIONSHIP = 'relationship';
	const ACF_FIELD_TYPE_RELATIONSHIP_TAXONOMY = 'taxonomy';
	const ACF_FIELD_TYPE_RELATIONSHIP_USER = 'user';

	// Format lat,long
	const FORMAT_LAT_LONG = '%s,%s';

	/**
	 * Factory
	 *
	 * @return WPSOLR_Plugin_Acf
	 */
	static function create() {

		return new self();
	}

	/*
	 * Constructor
	 * Subscribe to actions
	 */

	/**
	 * WPSOLR_Plugin_Acf constructor.
	 */
	function __construct() {

		$this->fields = [];

		$this->_is_index_all_file_fields = WPSOLR_Service_Container::getOption()->get_plugin_acf_is_index_all_file_fields();

		$this->_options = self::get_option_data( self::EXTENSION_ACF );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_INDEX_CUSTOM_FIELDS, [
			$this,
			'get_index_custom_fields',
		], 10, 2 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_SEARCH_PAGE_FACET_NAME, [
			$this,
			'get_field_label',
		], 10, 1 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_POST_CUSTOM_FIELDS, [
			$this,
			'filter_custom_fields',
		], 10, 2 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_GET_POST_ATTACHMENTS, [
			$this,
			'filter_get_post_attachments',
		], 10, 2 );

		if ( is_admin() ) {
			add_action( 'acf/init', [
				$this,
				'acf_google_map_init_pro',
			], 10 );

			add_filter( WPSOLR_Events::WPSOLR_FILTER_FACET_ITEMS, [
				$this,
				'get_facet_items',
			], 10, 2 );
		}

	}

	/**
	 * Return all decoded choices of an ACF facet
	 *
	 * @param $facet_items
	 * @param $facet_name
	 *
	 * @return array
	 */
	public function get_facet_items( $facet_items, $facet_name ) {

		$field = acf_get_field( $facet_name );

		if ( $field ) {

			if ( isset( $field['choices'] ) ) {

				foreach ( $field['choices'] as $choice_id => $choice_label ) {
					array_push( $facet_items, $choice_id );
				}

			} else {

				$parent = acf_get_field( $field['parent'] );
				if ( $parent ) {
					// Repeated field: requires a special sql to retrieve it's values.

					$fields = $this->get_acf_fields( $field['key'] );
					foreach ( $fields as $field_key => $field_value ) {
						array_push( $facet_items, $field_value );
					}
				}
			}
		}

		return $facet_items;
	}


	/**
	 * Retrieve all field keys of all ACF fields.
	 *
	 * @return array
	 */
	function get_acf_fields( $field_key = '' ) {
		global $wpdb;

		// Use cached fields if exist
		if ( ! empty( $this->_fields[ $field_key ] ) ) {
			return $this->_fields[ $field_key ];
		}

		$fields = [];

		// Else create the cached fields
		if ( ! empty( $field_key ) ) {

			// Bad perfs when opening the 2.3 facets page, but only way to get all the row values for a repeated field
			$sql = $wpdb->prepare( "SELECT distinct m1.meta_key, m2.meta_value
	                                        FROM $wpdb->postmeta m1 JOIN $wpdb->postmeta m2
	                                        ON (m1.meta_value = %s AND m1.meta_key = CONCAT('_', m2.meta_key))", $field_key );

			$results = $wpdb->get_results( $sql );

		} else {

			$results = $wpdb->get_results( "SELECT distinct meta_key, meta_value
	                                        FROM $wpdb->postmeta
	                                        WHERE meta_key LIKE '_%'
	                                        AND   meta_value like 'field_%'" );

		}

		$nb_results = count( $results );
		for ( $loop = 0; $loop < $nb_results; $loop ++ ) {
			$fields[ $results[ $loop ]->meta_key ] = $results[ $loop ]->meta_value;

		}

		// Save the cache
		$this->_fields[ $field_key ] = $fields;

		return $this->_fields[ $field_key ];
	}


	/**
	 * Update custom fields list to be indexed
	 * Replace _groupRepeater_0_repeatedFieldName by repeatedFieldName
	 *
	 * @param string[] $custom_fields
	 * @param string $model_type
	 *
	 * @return string[]
	 */
	function get_index_custom_fields( $custom_fields, $model_type ) {

		if ( ! isset( $custom_fields ) ) {
			$custom_fields = [];
		}

		$fields = $this->get_acf_fields();

		$results = [];

		$indexable_acf_fields = [
			/**
			 * ACF field types 'Content'
			 */
			self::ACF_FIELD_TYPE_CONTENT_WYSIWYG,
			/**
			 * ACF field types 'Layout'
			 */
			self::ACF_FIELD_TYPE_LAYOUT_TAB,
			self::ACF_FIELD_TYPE_LAYOUT_CLONE,
			/**
			 * ACF field types 'Choice'
			 */
			self::ACF_FIELD_TYPE_CHOICE_TRUE_FALSE,
			self::ACF_FIELD_TYPE_CHOICE_SELECT,
			self::ACF_FIELD_TYPE_CHOICE_CHECKBOX,
			self::ACF_FIELD_TYPE_CHOICE_RADIOBOX,
			/**
			 * ACF field types 'Basic'
			 */
			self::ACF_FIELD_TYPE_BASIC_TEXT,
			self::ACF_FIELD_TYPE_BASIC_TEXTAREA,
			self::ACF_FIELD_TYPE_BASIC_NUMBER,
			self::ACF_FIELD_TYPE_BASIC_PASSWORD,
			self::ACF_FIELD_TYPE_BASIC_URL,
			self::ACF_FIELD_TYPE_BASIC_EMAIL,
		];

		foreach ( $custom_fields as $custom_field_name ) {

			$do_not_include = false;

			if ( isset( $fields[ self::FIELD_PREFIX . $custom_field_name ] ) || isset( $fields[ $custom_field_name ] ) ) {

				$field_key = isset( $fields[ self::FIELD_PREFIX . $custom_field_name ] ) ? $fields[ self::FIELD_PREFIX . $custom_field_name ] : $fields[ $custom_field_name ];
				$field     = get_field_object( $field_key, false, false, false );

				if ( $field ) {

					$do_not_include = true;

					if ( in_array( $field['type'], $indexable_acf_fields ) ) {

						/**
						 * Get the canonical form of a repeated field name, eventually.
						 * Examples:
						 * _xxxxx_0_field => field
						 * __xxxxx_0__field => field
						 * xxxxx_0_field => field
						 * _xxxxx_10_field => field
						 * _xxxxx_yy_field => _xxxxx_yy_field
						 * xxxxx_yy_field => xxxxx_yy_field
						 * field => field
						 */
						$repeated_field_name = preg_replace( '/(.*)_(\d*)_(.*)/', '$3', $custom_field_name );

						if ( ! in_array( $repeated_field_name, $results, true ) ) {
							// Add the non repeated field name, or the repeated field canonical name.
							array_push( $results, $repeated_field_name );
						}
					}
				}
			}

			if ( ! $do_not_include && ! in_array( $custom_field_name, $results, true ) ) {
				// Add the non repeated field name, or the non ACF field name.
				array_push( $results, $custom_field_name );
			}
		}

		return $results;
	}

	/**
	 * Get the ACF field label from the custom field name.
	 *
	 * @param string $custom_field_name
	 *
	 * @return mixed
	 */
	public
	function get_field_label(
		$custom_field_name
	) {

		$result = $custom_field_name;

		if ( ! isset( $this->_options['display_acf_label_on_facet'] ) ) {
			// No need to replace custom field name by acf field label
			return $result;
		}

		// Retrieve field among ACF fields
		$fields = $this->get_acf_fields();
		if ( isset( $fields[ self::FIELD_PREFIX . $custom_field_name ] ) ) {
			$field_key = $fields[ self::FIELD_PREFIX . $custom_field_name ];
			$field     = get_field_object( $field_key );
			$result    = isset( $field['label'] ) ? $field['label'] : $custom_field_name;
		}

		return $result;
	}


	/**
	 * Decode acf values before indexing.
	 * Get all field values, recursively in containers if necessary, which are not containers, and not files.
	 * Files are treated in attachments code.
	 *
	 * @param $custom_fields
	 * @param $post_id
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

		// Get post ACF field objects
		$fields_set = [];
		$this->get_fields_all_levels(
			$fields_set,
			get_field_objects( $post_id ),
			[], // We want All files
			[
				self::ACF_FIELD_TYPE_CONTENT_FILE, // But we don't want files. They are dealt with attachments.
			]
		);

		if ( $fields_set ) {

			$is_first = [];

			foreach ( $fields_set as $field_name => $fields ) {

				foreach ( $fields as $field ) {

					if ( ! empty( $field['value'] ) ) {

						switch ( $field['type'] ) {
							case self::ACF_TYPE_GOOGLE_MAP:
								/*
								array (
									'address' => 'some adress',
									'lat' => '48.631077',
									'lng' => '-10.1482240000000274',
								)*/
								// Convert to a lat,long format
								if ( ! empty( $field['value']['lat'] ) && ! empty( $field['value']['lng'] ) ) {
									$custom_fields[ $field['name'] ] = sprintf( self::FORMAT_LAT_LONG, $field['value']['lat'], $field['value']['lng'] );
								}

								break;

							default:
								// Same treatments for all other types.
								if ( ! isset( $is_first[ $field['name'] ] ) ) {
									unset( $custom_fields[ $field['name'] ] );
								}

								foreach ( is_array( $field['value'] ) ? $field['value'] : [ $field['value'] ] as $field_value ) {
									$custom_fields[ $field['name'] ][] = $field_value;
								}

								$is_first[ $field['name'] ] = false;

								break;
						}
					}
				}
			}
		}

		return $custom_fields;
	}

	/**
	 * Retrieve attachments in the fields of type file of the post
	 *
	 * @param array $attachments
	 * @param string $post_id
	 *
	 * @return array
	 */
	public
	function filter_get_post_attachments(
		$attachments, $post_id
	) {

		$is_metabox_file_selected = WPSOLR_Metabox::get_metabox_is_do_index_acf_field_files( $post_id );
		$is_file_indexed          = $this->_is_index_all_file_fields ? $is_metabox_file_selected : ! $is_metabox_file_selected;
		if ( $is_file_indexed ) {
			// Do nothing
			return $attachments;
		}

		// Get post ACF field objects
		$fields_set = [];
		$this->get_fields_all_levels(
			$fields_set,
			get_field_objects( $post_id ),
			[
				self::ACF_FIELD_TYPE_CONTENT_FILE,
			],
			[]
		);

		if ( $fields_set ) {

			foreach ( $fields_set as $field_name => $fields ) {

				foreach ( $fields as $field ) {

					// Retrieve the post_id of the file
					if ( ! empty( $field['value'] ) && ( self::ACF_FIELD_TYPE_CONTENT_FILE === $field['type'] ) ) {
						switch ( $field['return_format'] ) {
							case self::ACF_FIELD_TYPE_CONTENT_FILE_ID:
								array_push( $attachments, [ 'post_id' => $field['value'] ] );
								break;

							case self::ACF_FIELD_TYPE_CONTENT_FILE_ARRAY:
								array_push( $attachments, [ 'post_id' => $field['value']['id'] ] );
								break;

							case self::ACF_FIELD_TYPE_CONTENT_FILE_URL:
								array_push( $attachments, [ 'url' => $field['value'] ] );
								break;

							default:
								// Do nothing
								break;
						}
					}
				}
			}
		}

		return $attachments;
	}


	/**
	 * Get subfields of fields recursively
	 *
	 * @param array $all_fields
	 * @param array $fields
	 * @param array $field_types
	 * @param array $excluded_field_types
	 *
	 */
	public
	function get_fields_all_levels(
		&$all_fields, $fields, $field_types, $excluded_field_types
	) {

		if ( empty( $fields ) ) {
			// Nothing to do.
			return;
		}

		foreach ( $fields as $field_name => $field ) {

			if ( ! empty( $field['value'] ) ) {

				switch ( $field['type'] ) {
					case self::ACF_FIELD_TYPE_LAYOUT_FLEXIBLE_CONTENT:

						// Extract sub_fields of each layout, then proceed on sub_fields
						$field['sub_fields'] = [];
						foreach ( $field['layouts'] as $layout ) {
							foreach ( $layout['sub_fields'] as $sub_field ) {
								$field['sub_fields'][] = $sub_field;
							}
						}

					// No break here!!!
					//break;

					case self::ACF_FIELD_TYPE_LAYOUT_REPEATER:
						foreach ( $field['sub_fields'] as $sub_field ) {

							// Copy sub_field value(s)
							foreach ( $field['value'] as $value ) {

								if ( ! empty( $value[ $sub_field['name'] ] ) ) {
									$sub_field['value'] = $value[ $sub_field['name'] ];

									$this->get_fields_all_levels( $all_fields, [ $sub_field['name'] => $sub_field ], $field_types, $excluded_field_types );
								}
							}
						}
						break;

					default:
						// This is a non-recursive type, with value(s). Add it to results.
						if (
							( empty( $field_types ) || in_array( $field['type'], $field_types, true ) ) // Field type is in included types
							&& ( empty( $excluded_field_types ) || ! in_array( $field['type'], $excluded_field_types, true ) ) // And field type is not in excluded types
						) {
							$all_fields[ $field['name'] ][] = $field;
						}
						break;
				}
			}
		}
	}

	/**
	 * Initialize ACF google map api for ACF PRO, if not already set by ACF before.
	 *
	 */
	function acf_google_map_init_pro() {

		$acf_api_key = acf_get_setting( 'google_api_key' );
		if ( empty( $acf_api_key ) ) {

			$wpsolr_api_key = WPSOLR_Service_Container::getOption()->get_plugin_acf_google_map_api_key();

			if ( ! empty( $wpsolr_api_key ) ) {
				acf_update_setting( 'google_api_key', $wpsolr_api_key );
			}
		}
	}

}
