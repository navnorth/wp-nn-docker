<?php

namespace wpsolr\core\classes\models\post;


use wpsolr\core\classes\engines\WPSOLR_AbstractIndexClient;
use wpsolr\core\classes\models\WPSOLR_Model_Abstract;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WPSOLR_Model_Post
 * @package wpsolr\core\classes\models
 */
class WPSOLR_Model_Post extends WPSOLR_Model_Abstract {

	/** @var \WP_Post */
	protected $data;

	/**
	 * @inheritdoc
	 */
	public function get_id() {
		return $this->data->ID;
	}

	/**
	 * @inheritdoc
	 */
	function get_type() {
		return $this->data->post_type;
	}

	/**
	 * @inheritdoc
	 */
	public function get_date_modified() {
		return $this->data->post_modified;
	}

	/**
	 * @inheritdoc
	 * @throws \Exception
	 */
	public function create_document_from_model_or_attachment_inner( WPSOLR_AbstractIndexClient $search_engine_client, $attachment_body ) {

		$post_to_index = $this->data;

		$pauthor_id = $post_to_index->post_author;
		$ptitle     = $post_to_index->post_title;
		// Post is NOT an attachment: we get the document body from the post object
		$pcontent    = $post_to_index->post_content . ( empty( $attachment_body ) ? '' : ( '. ' . $attachment_body ) );
		$post_parent = isset( $post_to_index->post_parent ) ? $post_to_index->post_parent : 0;

		$pexcerpt   = $post_to_index->post_excerpt;
		$pauth_info = get_userdata( $pauthor_id );
		$pauthor    = isset( $pauth_info ) && isset( $pauth_info->display_name ) ? $pauth_info->display_name : '';
		$pauthor_s  = isset( $pauth_info ) && isset( $pauth_info->user_nicename ) ? get_author_posts_url( $pauth_info->ID, $pauth_info->user_nicename ) : '';

		// Get the current post language
		$post_language = apply_filters( WPSOLR_Events::WPSOLR_FILTER_POST_LANGUAGE, null, $post_to_index );

		$pdate        = solr_format_date( $post_to_index->post_date_gmt );
		$pmodified    = solr_format_date( $post_to_index->post_modified_gmt );
		$pdisplaydate = $search_engine_client->search_engine_client_format_date( $post_to_index->post_date );
		$purl         = get_permalink( $this->data );
		$comments_con = [];

		$indexing_options = $search_engine_client->get_search_engine_indexing_options();
		$comm             = isset( $indexing_options[ WpSolrSchema::_FIELD_NAME_COMMENTS ] ) ? $indexing_options[ WpSolrSchema::_FIELD_NAME_COMMENTS ] : '';

		$numcomments = 0;
		if ( $comm ) {
			$comments_con = [];

			$comments = get_comments( "status=approve&post_id={$post_to_index->ID}" );
			foreach ( $comments as $comment ) {
				array_push( $comments_con, $comment->comment_content );
				$numcomments += 1;
			}

		}
		$pcomments    = $comments_con;
		$pnumcomments = $numcomments;


		/*
			Get all custom categories selected for indexing, including 'category'
		*/
		$cats                            = [];
		$categories_flat_hierarchies     = [];
		$categories_non_flat_hierarchies = [];
		$aTaxo                           = WPSOLR_Service_Container::getOption()->get_option_index_taxonomies();
		$newTax                          = []; // Add categories by default
		if ( is_array( $aTaxo ) && count( $aTaxo ) ) {
		}
		foreach ( $aTaxo as $a ) {

			if ( WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING === substr( $a, ( strlen( $a ) - 4 ), strlen( $a ) ) ) {
				$a = substr( $a, 0, ( strlen( $a ) - 4 ) );
			}

			// Add only non empty categories
			if ( strlen( trim( $a ) ) > 0 ) {
				array_push( $newTax, $a );
			}
		}


		// Get all categories ot this post
		$terms = wp_get_post_terms( $post_to_index->ID, [ 'category' ], [ 'fields' => 'all_with_object_id' ] );
		if ( $terms && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {

				// Add category and it's parents
				$term_parents_names = [];
				// Add parents in reverse order ( top-bottom)
				$term_parents_ids = array_reverse( get_ancestors( $term->term_id, 'category' ) );
				array_push( $term_parents_ids, $term->term_id );

				foreach ( $term_parents_ids as $term_parent_id ) {
					$term_parent = get_term( $term_parent_id, 'category' );

					array_push( $term_parents_names, $term_parent->name );

					// Add the term to the non-flat hierarchy (for filter queries on all the hierarchy levels)
					array_push( $categories_non_flat_hierarchies, $term_parent->name );
				}

				// Add the term to the flat hierarchy
				array_push( $categories_flat_hierarchies, implode( WpSolrSchema::FACET_HIERARCHY_SEPARATOR, $term_parents_names ) );

				// Add the term to the categories
				array_push( $cats, $term->name );
			}
		}

		// Get all tags of this port
		$tag_array = [];
		$tags      = get_the_tags( $post_to_index->ID );
		if ( ! $tags == null ) {
			foreach ( $tags as $tag ) {
				array_push( $tag_array, $tag->name );

			}
		}

		if ( $search_engine_client->get_is_in_galaxy() ) {
			$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_BLOG_NAME_STR ] = $search_engine_client->get_galaxy_slave_filter_value();
		}

