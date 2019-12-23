<?php
/*
 Plugin Name:  WP Academic Standards
 Plugin URI:   https://www.navigationnorth.com
 Description:  Wordpress Academic Standards
 Version:      0.2.1
 Author:       Navigation North
 Author URI:   https://www.navigationnorth.com
 Text Domain:  wp-academic-standards
 License:      GPL3
 License URI:  https://www.gnu.org/licenses/gpl-3.0.html

 Copyright (C) 2019 Navigation North

 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.

 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

//defining constants for slugs, path, name, and version
define( 'WAS_URL', plugin_dir_url(__FILE__) );
define( 'WAS_PATH', plugin_dir_path(__FILE__) );
define( 'WAS_SLUG','wp-academic-standards' );
define( 'WAS_FILE',__FILE__);
define( 'WAS_PLUGIN_NAME', 'WP Academic Standards' );
define( 'WAS_ADMIN_PLUGIN_NAME', 'WP Academic Standards');
define( 'WAS_VERSION', '0.2.1' );

global $_oer_prefix, $message, $type;
$_oer_prefix = "oer_";

include_once(WAS_PATH.'includes/init.php');
include_once(WAS_PATH.'includes/functions.php');

register_activation_hook(__FILE__, 'was_create_table');
function was_create_table()
{
	global $wpdb;
	$subprefix = "oer_";

	//Change hard-coded table prefix to $wpdb->prefix
	$table_name = $wpdb->prefix . $subprefix . "core_standards";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name)
	{
	  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			    id int(20) NOT NULL AUTO_INCREMENT,
			    standard_name varchar(255) NOT NULL,
			    standard_url varchar(255) NOT NULL,
			    PRIMARY KEY (id)
			    );";
	  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	  dbDelta($sql);
       }

	//Change hard-coded table prefix to $wpdb->prefix
	$table_name = $wpdb->prefix . $subprefix . "sub_standards";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name)
	{
	  $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			    id int(20) NOT NULL AUTO_INCREMENT,
			    parent_id varchar(255) NOT NULL,
			    standard_title varchar(1000) NOT NULL,
			    url varchar(255) NOT NULL,
			    PRIMARY KEY (id)
			    );";
	  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	  dbDelta($sql);
	}

        // Alter substandards table and add pos field
        $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table_name."' AND column_name = 'pos'"  );

        if(empty($row)){
            $sql = "ALTER TABLE ".$table_name." ADD pos INT(11) NOT NULL";
            $wpdb->query($sql);
        }

	// One Time alteration of standard_title field size in sub standards table
	//$sql = "ALTER TABLE ".$table_name." MODIFY COLUMN standard_title VARCHAR(1000)";
        //$wpdb->query($sql);

	//Change hard-coded table prefix to $wpdb->prefix
	$table_name = $wpdb->prefix . $subprefix . "standard_notation";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name)
	 {
	   $sql = "CREATE TABLE IF NOT EXISTS $table_name (
			     id int(20) NOT NULL AUTO_INCREMENT,
			     parent_id varchar(255) NOT NULL,
			     standard_notation varchar(255) NOT NULL,
			     description varchar(1000) NOT NULL,
			     comment varchar(255) NOT NULL,
			     url varchar(255) NOT NULL,
			     PRIMARY KEY (id)
			     );";
	   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	   dbDelta($sql);
	}

        // Alter standard notation table and add pos field
        $row = $wpdb->get_results(  "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE table_name = '".$table_name."' AND column_name = 'pos'"  );

        if(empty($row)){
            $sql = "ALTER TABLE ".$table_name." ADD pos INT(11) NOT NULL";
            $wpdb->query($sql);
        }

	// One Time alteration of description field size in standard notation table
	//$sql = "ALTER TABLE ".$table_name." MODIFY COLUMN description VARCHAR(1000)";
        //$wpdb->query($sql);

    was_add_rewrites();
    //Trigger permalink reset
    flush_rewrite_rules();
}

//Load localization directory
add_action('plugins_loaded', 'was_load_textdomain');
function was_load_textdomain() {
	load_plugin_textdomain( 'wp-academic-standards', false, dirname( plugin_basename(__FILE__) ) . '/lang/' );
}

/** Add Settings Link on Plugins page **/
add_filter( 'plugin_action_links' , 'was_add_settings_link' , 10 , 2 );
/** Add Settings Link function **/
function was_add_settings_link( $links, $file ){
	if ( $file == plugin_basename(dirname(__FILE__).'/wp-academic-standards.php') ) {
		/** Insert settings link **/
		$link = "<a href='edit.php?post_type=standards&page=was_settings'>".__('Settings','wp-acad')."</a>";
		array_unshift($links, $link);
		/** End of Insert settings link **/
	}
	return $links;
}

