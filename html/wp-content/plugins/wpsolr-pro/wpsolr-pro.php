<?php
/**
 * Plugin Name: WPSOLR PRO
 * Description: WPSOLR PRO
 * Version: 20.9
 * Author: wpsolr
 * Plugin URI: https://www.wpsolr.com
 * License: GPL2
 */

use wpsolr\pro\WPSOLR_Pro_Updates;

// Definitions
define( 'WPSOLR_PLUGIN_SHORT_NAME', 'WPSOLR PRO' );
define( 'WPSOLR_SLUG', 'wpsolr-pro/wpsolr-pro.php' );
define( 'WPSOLR_PLUGIN_PRO_DIR', dirname( __FILE__ ) );
define( 'WPSOLR_PLUGIN_PRO_DIR_URL', substr_replace( plugin_dir_url( __FILE__ ), '', - 1 ), false );

require_once( 'wpsolr/core/wpsolr_include.inc.php' );

add_action( 'after_setup_theme', function () {
	new WPSOLR_Pro_Updates( WPSOLR_SLUG, WPSOLR_PLUGIN_PRO_DIR );
} );


function wpsolr_add_js_global_error() {
// Store js errors in a global used by Selenium tests
	if ( true ) {
		?>
        <script>
            wpsolr_globalError = [];
            window.onerror = function (msg, url, line, col, error) {
                wpsolr_globalError.push({msg: msg, url: url, line: line, error: error});
            };
        </script>
		<?php
	}
}

add_action( 'admin_head', 'wpsolr_add_js_global_error' );
add_action( 'wp_head', 'wpsolr_add_js_global_error' );
