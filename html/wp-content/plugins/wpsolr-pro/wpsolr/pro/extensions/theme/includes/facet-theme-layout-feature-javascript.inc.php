<?php

use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\ui\layout\WPSOLR_UI_Layout_Abstract;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\pro\extensions\theme\WPSOLR_Option_Theme;

?>

<div style="display:none"
     class="wpsolr-remove-if-hidden wpsolr_facet_type <?php echo WPSOLR_UI_Layout_Abstract::get_css_class_feature_layouts( WPSOLR_UI_Layout_Abstract::FEATURE_JAVASCRIPT ); ?>">

	<?php
	$facet_skin            = WPSOLR_Service_Container::getOption()->get_facets_js_value( $selected_val );
	$facet_skins_available = ( ! empty( $facet_layout_skins_available ) && ! empty( $facet_layout_skins_available[ $current_layout_id ] ) )
		? $facet_layout_skins_available[ $current_layout_id ]
		: [ '' => 'Default' ];
	?>

    <div class="wdm_row" style="top-margin:5px;">
        <div class='col_left'>
			<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_THEME, 'Javascript options', true ); ?>
        </div>
        <div class='col_right'>

            <textarea
                    name='wdm_solr_facet_data[<?php echo WPSOLR_Option::OPTION_FACET_FACETS_JS; ?>][<?php echo $selected_val; ?>]'
                    class="wpsolr-remove-if-empty"
                    data-wpsolr-empty-value=""
	            <?php echo $license_manager->get_license_enable_html_code( OptionLicenses::LICENSE_PACKAGE_THEME ); ?>
            ><?php echo $facet_skin; ?></textarea>

            <div class="wpsolr_collapser" style="text-decoration:underline">What is it?</div>
            <div class="wpsolr_collapsed">
                <br/>
				<?php echo WPSOLR_Option_Theme::get_layout_js_help( $current_layout_id ); ?>
                <p>
                    Options can also be translated in WPML/POLYLANG string modules. For instance, to show '$', '€', LTR,
                    RTL for some languages.
                </p>
            </div>

        </div>
        <div class="clear"></div>
    </div>
</div>
