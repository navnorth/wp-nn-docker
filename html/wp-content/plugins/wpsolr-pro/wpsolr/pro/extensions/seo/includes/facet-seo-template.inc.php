<?php

use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\ui\layout\WPSOLR_UI_Layout_Abstract;
use wpsolr\core\classes\utilities\WPSOLR_Option;

$is_seo_permalink   = isset( $selected_facets_seo_is_permalink[ $selected_val ] );
$facet_seo_template = ! empty( $selected_facets_seo_templates[ $selected_val ] ) ? $selected_facets_seo_templates[ $selected_val ] : '';

?>

<div style="display:none"
     class="wpsolr-remove-if-hidden wpsolr_facet_type <?php echo WPSOLR_UI_Layout_Abstract::get_css_class_feature_layouts( WPSOLR_UI_Layout_Abstract::FEATURE_SEO_TEMPLATE ); ?>">

    <div class="wdm_row" style="top-margin:5px;">
        <div class='col_left'>
			<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_YOAST_SEO, 'SEO permalink', true ); ?>
        </div>
    </div>

    <div class='col_right'>
        <input type='checkbox'
               class="wpsolr_collapser"
               name='wdm_solr_facet_data[<?php echo WPSOLR_Option::OPTION_FACET_FACETS_SEO_IS_PERMALINK; ?>][<?php echo $selected_val; ?>]'
               value='1'
			<?php echo $license_manager->get_license_enable_html_code( OptionLicenses::LICENSE_PACKAGE_YOAST_SEO ); ?>
			<?php echo checked( $is_seo_permalink ); ?>
        />
        Generate a permalink for this facet

        <div style="clear:both;margin-top:10px;"></div>
        <div class="wpsolr_collapsed">

			<?php echo sprintf(
				'<a href="#TB_inline?width=800&height=800&inlineId=%s" class="thickbox wpsolr_premium_class" ><img src="%s" class="wpsolr_premium_text_class" style="display:inline"><span>%s</span></a>',
				'form_permalinks_positions',
				'',
				'Set a position in the permalink url'
			);
			?>

            <div style="margin-top:5px;">
				<?php
				include 'facet-seo-template-field.inc.php';
				include 'facet-seo-template-range.inc.php';
				?>
            </div>

        </div>
    </div>
    <div style="clear:both;"></div>

</div>
