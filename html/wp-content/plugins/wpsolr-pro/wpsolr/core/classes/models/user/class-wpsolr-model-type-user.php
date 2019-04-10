<?php

namespace wpsolr\core\classes\models\user;


use wpsolr\core\classes\models\WPSOLR_Model_Type_Abstract;

/**
 * Class WPSOLR_Model_Type_User
 * @package wpsolr\core\classes\models
 */
class WPSOLR_Model_Type_User extends WPSOLR_Model_Type_Abstract {

	const TYPE = 'wpsolr_user';

	/**
	 * @inheritDoc
	 */
	public function __construct() {

		$this->set_label( 'Users' )
		     ->set_table_name( 'users' )
		     ->set_column_id( 'ID' )
		     ->set_column_last_updated( 'user_registered' )
		     ->set_type( static::TYPE );

	}

	/**
	 * @inheritDoc
	 */
	public function get_indexing_sql( $debug_text, $batch_size = 100, $post = null, $is_debug_indexing = false, $is_only_exclude_ids = false ) {

		if ( ! empty( $this->indexing_sql ) ) {
			return $this->indexing_sql;
		}

		global $wpdb;

		$column_last_updated = $this->get_column_last_updated();

		$query_from       = $wpdb->prefix . $this->get_table_name() . ' AS ' . $this->get_table_name();
		$query_join_stmt  = '';
		$query_where_stmt = '';

		if ( 0 === $batch_size ) {
			// count only
			$query_select_stmt = 'count(ID) as TOTAL';
		} else {
			$query_select_stmt = sprintf( 'ID, %s as post_modified, null as post_parent, "%s" as post_type', $column_last_updated, self::TYPE );
		}


		if ( isset( $post ) ) {
			// Add condition on the $post

			$query_where_stmt = " ID = %d AND ( $query_where_stmt ) ";

		} else {
			// Condition on the date only for the batch, not for individual posts

			if ( $is_only_exclude_ids ) {

				$query_where_stmt = '(1 = 2)';

			} else {
				$query_where_stmt = sprintf( ' ((%s = %%s AND ID > %%d) OR (%s > %%s)) ', $column_last_updated, $column_last_updated );
			}
		}

		$query_order_by_stmt = sprintf( '%s ASC, ID ASC', $column_last_updated );

		return [
			'debug_info' => '',
			'SELECT'     => $query_select_stmt,
			'FROM'       => $query_from,
			'JOIN'       => $query_join_stmt,
			'WHERE'      => $query_where_stmt,
			'ORDER'      => $query_order_by_stmt,
			'LIMIT'      => $batch_size,
		];
	}

	/**
	 * @inheritdoc
	 *
	 * @return WPSOLR_Model_User
	 */
	public static function get_model( $model_type, $user_id ) {
		return ( new WPSOLR_Model_User() )->set_data( get_user_by( 'id', $user_id ) );
	}

	/**
	 * @inherit
	 */
	public function get_fields() {
		return [];
	}

}