// Add rewrite rule for substandards
add_action( 'init', 'was_add_rewrites', 10, 0 );
function was_add_rewrites($root_slug="standards")
{
	global $wp_rewrite;
	$root_slug = get_option('was_standard_slug');
	add_rewrite_tag( '%standard%', '([^&]+)' );
	add_rewrite_tag( '%substandard%' , '([^&]+)' );
	add_rewrite_tag( '%notation%' , '([^&]+)' );
	add_rewrite_rule( '^'.$root_slug.'/([^/]*)/?$', 'index.php?standard=$matches[1]', 'top' );
	add_rewrite_rule( '^'.$root_slug.'/([^/]*)/([^&]+)/?$', 'index.php?standard=$matches[1]&substandard=$matches[2]', 'top' );
	add_rewrite_rule( '^'.$root_slug.'/([^/]*)/([^&]+)/([^/]*)/?$', 'index.php?standard=$matches[1]&substandard=$matches[2]&notation=$matches[3]', 'top' );

	$flush_rewrite = get_option('oer_rewrite_rules');

	if ($flush_rewrite==false) {
		$wp_rewrite->init();
		$wp_rewrite->flush_rules();
		update_option('oer_rewrite_rules', true);
	}
}

add_filter( 'query_vars', 'was_add_query_vars' );
function was_add_query_vars( $vars ){
	$vars[] = "standard";
	$vars[] = "substandard";
	$vars[] = "notation";
	return $vars;
}

add_action( 'template_include' , 'was_assign_standard_template' );
function was_assign_standard_template($template) {
	global $wp_query;

	$url_path = trim(parse_url(add_query_arg(array()), PHP_URL_PATH), '/');

        $root_slug = get_option('was_standard_slug');
        if (!$root_slug || $root_slug==""){
            $root_slug = "standards";
        }

	status_header(200);

	$slug_chars = strlen($root_slug);

	if ( substr($url_path,-$slug_chars)==$root_slug && !get_query_var('standard') && !get_query_var('substandard') && !get_query_var('notation') ) {
		// load the file if exists
		$wp_query->is_404 = false;
		$template = locate_template('template/frontend/standards.php', true);
		if (!$template) {
			$template = dirname(__FILE__) . '/template/frontend/standards.php';
		}
	} elseif (get_query_var('standard') && !get_query_var('substandard') && !get_query_var('notation')){
		$wp_query->is_404 = false;
		$template = locate_template('template/frontend/template-standard.php', true);
		if (!$template) {
			$template = dirname(__FILE__) . '/template/frontend/template-standard.php';
		}
	} elseif (get_query_var('standard') && get_query_var('substandard') && !get_query_var('notation')){
		$wp_query->is_404 = false;
		$template = locate_template('template/frontend/template-substandard.php', true);
		if (!$template) {
			$template = dirname(__FILE__) . '/template/frontend/template-substandard.php';
		}
	} elseif (get_query_var('standard') && get_query_var('substandard') && get_query_var('notation')){
		$wp_query->is_404 = false;
		$template = locate_template('template/frontend/template-notation.php', true);
		if (!$template) {
			$template = dirname(__FILE__) . '/template/frontend/template-notation.php';
		}
	}
	return $template;
}

?>
