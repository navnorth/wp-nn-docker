<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
global $wpdb;

if (isset($_REQUEST['std'])){
    include_once(WAS_PATH."template/admin/standard-children.php");
} else {
    if (isset($_REQUEST['delete'])){
        $standard_title = null;
        
        $standard = was_standard_by_id($_REQUEST['delete']);
        if ($standard)
            $standard_title = $standard->standard_name;
            
        was_admin_delete_standard($_REQUEST['delete']);
        
        $imported_standards = get_option("oer_standard_others");
        
        if ($standard_title) {
            $element = was_search_imported_standards($imported_standards, $standard_title);
            if ($element){
                foreach($imported_standards as $key=>$val){
                    if ($key==$element){
                        unset($val['other_title']);;
                        unset($imported_standards[$key]);
                    }
                }
            }
            update_option("oer_standard_others", $imported_standards);
        }
    }
?>
<div class="wrap">
    <div id="icon-themes" class="icon32"></div>
    <div class="wrap-header">
        <h1 class="wp-heading-inline"><?php _e('WP Academic Standards', WAS_SLUG); ?></h1>
        <a data-target="#addStandardModal" class="page-title-action" id="addStandardSet">Add New Standard Set</a>
    </div>
    <div class="notice notice-success standards-notice-success is-dismissible hidden-block"></div>
    <div id="admin-standard-list">
    <?php was_display_admin_core_standards(); ?>
    </div>
</div>
<?php } ?>