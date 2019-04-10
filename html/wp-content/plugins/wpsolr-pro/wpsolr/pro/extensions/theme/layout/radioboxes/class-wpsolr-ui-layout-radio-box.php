<?php

namespace wpsolr\pro\extensions\theme\layout\radioboxes;

use wpsolr\core\classes\ui\layout\checkboxes\WPSOLR_UI_Layout_Check_Box;

/**
 * Class WPSOLR_UI_Layout_Radio_Box
 * @package wpsolr\pro\extensions\theme\layout\radioboxes
 */
class WPSOLR_UI_Layout_Radio_Box extends WPSOLR_UI_Layout_Check_Box {

	const CHILD_LAYOUT_ID = 'id_radioboxes';

	/**
	 * @inheritdoc
	 */
	function get_css_class_name() {
		return 'wpsolr_facet_radiobox';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_label() {
		return 'Radio boxes';
	}

	/**
	 * @inheritdoc
	 */
	public function get_is_multi_filter( $is_multiple = false ) {
		// Radiobox can only select one item at a time
		return false;
	}

}