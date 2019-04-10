<?php

namespace wpsolr\core\classes\models;


use wpsolr\core\classes\models\post\WPSOLR_Model_Type_Post;
use wpsolr\core\classes\models\user\WPSOLR_Model_Type_User;
use wpsolr\core\classes\services\WPSOLR_Service_Container;

/**
 * Class WPSOLR_Model_Type_Abstract
 * @package wpsolr\core\classes\models
 */
abstract class WPSOLR_Model_Type_Abstract {

	/* @var string Model label */
	protected $label;

	/* @var string Table name storing the model */
	protected $table_name;

	/* @var string Column containing the model id */
	protected $column_id;

	/* @var string Column containing the model timestamp */
	protected $column_last_updated;

	/* @var array SQL statement for the indexing loop */
	protected $indexing_sql;

	/** @var  string $type */
	protected $type;

	/**
	 * @return string
	 */
	public function get_table_name() {
		return $this->table_name;
	}

	/**
	 * @param string $table_name
	 *
	 * @return $this
	 */
	public function set_table_name( $table_name ) {
		$this->table_name = $table_name;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_column_id() {
		return $this->column_id;
	}

	/**
	 * @param string $column_id
	 *
	 * @return $this
	 */
	public function set_column_id( $column_id ) {
		$this->column_id = $column_id;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_column_last_updated() {
		return $this->column_last_updated;
	}

	/**
	 * @param string $column_last_updated
	 *
	 * @return $this
	 */
	public function set_column_last_updated( $column_last_updated ) {
		$this->column_last_updated = $column_last_updated;

		return $this;
	}

	/**
	 * @param $debug_text
	 * @param int $batch_size
	 * @param \WP_Post $post
	 * @param bool $is_debug_indexing
	 * @param bool $is_only_exclude_ids
	 *
	 * @return array
	 */
	public function get_indexing_sql( $debug_text, $batch_size = 100, $post = null, $is_debug_indexing = false, $is_only_exclude_ids = false ) {
		return $this->indexing_sql;
	}

	/**
	 * @param array $column_last_updated
	 *
	 * @return $this
	 */
	public function set_indexing_sql( $indexing_sql ) {
		$this->indexing_sql = $indexing_sql;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_label() {
		return $this->label;
	}

	/**
	 * @param string $label
	 *
	 * @return WPSOLR_Model_Type_Abstract
	 */
	public function set_label( $label ) {
		$this->label = $label;

		return $this;
	}

	/**
	 * Set a type
	 *
	 * @param string $type
	 *
	 * @return WPSOLR_Model_Type_Abstract
	 */
	public function set_type( $type ) {
		$this->type = $type;

		return $this;
	}

	/**
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}


	/**
	 * Create models from types
	 *
	 * @param string[] $model_types
	 *
	 * @return WPSOLR_Model_Type_Abstract[]
	 * @throws \Exception
	 */
	static public function get_model_types( $model_types ) {

		$results = [];
		foreach ( $model_types as $model_type ) {

			switch ( $model_type ) {
				case WPSOLR_Model_Type_User::TYPE:
					$results[] = ( new WPSOLR_Model_Type_User() );
					break;


				default:
					$results[] = new WPSOLR_Model_Type_Post( $model_type );
					break;
			}
		}

		return $results;
	}

	/**
	 * Retrieve a model from type and an id
	 *
	 * @param string $model_type
	 * @param string $model_id
	 *
	 * @return null|WPSOLR_Model_Type_Abstract
	 * @throws \Exception
	 */
	public static function get_model( $model_type, $model_id ) {
		throw new \Exception( sprintf( 'get_model() not implemented for class: %s.', static::class ) );
	}

	/**
	 * Model type is authorized to be indexed ?
	 *
	 * @param string $post_type
	 *
	 * @return bool
	 */
	static public function get_is_model_type_can_be_indexed( $post_type ) {

		$post_types = WPSOLR_Service_Container::getOption()->get_option_index_post_types();

		return in_array( $post_type, $post_types, true );
	}

	/**
	 * Get the model type taxonomies
	 *
	 * @return string[]
	 */
	public function get_taxonomies() {
		return get_object_taxonomies( $this->get_type(), $output = 'names' );
	}

	/**
	 * Get the model type fields
	 *
	 * @return string[]
	 */
	abstract public function get_fields();

}
