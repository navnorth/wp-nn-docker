<?php

namespace wpsolr\pro\extensions\s2member;

use wpsolr\core\classes\engines\WPSOLR_AbstractSearchClient;
use wpsolr\core\classes\exceptions\WPSOLR_Exception_Security;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\ui\WPSOLR_Query;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WpSolrS2Member
 * @package wpsolr\pro\extensions\groups
 *
 * Manage authorizations for s2member plugin
 * @link https://wordpress.org/plugins/s2member/
 * @link http://www.s2member.com/
 */
class WPSOLR_Plugin_S2Member extends WpSolrExtensions {

	const CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES = 's2member_ccaps_req_str';
	const CUSTOM_FIELD_NAME_STORING_POST_LEVEL = 's2member_level_req';
	const DEFAULT_MESSAGE_NOT_AUTHORIZED = 'Sorry, your profile is not associated whith any level/custom capabilities, therefore you are not allowed to see any results.
<br/>Please contact your administrator.';

	// s2Member's prefix for roles and capabilities
	const PREFIX_S2MEMBER_ROLE_OR_CAPABILITY = 'access_s2member_';
	const PREFIX_S2MEMBER_ROLE = 'level';
	const PREFIX_S2MEMBER_CAPABILITY = 'ccap_';

	/** @var array */
	protected $_extension_s2member_options;

	/**
	 * Constructor
	 *
	 * Subscribe to actions
	 */
	function __construct() {

		$this->_extension_s2member_options = self::get_option_data( self::EXTENSION_S2MEMBER, [] );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_INDEX_CUSTOM_FIELDS, [
			$this,
			'get_index_custom_fields',
		], 10, 1 );

