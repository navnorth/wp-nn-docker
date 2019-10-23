<?php

use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\ui\layout\WPSOLR_UI_Layout_Abstract;
use wpsolr\core\classes\utilities\WPSOLR_Option;

?>

<div style="display:none"
     class="wpsolr-remove-if-hidden wpsolr_facet_type <?php echo WPSOLR_UI_Layout_Abstract::get_css_class_feature_layouts( WPSOLR_UI_Layout_Abstract::FEATURE_RANGE_IRREGULAR ); ?>">


    <div class='col_left' style="font-weight: normal">
        One range per row, with 3 columns separated by '|'.</br></br>
        0|9|Range %1$d - %2$d (%3$d)</br>
        10|20|Range 10 TO 20 (%3$d)</br>
        21|100|Range %s => %s (%3$d)</br>
        101|*|More than 100 (%3$d)</br>
    </div>
    <div class='col_right'>
				<textarea type='text' rows="10" style="width:98%"
                          name='wdm_solr_facet_data[<?php echo WPSOLR_Option::FACET_FIELD_CUSTOM_RANGES; ?>][<?php echo $selected_val; ?>]'
                ><?php echo esc_attr( WPSOLR_Service_Container::getOption()->get_facets_range_irregular_ranges( $selected_val ) ); ?></textarea>

    </div>

</div>
