<?php

use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\ui\layout\WPSOLR_UI_Layout_Abstract;
use wpsolr\core\classes\utilities\WPSOLR_Option;

?>

<div style="display:none"
     class="wpsolr-remove-if-hidden wpsolr_facet_type <?php echo WPSOLR_UI_Layout_Abstract::get_css_class_feature_layouts( WPSOLR_UI_Layout_Abstract::FEATURE_SEO_TEMPLATE_RANGE ); ?>">

<input type='text'
           class="wpsolr-remove-if-empty"
           placeholder="<?php echo WPSOLR_Option::FACET_LABEL_SEO_TEMPLATE_RANGE; ?>"
           name='wdm_solr_facet_data[<?php echo WPSOLR_Option::OPTION_FACET_FACETS_SEO_PERMALINK_TEMPLATE; ?>][<?php echo $selected_val; ?>]'
           value='<?php echo esc_attr( empty( $facet_seo_template ) ? WPSOLR_Option::FACET_LABEL_SEO_TEMPLATE_RANGE : $facet_seo_template ); ?>'
		<?php echo $license_manager->get_license_enable_html_code( OptionLicenses::LICENSE_PACKAGE_YOAST_SEO ); ?>
    />
    <p>
        Define a permalink template for this facet. Use the
        variables <?php echo WPSOLR_Option::FACET_LABEL_TEMPLATE_VAR_START; ?>
        and <?php echo WPSOLR_Option::FACET_LABEL_TEMPLATE_VAR_END; ?> to replace with the current facet item
        localized value.
    </p>

</div>