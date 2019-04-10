<?php

/**
 * Included file to display admin options
 */

use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Help;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\pro\extensions\acf\WPSOLR_Plugin_Acf;

global $license_manager;

WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_ACF, true );

$extension_options_name = WPSOLR_Option::OPTION_EXTENSION_ACF;
$settings_fields_name   = 'solr_extension_acf_options';

$extension_options = WPSOLR_Service_Container::getOption()->get_option_acf();
$is_plugin_active  = WpSolrExtensions::is_plugin_active( WpSolrExtensions::EXTENSION_ACF );

$plugin_name    = "Advanced Custom Fields";
$plugin_link    = "https://wordpress.org/plugins/advanced-custom-fields/";
$plugin_version = "(>= 4.4.3)";

if ( $is_plugin_active ) {
	$ml_plugin = WPSOLR_Plugin_Acf::create();
}
?>

<div wdm-vertical-tabs-contentid="extension_groups-options" class="wdm-vertical-tabs-content wpsolr-col-9">
    <form action="options.php" method="POST" id='extension_groups_settings_form'>
		<?php
		settings_fields( 'solr_extension_acf_options' );
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
                <div class='col_left'>
					<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_ACF_REPEATERS_AND_FLEXIBLE_CONTENT_LAYOUTS ); ?>

                    Use the <a
                            href="<?php echo $plugin_link; ?>"
                            target="_blank"><?php echo $plugin_name; ?> <?php echo $plugin_version; ?>
                        plugin</a>
                    to format repeaters and flexible content layouts.
                    <br/><br/>Think of re-indexing all your data if <a
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

            <div class="wdm_row">
                <div class='col_left'>
                    Replace custom field name by ACF custom field label on facets.
                </div>
                <div class='col_right'>
                    <input type='checkbox'
                           name='<?php echo $extension_options_name; ?>[display_acf_label_on_facet]'
                           value='display_acf_label_on_facet'
						<?php checked( 'display_acf_label_on_facet', isset( $extension_options['display_acf_label_on_facet'] ) ? $extension_options['display_acf_label_on_facet'] : '' ); ?>>
                </div>
                <div class="clear"></div>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Index all ACF file fields
                </div>
                <div class='col_right'>
                    <input type='checkbox'
                           class="wpsolr_collapser"
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_PLUGIN_ACF_IS_INDEX_ALL_FILES; ?>]'
                           value='<?php echo WPSOLR_Option::OPTION_PLUGIN_ACF_IS_INDEX_ALL_FILES; ?>'
						<?php checked( WPSOLR_Option::OPTION_PLUGIN_ACF_IS_INDEX_ALL_FILES, isset( $extension_options[ WPSOLR_Option::OPTION_PLUGIN_ACF_IS_INDEX_ALL_FILES ] ) ? $extension_options[ WPSOLR_Option::OPTION_PLUGIN_ACF_IS_INDEX_ALL_FILES ] : '' ); ?>>
                    <span class="wpsolr_collapsed">
                        All ACF fields of type 'file' will be indexed, except for those belonging to a post with the WPSOLR metabox "Don't search ACF fields file".<br>Do not select this option if you prefer to select which posts will be indexed with their file field content.
                    </span>
                </div>
                <div class="clear"></div>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
					<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_ACF_GOOGLE_MAP ); ?>
                    Your Google Map API key, to use ACF Google Map fields with our Geolocation Pack
                </div>
                <div class='col_right'>
                    <input type='text'
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_PLUGIN_ACF_GOOGLE_MAP_API_KEY; ?>]'
                           placeholder="Enter your Google Map API key ..."
                           value="<?php echo empty( $extension_options[ WPSOLR_Option::OPTION_PLUGIN_ACF_GOOGLE_MAP_API_KEY ] ) ? '' : $extension_options[ WPSOLR_Option::OPTION_PLUGIN_ACF_GOOGLE_MAP_API_KEY ]; ?>"><span
                            class='fac_err'></span> <br>
                </div>
                <div class="clear"></div>
            </div>


            <div class='wdm_row'>
                <div class="submit">
					<?php if ( ! $license_manager->is_installed || $license_manager->get_license_is_activated( OptionLicenses::LICENSE_PACKAGE_ACF ) ) { ?>
                        <div class="wpsolr_premium_block_class">
							<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_ACF, OptionLicenses::TEXT_LICENSE_ACTIVATED, true ); ?>
                        </div>
                        <input
                                name="save_selected_options_res_form"
                                id="save_selected_extension_groups_form" type="submit"
                                class="button-primary wdm-save"
                                value="Save Options"/>
					<?php } else { ?>
						<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_ACF, 'Save Options', true ); ?>
                        <br/>
					<?php } ?>
                </div>
            </div>
        </div>

    </form>
</div>