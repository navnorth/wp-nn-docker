<?php
use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Help;
use wpsolr\core\classes\utilities\WPSOLR_Option;

/**
 * Included file to display admin options
 */
global $license_manager;

WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_ALL_IN_ONE_SEO, true );

$extension_options_name = WPSOLR_Option::OPTION_ALL_IN_ONE_SEO_PACK;
$settings_fields_name   = 'extension_all_in_one_seo_opt';

$extension_options = WPSOLR_Service_Container::getOption()->get_option_all_in_one_seo_pack();

$is_plugin_active = WpSolrExtensions::is_plugin_active( WpSolrExtensions::EXTENSION_ALL_IN_ONE_SEO );

?>

<div wdm-vertical-tabs-contentid="extension_groups-options" class="wdm-vertical-tabs-content wpsolr-col-9">
    <form action="options.php" method="POST" id='extension_groups_settings_form'>
		<?php
		settings_fields( $settings_fields_name );
		?>

        <div class='wrapper'>
            <h4 class='head_div'>All in One SEO Pack</h4>

            <div class="wdm_note">
                Improve your SEO drastically:
                <ol>
                    <li>
                        Generate beautiful SEO url permalinks for the facets
                    </li>
                    <li>
                        Redirect permalinks to the corresponding search results
                    </li>
                    <li>
                        Integrate with All in One SEO Pack to generate SEO metas in search results
                    </li>
                </ol>

            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Activate the All in One SEO Pack SEO extension
					<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_ALL_IN_ONE_SEO_PACK ); ?>
                </div>
                <div class='col_right'>
                    <input type='checkbox' <?php echo $is_plugin_active ? '' : 'readonly' ?>
                           name='<?php echo $extension_options_name; ?>[is_extension_active]'
                           value='is_extension_active'
						<?php checked( 'is_extension_active', isset( $extension_options['is_extension_active'] ) ? $extension_options['is_extension_active'] : '' ); ?>>
                </div>
                <div class="clear"></div>
            </div>

			<?php include dirname( __FILE__ ) . '/../seo/admin_options.inc.php' ?>

            <div class='wdm_row'>
                <div class="submit">
					<?php if ( $license_manager->get_license_is_activated( OptionLicenses::LICENSE_PACKAGE_ALL_IN_ONE_SEO_PACK ) ) { ?>
                        <div class="wpsolr_premium_block_class">
							<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_ALL_IN_ONE_SEO_PACK, OptionLicenses::TEXT_LICENSE_ACTIVATED, true, true ); ?>
                        </div>
                        <input
                                name="save_selected_options_res_form"
                                id="save_selected_extension_groups_form" type="submit"
                                class="button-primary wdm-save"
                                value="Save Options"/>
					<?php } else { ?>
						<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_ALL_IN_ONE_SEO_PACK, 'Save Options', true, true ); ?>
                        <br/>
					<?php } ?>
                </div>
            </div>
        </div>

    </form>
</div>