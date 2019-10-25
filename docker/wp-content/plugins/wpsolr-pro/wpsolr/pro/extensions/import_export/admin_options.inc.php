<?php

use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Help;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\core\classes\WPSOLR_Events;


WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::OPTION_IMPORT_EXPORT, true );

$extension_options_name = WPSOLR_Option::OPTION_IMPORT_EXPORT;
$settings_fields_name   = 'extension_import_export_opt';

$options          = WPSOLR_Service_Container::getOption()->get_option_import_export();
$is_plugin_active = WpSolrExtensions::is_plugin_active( WpSolrExtensions::OPTION_IMPORT_EXPORT );


const FIELD_PLUGIN = ' plugin';

$option_names_to_export = [];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_ACF ] = [
	'description' => 'ACF',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_acf(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_SCORING ] = [
	'description' => 'Advanced Scoring',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_scoring(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_ALL_IN_ONE_SEO_PACK ] = [
	'description' => 'All In One SEO Pack',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_all_in_one_seo_pack(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_BBPRESS ] = [
	'description' => 'bbPress',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_bbPress(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_SEARCH_FIELDS ] = [
	'description' => 'Boosts',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_boost(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_CRON ] = [
	'description' => 'Cron',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_cron(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_EMBED_ANY_DOCUMENT ] = [
	'description' => 'Embed Any Document',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_embed_any_document(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_FACET ] = [
	'description' => 'Facets',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_facet(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_GEOLOCATION ] = [
	'description' => 'Geolocation',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_geolocation(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_GOOGLE_DOC_EMBEDDER ] = [
	'description' => 'Google Doc Embedder',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_google_doc_embedder(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_GROUPS ] = [
	'description' => 'Groups',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_groups(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_IMPORT_EXPORT ] = [
	'description' => 'Import / Export',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_import_export(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_INDEXES ] = [
	'description' => 'Index',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_indexes(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_LOCKING ] = [
	'description' => 'Index lock',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_locking(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_THEME_JOBIFY ] = [
	'description' => 'Jobify',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_theme_jobify(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_INDEX ] = [
	'description' => 'Operation',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_index(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_THEME_LISTIFY ] = [
	'description' => 'Listify',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_theme_listify(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_THEME_LISTABLE ] = [
	'description' => 'Listable',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_theme_listable(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_LOCALIZATION ] = [
	'description' => 'Localization',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_localization(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_PDF_EMBEDDER ] = [
	'description' => 'PDF Embedder',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_pdf_embedder(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_POLYLANG ] = [
	'description' => 'Polylang',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_polylang(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_PREMIUM ] = [
	'description' => 'Premium',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_premium(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_S2MEMBER ] = [
	'description' => 's2Member',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_s2member(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_SEARCH ] = [
	'description' => 'Search',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_search(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_SORTBY ] = [
	'description' => 'Sorts',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_sortby(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_TABLEPRESS ] = [
	'description' => 'TablePress',
	'data'        => WPSOLR_Service_Container::getOption()->get_tablepress_is_index_shortcodes(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_THEME ] = [
	'description' => 'Theme',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_theme(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_TYPES ] = [
	'description' => 'Toolset',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_toolset_types(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_WOOCOMMERCE ] = [
	'description' => 'WooCommerce',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_plugin_woocommerce(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_WP_ALL_IMPORT ] = [
	'description' => 'WP All Import',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_wp_all_import_pack(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_WPML ] = [
	'description' => 'WPML',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_wpml(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_EXTENSION_YITH_WOOCOMMERCE_AJAX_SEARCH_FREE ] = [
	'description' => 'YITH Woo Search (Free)',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_yith_woocommerce_ajax_search_free(),
];

$option_names_to_export[ WPSOLR_Option::OPTION_YOAST_SEO ] = [
	'description' => 'Yoast SEO',
	'data'        => WPSOLR_Service_Container::getOption()->get_option_yoast_seo(),
];


// Import
$wpsolr_data_to_import_string = '';
if ( ! empty( $_POST['wpsolr_action'] ) && ( 'wpsolr_action_import_settings' === $_POST['wpsolr_action'] ) ) {

	// Remove escaped quotes added by the POST
	$wpsolr_data_to_import_string = ! empty( $_POST['wpsolr_data_to_import'] ) ? stripslashes( $_POST['wpsolr_data_to_import'] ) : '';
	if ( ! empty( $wpsolr_data_to_import_string ) ) {

		$wpsolr_data_to_import = json_decode( $wpsolr_data_to_import_string, true );

		foreach ( $option_names_to_export as $option_name => $option_description ) {

			if ( ! empty( $wpsolr_data_to_import[ $option_name ] ) ) {

				// Save the option
				update_option( $option_name, $wpsolr_data_to_import[ $option_name ] );
			}

		}
	}
}

// Export
$exports = [];
foreach ( $option_names_to_export as $option_name => $option ) {

	if ( isset( $options[ $option_name ] ) ) {
		// Export options selected

		$exports[ $option_name ] = $option['data'];
	}
}


?>

<style>
    .wpsolr_export_col {
        float: left;
        width: 200px;
        margin-bottom: 7px;
    }

    .wpsolr-export {
        margin-top: 20px;
    }
</style>

<div id="export-options" class="wpdm-vertical-tabs-content">
    <form action="options.php" method="POST" id='settings_form'>
        <input type="hidden" name="wpsolr_action" value="wpsolr_action_export_settings"/>
		<?php
		settings_fields( $settings_fields_name );
		?>

        <div class='wpsolr-indexing-option wrapper'>
            <h4 class='wpsolr-head-div'>Export configuration</h4>

            <div class="wdm_note">

                Choose the WPSOLR settings that you want to export to a file.
            </div>

            <div class="wdm_row">
                <div class='col_left'>
                    Select the settings to export<br/>
					<?php
					if ( file_exists( $file_to_include = apply_filters( WPSOLR_Events::WPSOLR_FILTER_INCLUDE_FILE, WPSOLR_Help::HELP_CHECKER ) ) ) {
						require_once $file_to_include;
					}
					?>
                </div>
                <div class='col_right'>

                    <div class="clear"></div>

                    <div class="wpsolr-export">
						<?php foreach ( $option_names_to_export as $option_name => $option ) { ?>
                            <div class="wpsolr_export_col">
                                <input type='checkbox' class="wpsolr_checked"
                                       name='<?php echo $extension_options_name ?>[<?php echo $option_name; ?>]'
                                       value='1' <?php checked( '1', isset( $options[ $option_name ] ) ? $options[ $option_name ] : '' ); ?>>
								<?php echo $option['description']; ?>
                            </div>
						<?php } ?>
                    </div>

                </div>
                <div class="clear"></div>
            </div>
            <div class="wdm_row">
                <div class='col_left'>
                    Settings exported<br/>
                    Copy that settings to your target WPSOLR import text area
                </div>
                <div class='col_right'>
					<textarea name="wpsolr_data_exported" rows="10"
                              style="width: 100%"><?php echo ! empty( $exports ) ? json_encode( $exports, JSON_PRETTY_PRINT ) : ''; ?></textarea>
                </div>
                <div class="clear"></div>
            </div>
            <div class='wdm_row'>
                <div class="submit">
                    <input name="save_selected_importexport_options_form"
                           type="submit"
                           class="button-primary wpsolr-save" value="Generate data to export"/>
                </div>
            </div>
    </form>
</div>

<form method="POST" id='import_form'>
    <input type="hidden" name="wpsolr_action" value="wpsolr_action_import_settings"/>
	<?php
	settings_fields( $extension_options_name );
	?>

    <div class='wpsolr-indexing-option wrapper'>
        <h4 class='wpsolr-head-div'>Import configuration</h4>

        <div class="wdm_note">

            Paste here, from the source WPSOLR, the data to import.
        </div>

        <div class="wdm_row">
            <div class='col_left'>
                Data to import
            </div>
            <div class='col_right'>
					<textarea name="wpsolr_data_to_import" rows="20"
                              style="width: 100%"><?php echo ! empty( $wpsolr_data_to_import_string ) ? $wpsolr_data_to_import_string : ''; ?></textarea>
            </div>
            <div class="clear"></div>
        </div>
        <div class='wdm_row'>
            <div class="submit">
                <input name="import"
                       type="submit"
                       class="button-primary wpsolr-save" value="Import generated data"/>
            </div>
        </div>

    </div>
</form>

</div>
