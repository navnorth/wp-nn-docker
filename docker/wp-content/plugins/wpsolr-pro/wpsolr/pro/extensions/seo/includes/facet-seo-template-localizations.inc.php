<?php

$facet_item_seo_template = ( ! empty( $selected_facets_seo_items_templates[ $selected_val ] ) && ! empty( $selected_facets_seo_items_templates[ $selected_val ][ $facet_item_label ] ) )
	? $selected_facets_seo_items_templates[ $selected_val ][ $facet_item_label ] : '';

include 'facet-seo-template-localizations-field.inc.php';
//include 'facet-seo-template-range.inc.php';
?>
