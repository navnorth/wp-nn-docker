<?php

namespace wpsolr\pro\extensions\theme\layout\select2;

use wpsolr\core\classes\ui\layout\select\WPSOLR_UI_Layout_Select;
use wpsolr\core\classes\utilities\WPSOLR_Option;

/**
 *
 * Select2 4.0.4 library
 * Source: https://github.com/select2/select2/releases/tag/4.0.4
 * Documentation: https://select2.org/getting-started/installation
 *
 * Class WPSOLR_UI_Layout_Select2
 * @package wpsolr\core\classes\ui\layout\select2
 */
class WPSOLR_UI_Layout_Select2 extends WPSOLR_UI_Layout_Select {

	const CHILD_LAYOUT_ID = 'id_select2';

	// Class of all select2 objects
	const INNER_CLASS = 'wpsolr-select2';

	/**
	 * @inheritdoc
	 */
	function get_css_class_name() {
		return 'wpsolr_facet_select2';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_js_help_text() {
		return <<<'TAG'
			Set the javascript variable <b>wpsolr_select2_options</b>, in the area above, to configure your own options for the Select2 select box (minimumInputLength, minimumResultsForSearch ...). 
			See <a href="https://select2.org/configuration/options-api" target="_new">all options available with official demos</a>.<br/><br/>
			Example:<br/><pre><code>wpsolr_select2_options = { minimumInputLength: 3};</code></pre>
TAG;
	}

	/**
	 * @inheritdoc
	 */
	public static function get_label() {
		return 'Select box - select2 js library';
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
	static function get_types() {
		return []; // All field types ok
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
	static function get_facet_type() {
		return WPSOLR_Option::OPTION_FACET_FACETS_TYPE_FIELD;
	}

	/**
	 * @inheritdoc
	 */
	static function get_files() {
		return [
			self::FIELD_CSS_FILES => [
				'js/select2/css/select2.min.css',
				'template/facet-select2/wpsolr-facet-select2.css',
			],
			self::FIELD_JS_FILES  => [
				'js/select2/js/select2.min.js'                   => [],
				'template/facet-select2/wpsolr-facet-select2.js' => [
					self::FIELD_JS_LAYOUT_CLASS => static::INNER_CLASS,
					self::FIELD_JS_LAYOUT_FILES => [ 'dir_i18n' => 'js/select2/js/i18n/' ],
				],
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	protected function get_inner_class( $is_multiple = false ) {
		return static::INNER_CLASS;
	}

}