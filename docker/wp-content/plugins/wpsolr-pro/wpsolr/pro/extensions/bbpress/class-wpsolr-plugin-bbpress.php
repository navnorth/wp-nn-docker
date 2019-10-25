<?php

namespace wpsolr\pro\extensions\bbpress;

use WP_Query;
use wpsolr\core\classes\engines\WPSOLR_AbstractIndexClient;
use wpsolr\core\classes\engines\WPSOLR_AbstractSearchClient;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\ui\WPSOLR_Query_Parameters;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WPSOLR_Plugin_BbPress
 * @package wpsolr\pro\extensions\bbpress
 *
 * Manage WPSOLR_Plugin_BbPress plugin
 */
class WPSOLR_Plugin_BbPress extends WpSolrExtensions {

	/** @var string */
	protected $custom_field_bbpress_forum_id_str;

	/** @var string */
	protected $custom_field_bbpress_forum_id;

	/**
	 * Forum visibility
	 */
	const CUSTOM_FIELD_BBPRESS_FORUM_ID = '_bbp_forum_id';
	const CUSTOM_FIELD_VISIBILITY = '_visibility';
	const VISIBILITY_CATALOG_AND_SEARCH = 'visible';
	const VISIBILITY_CATALOG = 'catalog';
	const VISIBILITY_SEARCH = 'search';

	/**
	 * Constructor
	 * Subscribe to actions
	 */

	function __construct() {

		$this->init_default_events();

		$this->custom_field_bbpress_forum_id     = self::CUSTOM_FIELD_BBPRESS_FORUM_ID;
		$this->custom_field_bbpress_forum_id_str = self::CUSTOM_FIELD_BBPRESS_FORUM_ID . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING;

		/**
		 * bbp events
		 */
		add_filter( 'bbp_include_all_forums', [
			$this,
			'is_bbp_include_all_forums',
		], 10, 2 );

		add_filter( 'bbp_after_has_search_results_parse_args', [
			$this,
			'bbp_after_has_search_results_parse_args',
		], 10, 1 );


		/**
		 * WPSOLR events
		 */
		add_filter( WPSOLR_Events::WPSOLR_FILTER_SOLARIUM_DOCUMENT_FOR_UPDATE, [
			$this,
			'add_fields_to_document_for_update',
		], 10, 5 );

		add_action( WPSOLR_Events::WPSOLR_FILTER_IS_REPLACE_BY_WPSOLR_QUERY, [
			$this,
			'wpsolr_filter_is_replace_by_wpsolr_query',
		], 10, 1 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_QUERY_RESULTS_GET_POSTS_ARGUMENTS, [
			$this,
			'wpsolr_filter_query_results_get_posts_arguments',
		], 10, 1 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_POST_STATUSES_TO_FILTER_OUT, [
			$this,
			'get_post_statuses_to_filter_out',
		], 10, 1 );

		/**
		 * WPSOLR events
		 */
		add_filter( WPSOLR_Events::WPSOLR_FILTER_INDEX_CUSTOM_FIELDS, [
			$this,
			'get_index_custom_fields',
		], 10, 2 );

		add_action( WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY, [
			$this,
			'wpsolr_action_query',
		], 10, 1 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_POST_STATUSES_TO_INDEX, [
			$this,
			'filter_post_statuses_to_index',
		], 10, 2 );

	}

	/**
	 * Add forum id to topic/reply when empty. It happens whhen creation from front-end
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

		$field_name = $this->custom_field_bbpress_forum_id_str;

		if ( empty( $document_for_update[ $field_name ] ) ) {

			switch ( $post->post_type ) {
				case bbp_get_topic_post_type():
					$document_for_update[ $field_name ] = $post->post_parent;
					break;

				case bbp_get_reply_post_type():
					$document_for_update[ $field_name ] = bbp_get_topic_forum_id( $post->post_parent );
					break;
			}

		}

		return $document_for_update;
	}

	/**
	 *
	 * Replace WP query by a WPSOLR query when the current WP Query is a bbPress search.
	 *
	 * @param bool $is_replace_by_wpsolr_query
	 *
	 * @return bool
	 */
	public function wpsolr_filter_is_replace_by_wpsolr_query( $is_replace_by_wpsolr_query ) {
		$result = bbp_is_search() || bbp_is_search_results();

		return $result;
	}

