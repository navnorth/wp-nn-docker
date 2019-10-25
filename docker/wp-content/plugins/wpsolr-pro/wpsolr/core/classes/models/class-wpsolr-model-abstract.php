<?php

namespace wpsolr\core\classes\models;

use wpsolr\core\classes\engines\WPSOLR_AbstractIndexClient;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WPSOLR_Model_Abstract
 * @package wpsolr\core\classes\models
 */
abstract class WPSOLR_Model_Abstract {

	/** @var mixed */
	protected $data;

	/** @var array */
	protected $solarium_document_for_update;

	/**
	 * @return mixed
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * @param object $data
	 *
	 * @return $this
	 */
	public function set_data( $data ) {
		$this->data = $data;

		return $this;
	}

	/**
	 * Child generate a document to index
	 *
	 * @param string $attachment_body
	 *
	 * @return mixed
	 */
	public function create_document_from_model_or_attachment( WPSOLR_AbstractIndexClient $search_engine_client, $attachment_body ) {

		// Common indexing part here
		$this->solarium_document_for_update                                               = [];
		$data_id                                                                          = $this->get_id();
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_ID ]               = $search_engine_client->generate_unique_post_id( $data_id );
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_PID ]              = $data_id;
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_TYPE ]             = $this->get_type();
		$this->solarium_document_for_update[ WpSolrSchema::_FIELD_NAME_DISPLAY_MODIFIED ] = $search_engine_client->search_engine_client_format_date( $this->get_date_modified() );


		// Let models do the indexing from now
		$this->create_document_from_model_or_attachment_inner( $search_engine_client, $attachment_body );

		return $this->solarium_document_for_update;
	}

	/**
	 * get model's data ID
	 *
	 * @return string
	 */
	abstract public function get_id();

	/**
	 * Child generate a document to index
	 *
	 * @param string $attachment_body
	 *
	 * @return mixed
	 */
	abstract public function create_document_from_model_or_attachment_inner( WPSOLR_AbstractIndexClient $search_engine_client, $attachment_body );

	/**
	 * Get type
	 *
	 * @return string
	 */
	abstract function get_type();

	/**
	 * get model's date of last modification. Used for indexing only models modified after last indexing date.
	 *
	 * @return string
	 */
	abstract public function get_date_modified();

}