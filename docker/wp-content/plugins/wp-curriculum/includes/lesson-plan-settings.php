<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $message, $type;

	if (isset($_REQUEST['settings-updated'])) {
		if (!current_user_can('manage_options')) {
			wp_die( "You don't have permission to access this page!" );
		}
	}
	
?>
<div class="wrap">
    
    <div id="icon-themes" class="oer-logo"><img src="<?php echo OER_URL ?>images/wp-oer-admin-logo.png" /></div>
    <h2><?php _e("Settings - WP Curriculum", OER_LESSON_PLAN_SLUG); ?></h2>
    <?php settings_errors(); ?>
     
	<?php
	$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : 'metadata';
	?>
     
    <h2 class="nav-tab-wrapper">
        <a href="?post_type=resource&page=oer_settings&tab=general" class="nav-tab <?php echo $active_tab == 'metadata' ? 'nav-tab-active' : ''; ?>"><?php _e("Metadata", OER_LESSON_PLAN_SLUG); ?></a>
    </h2>
    
    <?php
	switch ($active_tab) {
		case "metadata":
			oer_curriculum_show_metadata_settings();
			break;
		default:
			break;
	}
    ?>
</div><!-- /.wrap -->
<div class="oer-plugin-footer">
	<div class="oer-plugin-info"><?php echo OER_LESSON_PLAN_PLUGIN_NAME . " " . OER_LESSON_PLAN_VERSION .""; ?></div>
	<div class="oer-plugin-link"><a href='https://www.wp-oer.com/' target='_blank'><?php _e("More Information", OER_LESSON_PLAN_SLUG); ?></a></div>
	<div class="clear"></div>
</div>
<?php

function oer_curriculum_show_metadata_settings() {
    global $oer_lp_deleted_fields;
	$metas = oer_lp_get_all_meta("lesson-plans");
	$inquirysets = null;
	$metadata = null;
	
	foreach($metas as $met){
		if (strpos($met['meta_key'],"oer_")!==false){
			$metadata[] = $met['meta_key'];
		}
	}
	
	// Add Options for related inquiry sets 1-3
	for($i=1;$i<=3;$i++){
		$metadata[] = $inquirysets[] = 'oer_lp_related_inquiry_set_'.$i;
	}
	
	$meta = array_unique($metadata);
	
	// Save Option
	if ($_POST){
		// Remove meta key enabled option
		foreach($metas as $met){
			if (strpos($met['meta_key'],"oer_")!==false || strpos($met['meta_key'],"lp_oer_")!==false){
				delete_option($met['meta_key']."_enabled");
			}
		}
		foreach($inquirysets as $inquiryset){
			delete_option($inquiryset."_enabled");
		}
		oer_lp_save_metadata_options($_POST);
	}
?>
<div class="lesson-plan-plugin-body">
	<div class="lesson-plan-plugin-row">
		<div class="oer-row-left">
			<?php _e("Use the options below to update metadata field options.", OER_SLUG); ?>
		</div>
		<div class="oer-row-right">
		</div>
	</div>
	<div class="lesson-plan-plugin-row">
		<form method="post" class="oer_settings_form" onsubmit="return lpInitialSettings(this);">
			<table class="table">
				<thead>
					<tr>
						<th>Field Name</th>
						<th>Label</th>
						<th>Enabled</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($meta as $key) {
						$label = "";
						$enabled = "0";
						$option_set = false;
							if (get_option($key."_label")){
								$label = get_option($key."_label");
								$option_set = true;
							}
							else
								$label = oer_lp_get_meta_label($key);
							
							if (get_option($key."_enabled"))
								$enabled = (get_option($key."_enabled")=="1")?true:false;
							elseif ($option_set==false)
								$enabled = "1";
						
                        if (!in_array($key,$oer_lp_deleted_fields)){	
                        ?>
                        <tr>
                            <td><?php echo $key; ?></td>
                            <td><input type="text" name="<?php echo $key."_label"; ?>" value="<?php echo $label; ?>" /></td>
                            <td><input type="checkbox" name="<?php echo $key."_enabled"; ?>" value="1" <?php checked($enabled,"1",true); ?>/></td>
                        </tr>
                        <?php
                        }
					} ?>
				</tbody>
			</table>
			<?php submit_button("Save Metadata Options"); ?>
		</form>
	</div>
</div>
<?php
}
oer_curriculum_display_loader();