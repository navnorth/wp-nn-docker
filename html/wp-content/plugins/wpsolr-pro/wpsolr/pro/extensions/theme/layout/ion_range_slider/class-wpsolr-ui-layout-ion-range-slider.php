<?php

namespace wpsolr\pro\extensions\theme\layout\ion_range_slider;

use wpsolr\core\classes\ui\layout\WPSOLR_UI_Layout_Abstract;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WpSolrSchema;

/**
 * Class WPSOLR_UI_Layout_Ion_Range_slider
 * @package wpsolr\pro\extensions\theme\layout\ion_range_slider
 */
class WPSOLR_UI_Layout_Ion_Range_slider extends WPSOLR_UI_Layout_Abstract {

	const CHILD_LAYOUT_ID = 'id_ion_range_slider';

	// Class of all ion range slider objects
	const ION_RANGE_SLIDER_CLASS = 'wpsolr-ion-range-slider';
	const WPSOLR_FACET_SLIDER_ION_SKIN_FLAT_CLASS = 'wpsolr_facet_slider_ion_skin_flat';
	const WPSOLR_FACET_SLIDER_ION_SKIN_HTML5_CLASS = 'wpsolr_facet_slider_ion_skin_html5';
	const WPSOLR_FACET_SLIDER_ION_SKIN_MODERN_CLASS = 'wpsolr_facet_slider_ion_skin_modern';
	const WPSOLR_FACET_SLIDER_ION_SKIN_NICE_CLASS = 'wpsolr_facet_slider_ion_skin_nice';
	const WPSOLR_FACET_SLIDER_ION_SKIN_SIMPLE_CLASS = 'wpsolr_facet_slider_ion_skin_simple';

	/**
	 * @inheritdoc
	 */
	function get_css_class_name() {
		return 'wpsolr_facet_slider_ion';
	}

