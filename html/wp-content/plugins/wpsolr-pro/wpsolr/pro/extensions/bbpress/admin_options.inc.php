<?php
use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Option;

/**
 * Included file to display admin options
 */
global $license_manager;

WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_BBPRESS, true );

$extension_options_name = WPSOLR_Option::OPTION_EXTENSION_BBPRESS;
$settings_fields_name   = 'solr_extension_bbpress_options';

$extension_options = WPSOLR_Service_Container::getOption()->get_option_bbPress();

$is_plugin_active = WpSolrExtensions::is_plugin_active( WpSolrExtensions::EXTENSION_BBPRESS );

$plugin_name    = "bbPress";
$plugin_link    = "https://wordpress.org/plugins/bbpress/";
$plugin_version = "(>= 2.5.10)";

?>

<div wdm-vertical-tabs-contentid="extension_groups-options" class="wdm-vertical-tabs-content wpsolr-col-9">
    <form action="options.php" method="POST" id='extension_groups_settings_form'>
		<?php
		settings_fields( 'solr_extension_bbpress_options' );
		?>

        <div class='wrapper'>
            <h4 class='head_div'><?php echo $plugin_name; ?> plugin Options</h4>

            <div class="wdm_note">

                In this section, you will configure WPSOLR to work with <?php echo $plugin_name; ?>.<br/>

				<?php if ( ! $is_plugin_active ): ?>
                    <p>
                        Status: <a href="<?php echo $plugin_link; ?>"
                                   target="_blank"><?php echo $plugin_name; ?>
                            plugin</a> is not activated. First, you need to install and
                        activate it to configure WPSOLR.
                    </p>
                    <p>
                        You will also need to re-index all your data if you activated
                        <a href="<?php echo $plugin_link; ?>" target="_blank"><?php echo $plugin_name; ?>
                            plugin</a>
                        after you activated WPSOLR.
                    </p>
				<?php else : ?>
                    <p>
                        Status: <a href="<?php echo $plugin_link; ?>"
                                   target="_blank"><?php echo $plugin_name; ?>
                            plugin</a>
                        is activated. You can now configure WPSOLR to use it.
                    </p>
				<?php endif; ?>
            </div>
            <div class="wdm_row">
                <div class='col_left'>Use the <a
                            href="<?php echo $plugin_link; ?>"
                            target="_blank"><?php echo $plugin_name; ?> <?php echo $plugin_version; ?>
                        plugin</a>
                    to filter search results.
                    <br/>Think of re-indexing all your data if <a
                            href="<?php echo $plugin_link; ?>" target="_blank"><?php echo $plugin_name; ?>
                        plugin</a> was installed after WPSOLR.
                </div>
                <div class='col_right'>
                    <input type='checkbox' <?php echo $is_plugin_active ? '' : 'readonly' ?>
                           name='<?php echo $extension_options_name; ?>[is_extension_active]'
                           value='is_extension_active'
						<?php checked( 'is_extension_active', isset( $extension_options['is_extension_active'] ) ? $extension_options['is_extension_active'] : '' ); ?>>
                </div>
                <div class="clear"></div>
            </div>

            <div class='wdm_row'>
                <div class="submit">
					<?php if ( $license_manager->get_license_is_activated( OptionLicenses::LICENSE_PACKAGE_BBPRESS ) ) { ?>
                        <div
                                class="wpsolr_premium_block_class"><?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_BBPRESS, OptionLicenses::TEXT_LICENSE_ACTIVATED, true, true ); ?></div>
                        <input
                                name="save_selected_options_res_form"
                                id="save_selected_extension_groups_form" type="submit"
                                class="button-primary wdm-save"
                                value="Save Options"/>
					<?php } else { ?>
						<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_BBPRESS, 'Save Options', true, true ); ?>
                        <br/>
					<?php } ?>
                </div>
            </div>

        </div>

    </form>
</div>