<?php

namespace wpsolr\pro\extensions\embed_any_document;

use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;

/**
 * Class WPSOLR_Plugin_EmbedAnyDocument
 * @package wpsolr\pro\extensions\embed_any_document
 *
 * Manage Embed Any Document plugin
 * @link https://wordpress.org/plugins/embed-any-document/
 */
class WPSOLR_Plugin_EmbedAnyDocument extends WpSolrExtensions {

	// Options
	const _OPTIONS_NAME = WPSOLR_Option::OPTION_EXTENSION_BBPRESS;

	protected $is_do_embed_documents;
	protected $pattern;

	// Options
	private $_options;

	// Overide in child classes
	const EMBEDDOC_SHORTCODE = 'embeddoc';
	const EMBEDDOC_SHORTCODE_ATTRIBUTE_URL = 'url';


	/**
	 * Factory
	 *
	 * @return WPSOLR_Plugin_EmbedAnyDocument
	 */
	static function create() {

		return new self();
	}

	/**
	 * Constructor
	 * Subscribe to actions
	 */
	function __construct() {

		$this->set_is_do_embed_documents();
		$this->pattern = get_shortcode_regex( [ static::EMBEDDOC_SHORTCODE ] );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_GET_POST_ATTACHMENTS, [
			$this,
			'filter_get_post_attachments'
		], 10, 2 );

	}

	protected function set_is_do_embed_documents() {

		$this->is_do_embed_documents = WPSOLR_Service_Container::getOption()->get_embed_any_document_is_do_embed_documents();
	}

	/**
	 * Retrieve embedded urls in the post shortcodes
	 *
	 * @param array $attachments
	 * @param string $post
	 *
	 * @return array
	 */
	public function filter_get_post_attachments( $attachments, $post_id ) {

		if ( ! $this->is_do_embed_documents ) {
			// Do nothing
			return $attachments;
		}

		$post = get_post( $post_id );

		// Extract shortcodes
		$pattern = $this->pattern;
		preg_match_all( "/$pattern/", $post->post_content, $matches );

		if ( ! empty( $matches ) && ! empty( $matches[3] ) ) {

			foreach ( $matches[3] as $match ) {

				// Extract shortcode attributes
				$attributes = shortcode_parse_atts( $match );

				if ( ! empty( $attributes ) && ! empty( $attributes[ static::EMBEDDOC_SHORTCODE_ATTRIBUTE_URL ] ) ) {

					array_push( $attachments, [ 'url' => $attributes[ static::EMBEDDOC_SHORTCODE_ATTRIBUTE_URL ] ] );
				}
			}
		}

		return $attachments;
	}
}
