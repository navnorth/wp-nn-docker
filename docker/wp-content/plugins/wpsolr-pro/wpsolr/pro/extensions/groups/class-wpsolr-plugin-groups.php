<?php

namespace wpsolr\pro\extensions\groups;

use wpsolr\core\classes\engines\WPSOLR_AbstractSearchClient;
use wpsolr\core\classes\exceptions\WPSOLR_Exception_Security;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\ui\WPSOLR_Query;
use wpsolr\core\classes\WPSOLR_Events;

/**
 * Class WPSOLR_Plugin_Groups
 * @package wpsolr\pro\extensions\groups
 *
 * Manage authorizations for groups plugin
 * @link https://wordpress.org/plugins/groups/
 * @link http://api.itthinx.com/groups/package-groups.html
 */
class WPSOLR_Plugin_Groups extends WpSolrExtensions {

	const CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES = 'groups-groups_read_post_str';
	const DEFAULT_MESSAGE_NOT_AUTHORIZED = 'Sorry, your profile is not associated whith any group, therefore you are not allowed to see any results.
<br/>Please contact your administrator.';

	// [capability, group]
	private $_user_capabilities_groups;

	private $_extension_groups_options;

	/**
	 * Constructor
	 *
	 * Subscribe to actions
	 */
	function __construct() {

		$this->_extension_groups_options = self::get_option_data( self::EXTENSION_GROUPS );

		add_action( WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY, [ $this, 'set_custom_query' ], 10, 1 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_FIELDS, [
			$this,
			'wpsolr_filter_add_fields',
		], 10, 4 );

		add_filter( WPSOLR_Events::WPSOLR_FILTER_SOLR_RESULTS_DOCUMENT_GROUPS_INFOS, [
			$this,
			'get_groups_of_user_document'
		], 10, 2 );

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
	 *
	 * Add user's capabilities filters to the Solr query.
	 *
	 * @param $parameters array
	 *
	 * @throws \Exception
	 */
	public function set_custom_query( $parameters ) {

		/* @var WPSOLR_Query $wpsolr_query */
		$wpsolr_query = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_WPSOLR_QUERY ];
		/* @var mixed $search_engine_query */
		$search_engine_query = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_QUERY ];
		/* @var WPSOLR_AbstractSearchClient $search_engine_client */
		$search_engine_client = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SOLARIUM_CLIENT ];

		$user = $parameters[ WPSOLR_Events::WPSOLR_ACTION_SOLARIUM_QUERY__PARAM_SEARCH_USER ];

		if ( ! $user ) {
			return;
		}

		$is_users_without_groups_see_all_results          = isset( $this->_extension_groups_options['is_users_without_groups_see_all_results'] );
		$is_result_without_capabilities_seen_by_all_users = isset( $this->_extension_groups_options['is_result_without_capabilities_seen_by_all_users'] );

		$user_capability_and_group_array = $this->get_user_capabilities_and_groups( $user->ID );

		if ( ( count( $user_capability_and_group_array ) === 0 ) && ! $is_users_without_groups_see_all_results ) {

			// No activities for current user, and setup forbid display of any content: not allowed to see any content. Stop here.
			throw new WPSOLR_Exception_Security( isset( $this->_extension_groups_options['message_user_without_groups_shown_no_results'] )
				? $this->_extension_groups_options['message_user_without_groups_shown_no_results']
				: self::DEFAULT_MESSAGE_NOT_AUTHORIZED );
		}

		if ( ( count( $user_capability_and_group_array ) === 0 ) && $is_users_without_groups_see_all_results ) {

			// No activities for current user, and setup authorize display of any content. Stop here.
			return;
		}

		if ( count( $user_capability_and_group_array ) > 0 ) {

			$filter_query_or = [];
			foreach ( $user_capability_and_group_array as $user_capability_and_group ) {
				// Add capability to query field, if not empty.

				if ( ! empty( $user_capability_and_group['capability'] ) ) {
					$filter_query_or[] = $user_capability_and_group['capability'];
				}
			}

			if ( ( ! empty( $filter_query_or ) !== '' ) && $is_result_without_capabilities_seen_by_all_users ) {
				// Authorize documents without capabilities, or with empty capabilities, to be retrieved.

				$search_engine_client->search_engine_client_add_filter_empty_or_in_terms(
					'groups filter post empty or in capabilities',
					self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES,
					$filter_query_or
				);

			} else {

				$search_engine_client->search_engine_client_add_filter_in_terms(
					'groups filter post in capabilities',
					self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES,
					$filter_query_or
				);
			}

		}

	}

	/**
	 * Get all the capabilities that the user has (only user defined).
	 *
	 * @param $user
	 *
	 * @return array array of capabilities and groups
	 */
	public function get_user_capabilities_and_groups( $user_id ) {

		if ( isset( $this->_user_capabilities_groups ) ) {
			// return value in cache
			return $this->_user_capabilities_groups;
		}

		$user_capabilities = [];

		// Fetch current user's groups
		$groups_user = new \Groups_User( $user_id );

		$groups = $groups_user->__get( \Groups_User::CACHE_GROUP );

		if ( ! isset( $groups ) ) {
			return null;
		}

		foreach ( $groups as $group ) {

			if ( ! isset( $group ) ) {
				continue;
			}

			// Fetch capabilities of current user's groups
			$capabilities = $group->__get( \Groups_User::CAPABILITIES );

			if ( ! isset( $capabilities ) ) {
				continue;
			}

			foreach ( $capabilities as $capability ) {

				if ( isset( $capability ) && isset( $capability->capability ) && isset( $capability->capability->capability ) ) {

					$user_capabilities[] = [
						'capability' => $capability->capability->capability,
						'group'      => $group->name
					];

				}
			}

		}

		// Store in cache: this value is used for all documents returned by a Solr query
		$this->_user_capabilities_groups = $user_capabilities;

		return $this->_user_capabilities_groups;
	}

	/**
	 * Get all the capabilities that the user has, including those that are inherited (not user defined).
	 * Ex: add_users, delete_posts, upload_files
	 *
	 * @param $user
	 *
	 * @return array Array of string capabilities
	 */
	public function get_user_deep_capabilities( $user_id ) {

		// Fetch current user's groups
		$groups_user = new \Groups_User( $user_id );

		return $groups_user->capabilities_deep;

	}

	/**
	 * Get groups of user containing at least one capability of document
	 *
	 * @param $user_id
	 * @param $document
	 *
	 * return array[string] Array of groups
	 */
	public function get_groups_of_user_document( $user_id, $document ) {

		$wpsolr_groups_array = [];

		$custom_field_name_storing_post_capabilities = self::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES;
		$document_capabilities_array                 = $document->$custom_field_name_storing_post_capabilities;
		if ( is_array( $document_capabilities_array ) && ( count( $document_capabilities_array ) > 0 ) ) {

			// Calculate groups of this user which owns at least one the document capability
			$user_capabilities_groups = $this->get_user_capabilities_and_groups( $user_id );

			if ( is_array( $user_capabilities_groups ) && ( count( $user_capabilities_groups ) > 0 ) ) {

				foreach ( $user_capabilities_groups as $user_capabilities_group ) {

					// Add group if its capability is in capabilities
					if ( ! ( array_search( $user_capabilities_group['capability'], $document_capabilities_array, true ) === false ) ) {

						// Use associative to prevent duplicates
						$wpsolr_groups_array[ $user_capabilities_group['group'] ] = $user_capabilities_group['group'];

					}
				}
			}
		}

		// Message to display on every line
		$message = $this->_extension_groups_options['message_result_capability_matches_user_group'];
		$message = str_replace( '%1', implode( ',', $wpsolr_groups_array ), $message );

		// Get values from associative
		$wpsolr_groups_array = array_values( $wpsolr_groups_array );

		return [ 'groups' => $wpsolr_groups_array, 'message' => $message ];

	}

}
