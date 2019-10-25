<?php

namespace wpsolr\core\classes\engines;

use wpsolr\core\classes\engines\elastica\WPSOLR_IndexElasticaClient;
use wpsolr\core\classes\engines\solarium\WPSOLR_IndexSolariumClient;
use wpsolr\core\classes\exceptions\WPSOLR_Exception_Locking;
use wpsolr\core\classes\extensions\indexes\WPSOLR_Option_Indexes;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\models\WPSOLR_Model_Builder;
use wpsolr\core\classes\models\WPSOLR_Model_Type_Abstract;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;

/**
 * Class WPSOLR_AbstractIndexClient
 * @package wpsolr\core\classes\engines
 */
abstract class WPSOLR_AbstractIndexClient extends WPSOLR_AbstractEngineClient {


	// Posts table name
	const CONTENT_SEPARATOR = ' ';

	const SQL_DATE_NULL = '1000-01-01 00:00:00';
	const MAIN_SQL_LOOP = /** @lang text */
		'SELECT %s FROM %s %s WHERE %s ORDER BY %s %s';

	protected $solr_indexing_options;

	protected $last_post_infos_to_start = [
		'date' => self::SQL_DATE_NULL,
		'ID'   => '0',
	];
	const MODEL_LAST_POST_DATE_INDEXED = 'solr_last_post_date_indexed';

	const STOP_INDEXING_ID = 'action_stop_indexing';

	/**
	 * Use Tika to extract a file content.
	 *
	 * @param $file
	 *
	 * @return string
	 */
	abstract protected function search_engine_client_extract_document_content( $file );

	/**
	 * @return array
	 */
	public function get_search_engine_indexing_options() {
		return $this->solr_indexing_options;
	}


	/**
	 * Execute a solarium query. Retry 2 times if an error occurs.
	 *
	 * @param $search_engine_client
	 * @param $update_query
	 *
	 * @return mixed
	 */
	protected function execute( $search_engine_client, $update_query ) {


		for ( $i = 0; ; $i ++ ) {

			try {

				$result = $this->search_engine_client_execute( $search_engine_client, $update_query );

				return $result;

			} catch ( \Exception $e ) {

				// Catch error here, to retry in next loop, or throw error after enough retries.
				if ( $i >= 3 ) {
					throw $e;
				}

				// Sleep 1 second before retrying
				sleep( 1 );
			}

		}

	}


	/**
	 * Retrieve the Solr index for a post (usefull for multi languages extensions).
	 *
	 * @param $post
	 *
	 * @return WPSOLR_IndexSolariumClient
	 */
	static function create_from_post( $post ) {

		// Get the current post language
		$post_language = apply_filters( WPSOLR_Events::WPSOLR_FILTER_POST_LANGUAGE, null, $post );

		return static::create( null, $post_language );
	}

	/**
	 * @param null $solr_index_indice
	 * @param null $post_language
	 *
	 * @return WPSOLR_IndexSolariumClient|WPSOLR_IndexElasticaClient
	 */
	static function create( $solr_index_indice = null, $post_language = null ) {

		// Build Solarium config from the default indexing Solr index
		WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::OPTION_INDEXES, true );
		$options_indexes = new WPSOLR_Option_Indexes();
		$config          = $options_indexes->build_config( $solr_index_indice, $post_language, self::DEFAULT_SEARCH_ENGINE_TIMEOUT_IN_SECOND );