	/**
	 * Replace 'post_status' => 'any', else forums with status 'hidden' are filtered out by WP.
	 *
	 * @param array $arguments
	 *
	 * @return array
	 */
	function wpsolr_filter_query_results_get_posts_arguments( $arguments ) {

		$arguments['post_status'] = [
			bbp_get_public_status_id(),
			bbp_get_closed_status_id(),
			bbp_get_private_status_id(),
			bbp_get_hidden_status_id(),
		];

		return $arguments;
	}


	/**
	 * No need to filter statuses. They are filtered already in the query.
	 *
	 * @param string[] $post_statuses_to_filter_out
	 *
	 * @return string[]
	 */
	function get_post_statuses_to_filter_out( $post_statuses_to_filter_out ) {

		return [];
	}


	/**
	 * @inheritdoc
	 */
	protected function get_default_custom_fields() {

		return [
			$this->custom_field_bbpress_forum_id => [
				self::_FIELD_POST_TYPES                                                   => [
					bbp_get_topic_post_type(),
					bbp_get_reply_post_type()
				],
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE               => WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
				WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION => WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD
			],
		];

	}

	/**
	 * @inheritdoc
	 */
	protected function get_default_post_types() {

		return [ bbp_get_forum_post_type(), bbp_get_topic_post_type(), bbp_get_reply_post_type() ];

	}

	/**
	 * Add the forum id to custom fields list to be indexed
	 *
	 * @param string[] $custom_fields
	 * @param string $model_type
	 *
	 * @return string[]
	 */
	function get_index_custom_fields( $custom_fields, $model_type ) {

		switch ( $model_type ) {
			case bbp_get_topic_post_type():
			case bbp_get_reply_post_type():

				if ( ! isset( $custom_fields ) ) {
					$custom_fields = [];
				}
				if ( ! in_array( $this->custom_field_bbpress_forum_id, $custom_fields, true ) ) {
					array_push( $custom_fields, $this->custom_field_bbpress_forum_id );
				}

				break;
		}

		return $custom_fields;
	}

	/**
	 * Authorize all forums appear in WPSOLR search results. Else, public forums are filtered out by bbPress with un-logged visitors.
	 *
	 * @param bool $current_value
	 * @param WP_Query $posts_query
	 *
	 * @return bool
	 */
	public function is_bbp_include_all_forums( $current_value, $posts_query ) {
		return ! empty( $posts_query->query['is_wpsolr'] );
	}

	/**
	 * Return post status valid for forums (we need to also index non-published status: private and hidden)
	 *
	 * @param string[] $post_statuses
	 * @param string $post_type
	 *
	 * @return string[]
	 */
	public function filter_post_statuses_to_index( array $post_statuses, $post_type ) {

		switch ( $post_type ) {
			case bbp_get_forum_post_type():
				// Add all forum statuses to indexable statuses
				return array_keys( bbp_get_forum_visibilities() );
				break;

			case bbp_get_topic_post_type():
			case bbp_get_reply_post_type():
				// Add all topic/reply statuses to indexable statuses
				$statuses = [
					bbp_get_public_status_id(),
					bbp_get_closed_status_id(),
				];

				return $statuses;
				break;

		}

		// Default statuses.
		return $post_statuses;
	}

	/**
	 *
	 * Add a filter on order post type.
	 *
	 * @param array $parameters
	 *
	 */
	public function wpsolr_action_query( $parameters ) {

		/* @var WPSOLR_AbstractSearchClient $search_engine_client */
		$search_engine_client = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_CLIENT ];

