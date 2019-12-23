<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $message, $type;

if (!current_user_can('manage_options')) {
    wp_die( "You don't have permission to access this page!" );
}
?>
<div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <h2 class="was-plugin-page-title"><?php _e("Settings - WP Academic Standards", WAS_SLUG); ?></h2>
    <?php settings_errors(); ?>
    <?php was_show_setup_settings(); ?>
</div><!-- /.wrap -->
<div class="was-plugin-footer">
    <div class="plugin-info"><?php echo WAS_ADMIN_PLUGIN_NAME . " " . WAS_VERSION .""; ?></div>
    <div class="clear"></div>
</div>
<?php was_display_loader(); ?>