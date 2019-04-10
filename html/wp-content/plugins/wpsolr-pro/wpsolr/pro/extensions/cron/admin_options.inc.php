<?php

use wpsolr\core\classes\engines\WPSOLR_AbstractIndexClient;
use wpsolr\core\classes\extensions\indexes\WPSOLR_Option_Indexes;
use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\models\WPSOLR_Model_Builder;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Help;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\pro\extensions\cron\WPSOLR_Option_Cron;

/**
 * Included file to display admin options
 */
global $license_manager;

WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_CRON, true );

$extension_options_name = WPSOLR_Option::OPTION_CRON;
$settings_fields_name   = 'extension_cron_opt';

$options = WPSOLR_Service_Container::getOption()->get_option_cron();

$is_plugin_active = WpSolrExtensions::is_plugin_active( WpSolrExtensions::EXTENSION_CRON );
?>

<?php
$option_indexes = new WPSOLR_Option_Indexes();
$indexes        = $option_indexes->get_indexes();
$post_types     = WPSOLR_Service_Container::getOption()->get_option_index_post_types();
$models         = WPSOLR_Model_Builder::get_model_types( $post_types );

$crons = WPSOLR_Service_Container::getOption()->get_option_cron_indexing();
if ( isset( $_POST['wpsolr_new_cron'] ) && ! isset( $crons[ $_POST['wpsolr_new_cron'] ] ) ) {
	$crons = array_merge( [ sanitize_text_field( $_POST['wpsolr_new_cron'] ) => [] ], $crons );
}
?>

<form id="wpsolr_form_new_cron" method="post">
    <input type="hidden" name="wpsolr_new_cron" value="<?php echo WPSOLR_Option_Indexes::generate_uuid(); ?>"/>
</form>