		/**
		 * Ensure topic/reply forum id is set. It could happen, and we don't want them to be shown
		 */
		$filter_forum_or_topic_reply_with_a_forum_id = $search_engine_client->search_engine_client_create_or(
			[
				$search_engine_client->search_engine_client_create_filter_exists( WpSolrSchema::_FIELD_NAME_POST_PARENT_I ),
				$search_engine_client->search_engine_client_create_filter_in_terms(
					WpSolrSchema::_FIELD_NAME_TYPE,
					[ bbp_get_forum_post_type() ]
				)
			]
		);
		$search_engine_client->search_engine_client_add_filter( 'bbpress is forum or topic/reply with a parent forum id', $filter_forum_or_topic_reply_with_a_forum_id );


		// bbPress roles keymaster and moderator
		$is_user_can_moderate = current_user_can( 'moderate' );
		$is_user_logged       = ! empty( bbp_get_current_user_id() );
		if ( ! $is_user_can_moderate ) {
			// User cannot see all forums results

			$visible_forum_invisible_ids = bbp_get_hidden_forum_ids();
			if ( ! $is_user_logged ) {
				$visible_forum_invisible_ids = array_merge( $visible_forum_invisible_ids, bbp_get_private_forum_ids() );
			}

			if ( ! empty( $visible_forum_invisible_ids ) ) {

				$search_engine_client->search_engine_client_add_filter_not_in_terms(
					'bbpress visibility parent forum',
					$this->custom_field_bbpress_forum_id_str,
					$visible_forum_invisible_ids
				);

				$search_engine_client->search_engine_client_add_filter_not_in_terms(
					'bbpress visibility forum',
					WpSolrSchema::_FIELD_NAME_PID,
					$visible_forum_invisible_ids
				);

			}
		}

		$filter_statuses = [];

		/**
		 * Forum/topic/reply status is open or closed
		 */
		$filter_statuses[] = $search_engine_client->search_engine_client_create_filter_in_terms(
			WpSolrSchema::_FIELD_NAME_STATUS_S,
			[ bbp_get_public_status_id(), bbp_get_closed_status_id() ]
		);

		if ( $is_user_logged ) {
			/**
			 * Forum author is user and status is private/hidden
			 */
			$filter_statuses[] = $search_engine_client->search_engine_client_create_and(
				[
					$search_engine_client->search_engine_client_create_filter_in_terms(
						WpSolrSchema::_FIELD_NAME_STATUS_S,
						[ bbp_get_private_status_id(), bbp_get_hidden_status_id() ]
					),
					$search_engine_client->search_engine_client_create_filter_in_terms(
						WpSolrSchema::_FIELD_NAME_AUTHOR_ID_S,
						[ bbp_get_current_user_id() ]
					)
				]
			);
		}

		$filter_statuses_or = $search_engine_client->search_engine_client_create_or( $filter_statuses );
		$search_engine_client->search_engine_client_add_filter( 'bbpress status publish/closed or private/hidden author', $filter_statuses_or );

	}

	/**
	 * Filter bbPress arguments
	 *
	 * @param $bbp_args
	 *
	 * @return mixed
	 */
	public function bbp_after_has_search_results_parse_args( $bbp_args ) {

		// Execute WPSOLR query instead of bbPress query
		$bbp                                = bbpress();
		$bbp->search_query                  = WPSOLR_Query_Parameters::CreateQuery();
		$bbp->search_query->query_vars['s'] = empty( $bbp_args['s'] ) ? '' : $bbp_args['s'];
		$bbp->search_query->get_posts();

		// Remove the 's' parameter, to prevent bbPress executing it's own wp_query
		if ( ! empty( $bbp_args['s'] ) ) {
			unset( $bbp_args['s'] );
		}

		return $bbp_args;
	}

}
