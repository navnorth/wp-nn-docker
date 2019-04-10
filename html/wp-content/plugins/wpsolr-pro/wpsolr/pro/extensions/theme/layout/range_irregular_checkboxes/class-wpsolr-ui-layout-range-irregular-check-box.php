<?php

namespace wpsolr\pro\extensions\theme\layout\range_irregular_checkboxes;

use wpsolr\pro\extensions\theme\layout\range_regular_checkboxes\WPSOLR_UI_Layout_Range_Regular_Check_box;

/**
 * Class WPSOLR_UI_Layout_Range_Irregular_Check_box
 * @package wpsolr\pro\extensions\theme\layout\range_irregular_checkboxes
 */
class WPSOLR_UI_Layout_Range_Irregular_Check_box extends WPSOLR_UI_Layout_Range_Regular_Check_box {

	const CHILD_LAYOUT_ID = 'id_range_irregular_checkboxes';

	/**
	 * @inheritdoc
	 */
	function get_css_class_name() {
		return 'wpsolr_facet_checkbox';
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
		return 'Irregular Range with checkboxes';
	}

}