<div wdm-vertical-tabs-contentid="extension_groups-options" class="wdm-vertical-tabs-content wpsolr-col-9">
    <form action="options.php" method="POST" id='extension_groups_settings_form'>
		<?php
		settings_fields( $settings_fields_name );
		?>

        <div class='wrapper'>
            <h4 class='head_div'>Cron extension</h4>

            <div class="wdm_note">
                <ol>
                    <li>Define one, or several crons, to index your data</li>
                    <li>Each cron is called with it's own REST url. cURL command is provided</li>
                    <li>Each cron REST url is protected by a Basic authentication</li>
                    <li>Each cron REST url returns a JSON detailing: how many documents where sent in how many seconds,
                        agregated by index. The total cron time is also indicated. Errors are also shown at the cron
                        level, and at each index level.
                    </li>
                    <li>Call sequentially any index in each cron. Reorder the sequence by drag&drop</li>
                    <li>Call crons in parallel. Indexes called in concurrent crons are discarded.</li>
                </ol>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Activate the Cron extension
					<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_CRON ); ?>
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
                </div>
                <div class='col_right'>
                    <input type="button"
                           name="add_cron"
                           id="add_cron"
                           class="button-primary"
                           value="Add a cron"
                           onclick="jQuery('#wpsolr_form_new_cron').submit();"
                    />
                    (<?php echo count( $crons ); ?> cron already)
                </div>
                <div class="clear"></div>
            </div>

			<?php foreach ( $crons as $cron_uuid => $cron ) {
				$cron_label   = isset( $cron[ WPSOLR_Option::OPTION_CRON_INDEXING_LABEL ] ) ? $cron[ WPSOLR_Option::OPTION_CRON_INDEXING_LABEL ] : 'rename me';
				$password     = ! empty( $options['indexing'][ $cron_uuid ][ WPSOLR_Option::OPTION_CRON_INDEXING_PASSWORD ] ) ? $options['indexing'][ $cron_uuid ][ WPSOLR_Option::OPTION_CRON_INDEXING_PASSWORD ] : WPSOLR_Option_Indexes::generate_uuid();
				$command_curl = WPSOLR_Option_Cron::get_command_curl( $cron_uuid, $password );
				$command_wget = WPSOLR_Option_Cron::get_command_wget( $cron_uuid, $password );
				?>
                <div class="wpsolr_cron" data-wpsolr-cron-label="<?php echo $cron_label; ?>">
                    <h4 class='head_div'><?php echo $cron_label; ?></h4>
                    <div class="wdm_row">
                        <div class='col_left'>
                            Cron label
                            <input type="button"
                                   style="float:right;"
                                   name="delete_cron"
                                   class="wpsolr-cron-delete-button button-secondary"
                                   value="Delete"
                                   onclick="jQuery(this).closest('.wpsolr_cron').remove();"
                            />
                        </div>
                        <div class='col_right'>
                            <input type='text'
                                   name='<?php echo $extension_options_name; ?>[indexing][<?php echo $cron_uuid ?>][<?php echo WPSOLR_Option::OPTION_CRON_INDEXING_LABEL; ?>]'
                                   placeholder="Enter a Number"
                                   value="<?php echo $cron_label; ?>">

                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="wdm_row">
                        <div class='col_left'>
                            Password to protect your crons
                        </div>
                        <div class='col_right'>
                            <input type='password' <?php echo $is_plugin_active ? '' : 'readonly' ?>
                                   class="wpsolr_password"
                                   name='<?php echo $extension_options_name; ?>[indexing][<?php echo $cron_uuid ?>][<?php echo WPSOLR_Option::OPTION_CRON_INDEXING_PASSWORD; ?>]'
                                   value='<?php echo $password ?>'

                            <br/><input type="checkbox" class="wpsolr_password_toggle"/> Show the password

                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="wdm_row">
                        <div class='col_left'>
                            Url to call the cron
                        </div>
                        <div class='col_right'>
                            <input type="button"
                                   class="wpsolr-cron-command-url-button button-secondary wpsolr_collapser"
                                   value="Show the cURL command"/>
                            <div class="wpsolr_collapsed" style="margin:10px;">
                                <textarea rows="4"
                                          class="wpsolr-cron-command-url"><?php echo $command_curl; ?></textarea>
                            </div>
                            <!--
                            <input type="button" class="button-primary wpsolr_collapser" value="Show the wget command"/>
                            <div class="wpsolr_collapsed" style="margin:10px;">
                                <textarea rows="4"><?php echo $command_wget; ?></textarea>
                            </div>
                            -->
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="wdm_row">
						<?php $is_locked = WPSOLR_AbstractIndexClient::is_locked( $cron_uuid ); ?>
                        <div class='col_left'>
                            Status
							<?php if ( $is_locked ) { ?>
                                <span class="img-load" style="display: inline-block;float:right"></span>
							<?php } ?>
                        </div>
                        <div class='col_right'>
							<?php if ( $is_locked ) { ?>
                                <input type="button"
                                       data-wpsolr-process-id="<?php echo $cron_uuid; ?>"
                                       style="float:left;"
                                       class="wpsolr_unlock_process button-primary wdm-save"
                                       value="Running ... Stop the cron"/>

                                <span class="solr_error"></span>

							<?php } else { ?>
                                <span class="wpsolr-cron-status">Idle</span>
							<?php } ?>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="wdm_row">
                        <div class='col_left'>
                            Logs
                        </div>
                        <div class='col_right'>
							<?php
							$log = isset( $options['indexing'][ $cron_uuid ][ WPSOLR_Option::OPTION_CRON_LOG ] )
								? $options['indexing'][ $cron_uuid ][ WPSOLR_Option::OPTION_CRON_LOG ]
								: '';
							?>
                            <input type="button" class="wpsolr-cron-logs-button button-secondary wpsolr_collapser"
                                   value="Show last execution logs"/>
                            <div class="wpsolr_collapsed" style="margin:10px;">
                                <textarea
                                        class="wpsolr-cron-logs"
                                        name='<?php echo $extension_options_name; ?>[indexing][<?php echo $cron_uuid ?>][<?php echo WPSOLR_Option::OPTION_CRON_LOG; ?>]'
                                        rows="30"><?php echo empty( $log ) ? 'No log available.' : $log; ?>
                                </textarea>
                            </div>
                        </div>
                        <div class="clear"></div>
                    </div>
                    <div class="wdm_row">
                        <div class='col_left'>
                            Indexes
                        </div>
                        <div class='col_right'>
                            <ul class="ui-sortable">
								<?php
								$loop       = 0;
								$batch_size = 100;

								if ( isset( $cron['indexes'] ) ) {
									foreach ( $cron['indexes'] as $index_uuid => $cron_index ) {
										$index = $option_indexes->get_index( $index_uuid );
										include( 'cron_index.inc.php' );
									}
								}

								if ( ! empty( $indexes ) ) {
									foreach ( $indexes as $index_uuid => $index ) {
										if ( ! isset( $cron['indexes'] ) || ! isset( $cron['indexes'][ $index_uuid ] ) ) { // Prevent duplicate
											include( 'cron_index.inc.php' );
										}
									}
								} else {
									?>
                                    <span>First <a href="/wp-admin/admin.php?page=solr_settings&tab=solr_indexes">add an index</a>. Then configure it here.</span>
									<?php
								}
								?>
                            </ul>
                        </div>
                        <div class="clear"></div>
                    </div>
                </div>
			<?php } ?>


            <div class='wdm_row'>
                <div class="submit">
					<?php if ( $license_manager->get_license_is_activated( OptionLicenses::LICENSE_PACKAGE_CRON ) ) { ?>
                        <div class="wpsolr_premium_block_class">
							<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_CRON, OptionLicenses::TEXT_LICENSE_ACTIVATED, true, true ); ?>
                        </div>
                        <input
                                name="save_cron"
                                id="save_cron" type="submit"
                                class="button-primary wdm-save"
                                value="Save Options"/>
					<?php } else { ?>
						<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_CRON, 'Save Options', true, true ); ?>
                        <br/>
					<?php } ?>
                </div>
            </div>
        </div>

    </form>
</div>