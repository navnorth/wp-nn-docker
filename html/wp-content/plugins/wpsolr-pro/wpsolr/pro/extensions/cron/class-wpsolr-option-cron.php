<?php

namespace wpsolr\pro\extensions\cron;

use wpsolr\core\classes\engines\solarium\WPSOLR_IndexSolariumClient;
use wpsolr\core\classes\extensions\indexes\WPSOLR_Option_Indexes;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\models\WPSOLR_Model_Builder;
use wpsolr\core\classes\utilities\WPSOLR_Option;

/**
 * Class WPSOLR_Option_Cron
 * @package wpsolr\pro\extensions\cron
 *
 * Manage crons
 */
class WPSOLR_Option_Cron extends WpSolrExtensions {

	const WPSOLR_PATH_CRON = 'wpsolr-api/json/cron';
	const WPSOLR_USER_CRON = 'wpsolr-cron';

	const COMMAND_CURL = 'curl -X GET -H "Content-type: application/json" -H "Accept: application/json" -u %s:%s "%s/%s/%s"';
	const COMMAND_WGET = 'wget --user=%s --password=%s "%s/%s/%s"';
	const MESSAGE_DURATION_IN_SECONDS = 'duration_in_seconds';
	const MESSAGE_STATUS = 'status';
	const MESSAGE_TEXT = 'message';
	const MESSAGE_STATUS_ERR = 'ERR';
	const MESSAGE_STATUS_OK = 'OK';
	const MESSAGE_INDEXES = 'indexes';
	const MESSAGE_CRON_UUID = 'cron_uuid';
	const MESSAGE_CRON_LABEL = 'cron_label';
	const MESSAGE_CRON_ERROR_IN_ONE_OR_MORE_INDEXES = 'Cron error in one or more indexes';
	const MESSAGE_CRON_SUCCESS_FOR_ALL_INDEXES = 'Cron success for all indexes';
	const MESSAGE_INDEX_UUID = 'index_uuid';
	const MESSAGE_INDEX_LABEL = 'index_label';
	const MESSAGE_NB_DOCUMENTS_INDEXED = 'nb_documents_indexed';
	const MESSAGE_NB_DOCUMENTS_INDEXED_POST_TYPE = 'post_type';
	const MESSAGE_CRON_INDEX_SUCCESS = 'Cron index success';
	const MESSAGE_EMPTY_CRON_UUID = 'Empty cron uuid.';
	const MESSAGE_UNKNOWN_CRON_UUID = 'Unknown cron %s';
	const MESSAGE_THIS_CRON_IS_NOT_PROTECTED_BY_A_PASSWORD_PLEASE_SET_A_PASSWORD = 'This cron is not protected by a password. Please set a password.';
	const MESSAGE_UNKNOWN_USER = 'Unknown user "%s"';
	const MESSAGE_WRONG_PASSWORD = 'Wrong password';
	const MESSAGE_START_DATETIME = 'start_datetime';
	const MESSAGE_DOCUMENTS_DELETED_FIRST = 'documents_deleted_first';
	const NB_CALLS_TO_INDEX = 'nb_calls_to_index';
	const INDEXING_COMPLETE = 'indexing_complete';
	const MODELS_NB_RESULTS = 'models_nb_results';
	const NB_DOCUMENTS = 'nb_documents';

	/** @var array $indexing */
	protected $indexing;

	/**
	 * Constructor
	 * Subscribe to actions/filters
	 **/
	function __construct() {

		$this->indexing = $this->get_container()->get_service_option()->get_option_cron_indexing();

		if ( is_admin() ) {

			// Create rewriting
			add_action( 'init', [ $this, 'wp_action_init' ] );
		}

		// Catch rewriting
		add_action( 'parse_request', [ $this, 'wp_action_parse_request' ] );

		// Rewriting parameters
		add_filter( 'query_vars', [ $this, 'wp_filter_query_vars' ] );

	}

	/**
	 * @param string $cron_uuid
	 * @param string $property_name
	 *
	 * @return string
	 */
	protected function get_cron_property( $cron_uuid, $property_name, $property_default_value = '' ) {

		return ( ! empty( $this->indexing ) && ! empty( $this->indexing[ $cron_uuid ] ) && ! empty( $this->indexing[ $cron_uuid ][ $property_name ] ) )
			? $this->indexing[ $cron_uuid ][ $property_name ]
			: $property_default_value;
	}

