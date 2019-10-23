<?php

use wpsolr\core\classes\extensions\licenses\OptionLicenses;
use wpsolr\core\classes\extensions\WpSolrExtensions;

$subtabs1 = [
	'extension_theme_directory2_opt' => [
		'name'  => '>> Directory+',
		'class' => wpsolr_get_extension_tab_class( OptionLicenses::LICENSE_PACKAGE_DIRECTORY2, WpSolrExtensions::EXTENSION_THEME_DIRECTORY2 ),
	],
	'extension_theme_jobify_opt'     => [
		'name'  => '>> Jobify',
		'class' => wpsolr_get_extension_tab_class( OptionLicenses::LICENSE_PACKAGE_JOBIFY, WpSolrExtensions::EXTENSION_THEME_JOBIFY ),
	],
	'extension_theme_listable_opt'   => [
		'name'  => '>> Listable',
		'class' => wpsolr_get_extension_tab_class( OptionLicenses::LICENSE_PACKAGE_LISTABLE, WpSolrExtensions::EXTENSION_THEME_LISTABLE ),
	],
	'extension_theme_listify_opt'    => [
		'name'  => '>> Listify',
		'class' => wpsolr_get_extension_tab_class( OptionLicenses::LICENSE_PACKAGE_LISTIFY, WpSolrExtensions::EXTENSION_THEME_LISTIFY ),
	],
];

// Diplay the subtabs
include( 'dashboard_extensions.inc.php' );
