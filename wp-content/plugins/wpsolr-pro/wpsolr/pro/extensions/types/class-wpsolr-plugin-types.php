<?php

namespace wpsolr\pro\extensions\types;

use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\metabox\WPSOLR_Metabox;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;

/**
 * Class WPSOLR_Plugin_Types
 * @package wpsolr\pro\extensions\types
 *
 * Manage "Types" plugin (Custom fields)
 * @link https://wordpress.org/plugins/types/
 */
class WPSOLR_Plugin_Types extends WpSolrExtensions {

	// Prefix of TYPES custom fields
	const CONST_TYPES_FIELD_PREFIX = 'wpcf-';

	// Toolset type options
	const _OPTIONS_NAME = WPSOLR_Option::OPTION_EXTENSION_TYPES;

	// File field type
	const CONST_TYPES_FIELD_FILE = 'file';

	// Options
	private $_options;


	/**
	 * Factory
	 *
	 * @return WPSOLR_Plugin_Types
	 */
	static function create() {

		return new self();
	}

	/**
	 * Constructor
	 * Subscribe to actions
	 */

	function __construct() {

		$this->_options = self::get_option_data( self::EXTENSION_TYPES );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_SEARCH_PAGE_FACET_NAME, [
			$this,
			'get_field_label',
		], 10, 1 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_GET_POST_ATTACHMENTS, [
			$this,
			'filter_get_post_attachments',
		], 10, 2 );

	}

	/**
	 * Get the TYPES field label from the custom field name.
	 *
	 * @param $custom_field_name
	 *
	 * @return mixed
	 */
	public
	function get_field_label(
		$custom_field_name
	) {

		$result = $custom_field_name;

		if ( ! isset( $this->_options['display_types_label_on_facet'] ) || ! ( self::CONST_TYPES_FIELD_PREFIX === substr( $custom_field_name, 0, strlen( self::CONST_TYPES_FIELD_PREFIX ) ) ) ) {
			// No need to replace custom field name by types field label
			return $result;
		}


		$custom_field_name_without_prefix = substr( $custom_field_name, strlen( self::CONST_TYPES_FIELD_PREFIX ) );
		$field                            = wpcf_fields_get_field_by_slug( $custom_field_name_without_prefix );

		// Retrieve field among TYPES fields
		if ( isset( $field ) ) {
			$result = $field['name'];
		}

		return $result;
	}

	/**
	 * Retrieve attachments in the custom fields of type file of the post
	 *
	 * @param array $attachments
	 * @param string $post
	 *
	 * @return array
	 */
	public
	function filter_get_post_attachments(
		$attachments, $post_id
	) {

		if ( ! WPSOLR_Metabox::get_metabox_is_do_index_toolset_field_files( $post_id ) ) {
			// Do nothing
			return $attachments;
		}

		$post_custom_fields = get_post_meta( $post_id );

		foreach ( $post_custom_fields as $post_custom_field_name => $post_custom_field_value ) {

			if ( ( self::CONST_TYPES_FIELD_PREFIX === substr( $post_custom_field_name, 0, strlen( self::CONST_TYPES_FIELD_PREFIX ) ) ) ) {
				// Custom field is a Toolset type custom field
				$custom_field_name_without_prefix = substr( $post_custom_field_name, strlen( self::CONST_TYPES_FIELD_PREFIX ) );
				$field                            = wpcf_fields_get_field_by_slug( $custom_field_name_without_prefix );

				if ( ! empty( $field ) && ( self::CONST_TYPES_FIELD_FILE === $field['type'] ) && ! empty( $post_custom_field_value ) && ! empty( $post_custom_field_value[0] ) ) {
					// We found a file type: add the url to the attachments.
					array_push( $attachments, [ 'url' => $post_custom_field_value[0] ] );
				}
			}
		}

		return $attachments;
	}

}
