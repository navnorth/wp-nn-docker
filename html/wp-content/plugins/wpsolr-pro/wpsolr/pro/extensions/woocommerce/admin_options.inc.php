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

WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_WOOCOMMERCE, true );

$extension_options_name = WPSOLR_Option::OPTION_EXTENSION_WOOCOMMERCE;
$settings_fields_name   = 'solr_extension_woocommerce_options';

$extension_options = WPSOLR_Service_Container::getOption()->get_option_plugin_woocommerce();
$is_plugin_active  = WpSolrExtensions::is_plugin_active( WpSolrExtensions::EXTENSION_WOOCOMMERCE );

$plugin_name    = "WooCommerce";
$plugin_link    = "https://wordpress.org/plugins/woocommerce/";
$plugin_version = "(>= 2.4.10)";
?>

<div wdm-vertical-tabs-contentid="extension_groups-options" class="wdm-vertical-tabs-content wpsolr-col-9">
    <form action="options.php" method="POST" id='extension_groups_settings_form'>
		<?php
		settings_fields( 'solr_extension_woocommerce_options' );
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

            <div class="wdm_row">
                <div class='col_left'>
                    Replace WooCommerce orders search by WPSOLR's orders search.
					<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_SEARCH_ORDERS ); ?>
                </div>
                <div class='col_right'>
                    <input type='checkbox' <?php echo $is_plugin_active ? '' : 'readonly' ?>
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_PLUGIN_WOOCOMMERCE_IS_REPLACE_ADMIN_ORDERS_SEARCH; ?>]'
                           value='is_extension_active'
						<?php checked( 'is_extension_active', isset( $extension_options[ WPSOLR_Option::OPTION_PLUGIN_WOOCOMMERCE_IS_REPLACE_ADMIN_ORDERS_SEARCH ] ) ? $extension_options[ WPSOLR_Option::OPTION_PLUGIN_WOOCOMMERCE_IS_REPLACE_ADMIN_ORDERS_SEARCH ] : '' ); ?>>
                </div>
                <div class="clear"></div>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Replace WooCommerce drop-down list sort content with WPSOLR's.
					<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_WOOCOMMERCE_REPLACE_SORT ); ?>
                </div>
                <div class='col_right'>
                    <input type='checkbox' <?php echo $is_plugin_active ? '' : 'readonly' ?>
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_PLUGIN_WOOCOMMERCE_IS_REPLACE_SORT_ITEMS; ?>]'
                           value='is_extension_active'
						<?php checked( 'is_extension_active', isset( $extension_options[ WPSOLR_Option::OPTION_PLUGIN_WOOCOMMERCE_IS_REPLACE_SORT_ITEMS ] ) ? $extension_options[ WPSOLR_Option::OPTION_PLUGIN_WOOCOMMERCE_IS_REPLACE_SORT_ITEMS ] : '' ); ?>>
                </div>
                <div class="clear"></div>
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Replace WooCommerce category and shop search with WPSOLR's.
					<?php echo WPSOLR_Help::get_help( WPSOLR_Help::HELP_WOOCOMMERCE_REPLACE_CATEGORY_SEARCH ); ?>
                </div>
                <div class='col_right'>
                    <input type='checkbox' <?php echo $is_plugin_active ? '' : 'readonly' ?>
                           name='<?php echo $extension_options_name; ?>[<?php echo WPSOLR_Option::OPTION_PLUGIN_WOOCOMMERCE_IS_REPLACE_PRODUCT_CATEGORY_SEARCH; ?>]'
                           value='is_extension_active'
						<?php checked( 'is_extension_active', isset( $extension_options[ WPSOLR_Option::OPTION_PLUGIN_WOOCOMMERCE_IS_REPLACE_PRODUCT_CATEGORY_SEARCH ] ) ? $extension_options[ WPSOLR_Option::OPTION_PLUGIN_WOOCOMMERCE_IS_REPLACE_PRODUCT_CATEGORY_SEARCH ] : '' ); ?>>
                </div>
                <div class="clear"></div>
            </div>

            <div class='wdm_row'>
                <div class="submit">
					<?php if ( ! $license_manager->is_installed || $license_manager->get_license_is_activated( OptionLicenses::LICENSE_PACKAGE_WOOCOMMERCE ) ) { ?>
                        <div
                                class="wpsolr_premium_block_class"><?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_WOOCOMMERCE, OptionLicenses::TEXT_LICENSE_ACTIVATED, true ); ?></div>
                        <input
                                name="save_selected_options_res_form"
                                id="save_selected_extension_groups_form" type="submit"
                                class="button-primary wdm-save"
                                value="Save Options"/>
					<?php } else { ?>
						<?php echo $license_manager->show_premium_link( true, OptionLicenses::LICENSE_PACKAGE_WOOCOMMERCE, 'Save Options', true ); ?>
                        <br/>
					<?php } ?>
                </div>
            </div>

        </div>

    </form>
</div>