<?php

namespace wpsolr\pro\extensions\theme\layout\range_regular_radioboxes;

use wpsolr\pro\extensions\theme\layout\range_regular_checkboxes\WPSOLR_UI_Layout_Range_Regular_Check_box;

/**
 * Class WPSOLR_UI_Layout_Range_Regular_Radio_Box
 * @package wpsolr\pro\extensions\theme\layout\range_regular_radioboxes
 */
class WPSOLR_UI_Layout_Range_Regular_Radio_Box extends WPSOLR_UI_Layout_Range_Regular_Check_box {

	const CHILD_LAYOUT_ID = 'id_range_regular_radioboxes';

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
		return 'Regular Range with radioboxes';
	}

}