		switch ( ! empty( $config['index_engine'] ) ? $config['index_engine'] : self::ENGINE_SOLR ) {

			case self::ENGINE_ELASTICSEARCH:
				return new WPSOLR_IndexElasticaClient( $config, $solr_index_indice, $post_language );
				break;

			default:
				return new WPSOLR_IndexSolariumClient( $config, $solr_index_indice, $post_language );
				break;

		}
	}

	/**
	 * WPSOLR_AbstractIndexClient constructor.
	 *
	 * @param $config
	 * @param null $solr_index_indice
	 * @param null $language_code
	 */
	public function __construct( $config, $solr_index_indice = null, $language_code = null ) {

		$this->init( $config );

		$path = plugin_dir_path( __FILE__ ) . '../../vendor/autoload.php';
		require_once $path;

		// Load options
		$this->solr_indexing_options = WPSOLR_Service_Container::getOption()->get_option_index();

		$this->index_indice = $solr_index_indice;

		$options_indexes = new WPSOLR_Option_Indexes();
		$this->index     = $options_indexes->get_index( $solr_index_indice );

		$this->search_engine_client = $this->create_search_engine_client( $config );
	}


	/**
	 * Delete all documents for some post types
	 *
	 * @param string[] $post_types
	 * @param string $site_id
	 *
	 * @return
	 */
	abstract protected function search_engine_client_delete_all_documents( $post_types = null, $site_id = '' );

	/**
	 * Delete all documents for some models
	 *
	 * @param string $process_id
	 * @param WPSOLR_Model_Type_Abstract[] $models
	 *
	 * @throws \Exception
	 */
	public
	function delete_documents(
		$process_id, $models = null
	) {

		// Reset docs first (and lock models)
		if ( is_null( $models ) ) {
			$this->reset_documents( $process_id, $this->models );
		} else {
			$this->reset_documents( $process_id, $models );
		}

		$site_id = '';
		if ( $this->is_in_galaxy ) {
			// Delete only current site content

			$site_id = $this->galaxy_slave_filter_value;
		}

		// Delete all content

		if ( is_null( $models ) ) {

			$this->search_engine_client_delete_all_documents( null, $site_id );
			$this->unlock_models( $process_id, $this->models );

		} else {

			$this->search_engine_client_delete_all_documents( $this->get_models_post_types( $models ), $site_id );
			$this->unlock_models( $process_id, $models );
		}

	}

	/**
	 * Get post types for some models
	 *
	 * @param WPSOLR_Model_Type_Abstract[] $models
	 *
	 * @return string[] post types
	 */
	protected function get_models_post_types( $models ) {


		$results = [];

		if ( ! is_null( $models ) ) {
			foreach ( $models as $model ) {
				$results[] = $model->get_type();
			}
		}

		return $results;
	}

	/**
	 * @param string $process_id
	 * @param WPSOLR_Model_Type_Abstract[] $models
	 */
	public
	function reset_documents(
		$process_id, $models = null
	) {

		if ( is_null( $models ) ) {
			$models = $this->get_models();
		}


		if ( is_null( $models ) ) {
			throw new \Exception( 'WPSOLR: reset on empty models.' );
		}

		// Lock models
		$this->lock_models( $process_id, $models );

		// Store 0 in # of index documents
		self::set_index_indice_option_value( $models, 'solr_docs', 0 );

		// Reset last indexed post date
		self::reset_last_post_date_indexed( $models );

		// Update nb of documents updated/added
		self::set_index_indice_option_value( $models, 'solr_docs_added_or_updated_last_operation', - 1 );

	}

	/**
	 * How many documents were updated/added during last indexing operation
	 *
	 * @return int
	 */
	public
	function get_count_documents() {

		$nb_documents = $this->search_engine_client_get_count_document( $this->is_galaxy_slave ? $this->galaxy_slave_filter_value : '' );

		// Store 0 in # of index documents
		self::set_index_indice_option_value( null, 'solr_docs', $nb_documents );

		return $nb_documents;

	}

	/**
	 * Delete a document.
	 *
	 * @param string $document_id
	 *
	 */
	abstract protected function search_engine_client_delete_document( $document_id );

	/**
	 * @param \WP_Post $post
	 */
	public function delete_document( $post ) {

		$this->search_engine_client_delete_document( $this->generate_unique_post_id( $post->ID ) );
	}

	/**
	 * @param WPSOLR_Model_Type_Abstract $model
	 *
	 * @return array
	 */
	public function get_last_post_date_indexed( WPSOLR_Model_Type_Abstract $model ) {

		$result = $this->get_index_indice_option_value( $model, self::MODEL_LAST_POST_DATE_INDEXED, $this->last_post_infos_to_start );

		return $result;
	}

	/**
	 * @param WPSOLR_Model_Type_Abstract[] $models
	 *
	 * @return mixed
	 */
	public function reset_last_post_date_indexed( $models ) {

		return $this->set_index_indice_option_value( $models, self::MODEL_LAST_POST_DATE_INDEXED, $this->last_post_infos_to_start );
	}

	/**
	 * @param WPSOLR_Model_Type_Abstract $model
	 * @param $option_value
	 *
	 * @return mixed
	 */
	public function set_last_post_date_indexed( WPSOLR_Model_Type_Abstract $model, $option_value ) {

		return $this->set_index_indice_option_value( [ $model ], self::MODEL_LAST_POST_DATE_INDEXED, $option_value );
	}

	/**
	 * Lock one model with a process
	 *
	 * @param string $process_id
	 * @param WPSOLR_Model_Type_Abstract $model
	 *
	 * @throws \Exception
	 *
	 */
	public function lock_model( $process_id, WPSOLR_Model_Type_Abstract $model ) {

		$locked_post_types = WPSOLR_Service_Container::getOption()->get_option_locking_index_models( $this->index_indice );

		if ( ! empty( $locked_post_types[ $model->get_type() ] ) && ( $locked_post_types[ $model->get_type() ] !== $process_id ) ) {
			// This process tries to lock a post type already locked by another process.

			$locking_process_id = $locked_post_types[ $model->get_type() ];
			if ( self::STOP_INDEXING_ID === $locking_process_id ) {
				// Stop now
				$this->unlock_process( self::STOP_INDEXING_ID );
				throw new WPSOLR_Exception_Locking( "Indexing stopped as requested, while indexing {$model->get_type()} of index {$this->config['index_label']}" );
			}

			$crons         = WPSOLR_Service_Container::getOption()->get_option_cron_indexing();
			$process_label = ( isset( $crons[ $locking_process_id ] ) && isset( $crons[ $locking_process_id ]['label'] ) ) ? $crons[ $locking_process_id ]['label'] : $locking_process_id;

			throw new WPSOLR_Exception_Locking( "{$process_label} is already indexing post type {$model->get_type()} of index {$this->config['index_label']}" );
		}

		$this->set_index_indice_option_value( [ $model ], WPSOLR_Option::OPTION_LOCKING, $process_id );
	}


	/**
	 * Lock models with a process
	 *
	 * @param string $process_id
	 * @param WPSOLR_Model_Type_Abstract[] $models
	 *
	 * @throws \Exception
	 *
	 */
	public function lock_models( $process_id, $models ) {

		foreach ( $models as $model ) {
			$this->lock_model( $process_id, $model );
		}
	}

	/**
	 * Unlock one model
	 *
	 * @param string $process_id
	 * @param WPSOLR_Model_Type_Abstract $model
	 *
	 */
	public function unlock_model( $process_id, WPSOLR_Model_Type_Abstract $model ) {

		// Release the model lock
		$this->set_index_indice_option_value( [ $model ], WPSOLR_Option::OPTION_LOCKING, '' );
	}

	/**
	 * Unlock models
	 *
	 * @param string $process_id
	 * @param WPSOLR_Model_Type_Abstract[] $models
	 *
	 */
	public function unlock_models( $process_id, $models ) {

		// Release the model lock
		$this->set_index_indice_option_value( $models, WPSOLR_Option::OPTION_LOCKING, '' );
	}

	/**
	 * Unlock all the models
	 */
	public function unlock_all_models() {

		delete_option( WPSOLR_Option::OPTION_LOCKING );
	}

	/**
	 * Is a cron locked ?
	 *
	 * @param $cron_uuid
	 */
	static function is_locked( $process_id ) {

		$lockings = WPSOLR_Service_Container::getOption()->get_option_locking();

		foreach ( $lockings as $index_uuid => $locking ) {
			if ( array_search( $process_id, $locking, true ) ) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Unlock a process
	 *
	 * @param $cron_uuid
	 */
	static function unlock_process( $process_id ) {

		$lockings = WPSOLR_Service_Container::getOption()->get_option_locking();

		foreach ( $lockings as $index_uuid => &$locking ) {
			foreach ( $locking as $post_type => $locking_process_id ) {
				if ( $process_id === $locking_process_id ) {
					$locking[ $post_type ] = ( self::STOP_INDEXING_ID === $process_id ) ? '' : self::STOP_INDEXING_ID;
				}
			}
		}

		update_option( WPSOLR_Option::OPTION_LOCKING, $lockings );
	}

	/**
	 * @param WPSOLR_Model_Type_Abstract $model
	 * @param $option_name
	 * @param $option_value
	 *
	 * @return mixed
	 */
	public function get_index_indice_option_value( WPSOLR_Model_Type_Abstract $model, $option_name, $option_value ) {

		// Get option value. Replace by default value if undefined.
		//$option = WPSOLR_Service_Container::getOption()->get_option( $option_name, null );
		$option = get_option( $option_name, null );

		// Ensure compatibility
		$this->update_old_indice_format( $option, $this->index_indice );

		$result = $option_value;
		if ( isset( $option ) && isset( $option[ $this->index_indice ] ) && isset( $option[ $this->index_indice ][ $model->get_type() ] ) ) {

			$result = $option[ $this->index_indice ][ $model->get_type() ];
		}

		return $result;
	}

	/**
	 * @param WPSOLR_Model_Type_Abstract[] $models
	 * @param $option_name
	 * @param $option_value
	 *
	 * @return mixed
	 */
	public function set_index_indice_option_value( $models, $option_name, $option_value ) {

		$option = get_option( $option_name, null );

		if ( ! isset( $option ) ) {
			$option                        = [];
			$option[ $this->index_indice ] = [];
		}

		if ( is_null( $models ) ) {

			// Compatibility with post types models stored without the table name
			$option[ $this->index_indice ] = $option_value;

		} else {

			// Ensure compatibility
			$this->update_old_indice_format( $option, $this->index_indice );

			foreach ( $models as $model ) {
				$option[ $this->index_indice ][ $model->get_type() ] = $option_value;
			}
		}

		update_option( $option_name, $option );

		return $option_value;
	}


	/**
	 * @param array $option
	 * @param string $indice_uuid
	 */
	function update_old_indice_format( &$option, $indice_uuid ) {
		if ( ! isset( $option[ $indice_uuid ] ) || is_scalar( $option[ $indice_uuid ] ) ) {
			$option[ $indice_uuid ] = []; // Old format as a string, replaced by an array
		}
	}

	/**
	 * Count nb documents remaining to index for a solr index
	 *
	 * @param WPSOLR_Model_Type_Abstract $model
	 *
	 * @return int Nb documents remaining to index
	 */
	public
	function get_count_nb_documents_to_be_indexed(
		WPSOLR_Model_Type_Abstract $model
	) {

		return $this->index_data( false, 'default', [ $model ], 0, null );
	}

	/**
	 * @param bool $is_stopping
	 * @param string $process_id Process calling the indexing method
	 * @param WPSOLR_Model_Type_Abstract[] $models
	 * @param int $batch_size
	 * @param \WP_Post $post
	 *
	 * @param bool $is_debug_indexing
	 * @param bool $is_only_exclude_ids
	 *
	 * @return array|int
	 * @throws WPSOLR_Exception_Locking
	 * @throws \Exception
	 */
	public
	function index_data(
		$is_stopping, $process_id, $models, $batch_size = 100, $post = null, $is_debug_indexing = false, $is_only_exclude_ids = false
	) {

		global $wpdb;

		//$this->unlock_all_models();

		$models = ! is_null( $post )
			? WPSOLR_Model_Builder::get_model_types( [ $post->post_type ] )
			: ( is_null( $models ) ? $this->set_default_models() : $models );


		// Needs locking only on "real" indexing
		$is_needs_locking = is_null( $post ) && ! empty( $batch_size ) && ! $is_only_exclude_ids;

		// Debug variable containing debug text
		$debug_text = '';

		$doc_count         = 0;
		$no_more_posts     = 0;
		$models_nb_results = [];
		foreach ( $models as $model ) {

			try {
				$is_needs_unlocking = false;

				// Lock the model to prevent concurrent indexing between crons, or between crons and batches
				if ( $is_needs_locking ) {
					$this->lock_model( $process_id, $model );
				}

				$model_type                       = $model->get_type();
				$models_nb_results[ $model_type ] = 0;

				// Last post date set in previous call. We begin with posts published after.
				// Reset the last post date is reindexing is required.
				$last_post_date_indexed = $this->get_last_post_date_indexed( $model );

				$query_statements = $model->get_indexing_sql( $debug_text, $batch_size, $post, $is_debug_indexing, $is_only_exclude_ids );

				// Eventually, log some debug information
				if ( ! empty( $query_statements['debug_info'] ) ) {
					$this->add_debug_line( $debug_text, null, $query_statements['debug_info'] );
				}

				// Filter the query
				$query_statements = apply_filters( WPSOLR_Events::WPSOLR_FILTER_SQL_QUERY_STATEMENT,
					$query_statements,
					[
						'model'        => $model,
						'index_indice' => $this->index_indice,
					]
				);

				// Generate query string from the query statements
				$query = sprintf(
					self::MAIN_SQL_LOOP,
					$query_statements['SELECT'],
					$query_statements['FROM'],
					$query_statements['JOIN'],
					$query_statements['WHERE'],
					$query_statements['ORDER'],
					0 === $query_statements['LIMIT'] ? '' : 'LIMIT ' . $query_statements['LIMIT']
				);

				$documents = [];
				while ( true ) {

					if ( $is_debug_indexing ) {
						$this->add_debug_line( $debug_text, 'Beginning of new loop (batch size)' );
					}

					// Execute query (retrieve posts IDs, parents and types)
					if ( isset( $post ) ) {

						if ( $is_debug_indexing ) {
							$this->add_debug_line( $debug_text, 'Query document with post->ID', [
								'Query'   => $query,
								'Post ID' => $post->ID,
							] );
						}

						$ids_array = $wpdb->get_results( $wpdb->prepare( $query, $post->ID ), ARRAY_A );

					} elseif ( $is_only_exclude_ids ) {

						$ids_array = $wpdb->get_results( $query, ARRAY_A );

					} else {

						$query_expanded_text = $wpdb->prepare( $query, $last_post_date_indexed['date'], $last_post_date_indexed['ID'], $last_post_date_indexed['date'] );

						if ( $is_debug_indexing ) {
							$this->add_debug_line( $debug_text, 'Query documents from last post date', [
								'Query'          => $query_expanded_text,
								'Last post date' => $last_post_date_indexed['date'],
								'Last post ID'   => $last_post_date_indexed['ID'],
							] );
						}

						$ids_array = $wpdb->get_results( $query_expanded_text, ARRAY_A );
					}

					if ( 0 === $batch_size ) {

						$nb_docs = $ids_array[0]['TOTAL'];

						if ( $is_debug_indexing ) {
							$this->add_debug_line( $debug_text, 'End of loop', [
								$is_only_exclude_ids ? 'Number of documents in database excluded from indexing' : 'Number of documents in database to be indexed' => $nb_docs,
							] );
						}

						// Just return the count
						return $nb_docs;
					}


					// Aggregate current batch IDs in one Solr update statement
					$post_count = count( $ids_array );

					if ( 0 === $post_count ) {
						// No more documents to index, stop now by exiting the loop

						if ( $is_debug_indexing ) {
							$this->add_debug_line( $debug_text, 'No more documents, end of document loop' );
						}

						$no_more_posts ++;
						$is_needs_unlocking = true;
						break;
					}

					for ( $idx = 0; $idx < $post_count; $idx ++ ) {
						$model_id = $ids_array[ $idx ]['ID'];

						// If post is not an attachment
						if ( 'attachment' !== $ids_array[ $idx ]['post_type'] ) {

							// Count this post
							$doc_count ++;
							$models_nb_results[ $model_type ] ++;

							// Customize the attachment body, if attachments are linked to the current post
							$post_attachments = apply_filters( WPSOLR_Events::WPSOLR_FILTER_GET_POST_ATTACHMENTS, [], $model_id );

							// Get the attachments body with a Solr Tika extract query
							$attachment_body = '';
							foreach ( $post_attachments as $post_attachment ) {
								$attachment_body .= ( empty( $attachment_body ) ? '' : '. ' ) . self::extract_attachment_text_by_calling_solr_tika( $model_id, $post_attachment );
							}


							// Get the posts data
							$document = $this->create_solr_document_from_post_or_attachment( $model_type, $model_id, $attachment_body );

							if ( $is_debug_indexing ) {
								$this->add_debug_line( $debug_text, null, [
									'Post to be sent' => wp_json_encode( $document, JSON_PRETTY_PRINT ),
								] );
							}

							$documents[] = $document;

						} else {
							// Post is of type "attachment"

							if ( $is_debug_indexing ) {
								$this->add_debug_line( $debug_text, null, [
									'Post ID to be indexed (attachment)' => $model_id,
								] );
							}

							// Count this post
							$doc_count ++;
							$models_nb_results[ $model_type ] ++;

							// Get the attachments body with a Solr Tika extract query
							$attachment_body = self::extract_attachment_text_by_calling_solr_tika( $model_id, [ 'post_id' => $model_id ] );

							// Get the posts data
							$document = $this->create_solr_document_from_post_or_attachment( $model_type, $model_id, $attachment_body );

							if ( $is_debug_indexing ) {
								$this->add_debug_line( $debug_text, null, [
									'Attachment to be sent' => wp_json_encode( $document, JSON_PRETTY_PRINT ),
								] );
							}

							$documents[] = $document;

						}
					}

					if ( empty( $documents ) || ! isset( $documents ) ) {
						// No more documents to index, stop now by exiting the loop

						if ( $is_debug_indexing ) {
							$this->add_debug_line( $debug_text, 'End of loop, no more documents' );
						}

						break;
					}

					// Send batch documents to Solr
					try {

						$res_final = $this->send_posts_or_attachments_to_solr_index( $documents );

					} catch ( \Exception $e ) {

						if ( $is_debug_indexing ) {
							// Echo debug text now, else it will be hidden by the exception
							echo $debug_text;
						}

						// Continue
						throw $e;
					}

					// Solr error, or only $post to index: exit loop
					if ( ( null === $res_final ) || isset( $post ) ) {
						break;
					}

					if ( ! isset( $post ) ) {
						// Store last post date sent to Solr (for batch only)
						$last_post = end( $ids_array );
						$this->set_last_post_date_indexed(
							$model,
							[
								'date' => $last_post['post_modified'],
								'ID'   => $last_post['ID'],
							] );
					}

					// AJAX: one loop by ajax call
					break;
				}
			} catch ( WPSOLR_Exception_Locking $e ) {
				// Do nothing. Continue
				throw ( $e );

			} catch ( \Exception $e ) {

				// force unlock the model if error, else would be stuck locked
				if ( $is_needs_locking ) {
					$this->unlock_model( $process_id, $model );
				}

				// Continue
				throw ( $e );
			}

			// unlock the model only if it contains no more data to index, or if the indexing is stopping
			if ( $is_needs_locking && ( $is_stopping || $is_needs_unlocking ) ) {
				$this->unlock_model( $process_id, $model );
			}
		}

		$status = ! isset( $res_final ) ? 0 : $res_final;

		// All models have no more data ?
		$indexing_complete = ( $no_more_posts === count( $models ) );

		return $res_final = [
			'models_nb_results' => $models_nb_results,
			'nb_results'        => $doc_count,
			'status'            => $status,
			'indexing_complete' => $indexing_complete,
			'debug_text'        => $is_debug_indexing ? $debug_text : null,
		];

	}

	/*
	 * Fetch posts and attachments,
	 * Transform them in Solr documents,
	 * Send them in packs to Solr
	 */

	/**
	 * Add a debug line to the current debug text
	 *
	 * @param $is_debug_indexing
	 * @param $debug_text
	 * @param $debug_text_header
	 * @param $debug_text_content
	 */
	public
	function add_debug_line(
		&$debug_text, $debug_line_header, $debug_text_header_content = null
	) {

		if ( isset( $debug_line_header ) ) {
			$debug_text .= '******** DEBUG ACTIVATED - ' . $debug_line_header . ' *******' . '<br><br>';
		}

		if ( isset( $debug_text_header_content ) ) {

			foreach ( $debug_text_header_content as $key => $value ) {
				$debug_text .= $key . ':' . '<br>' . '<b>' . $value . '</b>' . '<br><br>';
			}
		}
	}

	/**
	 * Transform a string in a date.
	 *
	 * @param $date_str String date to convert from.
	 *
	 * @return mixed
	 */
	abstract public function search_engine_client_format_date( $date_str );

	/**
	 * @param string $model_type
	 * @param string $model_id
	 * @param string $attachment_body
	 *
	 * @return mixed
	 * @throws \Exception
	 * @internal param $solr_indexing_options
	 */
	public
	function create_solr_document_from_post_or_attachment(
		$model_type, $model_id, $attachment_body = ''
	) {

		$model_to_index = WPSOLR_Model_Builder::get_model( $model_type, $model_id );

		$solarium_document_for_update = $model_to_index->create_document_from_model_or_attachment( $this, $attachment_body );

		// Last chance to customize the solarium update document
		$solarium_document_for_update = apply_filters( WPSOLR_Events::WPSOLR_FILTER_SOLARIUM_DOCUMENT_FOR_UPDATE,
			$solarium_document_for_update,
			$this->solr_indexing_options,
			$model_to_index->get_data(),
			$attachment_body,
			$this
		);

		return $solarium_document_for_update;
	}

	/**
	 * @param string $postid
	 * @param array $post_attachement
	 *
	 * @return string
	 * @throws \Exception
	 */
	public
	function extract_attachment_text_by_calling_solr_tika(
		$postid, $post_attachement
	) {

		try {
			$post_attachement_file = ! empty( $post_attachement['post_id'] ) ? get_attached_file( $post_attachement['post_id'] ) : download_url( $post_attachement['url'] );

			if ( $post_attachement_file instanceof \WP_Error ) {
				throw new \Exception( sprintf( 'Could not access the attachement content. %s', $post_attachement_file->get_error_message() ) );
			}

			$response = $this->search_engine_client_extract_document_content( $post_attachement_file );

			$attachment_text_extracted_from_tika = preg_replace( '/^.*?\<body\>(.*)\<\/body\>.*$/i', '\1', $response );
			if ( PREG_NO_ERROR !== preg_last_error() ) {
				throw new \Exception( sprintf( 'Error code (%s) returned by preg_replace() on the extracted file.', PREG_NO_ERROR ) );
			}

			if ( empty( $attachment_text_extracted_from_tika ) ) {
				// Wrong preg_replace() result,. Use the original text.
				// Wrong preg_replace() result,. Use the original text.
				throw new \Exception( 'Wrong format returned for the extracted file, cannot extract the <body>.' );
			}

			$attachment_text_extracted_from_tika = str_replace( '\n', ' ', $attachment_text_extracted_from_tika );
		} catch ( \Exception $e ) {
			if ( ! empty( $post_attachement['post_id'] ) ) {

				$post = get_post( $post_attachement['post_id'] );

				throw new \Exception( 'Error on attached file ' . $post->post_title . ' (ID: ' . $post->ID . ')' . ': ' . $e->getMessage(), $e->getCode() );

			} else {

				throw new \Exception( sprintf( 'Error on embedded url "%s" of post_id %s. %s', $post_attachement['url'], $postid, $e->getMessage() ), $e->getCode() );
			}
		}

		// Last chance to customize the tika extracted attachment body
		$attachment_text_extracted_from_tika = apply_filters( WPSOLR_Events::WPSOLR_FILTER_ATTACHMENT_TEXT_EXTRACTED_BY_APACHE_TIKA, $attachment_text_extracted_from_tika, $post_attachement );

		return $attachment_text_extracted_from_tika;
	}

	/**
	 * @param array $documents
	 *
	 * @return mixed
	 */
	abstract public function send_posts_or_attachments_to_solr_index( $documents );

	/**
	 * Get count of blacklisted post ids
	 * @return int
	 */
	public function get_count_blacklisted_ids() {

		$result = $this->index_data( false, 'default', null, 0, null, false, true );

		return $result;
	}
}
