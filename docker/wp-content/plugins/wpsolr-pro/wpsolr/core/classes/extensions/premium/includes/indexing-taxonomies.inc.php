<?php

use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Help;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\core\classes\WpSolrSchema;

$model_type_taxonomies_selected = apply_filters(
	WPSOLR_Events::WPSOLR_FILTER_INDEX_TAXONOMIES_SELECTED,
	WPSOLR_Service_Container::getOption()->get_option_index_taxonomies()
);
$disabled                       = $license_manager->get_license_enable_html_code( OptionLicenses::LICENSE_PACKAGE_PREMIUM );

// Count nb of fields selected
$nb_taxonomies_selected = 0;
foreach ( $model_type_taxonomies as $type ) {
	if ( in_array( $type . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, $model_type_taxonomies_selected, true ) ) {
		$nb_taxonomies_selected ++;
	}
}
?>

<div class="wdm_row">
    <a href="javascript:void(0);" class="cust_tax wpsolr_collapser <?php echo $model_type; ?>"
       style="margin: 0px;">

		<?php echo sprintf( ( count( $model_type_taxonomies ) > 1 ) ? '%s Taxonomies - %s selected' : '%s Taxonomy - %s selected', count( $model_type_taxonomies ), empty( $nb_taxonomies_selected ) ? 'none' : $nb_taxonomies_selected ); ?></a>

    <div class='cust_tax wpsolr_collapsed <?php echo $model_type; ?>'>
        <br>
		<?php
		if ( file_exists( $file_to_include = apply_filters( WPSOLR_Events::WPSOLR_FILTER_INCLUDE_FILE, WPSOLR_Help::HELP_CHECKER ) ) ) {
			require $file_to_include;
		}
		?>

		<?php
		// Selected first
		foreach ( $model_type_taxonomies as $type ) {
			if ( in_array( $type . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, $model_type_taxonomies_selected, true ) ) {
				?>

                <input type='checkbox' name='taxon' class="wpsolr_checked"
                       value='<?php echo $type . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ?>'
					<?php echo $disabled; ?>
                       checked
                > <?php echo $type ?> <br>
				<?php
			}
		}

		// Unselected 2nd
		foreach ( $model_type_taxonomies as $type ) {
			if ( ! in_array( $type . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, $model_type_taxonomies_selected, true ) ) {
				?>

                <input type='checkbox' name='taxon' class="wpsolr_checked"
                       value='<?php echo $type . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ?>'
					<?php echo $disabled; ?>
                > <?php echo $type ?> <br>
				<?php
			}
		}
		?>

    </div>
    <div class="clear"></div>
</div>
