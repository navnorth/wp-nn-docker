<?php

use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\utilities\WPSOLR_Help;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;
use wpsolr\core\classes\WpSolrSchema;


$disabled = $license_manager->get_license_enable_html_code( OptionLicenses::LICENSE_PACKAGE_PREMIUM );

// Count nb of fields selected
$nb_fields_selected = 0;
foreach ( $model_type_fields as $model_type_field ) {
	if ( isset( $field_types_opt[ $model_type ] ) && ( false !== array_search( $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, $field_types_opt[ $model_type ], true ) ) ) {
		$nb_fields_selected ++;
	}
}
?>

<div class="wdm_row">
    <div>
        <a href="javascript:void(0);" class="cust_fields wpsolr_collapser <?php echo $model_type; ?>"
           style="margin: 0px;">

			<?php echo sprintf( ( count( $model_type_fields ) > 1 ) ? '%s Fields - %s selected' : '%s Field - %s selected', count( $model_type_fields ), empty( $nb_fields_selected ) ? 'none' : $nb_fields_selected ); ?></a>


        <div class='cust_fields wpsolr_collapsed <?php echo $model_type; ?>'>
            <br>
			<?php
			if ( file_exists( $file_to_include = apply_filters( WPSOLR_Events::WPSOLR_FILTER_INCLUDE_FILE, WPSOLR_Help::HELP_CHECKER ) ) ) {
				require $file_to_include;
			}
			?>

			<?php
			if ( ! empty( $custom_fields_error_message ) ) {
				echo sprintf( '<div class="error-message">%s</div>', $custom_fields_error_message );
			}
			?>

			<?php
			if ( count( $model_type_fields ) > 0 ) {
				// sort custom fields
				uasort( $model_type_fields, function ( $a, $b ) {
					return strcmp( str_replace( '_', 'zzzzzz', $a ), str_replace( '_', 'zzzzzz', $b ) ); // fields '_xxx' at the end
				} );

				// Show selected first
				foreach ( [ true, false ] as $is_show_selected ) {

					foreach ( $model_type_fields as $model_type_field ) {
						$is_selected = ( isset( $field_types_opt[ $model_type ] ) && ( false !== array_search( $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, $field_types_opt[ $model_type ], true ) ) );

						if ( $is_show_selected ? $is_selected : ! $is_selected ) {
							$is_indexed_custom_field = true;
							?>

                            <div class="wpsolr_custom_field_selected">
                                <input type='checkbox'
                                       name="<?php echo sprintf( '%s[%s][%s][%s]', WPSOLR_Option::OPTION_INDEX, WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELDS, $model_type, $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ); ?>"
                                       class="wpsolr-remove-if-empty  wpsolr_collapser wpsolr_checked"
                                       value='1'
									<?php echo $disabled; ?>
									<?php echo checked( $is_show_selected ); ?>>

                                <b><?php echo $model_type_field ?></b>

                                <br/>
                                <div class="wpsolr_collapsed" style="margin-left:30px;">

                                    <select
                                            class="wpsolr_same_name_same_value"
										<?php
										$solr_dynamic_types = WpSolrSchema::get_solr_dynamic_entensions();
										$field_solr_type    = ! empty( $custom_field_properties[ $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ] ) && ! empty( $custom_field_properties[ $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ][ WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE ] )
											? $custom_field_properties[ $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ][ WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE ]
											: WpSolrSchema::get_solr_dynamic_entension_id_by_default();
										if ( $disabled ) {
											echo ' disabled ';
										}
										?>
                                            name="<?php echo sprintf( '%s[%s][%s][%s]', WPSOLR_Option::OPTION_INDEX, WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTIES, $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_SOLR_TYPE ); ?>">
										<?php
										foreach ( $solr_dynamic_types as $solr_dynamic_type_id => $solr_dynamic_type_array ) {
											echo sprintf( '<option value="%s" %s %s>%s</option>',
												$solr_dynamic_type_id,
												selected( $field_solr_type, $solr_dynamic_type_id, false ),
												$solr_dynamic_type_array['disabled'],
												WpSolrSchema::get_solr_dynamic_entension_label( $solr_dynamic_type_array )
											);
										}
										?>
                                    </select>

                                    <select
                                            class="wpsolr_same_name_same_value"
										<?php
										$field_action_id = ! empty( $custom_field_properties[ $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ] ) && ! empty( $custom_field_properties[ $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ][ WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION ] )
											? $custom_field_properties[ $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ][ WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION ]
											: WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD;
										if ( $disabled ) {
											echo ' disabled ';
										}
										?>
                                            name="<?php echo sprintf( '%s[%s][%s][%s]', WPSOLR_Option::OPTION_INDEX, WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTIES, $model_type_field . WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING, WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION ); ?>">
										<?php
										foreach (
											[
												WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_IGNORE_FIELD => 'Use empty value if conversion error',
												WPSOLR_Option::OPTION_INDEX_CUSTOM_FIELD_PROPERTY_CONVERSION_ERROR_ACTION_THROW_ERROR  => 'Stop indexing at first conversion error',
											] as $action_id => $action_text
										) {
											echo sprintf( '<option value="%s" %s>%s</option>', $action_id, selected( $field_action_id, $action_id, false ), $action_text );
										}
										?>
                                    </select>
                                </div>

                            </div>

							<?php
						}
					}
				}

			} else {
				echo 'None';
			}
			?>
        </div>
    </div>
    <div class="clear"></div>
</div>