	/**
	 * @param string $cron_uuid
	 *
	 * @return string
	 */
	protected function get_cron_password( $cron_uuid ) {
		return $this->get_cron_property( $cron_uuid, WPSOLR_Option::OPTION_CRON_INDEXING_PASSWORD, '' );
	}

	/**
	 * @param string $cron_uuid
	 *
	 * @return string
	 */
	protected function get_cron_label( $cron_uuid ) {
		return $this->get_cron_property( $cron_uuid, WPSOLR_Option::OPTION_CRON_INDEXING_LABEL, '' );
	}

	/**
	 * Return the url to execute the cron with cURL.
	 *
	 * @param string $cron_uuid
	 * @param string $password
	 *
	 * @return string
	 */
	static public function get_command_curl( $cron_uuid, $password ) {
		return sprintf( self::COMMAND_CURL, self::WPSOLR_USER_CRON, $password, home_url(), self::WPSOLR_PATH_CRON, $cron_uuid );
	}


	/**
	 * Return the url to execute the cron with wget.
	 *
	 * @param string $cron_uuid
	 * @param string $password
	 *
	 * @return string
	 */
	static public function get_command_wget( $cron_uuid, $password ) {
		return sprintf( self::COMMAND_WGET, self::WPSOLR_USER_CRON, $password, home_url(), self::WPSOLR_PATH_CRON, $cron_uuid );
	}

	/**
	 * Create rewriting
	 */
	public function wp_action_init() {

		$rules = get_option( 'rewrite_rules' );

		$pattern = sprintf( '%s/([a-zA-z0-9_]+)/?$', self::WPSOLR_PATH_CRON );

		if ( empty( $rules ) || ! isset( $rules[ $pattern ] ) ) {
			// Create the url rewriting
			$this->get_container()->get_service_wp()->add_rewrite_rule( $pattern, sprintf( 'index.php?%s=$matches[1]', self::WPSOLR_PATH_CRON ), 'top' );

			flush_rewrite_rules( false );
		}
	}

	/**
	 * Rewriting parameters
	 *
	 * @param $query_vars
	 *
	 * @return array
	 */
	public function wp_filter_query_vars( $query_vars ) {
		$query_vars[] = self::WPSOLR_PATH_CRON;

		return $query_vars;
	}

	/**
	 * Catch the rewriting
	 * a
	 *
	 * @param \WP $wp
	 */
	public function wp_action_parse_request( &$wp ) {

		if ( array_key_exists( self::WPSOLR_PATH_CRON, $wp->query_vars ) ) {
			// This is a url rewrited

			// Start
			$start = microtime( true );

			$cron_uuid = $wp->query_vars[ self::WPSOLR_PATH_CRON ];

			$message = [
				self::MESSAGE_CRON_UUID      => $cron_uuid,
				self::MESSAGE_CRON_LABEL     => $this->get_cron_label( $cron_uuid ),
				self::MESSAGE_START_DATETIME => current_time( 'mysql' ),
			];

			// Go on now
			try {

				// Authenticate first
				$this->authenticate_cron( $cron_uuid );

				// Execute the cron now
				$results = $this->execute_cron( $cron_uuid );

				if ( false !== array_search( self::MESSAGE_STATUS_ERR, array_column( $results, self::MESSAGE_STATUS ) ) ) {

					$message[ self::MESSAGE_STATUS ] = self::MESSAGE_STATUS_ERR;
					$message[ self::MESSAGE_TEXT ]   = self::MESSAGE_CRON_ERROR_IN_ONE_OR_MORE_INDEXES;

				} else {

					$message[ self::MESSAGE_STATUS ] = self::MESSAGE_STATUS_OK;
					$message[ self::MESSAGE_TEXT ]   = self::MESSAGE_CRON_SUCCESS_FOR_ALL_INDEXES;
				}

				$message[ self::MESSAGE_INDEXES ] = $results;

			} catch ( \Exception $e ) {

				$message[ self::MESSAGE_STATUS ] = self::MESSAGE_STATUS_ERR;
				$message[ self::MESSAGE_TEXT ]   = $e->getMessage();
			}

			// End
			$time_elapsed_secs                            = number_format( microtime( true ) - $start, 2 );
			$message[ self::MESSAGE_DURATION_IN_SECONDS ] = $time_elapsed_secs;

			// Save this cron log
			$this->save_cron_log( $cron_uuid, $message );

			$json_message = wp_json_encode( $message );
			echo $json_message;

			exit();
		}

		return;
	}

