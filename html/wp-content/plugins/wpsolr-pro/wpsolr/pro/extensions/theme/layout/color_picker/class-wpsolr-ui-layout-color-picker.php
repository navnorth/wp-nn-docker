<?php

namespace wpsolr\pro\extensions\theme\layout\color_picker;

use wpsolr\core\classes\ui\layout\WPSOLR_UI_Layout_Abstract;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WPSOLR_UI_Layout_Color_Picker
 * @package wpsolr\pro\extensions\theme\layout
 */
class WPSOLR_UI_Layout_Color_Picker extends WPSOLR_UI_Layout_Abstract {

	const CHILD_LAYOUT_ID = 'id_color_picker';

	/**
	 * @inheritdoc
	 */
	public function get_button_localize_label() {
		return 'Associate a color to values';
	}

	/**
	 * @inheritdoc
	 */
	function get_css_class_name() {
		return 'wpsolr_facet_color_picker';
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
		return true;
	}

	/**
	 * @inheritdoc
	 */
	public static function get_label() {
		return 'Color picker';
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
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_S,
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING,
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function generate_html_permalink( $item, &$html_item ) {
		if ( isset( $item['permalink'] ) ) {
			$rel       = $this->get_html_rel( $item['permalink']['rel'] );
			$html_item = sprintf( self::PERMALINK_LINK_TEMPLATE, $item['permalink']['href'], $rel, $item['value'], $html_item );
		}
	}

	/**
	 * @inheritdoc
	 */
	protected function child_prepare_facet_item( $level, $facet_layout_id, $item_localized_name, &$item, $facet_label, $facet_data, &$html_item ) {

		$html_item = sprintf( '<label style="background-color:%s; color:rgba(148, 148, 148, 1);"><i></i></label>', $item_localized_name );
	}

}