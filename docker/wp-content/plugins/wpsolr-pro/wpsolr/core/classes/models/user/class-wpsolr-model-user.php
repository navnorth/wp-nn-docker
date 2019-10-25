<?php

namespace wpsolr\core\classes\models\user;


use wpsolr\core\classes\engines\WPSOLR_AbstractIndexClient;
use wpsolr\core\classes\models\WPSOLR_Model_Abstract;

/**
 * Class WPSOLR_Model_User
 * @package wpsolr\core\classes\models
 */
class WPSOLR_Model_User extends WPSOLR_Model_Abstract {

	/** @var \WP_User */
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
		return WPSOLR_Model_Type_User::TYPE;
	}

	/**
	 * @inheritdoc
	 */
	public function get_date_modified() {
		return $this->data->user_registered;
	}

	/**
	 * @inheritdoc
	 */
	public function create_document_from_model_or_attachment_inner( WPSOLR_AbstractIndexClient $search_engine_client, $attachment_body ) {
		// TODO: Implement create_document_from_model_or_attachment() method.
	}
}