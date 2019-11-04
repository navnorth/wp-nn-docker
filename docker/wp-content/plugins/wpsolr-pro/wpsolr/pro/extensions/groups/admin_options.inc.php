<?php
use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\pro\extensions\groups\WPSOLR_Plugin_Groups;

/**
 * Included file to display admin options
 */
global $license_manager;

WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_GROUPS, true );

WPSOLR_Plugin_Groups::update_custom_field_capabilities( WpSolrExtensions::EXTENSION_GROUPS, WPSOLR_Plugin_Groups::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES );

$extension_options                   = WPSOLR_Service_Container::getOption()->get_option_groups();
$is_plugin_active                    = WpSolrExtensions::is_plugin_active( WpSolrExtensions::EXTENSION_GROUPS );
$is_plugin_custom_field_for_indexing = WPSOLR_Plugin_Groups::get_custom_field_capabilities( WPSOLR_Plugin_Groups::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES );
$custom_field_for_indexing_name      = WPSOLR_Plugin_Groups::CUSTOM_FIELD_NAME_STORING_POST_CAPABILITIES;

$plugin_name = "Groups";
?>

<div wdm-vertical-tabs-contentid="extension_groups-options" class="wdm-vertical-tabs-content wpsolr-col-9">
    <form action="options.php" method="POST" id='extension_groups_settings_form'>
		<?php
		settings_fields( 'solr_extension_groups_options' );
		?>

        <div class='wrapper'>
            <h4 class='head_div'>Groups plugin extension (tested with version 2.2.0)</h4>

            <div class="wdm_note">

                Filter search results with user groups and post type capabilities.<br/>

				<?php if ( ! $is_plugin_active ): ?>
                    <p>
                        Status: <a href="https://wordpress.org/plugins/groups/"
                                   target="_blank">Groups
                            plugin</a> is not activated. First, you need to install and
                        activate it to configure WPSOLR.
                    </p>
                    <p>
                        You will also need to re-index all your data if you activated
                        <a href="https://wordpress.org/plugins/groups/" target="_blank">Groups
                            plugin</a>
                        after you activated WPSOLR.
                    </p>
				<?php else : ?>
                    <p>
                        Status: <a href="https://wordpress.org/plugins/groups/"
                                   target="_blank">Groups
                            plugin</a>
                        is activated. You can now configure WPSOLR to use it.
                    </p>
				<?php endif; ?>
				<?php if ( ( ! $is_plugin_custom_field_for_indexing ) && ( isset( $extension_options['is_extension_active'] ) ) ): ?>
                    <p>
                        The custom field <b>'<?php echo $custom_field_for_indexing_name ?>
                            '</b>
                        is not selected,
                        which means WPSOLR will not be able to index data from <a
                                href="https://wordpress.org/plugins/groups/" target="_blank">Groups
                            plugin</a>.
                        <br/>Please go to 'Indexing options' tab, and check
                        <b>'<?php echo $custom_field_for_indexing_name ?>'</b>.
                        <br/>You should also better re-index your data.
                    </p>
				<?php endif; ?>

            </div>
            <div class="wdm_row">
                <div class='col_left'>Use the <a
                            href="https://wordpress.org/plugins/groups/" target="_blank">Groups (>= 1.4.13)
                        plugin</a>
                    to filter search results.
                    <br/>Think of re-indexing all your data if <a
                            href="https://wordpress.org/plugins/groups/" target="_blank">Groups
                        plugin</a> was installed after WPSOLR.
                </div>
                <div class='col_right'>
                    <input type='checkbox'
                           name='wdm_solr_extension_groups_data[is_extension_active]'
                           value='is_extension_active'
						<?php checked( 'is_extension_active', isset( $extension_options['is_extension_active'] ) ? $extension_options['is_extension_active'] : '' ); ?>>
                </div>
                <div class="clear"></div>
            </div>
            <div class="wdm_row">
                <div class='col_left'>Users without groups can see all results, <br/>
                    whatever the results capabilities.
                </div>
                <div class='col_right'>
                    <input type='checkbox'
                           name='wdm_solr_extension_groups_data[is_users_without_groups_see_all_results]'
                           value='is_users_without_groups_see_all_results'
						<?php checked( 'is_users_without_groups_see_all_results', isset( $extension_options['is_users_without_groups_see_all_results'] ) ? $extension_options['is_users_without_groups_see_all_results'] : '?' ); ?>>
                </div>
                <div class="clear"></div>
            </div>
            <div class="wdm_row">
                <div class='col_left'>Results without capabilities can be seen by all users,
                    <br/> whatever the users groups.
                </div>
                <div class='col_right'>
                    <input type='checkbox'
                           name='wdm_solr_extension_groups_data[is_result_without_capabilities_seen_by_all_users]'
                           value='is_result_without_capabilities_seen_by_all_users'
						<?php checked( 'is_result_without_capabilities_seen_by_all_users', isset( $extension_options['is_result_without_capabilities_seen_by_all_users'] ) ? $extension_options['is_result_without_capabilities_seen_by_all_users'] : '?' ); ?>>
                </div>
                <div class="clear"></div>
            </div>
            <div class="wdm_row">
                <div class='col_left'>Phrase to display if you forbid users without groups
                    to see any results
                </div>
                <div class='col_right'>
												<textarea id='message_user_without_groups_shown_no_results'
                                                          name='wdm_solr_extension_groups_data[message_user_without_groups_shown_no_results]'
                                                          rows="4" cols="100"
                                                          placeholder="<?php echo WPSOLR_Plugin_Groups::DEFAULT_MESSAGE_NOT_AUTHORIZED; ?>"><?php echo empty( $extension_options['message_user_without_groups_shown_no_results'] ) ? trim( WPSOLR_Plugin_Groups::DEFAULT_MESSAGE_NOT_AUTHORIZED ) : $extension_options['message_user_without_groups_shown_no_results']; ?></textarea>
                    <span class='res_err'></span><br>
                </div>
                <div class="clear"></div>
            </div>
            <div class="wdm_row">
                <div class='col_left'>Phrase to display when a result matches
                    a user group. <br/>
                    %1 will be replaced by the matched group(s).
                </div>
                <div class='col_right'>
                    <input type='text' id='message_result_capability_matches_user_group'
                           name='wdm_solr_extension_groups_data[message_result_capability_matches_user_group]'
                           placeholder="Private content : %1"
                           value="<?php echo empty( $extension_options['message_result_capability_matches_user_group'] ) ? 'Private content : %1' : $extension_options['message_result_capability_matches_user_group']; ?>"><span
                            class='fac_err'></span> <br>
                </div>
                <div class="clear"></div>
            </div>
            <div class='wdm_row'>
                <div class="submit">
					<?php if ( ! $license_manager->is_installed || $license_manager->get_license_is_activated( OptionLicenses::LICENSE_PACKAGE_GROUPS ) ) { ?>
                        <div class="wpsolr_premium_block_class">
							<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_GROUPS, OptionLicenses::TEXT_LICENSE_ACTIVATED, true ); ?>
                        </div>
                        <input
                                name="save_selected_options_res_form"
                                id="save_selected_extension_groups_form" type="submit"
                                class="button-primary wdm-save"
                                value="Save Options"/>
					<?php } else { ?>
						<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_GROUPS, 'Save Options', true ); ?>
                        <br/>
					<?php } ?>
                </div>
            </div>

        </div>

    </form>
</div>