		add_action( WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY, [ $this, 'set_custom_query' ], 10, 1 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_POST_CUSTOM_FIELDS, [
			$this,
			'filter_custom_fields'
		], 10, 2 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_FIELDS, [
			$this,
			'wpsolr_filter_add_fields',
		], 10, 4 );

	}

	/**
	 *
	 * Add levels and capabilities filters to the Solr query.
	 *
	 * We will use the user's levels and capabilities as filters
	 * Every post has been indexed with it's level and capabilities
	 * Rules:
	 * <a> (all posts, unsecured user) if option 'unsecured users can see all posts' is set
	 * <b> (posts without security, any user) if option 'unsecured posts can be seen' is set
	 * <c> (post with capabilities but no level, users with a matching capability)
	 * <d> (posts with level but no capabilities, users with a higher level)
	 * <e> (posts with level and capabilities, users with a higher level and a matching capability)
	 *
	 * Examples:
	 * <!d> (Post level1, User level0) => user level is not higher than post level
	 * <d> (Post level1, User level1)  => user level is higher than post level
	 * <d> (Post level1, User level2) => user level is higher than post level
	 * <!c> (Post capability1, User level0) => user has not capability1
	 * <c> (Post capability1, User level0 capability1) => user has capability1
	 * <!e> (Post level1 capability1, User level0 capability1) => user level is not higher than post level
	 * <!e> (Post level1 capability1, User level1 capability2) => user has not capability1
	 * <e> (Post level1 capability1, User level2 capability1 capability2) => user level is higher than post level, user has capability1
	 * <!e> (Post level1 capability1, User level0 capability1 and capability2) => user level is not higher than post level
	 *
	 * @param $parameters array
	 *
	 * @throws \Exception
	 */
	public function set_custom_query( $parameters ) {

		$query = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_QUERY ];

		/* @var WPSOLR_AbstractSearchClient $search_engine_client */
		$search_engine_client = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_CLIENT ];

		/** @var \WP_User $user */
		$user = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SEARCH_USER ];

		if ( ! $user ) {
			return;
		}

		$is_users_without_capabilities_see_all_results    = isset( $this->_extension_s2member_options['is_users_without_capabilities_see_all_results'] );
		$is_result_without_capabilities_seen_by_all_users = isset( $this->_extension_s2member_options['is_result_without_capabilities_seen_by_all_users'] );

		// Find s2member roles (levels) and capabilities in user's roles capabilities
		$custom_capabilities = [];
		$level_int           = - 1;
		foreach ( $user->get_role_caps() as $key => $value ) {
			if ( substr_compare( $key, self::PREFIX_S2MEMBER_ROLE_OR_CAPABILITY, 0, strlen( self::PREFIX_S2MEMBER_ROLE_OR_CAPABILITY ) ) === 0 ) {
				if ( substr_compare( $key, self::PREFIX_S2MEMBER_ROLE_OR_CAPABILITY, 0, strlen( self::PREFIX_S2MEMBER_ROLE_OR_CAPABILITY ) ) === 0 ) {

					// Remove prefix
					$key = str_replace( self::PREFIX_S2MEMBER_ROLE_OR_CAPABILITY . self::PREFIX_S2MEMBER_CAPABILITY, '', $key );
					$key = str_replace( self::PREFIX_S2MEMBER_ROLE_OR_CAPABILITY . self::PREFIX_S2MEMBER_ROLE, '', $key );

					if ( preg_match( '/\d+$/', $key ) ) {
						// User's levels, as 'level0', 'level10'
						// Get max of user's levels, as an integer

						$level_int = max( $level_int, intval( $key ) );

					} else {
						// Others: custom capabilities
						$custom_capabilities[] = $key;
					}
				}
			}
		}

		if ( ! $is_result_without_capabilities_seen_by_all_users && ( ( ( count( $custom_capabilities ) === 0 ) && ( $level_int < 0 ) ) && ! $is_users_without_capabilities_see_all_results ) ) {

			// No activities for current user, and setup forbid display of any content: not allowed to see any content. Stop here.
			throw new WPSOLR_Exception_Security( isset( $this->_extension_s2member_options['message_user_without_capabilities_shown_no_results'] )
				? $this->_extension_s2member_options['message_user_without_capabilities_shown_no_results']
				: self::DEFAULT_MESSAGE_NOT_AUTHORIZED );
		}

		if ( ( count( $custom_capabilities ) === 0 ) && $is_users_without_capabilities_see_all_results ) {

			// No activities for current user, and setup authorize display of any content. Stop here.
			return;
		}

		if ( $level_int >= 0 ) {
			// User's levels, as 'level0', 'level10'
			// If user's level is n, it can see posts with level 0..n => OR (0..n)

			//$filter_query_levels = sprintf( '(%s:(%s))', self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES, implode( self::_SOLR_OR_OPERATOR, range( 0, $level_int ) ) );
			$filter_query_levels = $search_engine_client->search_engine_client_create_filter_in_terms(
				self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES,
				range( 0, $level_int )
			);

		}

		if ( ! empty( $custom_capabilities ) ) {

			//$filter_query_capabilities_str = sprintf( '(%s:(%s))', self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES, implode( self::_SOLR_OR_OPERATOR, $custom_capabilities ) );
			$filter_query_capabilities = $search_engine_client->search_engine_client_create_filter_in_terms(
				self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES,
				$custom_capabilities
			);

		} else {
			// This user has no capabilities. Must insure it does not see a post with any capability.
			// (Ensure the field contains only level numbers)
			//$filter_query_capabilities = sprintf( '-%s:[a TO z]', self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES );
			$filter_query_capabilities = $search_engine_client->search_engine_client_create_filter_only_numbers(
				self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES
			);

		}

		if ( $is_result_without_capabilities_seen_by_all_users ) {
			// Authorize documents without capabilities, or with empty capabilities, to be retrieved.
			//$filter_query_no_capabilities_str = '( ' . ' *:* -' . self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES . ':' . '*' . ' )'; // capability empty
			$filter_query_no_capabilities = $search_engine_client->search_engine_client_create_filter_no_values(
				self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES
			);
		}


		/**
		 * Build the final query
		 */
		if ( empty( $filter_query_levels ) ) {

			//$filter_query_str = sprintf( '(%s)', $filter_query_capabilities->getQuery() );
			$filter_query = $filter_query_capabilities;

		} else {

			//$filter_query = sprintf( '(%s %s %s)', $filter_query_capabilities->getQuery(), self::_SOLR_AND_OPERATOR, $filter_query_levels->getQuery() );
			$filter_query = $search_engine_client->search_engine_client_create_and( [
				$filter_query_capabilities,
				$filter_query_levels
			] );
		}

		if ( ! empty( $filter_query_no_capabilities ) ) {
			//$filter_query = sprintf( '(%s %s %s)', $filter_query, self::_SOLR_OR_OPERATOR, $filter_query_no_capabilities->getQuery() );
			$filter_query = $search_engine_client->search_engine_client_create_or( [
				$filter_query,
				$filter_query_no_capabilities
			] );
		}

		// Add query filter
		$search_engine_client->search_engine_client_add_filter( 's2member levels or capabilities', $filter_query );
	}


	/**
	 * Filter custom field self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES of a document
	 *
	 * s2Member serialize capabilities of the post
	 * ex: a:2:{i:0;s:25:"capability1";i:1;s:25:"capability2";}
	 *
	 * We must unserialize it
	 * ex: ['capability1', 'capability2']
	 *
	 * @param $custom_fields array Serialized array of capabilities
	 *
	 * return array Custom fields with unserialized array of capabilities in self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES
	 */
	public function filter_custom_fields( $custom_fields, $post_id ) {

		// Remove the WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING at the end of the custom field
		$custom_field_name = str_replace( WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, '', self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES );

		if ( $custom_fields && count( $custom_fields ) > 0 ) {

			if ( isset( $custom_fields[ $custom_field_name ] ) ) {
				$serialized_custom_field_array = $custom_fields[ $custom_field_name ];
				if ( $serialized_custom_field_array ) {
					// Field is serialiezd by s2Member; unserialize it before indexing

					$custom_fields[ $custom_field_name ] = unserialize( $serialized_custom_field_array[0] );
				}
			}
		}


		/*
			is_protected_by_s2member returns, after debugging:
		- false
		- or [s2member_level_req => i] is the level i (0, 1, 2, 3, 4) is set on the post
		- or [s2member_ccap_req => capability1] if capability1 is the first capability on the post
		Very different from what is described in the documentation !!!
		*/

		// levels used as filters too
		$protections = is_protected_by_s2member( $post_id );
		$level       = null;
		if ( is_array( $protections ) ) {

			if ( isset( $protections[ self::CUSTOM_FIELD_NAME_STORING_POST_LEVEL ] ) ) {
				// level is an integer >= 0
				$level = $protections[ self::CUSTOM_FIELD_NAME_STORING_POST_LEVEL ];

				// Add level to custom fields, as it should have been done
				$custom_fields[ $custom_field_name ][] = $level;
			}

		}


		return $custom_fields;
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
		$fields[] = self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES;

		return $fields;
	}


	/**
	 * Update custom fields list to be indexed
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

		$field_without_str = str_replace( WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, '', self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES );
		if ( ! in_array( $field_without_str, $custom_fields, true ) ) {
			array_push( $custom_fields, str_replace( WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, '', self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES ) );
		}

		return $custom_fields;
	}

}