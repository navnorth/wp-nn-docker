<?php

namespace wpsolr\pro\extensions\theme\layout\rating_stars;

use wpsolr\core\classes\ui\layout\checkboxes\WPSOLR_UI_Layout_Check_Box;

/**
 * Class WPSOLR_UI_Layout_Rating_Stars
 * @package wpsolr\pro\extensions\theme\layout\rating_stars
 */
class WPSOLR_UI_Layout_Rating_Stars extends WPSOLR_UI_Layout_Check_Box {

	const CHILD_LAYOUT_ID = 'id_rating_stars';

	/**
	 * @inheritdoc
	 */
	function get_css_class_name() {
		return 'wpsolr_facet_rating_stars';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_is_enabled() {
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public static function get_label() {
		return 'Rating Stars';
	}

}