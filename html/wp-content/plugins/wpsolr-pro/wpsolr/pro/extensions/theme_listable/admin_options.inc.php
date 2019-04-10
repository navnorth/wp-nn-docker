<?php

use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Option;

/**
 * Included file to display admin options
 */

global $license_manager;

WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_THEME_LISTABLE, true );

$extension_options_name = WPSOLR_Option::OPTION_THEME_LISTABLE;

$extension_options = WPSOLR_Service_Container::getOption()->get_option_theme_listable();
$is_theme_active   = WpSolrExtensions::is_plugin_active( WpSolrExtensions::EXTENSION_THEME_LISTABLE );

$theme_name    = "Listable";
$theme_link    = "https://themeforest.net/item/listable-a-friendly-directory-wordpress-theme/13398377";
$theme_version = "";
?>

<div wdm-vertical-tabs-contentid="extension_groups-options" class="wdm-vertical-tabs-content wpsolr-col-9">
    <form action="options.php" method="POST" id='extension_groups_settings_form'>
		<?php
		settings_fields( 'extension_theme_listable_opt' );
		?>

        <div class='wrapper'>
            <h4 class='head_div'><?php echo $theme_name; ?> Options</h4>

            <div class="wdm_note">

                In this section, you will configure WPSOLR to work with <?php echo $theme_name; ?>.<br/>

				<?php if ( ! $is_theme_active ): ?>
                    <p>
                        Status: <a href="<?php echo $theme_link; ?>"
                                   target="_blank"><?php echo $theme_name; ?>
                        </a> is not activated. First, you need to install and
                        activate it to configure WPSOLR.
                    </p>
				<?php else : ?>
                    <p>
                        Status: <a href="<?php echo $theme_link; ?>"
                                   target="_blank"><?php echo $theme_name; ?>
                        </a>
                        is activated. You can now configure WPSOLR to use it.
                    </p>
				<?php endif; ?>
            </div>

            <div class="wdm_row">
                <div class='col_left'>Use <a
                            href="<?php echo $theme_link; ?>"
                            target="_blank"><?php echo $theme_name; ?><?php echo $theme_version; ?>
                    </a>
                    to perform search.
                </div>
                <div class='col_right'>
                    <input type='checkbox' <?php echo $is_theme_active ? '' : 'readonly' ?>
                           name='<?php echo $extension_options_name; ?>[is_extension_active]'
                           value='is_extension_active'
						<?php checked( isset( $extension_options['is_extension_active'] ) ); ?>>
                </div>
                <div class="clear"></div>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Speed up listing search with WPSOLR
                </div>
                <div class='col_right'>
                    <input type='checkbox' <?php echo $is_theme_active ? '' : 'readonly' ?>
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_THEME_LISTABLE_IS_REPLACE_LISTING_SEARCH; ?>]'
                           value='y'
						<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_THEME_LISTABLE_IS_REPLACE_LISTING_SEARCH ] ) ); ?>>
                </div>
                <div class="clear"></div>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Use search results caching
                </div>
                <div class='col_right'>
                    <input type='checkbox' <?php echo $is_theme_active ? '' : 'readonly' ?>
                           class="wpsolr_collapser"
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_THEME_LISTABLE_IS_CACHING; ?>]'
                           value='y'
						<?php checked( isset( $extension_options[ WPSOLR_Option::OPTION_THEME_LISTABLE_IS_CACHING ] ) ); ?>>
                    <span class="wpsolr_collapsed">
                        This stores results in WordPress tables (as transient). It should not be necessary, as WPSOLR increase your search performance.
                    </span>
                </div>
                <div class="clear"></div>
            </div>

            <div class='wdm_row'>
                <div class="submit">
					<?php if ( ! $license_manager->is_installed || $license_manager->get_license_is_activated( OptionLicenses::LICENSE_PACKAGE_LISTABLE ) ) { ?>
                        <div
                                class="wpsolr_premium_block_class"><?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_LISTABLE, OptionLicenses::TEXT_LICENSE_ACTIVATED, true ); ?></div>
                        <input
                                name="save_selected_options_res_form"
                                id="save_selected_extension_groups_form" type="submit"
                                class="button-primary wdm-save"
                                value="Save Options"/>
					<?php } else { ?>
						<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_LISTABLE, 'Save Options', true ); ?>
                        <br/>
					<?php } ?>
                </div>
            </div>

        </div>

    </form>
</div>