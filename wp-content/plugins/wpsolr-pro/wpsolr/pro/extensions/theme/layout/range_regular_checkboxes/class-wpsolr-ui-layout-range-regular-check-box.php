<?php

namespace wpsolr\pro\extensions\theme\layout\range_regular_checkboxes;

use wpsolr\core\classes\ui\layout\WPSOLR_UI_Layout_Abstract;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WPSOLR_UI_Layout_Range_Regular_Check_box
 * @package wpsolr\pro\extensions\theme\layout\range_regular_checkboxes
 */
class WPSOLR_UI_Layout_Range_Regular_Check_box extends WPSOLR_UI_Layout_Abstract {

	const CHILD_LAYOUT_ID = 'id_range_regular_checkboxes';

	/**
	 * @inheritdoc
	 */
	public function get_button_localize_label() {
		return 'Customize the label of ranges';
	}

	/**
	 * @inheritdoc
	 */
	function get_css_class_name() {
		return 'wpsolr_facet_checkbox';
	}

	/**
	 * @inheritdoc
	 */
	static function get_facet_type() {
		return WPSOLR_Option::OPTION_FACET_FACETS_TYPE_RANGE;
	}

	/**
	 * @inheritdoc
	 */
	static function get_files() {
		return [];
	}

	/**
	 * @inheritdoc
	 */
	public static function get_is_enabled() {
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public static function get_label() {
		return 'Regular Range with checkboxes';
	}

	/**
	 * @inheritdoc
	 */
	static function get_skins() {
		return [];
	}

	/**
	 * @inheritdoc
	 */
	static function get_types() {
		return [
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_FLOAT,
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_FLOAT_DOUBLE,
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_INTEGER,
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_INTEGER_LONG,
			//WpSolrSchema::_SOLR_DYNAMIC_TYPE_DATE,
		];
	}

}