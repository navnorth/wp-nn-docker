<?php

namespace wpsolr\pro\extensions\theme\layout\date_picker;

use wpsolr\core\classes\ui\layout\WPSOLR_UI_Layout_Abstract;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WPSOLR_UI_Layout_Date_Picker
 * @package wpsolr\pro\extensions\theme\layout\date_picker
 */
class WPSOLR_UI_Layout_Date_Picker extends WPSOLR_UI_Layout_Abstract {

	const CHILD_LAYOUT_ID = 'id_date_picker';

	/**
	 * @inheritdoc
	 */
	function get_css_class_name() {
		return 'wpsolr_facet_date_picker';
	}

	/**
	 * @inheritdoc
	 */
	static function get_facet_type() {
		return WPSOLR_Option::OPTION_FACET_FACETS_TYPE_FIELD;
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
		return false;
	}

	/**
	 * @inheritdoc
	 */
	public static function get_label() {
		return 'Date picker';
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
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_DATE
		];
	}

}