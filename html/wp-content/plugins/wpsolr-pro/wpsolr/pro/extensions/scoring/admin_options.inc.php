<?php

use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Help;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WpSolrSchema;
use wpsolr\pro\extensions\scoring\WPSOLR_Option_Scoring;

/**
 * Included file to display admin options
 */
global $license_manager;

WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_SCORING, true );

$extension_options_name = WPSOLR_Option::OPTION_SCORING;
$settings_fields_name   = 'extension_scoring_opt';

$options = WPSOLR_Service_Container::getOption()->get_option_scoring();

$is_plugin_active = WpSolrExtensions::is_plugin_active( WpSolrExtensions::EXTENSION_SCORING );

?>

<?php
$is_decay = WPSOLR_Service_Container::getOption()->get_option_scoring_is_decay();
?>

<div id="extension_groups-options" class="wdm-vertical-tabs-content wpsolr-col-9">
    <form action="options.php" method="POST" id='extension_groups_settings_form'>
		<?php
		settings_fields( $settings_fields_name );
		?>

        <div class='wrapper'>
            <h4 class='head_div'>Advanced scoring</h4>

            <div class="wdm_note">
                Enhances the search with complex scoring, as:
                <ol>
                    <li>Show fresh AND relevant results (usually, you would sort and have fresh OR relevant)</li>
                    <li>Show close AND relevant results (usually, you would sort and have close OR relevant)</li>
                    <li>Show cheap AND relevant results (usually, you would sort and have cheap OR relevant)</li>
                    <li>Show fresh AND close AND cheap AND relevant results (usually, you would sort and have fresh OR
                        close OR cheap OR relevant)
                    </li>
                </ol>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Activate the advanced scoring extension
					<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SCORING ); ?>
                </div>
                <div class='col_right'>
                    <input type='checkbox' <?php echo $is_plugin_active ? '' : 'readonly' ?>
                           name='<?php echo $extension_options_name; ?>[is_extension_active]'
                           value='is_extension_active'
						<?php checked( 'is_extension_active', isset( $options['is_extension_active'] ) ? $options['is_extension_active'] : '' ); ?>>
                </div>
                <div class="clear"></div>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Decay penalty (freshness/distance boost)
					<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SCORING ); ?>
                </div>
                <div class='col_right'>
                    <input class="wpsolr_collapser"
                           type='checkbox' <?php echo $is_plugin_active ? '' : 'readonly' ?>
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_SCORING_IS_DECAY; ?>]'
                           value='1'
						<?php checked( $is_decay ); ?>
                    >
                    Elasticsearch only!! Apply a decay penalty on some of your custom fields

                    <div class="wpsolr_collapsed">
                        <p>
                            At equivalent relevancy, results with a custom field value close to the present date,
                            to the current location, to a numeric value, are displayed first.
                        </p>

						<?php
						$img_path   = plugins_url( 'images/plus.png', WPSOLR_PLUGIN_FILE );
						$minus_path = plugins_url( 'images/minus.png', WPSOLR_PLUGIN_FILE );

						$all_custom_fields_indexed   = WPSOLR_Service_Container::getOption()->get_option_index_custom_fields( true );
						$range_custom_fields_indexed = [
							WpSolrSchema::_FIELD_NAME_DISPLAY_DATE_DT,
							WpSolrSchema::_FIELD_NAME_DISPLAY_MODIFIED_DT,
						];
						foreach ( $all_custom_fields_indexed as $field_name ) {
							if ( WpSolrSchema::get_custom_field_is_range_type( $field_name ) ) {
								$range_custom_fields_indexed[] = $field_name;
							}
						}

						$scoring_fields_decays_str = WPSOLR_Service_Container::getOption()->get_option_scoring_fields_decays_str();
						$scoring_fields_decays     = WPSOLR_Service_Container::getOption()->get_option_scoring_fields_decays();
						?>

                        <div class="wdm_row">
							<?php if ( empty( $range_custom_fields_indexed ) ) { ?>
                                You must select a type "Integer", "Float", or "Geolocation" to at least one of your custom fields in screen 2.2.
							<?php } ?>

                            <div class='avail_fac' style="width:100%">
                                <input type='hidden' id='select_fac'
                                       name='<?php echo WPSOLR_Option::OPTION_SCORING; ?>[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_FIELDS; ?>]'
                                       value='<?php echo $scoring_fields_decays_str ?>'>

                                <ul id="sortable1" class="wdm_ul connectedSortable">
									<?php
									if ( ! empty( $scoring_fields_decays_str ) ) {
										foreach ( $scoring_fields_decays as $selected_val ) {
											if ( ! empty( $selected_val ) ) {
												if ( substr( $selected_val, ( strlen( $selected_val ) - 4 ), strlen( $selected_val ) ) === WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ) {
													$dis_text = substr( $selected_val, 0, ( strlen( $selected_val ) - 4 ) );
												} else {
													$dis_text = $selected_val;
												}
												?>
                                                <li id='<?php echo $selected_val; ?>'
                                                    class='ui-state-default facets facet_selected'>

                                                    <img src='<?php echo $img_path; ?>'
                                                         class='plus_icon'
                                                         style='display:none'>
                                                    <img src='<?php echo $minus_path ?>'
                                                         class='minus_icon'
                                                         style='display:inline'
                                                         title='Click to remove the field from the search'>
                                                    <span style="float:left;width: 80%;">
																<?php echo $dis_text; ?>
															</span>

                                                    <div>&nbsp;</div>

                                                    <div class="wdm_row">
                                                        <div class='col_left'>Distribution
                                                        </div>
                                                        <div class='col_right'>
                                                            <select name='<?php echo WPSOLR_Option::OPTION_SCORING; ?>[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_FUNCTIONS; ?>][<?php echo $selected_val; ?>]'>
																<?php foreach ( WPSOLR_Option_Scoring::$DECAY_FUNCTIONS as $function_id => $function_def ) { ?>
                                                                    <option
                                                                            value="<?php echo $function_id ?>" <?php echo selected( $function_id, WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_function( $selected_val, WPSOLR_Option_Scoring::DECAY_FUNCTION_GAUSS ) ) ?> ><?php echo $function_def['label'] ?></option>
																<?php } ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="wdm_row" style="top-margin:5px">
                                                        <div class='col_left'>Origin</div>
                                                        <div class='col_right'>

															<?php if ( WpSolrSchema::get_custom_field_is_date_type( $selected_val ) ) { ?>
                                                                <div>
                                                                    <input class="wpsolr_collapser"
                                                                           type='radio'
                                                                           name="origin"
                                                                           id="origin_now[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_ORIGINS; ?>][<?php echo $selected_val; ?>]"
                                                                           value='1'
																		<?php echo checked( WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_origin_is_now( $selected_val ) ); ?>
                                                                    /> Now
                                                                    <p class="wpsolr_collapsed">
                                                                        <input
                                                                                type="hidden"
                                                                                name='<?php echo WPSOLR_Option::OPTION_SCORING; ?>[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_ORIGINS; ?>][<?php echo $selected_val; ?>]'
                                                                                style="width: 100%"
                                                                                value="<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_ORIGIN_DATE_NOW; ?>"
                                                                        />

                                                                        The origin is the current date. The farther
                                                                        from now your results are, the more penaly they
                                                                        get.
                                                                    </p>
                                                                </div>
                                                                <div><input class="wpsolr_collapser"
                                                                            type='radio'
                                                                            name="origin"
                                                                            id="origin_custom[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_ORIGINS; ?>][<?php echo $selected_val; ?>]"
                                                                            value='2'
																		<?php echo checked( ! WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_origin_is_now( $selected_val ) ); ?>
                                                                    /> Pick an origin
                                                                    <div class="wpsolr_collapsed"
                                                                         style="margin-top:5px">
                                                                        <input class="wpsolr-remove-if-hidden"
                                                                               type="date"
                                                                               id='origin_custom_value[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_ORIGINS; ?>][<?php echo $selected_val; ?>]'
                                                                               name='<?php echo WPSOLR_Option::OPTION_SCORING; ?>[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_ORIGINS; ?>][<?php echo $selected_val; ?>]'
                                                                               value="<?php echo esc_attr( WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_origin( $selected_val, '' ) ); ?>"
                                                                               style="width: 100%"/>
                                                                        <span style="font-size: 9px;">html5 date picker</span>
                                                                        <p>The origin is the date you select. The
                                                                            farther
                                                                            from your selected date your results are,
                                                                            the more penaly
                                                                            they
                                                                            get.
                                                                        </p>
                                                                    </div>
                                                                </div>
															<?php } ?>

															<?php if ( WpSolrSchema::get_custom_field_is_numeric_type( $selected_val ) ) { ?>
                                                                <div>
                                                                    <input
                                                                            type='text'
                                                                            name='<?php echo WPSOLR_Option::OPTION_SCORING; ?>[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_ORIGINS; ?>][<?php echo $selected_val; ?>]'
                                                                            value='<?php echo esc_attr( WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_origin( $selected_val, WPSOLR_Option::OPTION_SCORING_DECAY_ORIGIN_ZERO ) ); ?>'
                                                                    />
                                                                    <p>The origin is the value you set here. The
                                                                        farther
                                                                        from your value your results are,
                                                                        the more penaly
                                                                        they
                                                                        get.
                                                                    </p>
                                                                </div>
															<?php } ?>

                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>
                                                    <div class="wdm_row" style="top-margin:5px">
                                                        <div class='col_left'>Start</div>
                                                        <div class='col_right'>
                                                            <input type='input'
                                                                   placeholder="<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_OFFSET_DEFAULT; ?>"
                                                                   class='wpsolr_field_boost_factor_class'
                                                                   name='<?php echo WPSOLR_Option::OPTION_SCORING; ?>[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_OFFSETS; ?>][<?php echo $selected_val; ?>]'
                                                                   value='<?php echo esc_attr( WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_offset( $selected_val ) ); ?>'
                                                            />
                                                            <p>
                                                                From origin to start, no penalty is applied. In days for
                                                                a date, km for a location.
                                                            </p>


                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>
                                                    <div class="wdm_row" style="top-margin:5px">
                                                        <div class='col_left'>Distance</div>
                                                        <div class='col_right'>
                                                            <input type='input'
                                                                   placeholder="<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_SCALE_DEFAULT; ?>"
                                                                   class='wpsolr_field_boost_factor_class'
                                                                   name='<?php echo WPSOLR_Option::OPTION_SCORING; ?>[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_SCALES; ?>][<?php echo $selected_val; ?>]'
                                                                   value='<?php echo esc_attr( WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_scale( $selected_val ) ); ?>'
                                                            />
                                                            <p>
                                                                Set an estimation of how far the penalty will be
                                                                applied. Added to start. In days for a date, km for a
                                                                location.

                                                            </p>


                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>
                                                    <div class="wdm_row" style="top-margin:5px">
                                                        <div class='col_left'>Penalty</div>
                                                        <div class='col_right'>
                                                            <input type='input'
                                                                   placeholder="<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_VALUES_DEFAULT; ?>"
                                                                   class='wpsolr_field_boost_factor_class'
                                                                   name='<?php echo WPSOLR_Option::OPTION_SCORING; ?>[<?php echo WPSOLR_Option::OPTION_SCORING_DECAY_VALUES; ?>][<?php echo $selected_val; ?>]'
                                                                   value='<?php echo esc_attr( WPSOLR_Service_Container::getOption()->get_option_scoring_field_decay_value( $selected_val ) ); ?>'
                                                            />
                                                            <p>
                                                                Set a penalty value > 0 and < 1. It defines how
                                                                much penalty is applied to values at the start +
                                                                distance you
                                                                defined above. "0.33" means the score is divided by
                                                                3 at the start + distance. Other penalties from offset
                                                                are calculated by the function shape.
                                                            </p>

                                                        </div>
                                                        <div class="clear"></div>
                                                    </div>

                                                </li>

											<?php }
										}
									}
									foreach ( $range_custom_fields_indexed as $built_fac ) {
										if ( $built_fac != '' ) {
											$buil_fac = strtolower( $built_fac );
											if ( substr( $buil_fac, ( strlen( $buil_fac ) - 4 ), strlen( $buil_fac ) ) == WpSolrSchema::_SOLR_DYNAMIC_TYPE_STRING ) {
												$dis_text = substr( $buil_fac, 0, ( strlen( $buil_fac ) - 4 ) );
											} else {
												$dis_text = $buil_fac;
											}

											if ( ! in_array( $buil_fac, $scoring_fields_decays ) ) {

												echo "<li id='$buil_fac' class='ui-state-default facets'>$dis_text
                                                                                                    <img src='$img_path'  class='plus_icon' style='display:inline' title='Click to add the field from the search'>
                                                                                                <img src='$minus_path' class='minus_icon' style='display:none'></li>";
											}
										}
									}
									?>


                                </ul>
                            </div>

                            <div class="clear"></div>
                        </div>


                    </div>
                </div>
                <div class="clear"></div>
            </div>

            <div class='wdm_row'>
                <div class="submit">
					<?php if ( $license_manager->get_license_is_activated( OptionLicenses::LICENSE_PACKAGE_SCORING ) ) { ?>
                        <div class="wpsolr_premium_block_class">
							<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_SCORING, OptionLicenses::TEXT_LICENSE_ACTIVATED, true, true ); ?>
                        </div>
                        <input
                                name="save_scoring"
                                id="save_scoring" type="submit"
                                class="button-primary wdm-save"
                                value="Save Options"/>
					<?php } else { ?>
						<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_SCORING, 'Save Options', true, true ); ?>
                        <br/>
					<?php } ?>
                </div>
            </div>
        </div>

    </form>
</div>