	/**
	 * Authenticate a cron
	 *
	 * @param string $cron_uuid
	 *
	 * @throws \Exception
	 */
	protected function authenticate_cron( $cron_uuid ) {

		if ( empty( $cron_uuid ) ) {
			throw new \Exception( self::MESSAGE_EMPTY_CRON_UUID );
		}

		$label = $this->get_cron_label( $cron_uuid );
		if ( empty( $label ) ) {
			throw new \Exception( sprintf( self::MESSAGE_UNKNOWN_CRON_UUID, $cron_uuid ) );
		}

		$password = $this->get_cron_password( $cron_uuid );
		if ( empty( $password ) ) {
			throw new \Exception( self::MESSAGE_THIS_CRON_IS_NOT_PROTECTED_BY_A_PASSWORD_PLEASE_SET_A_PASSWORD );
		}

		$auth_user     = isset( $_SERVER['PHP_AUTH_USER'] ) ? $_SERVER['PHP_AUTH_USER'] : '';
		$auth_password = isset( $_SERVER['PHP_AUTH_PW'] ) ? $_SERVER['PHP_AUTH_PW'] : '';

		if ( empty( $auth_user ) || ( self::WPSOLR_USER_CRON !== $auth_user ) ) {
			throw new \Exception( sprintf( self::MESSAGE_UNKNOWN_USER, $auth_user ) );
		}

		if ( empty( $auth_password ) || ( $password !== $auth_password ) ) {
			throw new \Exception( self::MESSAGE_WRONG_PASSWORD );
		}

		// Authenticated !
	}

	/**
	 * Execute a cron
	 *
	 * @param string $cron_uuid
	 *
	 * @return array
	 */
	protected function execute_cron( $cron_uuid ) {

		$messages       = [];
		$option_indexes = new WPSOLR_Option_Indexes();

		if ( isset( $this->indexing[ $cron_uuid ] ) && isset( $this->indexing[ $cron_uuid ][ self::MESSAGE_INDEXES ] ) ) {

			foreach ( $this->indexing[ $cron_uuid ][ self::MESSAGE_INDEXES ] as $index_uuid => $cron_index ) {

				// Start
				$start = microtime( true );

				$message = [
					self::MESSAGE_INDEX_UUID     => $index_uuid,
					self::MESSAGE_INDEX_LABEL    => $option_indexes->get_index_name( $option_indexes->get_index( $index_uuid ) ),
					self::MESSAGE_START_DATETIME => current_time( 'mysql' ),
				];

				// Go on now
				try {

					// Execute all indexes in cron, in sequence
					$results = $this->execute_cron_index( $cron_uuid, $index_uuid, $cron_index );

					$message[ self::MESSAGE_STATUS ]                  = self::MESSAGE_STATUS_OK;
					$message[ self::MESSAGE_TEXT ]                    = self::MESSAGE_CRON_INDEX_SUCCESS;
					$message[ self::MESSAGE_NB_DOCUMENTS_INDEXED ]    = isset( $results[ self::MESSAGE_NB_DOCUMENTS_INDEXED ] ) ? $results[ self::MESSAGE_NB_DOCUMENTS_INDEXED ] : [];
					$message[ self::MESSAGE_DOCUMENTS_DELETED_FIRST ] = isset( $results[ self::MESSAGE_DOCUMENTS_DELETED_FIRST ] ) ? $results[ self::MESSAGE_DOCUMENTS_DELETED_FIRST ] : false;

				} catch ( \Exception $e ) {

					$message[ self::MESSAGE_STATUS ] = self::MESSAGE_STATUS_ERR;
					$message[ self::MESSAGE_TEXT ]   = $e->getMessage();
				}

				// End
				$time_elapsed_secs                            = number_format( microtime( true ) - $start, 2 );
				$message[ self::MESSAGE_DURATION_IN_SECONDS ] = $time_elapsed_secs;

				// Save this index log in it's cron log
				$this->save_cron_index_log( $cron_uuid, $index_uuid, $message );

				$messages[] = $message;
			}
		}

		return $messages;
	}


