<?php
use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\extensions\WpSolrExtensions;
use wpsolr\core\classes\services\WPSOLR_Service_Container;
use wpsolr\core\classes\utilities\WPSOLR_Option;
use wpsolr\pro\extensions\wpml\WPSOLR_Plugin_Wpml;

/**
 * Included file to display admin options
 */


WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::EXTENSION_WPML, true );
WpSolrExtensions::require_once_wpsolr_extension( WpSolrExtensions::OPTION_INDEXES, true );

$extension_options_name = WPSOLR_Option::OPTION_EXTENSION_WPML;
$settings_fields_name   = 'solr_extension_wpml_options';

$extension_options          = WPSOLR_Service_Container::getOption()->get_option_wpml();
$is_plugin_active = WpSolrExtensions::is_plugin_active( WpSolrExtensions::EXTENSION_WPML );

$plugin_name    = "WPML";
$plugin_link    = "https://wpml.org/";
$plugin_version = "(Tested with WPML Multilingual CMS 3.7.1 and WPML String Translation 2.5.4)";

$ml_plugin = WPSOLR_Plugin_Wpml::create();

$package_name = OptionLicenses::LICENSE_PACKAGE_WPML;
?>

<?php
include_once( 'template.inc.php' );