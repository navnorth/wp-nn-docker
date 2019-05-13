<?php
  /*
  Plugin name: External Content Csv importer
  Plugin URI: http://PLUGIN_URI.com/
  Description: Automatically import HTML content from external web pages using csv
  Author: Navigation North
  Author URI: https://www.navigationnorth.com
  Version: 1.0
  */

if(!defined('ABSPATH')){
    die;
}

define("OESE_PLUGIN_PATH", plugin_dir_path(__FILE__));

include_once(OESE_PLUGIN_PATH . "/classes/oese-external-importer.php");

function activate(){

}

// Activation

register_activation_hook(__FILE__,'activate');



?>