	/**
	 * Execute a cron index
	 *
	 * @param string $cron_uuid
	 * @param string $index_uuid
	 * @param array $cron_index
	 *
	 * @return array
	 */
	protected function execute_cron_index( $cron_uuid, $index_uuid, $cron_index ) {

		$index = WPSOLR_IndexSolariumClient::create( $index_uuid );

		// Batch size
		$batch_size = isset( $cron_index[ WPSOLR_Option::OPTION_CRON_BATCH_SIZE ] ) ? intval( $cron_index[ WPSOLR_Option::OPTION_CRON_BATCH_SIZE ] ) : 100;

		// Debug infos displayed on screen ?
		$is_debug_indexing = false;

		$index_post_types = isset( $cron_index[ WPSOLR_Option::OPTION_CRON_INDEX_POST_TYPES ] ) ? array_keys( $cron_index[ WPSOLR_Option::OPTION_CRON_INDEX_POST_TYPES ] ) : [];
		$models           = WPSOLR_Model_Builder::get_model_types( $index_post_types );

		// Delete all the post types ?
		$is_delete_first = isset( $cron_index[ WPSOLR_Option::OPTION_CRON_IS_DELETE_FIRST ] );
		if ( $is_delete_first ) {
			$index->delete_documents( $cron_uuid, $models );
		}

		$is_index = false;
		switch ( $cron_index[ WPSOLR_Option::OPTION_CRON_INDEX_TYPE ] ) {
			case WPSOLR_Option::OPTION_CRON_INDEX_TYPE_FULL:
				$index->reset_documents( $cron_uuid, $models );
				$is_index = true;
				break;

			case WPSOLR_Option::OPTION_CRON_INDEX_TYPE_INCREMENTAL:
				$is_index = true;
				break;
		}

		$models_nb_results = [];
		if ( $is_index ) {

			foreach ( $models as $model ) {
				// Indexing model after model is more efficient than all models in parallel

				$post_type                                                  = $model->get_type();
				$models_nb_results[ $post_type ]                            = [];
				$models_nb_results[ $post_type ][ self::NB_DOCUMENTS ]      = 0;
				$models_nb_results[ $post_type ][ self::NB_CALLS_TO_INDEX ] = 0; // measure nb calls to the index

				$is_indexing_complete = false;
				while ( ! $is_indexing_complete ) {

					// Let's index now
					$res_final = $index->index_data( false, $cron_uuid, [ $model ], $batch_size, null, $is_debug_indexing );

					$is_indexing_complete = $res_final[ self::INDEXING_COMPLETE ];

					// One more call
					$models_nb_results[ $post_type ][ self::NB_CALLS_TO_INDEX ] ++;

					if ( ! empty( $res_final[ self::MODELS_NB_RESULTS ] ) ) {

						$models_nb_results[ $post_type ][ self::NB_DOCUMENTS ] += $res_final[ self::MODELS_NB_RESULTS ][ $post_type ];
					}
				}
			}


		}

		return [
			self::MESSAGE_DOCUMENTS_DELETED_FIRST => $is_delete_first ? $index_post_types : [],
			self::MESSAGE_NB_DOCUMENTS_INDEXED    => $models_nb_results,
		];
	}


	/**
	 * Save a cron index log
	 *
	 * @param string $cron_uuid
	 * @param string $index_uuid
	 * @param array $message
	 */
	private function save_cron_index_log( $cron_uuid, $index_uuid, $message ) {

		if ( ! empty ( $message ) ) {

			$crons = get_option( WPSOLR_Option::OPTION_CRON, [] );

			if ( isset( $crons[ WPSOLR_Option::OPTION_CRON_INDEXING ][ $cron_uuid ] ) && isset( $crons[ WPSOLR_Option::OPTION_CRON_INDEXING ][ $cron_uuid ]['indexes'][ $index_uuid ] ) ) {
				$crons[ WPSOLR_Option::OPTION_CRON_INDEXING ][ $cron_uuid ]['indexes'][ $index_uuid ][ WPSOLR_Option::OPTION_CRON_LOG ] = json_encode( $message, JSON_PRETTY_PRINT );

				update_option( WPSOLR_Option::OPTION_CRON, $crons );
			}
		}

	}

	/**
	 * Save a cron log
	 *
	 * @param string $cron_uuid
	 * @param array $message
	 */
	private function save_cron_log( $cron_uuid, $message ) {

		if ( ! empty ( $message ) ) {

			$crons = get_option( WPSOLR_Option::OPTION_CRON, [] );

			if ( isset( $crons[ WPSOLR_Option::OPTION_CRON_INDEXING ][ $cron_uuid ] ) ) {
				$crons[ WPSOLR_Option::OPTION_CRON_INDEXING ][ $cron_uuid ][ WPSOLR_Option::OPTION_CRON_LOG ] = json_encode( $message, JSON_PRETTY_PRINT );

				update_option( WPSOLR_Option::OPTION_CRON, $crons );
			}
		}

	}

}