		if ( ! empty( $post_parent ) ) {
			$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_POST_PARENT_I ] = $post_parent;
		}
		if ( ! empty( $ptitle ) ) {
			// bbPress by default does not have reply titles and if no titles then no indexing with ES, topic indexing works nicely thought.
			$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_TITLE ]   = $ptitle;
			$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_TITLE_S ] = $ptitle; // For sorting titles
		}
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_STATUS_S ] = $post_to_index->post_status;

		if ( isset( $indexing_options['p_excerpt'] ) && ( ! empty( $pexcerpt ) ) ) {

			// Index post excerpt, by adding it to the post content.
			// Excerpt can therefore be: searched, autocompleted, highlighted.
			$pcontent .= WPSOLR_AbstractIndexClient::CONTENT_SEPARATOR . $pexcerpt;
		}

		if ( ! empty( $pcomments ) ) {

			// Index post comments, by adding it to the post content.
			// Excerpt can therefore be: searched, autocompleted, highlighted.
			//$pcontent .= self::CONTENT_SEPARATOR . implode( self::CONTENT_SEPARATOR, $pcomments );
		}


		$content_with_shortcodes_expanded_or_stripped = $pcontent;
		if ( isset( $indexing_options['is_shortcode_expanded'] ) && ( strpos( $pcontent, '[solr_search_shortcode]' ) === false ) ) {

			// Expand shortcodes which have a plugin active, and are not the search form shortcode (else pb).
			global $post;
			$post                                         = $post_to_index;
			$content_with_shortcodes_expanded_or_stripped = do_shortcode( $pcontent );
		}

		// Remove shortcodes tags remaining, but not their content.
		// strip_shortcodes() does nothing, probably because shortcodes from themes are not loaded in admin.
		// Credit: https://wordpress.org/support/topic/stripping-shortcodes-keeping-the-content.
		// Modified to enable "/" in attributes
		$content_with_shortcodes_expanded_or_stripped = preg_replace( "~(?:\[/?)[^\]]+/?\]~s", '', $content_with_shortcodes_expanded_or_stripped );  # strip shortcodes, keep shortcode content;

		// Remove HTML tags
		$stripped_content                                                        = strip_tags( $content_with_shortcodes_expanded_or_stripped );
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_CONTENT ] = ! empty( $stripped_content ) ? $stripped_content : ' '; // Prevent empty content error with ES

		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_AUTHOR_ID_S ] = $pauthor_id;
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_AUTHOR ]      = $pauthor;
		$author_copy_to                                                              = $search_engine_client->copy_field_name( WpSolrSchema::_FIELD_NAME_AUTHOR );
		if ( WpSolrSchema::_FIELD_NAME_AUTHOR !== $author_copy_to ) {
			$this->solarium_document_for_update[ $author_copy_to ] = $pauthor;
		}

		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_AUTHOR_S ]            = $pauthor_s;
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_DATE ]                = $pdate;
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_MODIFIED ]            = $pmodified;
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_DISPLAY_DATE ]        = $pdisplaydate;
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_DISPLAY_DATE_DT ]     = $pdisplaydate;
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_DISPLAY_MODIFIED_DT ] = $search_engine_client->search_engine_client_format_date( $post_to_index->post_modified );
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_PERMALINK ]           = $purl;
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_COMMENTS ]            = $pcomments;
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_NUMBER_OF_COMMENTS ]  = $pnumcomments;

		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_CATEGORIES_STR ] = $cats;
		$categories_copy_to                                                             = $search_engine_client->copy_field_name( WpSolrSchema::_FIELD_NAME_CATEGORIES );
		if ( WpSolrSchema::_FIELD_NAME_CATEGORIES_STR !== $categories_copy_to ) {
			$this->solarium_document_for_update[ $categories_copy_to ] = $cats;
		}

		// Hierarchy of categories
		$this->solarium_document_for_update[ sprintf( WpSolrSchema::_FIELD_NAME_FLAT_HIERARCHY, WpSolrSchema::_FIELD_NAME_CATEGORIES_STR ) ]     = $categories_flat_hierarchies;
		$this->solarium_document_for_update[ sprintf( WpSolrSchema::_FIELD_NAME_NON_FLAT_HIERARCHY, WpSolrSchema::_FIELD_NAME_CATEGORIES_STR ) ] = $categories_non_flat_hierarchies;
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_TAGS ]                                                                    = $tag_array;
		$tags_copy_to                                                                                                                            = $search_engine_client->copy_field_name( WpSolrSchema::_FIELD_NAME_TAGS );
		if ( WpSolrSchema::_FIELD_NAME_TAGS !== $tags_copy_to ) {
			$this->solarium_document_for_update[ $tags_copy_to ] = $tag_array;
		}

		// Index post thumbnail
		$this->index_post_thumbnails( $solarium_document_for_update, $post_to_index->ID, $search_engine_client->get_is_in_galaxy() );

		// Index post url
		$this->index_post_url( $solarium_document_for_update, $post_to_index->ID, $search_engine_client->get_is_in_galaxy() );

		$taxonomies = (array) get_taxonomies( [ '_builtin' => false ], 'names' );
		foreach ( $taxonomies as $parent ) {
			if ( in_array( $parent, $newTax, true ) ) {
				$terms = get_the_terms( $post_to_index->ID, $parent );
				if ( (array) $terms === $terms ) {
					$parent    = strtolower( str_replace( ' ', '_', $parent ) );
					$nm1       = $parent . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING;
					$nm1_array = [];

					$taxonomy_non_flat_hierarchies = [];
					$taxonomy_flat_hierarchies     = [];

					foreach ( $terms as $term ) {

						// Add taxonomy and it's parents
						$term_parents_names = [];
						// Add parents in reverse order ( top-bottom)
						$term_parents_ids = array_reverse( get_ancestors( $term->term_id, $parent ) );
						array_push( $term_parents_ids, $term->term_id );

						foreach ( $term_parents_ids as $term_parent_id ) {
							$term_parent = get_term( $term_parent_id, $parent );

							if ( $term_parent instanceof \WP_Error ) {
								throw new \Exception( sprintf( 'WPSOLR: error on term %s for taxonomy \'%s\': %s', $term_parent_id, $parent, $term_parent->get_error_message() ) );
							}

							array_push( $term_parents_names, $term_parent->name );

							// Add the term to the non-flat hierarchy (for filter queries on all the hierarchy levels)
							array_push( $taxonomy_non_flat_hierarchies, $term_parent->name );
						}

						// Add the term to the flat hierarchy
						array_push( $taxonomy_flat_hierarchies, implode( WpSolrSchema::FACET_HIERARCHY_SEPARATOR, $term_parents_names ) );

						// Add the term to the taxonomy
						array_push( $nm1_array, $term->name );

						// Add the term to the categories searchable
						array_push( $cats, $term->name );

					}

					if ( count( $nm1_array ) > 0 ) {
						$this->solarium_document_for_update[ $nm1 ] = $nm1_array;
						$nm1_copy_to                                = $search_engine_client->copy_field_name( $nm1 );
						if ( $nm1 !== $nm1_copy_to ) {
							$this->solarium_document_for_update[ $nm1_copy_to ] = $nm1_array;
						}


						$this->solarium_document_for_update[ sprintf( WpSolrSchema::_FIELD_NAME_FLAT_HIERARCHY, $nm1 ) ]     = $taxonomy_flat_hierarchies;
						$this->solarium_document_for_update[ sprintf( WpSolrSchema::_FIELD_NAME_NON_FLAT_HIERARCHY, $nm1 ) ] = $taxonomy_non_flat_hierarchies;

					}
				}
			}
		}

		// Set categories and custom taxonomies as searchable
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_CATEGORIES ] = $cats;

		// Add custom fields to the document
		$this->set_custom_fields( $search_engine_client, $solarium_document_for_update, $post_to_index );

		if ( isset( $indexing_options['p_custom_fields'] ) && isset( $this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_CUSTOM_FIELDS ] ) ) {

			$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_CONTENT ] .= WPSOLR_AbstractIndexClient::CONTENT_SEPARATOR . implode( ". ", $this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_CUSTOM_FIELDS ] );
		}


		return $solarium_document_for_update;
	}

	/**
	 * Set custom fields to the update document.
	 * HTML and php tags are removed.
	 *
	 * @param WPSOLR_AbstractIndexClient $search_engine_client
	 * @param $solarium_document_for_update
	 * @param \WP_Post $post
	 *
	 * @throws \Exception
	 */
	function set_custom_fields( WPSOLR_AbstractIndexClient $search_engine_client, &$solarium_document_for_update, $post ) {

		$custom_fields = WPSOLR_Service_Container::getOption()->get_option_index_custom_fields();

		if ( count( $custom_fields ) > 0 ) {
			if ( count( $post_custom_fields = get_post_custom( $post->ID ) ) ) {

				// Apply filters on custom fields
				$post_custom_fields = apply_filters( WPSOLR_Events::WPSOLR_FILTER_POST_CUSTOM_FIELDS, $post_custom_fields, $post->ID );

				$existing_custom_fields = isset( $this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_CUSTOM_FIELDS ] )
					? $this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_CUSTOM_FIELDS ]
					: [];

				foreach ( ! empty( $custom_fields[ $post->post_type ] ) ? $custom_fields[ $post->post_type ] : [] as $field_name_with_str_ending ) {

					$field_name = WpSolrSchema::get_field_without_str_ending( $field_name_with_str_ending );

					if ( isset( $post_custom_fields[ $field_name ] ) ) {
						$field = (array) $post_custom_fields[ $field_name ];

						//$field_name = strtolower( str_replace( ' ', '_', $field_name ) );

						// Add custom field array of values
						//$nm1       = $field_name . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING;
						$nm1       = WpSolrSchema::replace_field_name_extension( $field_name_with_str_ending );
						$array_nm1 = [];
						foreach ( $field as $field_value ) {

							$field_value_sanitized = WpSolrSchema::get_sanitized_value( $search_engine_client, $field_name_with_str_ending, $field_value, $post );

							// Only index the field if it has a value.
							if ( ( '0' === $field_value_sanitized ) || ! empty( $field_value_sanitized ) ) {

								array_push( $array_nm1, $field_value_sanitized );

								// Add current custom field values to custom fields search field
								// $field being an array, we add each of it's element
								// Convert values to string, else error in the search engine if number, as a string is expected.
								array_push( $existing_custom_fields, is_array( $field_value_sanitized ) ? $field_value_sanitized : strval( $field_value_sanitized ) );
							}
						}

						$this->solarium_document_for_update[ $nm1 ] = $array_nm1;
						$nm1_copy                                   = $search_engine_client->copy_field_name( $field_name_with_str_ending );
						if ( $nm1 !== $nm1_copy ) {
							$this->solarium_document_for_update[ $nm1_copy ] = $array_nm1;
						}
					}
				}

				if ( count( $existing_custom_fields ) > 0 ) {
					$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_CUSTOM_FIELDS ] = $existing_custom_fields;
				}

			}

		}

	}

	/**
	 * Index a post thumbnail
	 *
	 * @param $solarium_document_for_update
	 * @param $post_id
	 * @param bool $is_in_galaxy
	 *
	 * @return void
	 */
	private
	function index_post_thumbnails(
		&$solarium_document_for_update, $post_id, $is_in_galaxy
	) {

		if ( $is_in_galaxy ) {

			// Master must get thumbnails from the index, as the $post_id is not in local database
			$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ) );
			if ( false !== $thumbnail ) {

				$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_POST_THUMBNAIL_HREF_STR ] = $thumbnail[0];
			}
		}

	}

	/**
	 * Index a post url
	 *
	 * @param $solarium_document_for_update
	 * @param $post_id
	 * @param bool $is_in_galaxy
	 *
	 * @return void
	 */
	private
	function index_post_url(
		&$solarium_document_for_update, $post_id, $is_in_galaxy
	) {

		if ( $is_in_galaxy ) {

			// Master must get urls from the index, as the $post_id is not in local database
			$url = get_permalink( $post_id );
			if ( false !== $url ) {

				$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_POST_HREF_STR ] = $url;
			}
		}
	}

}