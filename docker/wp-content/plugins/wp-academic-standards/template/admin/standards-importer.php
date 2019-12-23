<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/** Import Page **/
global $wpdb;

$message = isset($_GET['message'])?urldecode($_GET['message']):null;
$type = isset($_GET['type'])?urldecode($_GET['type']):null;

if ($type=="lr"){
	if ($message=="0")
		$message = "No record was imported.";
	elseif ($message=="1")
		$message .= " record imported.";
	else
		$message .= " records imported.";

	$type="success";
}

if (!current_user_can('manage_options')) {
	wp_die( "You don't have permission to access this page!" );
}
?>
<div class="wrap">
	<?php settings_errors(); ?>
<div id="importAcademicStandards" class="container">
	<form method="post" id="standards_form" action="<?php echo esc_url( admin_url('admin.php') ); ?>" onsubmit="return importWASStandards('#standards_form','#standards_submit')">
		<fieldset>
			<legend><div class="oer_heading"><?php _e("Import Academic Standards", WAS_SLUG); ?></div></legend>
			<?php if ($message) { ?>
    			<div class="notice notice-<?php echo $type; ?> is-dismissible">
    			    <p><?php echo $message; ?></p>
    			</div>
			<?php } ?>
			<div class="oer-import-row">
				<div class="row-left">
					<?php _e("Resources can be easily tagged to standards to provide additional alignment information to viewers. Datasets for the standards listed below are included with the plugin.", WAS_SLUG); ?>
				</div>
				<div class="row-right alignRight">
					<a href="http://asn.jesandco.org/resources/ASNJurisdiction/CCSS" target="_blank"><?php _e("ASN Standards Info", WAS_SLUG); ?></a>
				</div>
			</div>
			<div class="oer-import-row">
				<div class="import-row">
					<div class="fields">
						<table class="form-table">
							<tbody>
								<tr>
									<td>
										<?php
											$math = was_isStandardExisting("Math");
											$attr = "";
											$hidden = "";
											$class = "was-core-standard";
											$checkbox = "";
											if ($math){
												$attr = "disabled";
												$hidden = "class='hidden'";
												$class .= " disabled";
												$checkbox = '<span><i class="far fa-check-square"></i></span>';
											}

										?>
										<input name="oer_common_core_mathematics" id="oer_common_core_mathematics" <?php echo $hidden; ?> type="checkbox" value="1" <?php echo esc_attr($attr); ?>><?php echo $checkbox; ?><label for="oer_common_core_mathematics" class="<?php echo $class; ?>"><strong>Common Core Mathematics</strong> <?php if ($math): ?><span class="prev-import">(previously imported)</span><?php endif; ?></label>
									</td>
								</tr>
								<tr>
									<td>
										<?php
											$english = was_isStandardExisting("English");
											$attr = "";
											$hidden = "";
											$class = "was-core-standard";
											$checkbox = "";
											if ($english){
												$attr = "disabled";
												$class .= " disabled";
												$hidden = "class='hidden'";
												$checkbox = '<span><i class="far fa-check-square"></i></span>';
											}

										?>
										<input name="oer_common_core_english" id="oer_common_core_english" <?php echo $hidden; ?> type="checkbox" value="1" <?php echo esc_attr($attr); ?>><?php echo $checkbox; ?><label for="oer_common_core_english"  class="<?php echo $class; ?>"><strong>Common Core English Language Arts</strong> <?php if ($english): ?><span class="prev-import">(previously imported)</span><?php endif; ?></label>
									</td>
								</tr>
								<tr>
									<td>
										<?php
											$science = was_isStandardExisting("Next Generation Science");
											$attr = "";
											$hidden = "";
											$class = "was-core-standard";
											$checkbox = "";
											if ($science){
												$attr = "disabled";
												$class .= " disabled";
												$hidden = "class='hidden'";
												$checkbox = '<span><i class="far fa-check-square"></i></span>';
											}
										?>
										<input name="oer_next_generation_science" id="oer_next_generation_science" <?php echo $hidden; ?> type="checkbox" value="1" <?php echo esc_attr($attr); ?>><?php echo $checkbox; ?><label for="oer_next_generation_science" class="<?php echo $class; ?>"><strong>Next Generation Science Standards</strong> <?php if ($science): ?><span class="prev-import">(previously imported)</span><?php endif; ?></label>
									</td>
								</tr>
								<?php
								if ($others = get_option("oer_standard_others")) {
									$oIndex = 1;
									if (is_array($others)) {
										$loaded = array();
										foreach($others as $other){
											$class = "was-core-standard";
											if (!in_array($other['other_title'],$loaded)){
												$class .= " disabled";
								?>
								<tr>
									<td>

										<?php echo $checkbox; ?><label for="oer_other_standard_<?php echo $oIndex; ?>"  class="<?php echo $class; ?>"><strong><?php echo $other['other_title']; ?></strong> <span class="prev-import">(previously imported)</span></label>
									</td>
								</tr>
								<?php
											$loaded[] = $other['other_title'];
											}
											$oIndex++;
										}
									}
								}
								?>
								<tr>
									<td>
										<input name="oer_standard_other" id="oer_standard_other" type="checkbox" value="1"><label class="was-core-standard" for="oer_other_standards"><strong>Other</strong></label> <input name="oer_standard_other_url" class="large-text auto-width" id="oer_standard_other_url" type="textbox" disabled> <span class="field-error hidden notice-red">Invalid format! Only XML is allowed</span>
										<p class="description">Supports any XML standard set available from <a href="http://asn.desire2learn.com/resources/ASNJurisdiction" target="_blank">Achievement Standards Network</a></p>
									</td>
								</tr>
							</tbody>
						</table>
						<input type="hidden" value="" name="standards_import" />
					</div>
				</div>
				<div class="import-row">
					<div class="fields alignRight">
						<input type="hidden" name="action" value="import_standards">
						<?php wp_nonce_field( 'oer_standards_nonce_field' ); ?>
						<input type="submit" id="standards_submit" name="" value="<?php esc_attr(_e("Import", WAS_SLUG)); ?>" class="button button-primary"/>
					</div>
				</div>
			</div>
		</fieldset>
	</form>
</div>
</div>
<div class="plugin-footer">
	<div class="plugin-info"><?php echo WAS_ADMIN_PLUGIN_NAME . " " . WAS_VERSION .""; ?></div>
	<div class="clear"></div>
</div>
<?php was_display_loader(); ?>