	/**
	 * @inheritdoc
	 */
	public static function get_js_help_text() {
		return <<<'TAG'
            Set the javascript variable <b>wpsolr_ion_range_slider_options</b>, in the area above, to configure your own options for the Ion Range Slider (grid, prefix text, formatting, LTR/RTL ...). 
			See <a href="http://ionden.com/a/plugins/ion.rangeSlider/demo.html" target="_new">all options available with official demos</a>.<br/><br/>
			Example:<br/><pre><code>wpsolr_ion_range_slider_options = { grid: true, prefix: "$"};</code></pre>
TAG;
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

	/**
	 * @inheritdoc
	 */
	static function get_skins() {
		return [
			'wpsolr_flat'   => [
				self::FIELD_LABEL          => 'Flat',
				self::FIELD_SKIN_URL       => 'js/ion.rangeSlider/css/ion.rangeSlider.skinFlat.css',
				self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_SKIN_FLAT_CLASS,
			],
			'wpsolr_html5'  => [
				self::FIELD_LABEL          => 'HTML5',
				self::FIELD_SKIN_URL       => 'js/ion.rangeSlider/css/ion.rangeSlider.skinHTML5.css',
				self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_SKIN_HTML5_CLASS,
			],
			'wpsolr_modern' => [
				self::FIELD_LABEL          => 'Modern',
				self::FIELD_SKIN_URL       => 'js/ion.rangeSlider/css/ion.rangeSlider.skinModern.css',
				self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_SKIN_MODERN_CLASS,
			],
			'wpsolr_nice'   => [
				self::FIELD_LABEL          => 'Nice',
				self::FIELD_SKIN_URL       => 'js/ion.rangeSlider/css/ion.rangeSlider.skinNice.css',
				self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_SKIN_NICE_CLASS,
			],
			'wpsolr_simple' => [
				self::FIELD_LABEL          => 'Simple',
				self::FIELD_SKIN_URL       => 'js/ion.rangeSlider/css/ion.rangeSlider.skinSimple.css',
				self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_SKIN_SIMPLE_CLASS,
			],
		];
	}

	/**
	 * @inheritdoc
	 */
	static function get_facet_type() {
		return WPSOLR_Option::OPTION_FACET_FACETS_TYPE_MIN_MAX;
	}

	/**
	 * @inheritdoc
	 */
	static function get_files() {
		return [
			self::FIELD_CSS_FILES => [
				'js/ion.rangeSlider/css/ion.rangeSlider.css',
				'template/facet-ion-range-slider/wpsolr-facet-ion-range-slider.css',
			],
			self::FIELD_JS_FILES  => [
				//'js/moment/moment-with-locales.min.js'                             => [],
				'js/ion.rangeSlider/js/ion.rangeSlider.min.js'                     => [],
				'template/facet-ion-range-slider/wpsolr-facet-ion-range-slider.js' => [
					self::FIELD_JS_LAYOUT_CLASS => self::ION_RANGE_SLIDER_CLASS,
				],
			],
		];
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
		return 'Range Slider - Ion.RangeSlider js library';
	}

	static protected $all_layoutsx = [
		self::FIELD_LABEL        => 'Range Slider - Ion.RangeSlider js library',
		//self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_CLASS,
		'facet_type'             => WPSOLR_Option::OPTION_FACET_FACETS_TYPE_MIN_MAX,
		'types'                  => [
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_FLOAT,
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_FLOAT_DOUBLE,
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_INTEGER,
			WpSolrSchema::_SOLR_DYNAMIC_TYPE_INTEGER_LONG,
			//WpSolrSchema::_SOLR_DYNAMIC_TYPE_DATE,
		],
		'enabled'                => true,
		'multiselection'         => false,
		'button_localize_label'  => 'none',
		'seo_template'           => WPSOLR_Option::FACET_LABEL_SEO_TEMPLATE_RANGE,
		'seo_template_vars'      => [
			WPSOLR_Option::FACET_LABEL_TEMPLATE_VAR_START,
			WPSOLR_Option::FACET_LABEL_TEMPLATE_VAR_END,
		],
		self::FIELD_JS_HELP      => <<<'TAG'
			Set the javascript variable <b>wpsolr_ion_range_slider_options</b>, in the area above, to configure your own options for the Ion Range Slider (grid, prefix text, formatting, LTR/RTL ...). 
			See <a href="http://ionden.com/a/plugins/ion.rangeSlider/demo.html" target="_new">all options available with official demos</a>.<br/><br/>
			Example:<br/>
<pre><code>wpsolr_ion_range_slider_options = { grid: true, prefix: "$"};</code></pre>
TAG
		,
		/**
		 * Ion.RangeSlider library: https://github.com/IonDen/ion.rangeSlider
		 */
		self::FIELD_LAYOUT_FILES => [
			self::FIELD_CSS_FILES => [
				'js/ion.rangeSlider/css/ion.rangeSlider.css',
				'template/facet-ion-range-slider/wpsolr-facet-ion-range-slider.css',
			],
			self::FIELD_JS_FILES  => [
				//'js/moment/moment-with-locales.min.js'                             => [],
				'js/ion.rangeSlider/js/ion.rangeSlider.min.js'                     => [],
				'template/facet-ion-range-slider/wpsolr-facet-ion-range-slider.js' => [
					self::FIELD_JS_LAYOUT_CLASS => self::ION_RANGE_SLIDER_CLASS,
				],
			],
		],
		self::FIELD_LAYOUT_SKINS => [
			'wpsolr_flat'   => [
				self::FIELD_LABEL          => 'Flat',
				self::FIELD_SKIN_URL       => 'js/ion.rangeSlider/css/ion.rangeSlider.skinFlat.css',
				self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_SKIN_FLAT_CLASS,
			],
			'wpsolr_html5'  => [
				self::FIELD_LABEL          => 'HTML5',
				self::FIELD_SKIN_URL       => 'js/ion.rangeSlider/css/ion.rangeSlider.skinHTML5.css',
				self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_SKIN_HTML5_CLASS,
			],
			'wpsolr_modern' => [
				self::FIELD_LABEL          => 'Modern',
				self::FIELD_SKIN_URL       => 'js/ion.rangeSlider/css/ion.rangeSlider.skinModern.css',
				self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_SKIN_MODERN_CLASS,
			],
			'wpsolr_nice'   => [
				self::FIELD_LABEL          => 'Nice',
				self::FIELD_SKIN_URL       => 'js/ion.rangeSlider/css/ion.rangeSlider.skinNice.css',
				self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_SKIN_NICE_CLASS,
			],
			'wpsolr_simple' => [
				self::FIELD_LABEL          => 'Simple',
				self::FIELD_SKIN_URL       => 'js/ion.rangeSlider/css/ion.rangeSlider.skinSimple.css',
				self::FIELD_CSS_CLASS_NAME => self::WPSOLR_FACET_SLIDER_ION_SKIN_SIMPLE_CLASS,
			],
		],
	];

	/**
	 * @inheritdoc
	 */
	protected function get_inner_class( $is_multiple = false ) {
		return self::ION_RANGE_SLIDER_CLASS;
	}

	/**
	 * @inheritdoc
	 */
	protected function child_prepare_facet_item( $level, $facet_layout_id, $item_localized_name, &$item, $facet_label, $facet_data, &$html_item ) {

		$html_item = "<input type ='text' class='{$this->get_inner_class()}' value='' data-min='{$facet_data['min']}' data-max='{$facet_data['max']}' data-from='{$facet_data['from']}' data-to='{$facet_data['to']}' />";
	}


}