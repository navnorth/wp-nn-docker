<?php

namespace wpsolr\core\classes\models;


use wpsolr\core\classes\models\post\WPSOLR_Model_Type_Post;
use wpsolr\core\classes\models\user\WPSOLR_Model_Type_User;

/**
 * Class WPSOLR_Model_Builder
 * @package wpsolr\core\classes\models
 */
class WPSOLR_Model_Builder {

	/**
	 * Get all the models that can be indexed
	 *
	 * @return WPSOLR_Model_Type_Abstract[]
	 * @throws \Exception
	 */
	static private function _get_all_model_types() {

		$results = [];

		/**
		 * Add all post types, but a few ones
		 */
		foreach ( get_post_types() as $post_type ) {
			if ( ! in_array( $post_type, [ 'xxattachment', 'xxxrevision', 'xxxnav_menu_item' ] ) ) {
				array_push( $results, $post_type );
			}
		}

		/**
		 * Add custom User type
		 */
		//array_push( $results, WPSOLR_Model_Type_User::TYPE );


		/**
		 * Other types here
		 * TODO
		 */

		return $results;
	}


	/**
	 * @param string[] $model_types
	 * @param bool $is_get_all_if_none
	 *
	 * @return WPSOLR_Model_Type_Abstract[]
	 * @throws \Exception
	 */
	static public function get_model_types( $model_types = [], $is_get_all_if_none = true ) {

		if ( empty( $model_types ) && $is_get_all_if_none ) {
			$model_types = self::_get_all_model_types();
		}

		return WPSOLR_Model_Type_Abstract::get_model_types( $model_types );
	}

	/**
	 * Retrieve a model from type and an id
	 *
	 * @param string $model_type
	 * @param string $model_id
	 *
	 * @return null|WPSOLR_Model_Abstract
	 * @throws \Exception
	 */
	public static function get_model( $model_type, $model_id ) {

		switch ( $model_type ) {
			case WPSOLR_Model_Type_User::TYPE:
				$model = WPSOLR_Model_Type_User::get_model( $model_type, $model_id );
				break;

			default:
				// A post type
				$model = WPSOLR_Model_Type_Post::get_model( $model_type, $model_id );
				break;
		}

		return $model;
	}
}
