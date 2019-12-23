<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Load Admin Scripts
add_action( 'admin_enqueue_scripts' , 'was_load_admin_scripts' );
function was_load_admin_scripts(){
    $font_awesome = array('font-awesome', 'fontawesome');
    if (was_stylesheet_installed($font_awesome)===false)
        wp_enqueue_style( 'fontawesome', WAS_URL.'lib/fontawesome/css/all.css');
    wp_enqueue_style( 'admin-css', WAS_URL.'css/admin.css');
    wp_enqueue_style( 'bootstrap-css', WAS_URL.'lib/bootstrap/css/bootstrap.min.css');
    wp_enqueue_script( 'bootstrap-js', WAS_URL.'lib/bootstrap/js/bootstrap.min.js', array('jquery'));
    wp_enqueue_script( 'admin-js', WAS_URL.'js/admin.js', array('jquery'));
    wp_localize_script( 'admin-js', 'WPURLS', array( "site_url" => site_url(), "admin_url" => admin_url() ) );
}

// Load Frontend Scripts
add_action('wp_enqueue_scripts', 'was_load_frontend_scripts');
function was_load_frontend_scripts()
{
    $font_awesome = array('font-awesome', 'fontawesome');
    if (was_stylesheet_installed($font_awesome)===false)
        wp_enqueue_style( 'fontawesome', WAS_URL.'lib/fontawesome/css/all.css');
    wp_enqueue_style('was-styles', WAS_URL.'css/standards.css');
}

// Add Standards Menu on Admin
add_action( 'admin_menu' , 'add_standards_menu' );
function add_standards_menu(){
    add_menu_page(__("WP Acamedic Standards", WAS_SLUG),
                  __("Standards",WAS_SLUG),
                  "edit_posts",
                  "wp-academic-standards",
                  "wp_academic_standards_page",
                  "dashicons-awards",
                  26);
    add_submenu_page("wp-academic-standards",
                     __("Import Standards", WAS_SLUG),
                     __("Import", WAS_SLUG),
                     "edit_posts",
                     "import-standards",
                     "was_import_standards_page");
    add_submenu_page("wp-academic-standards",
                     __("Academic Standards Settings", WAS_SLUG),
                     __("Settings", WAS_SLUG),
                     "edit_posts",
                     "standards-settings",
                     "was_standards_settings_page");
}

function wp_academic_standards_page(){
    include_once(WAS_PATH."/template/admin/standards.php");
}

function was_import_standards_page(){
    include_once(WAS_PATH."/template/admin/standards-importer.php");
}

function was_standards_settings_page(){
    include_once(WAS_PATH."/template/admin/settings.php");
}

/**
 * Process Import Standards
 **/
add_action("admin_action_import_standards","import_was_standards");
function import_was_standards(){
    require_once(WAS_PATH."classes/class-standards-importer.php");
    $standard_importer = new was_standards_importer;

    $message = null;
    $type = null;
    $other = false;

    if (!current_user_can('manage_options')) {
	    wp_die( "You don't have permission to access this page!" );
    }

    //Standards Bulk Import
    if(isset($_POST['standards_import']))
    {
	check_admin_referer('oer_standards_nonce_field');

	$files = array();

	if (isset($_POST['oer_common_core_mathematics'])){
	       $files[] = WAS_PATH."samples/CCSS_Math.xml";
	}

	if (isset($_POST['oer_common_core_english'])){
	       $files[] = WAS_PATH."samples/CCSS_ELA.xml";
	}

	if (isset($_POST['oer_next_generation_science'])){
	       $files[] = WAS_PATH."samples/NGSS.xml";
	}

	if (isset($_POST['oer_standard_other']) && isset($_POST['oer_standard_other_url'])){
	       $files[] = $standard_importer->download_standard($_POST['oer_standard_other_url']);
	       $other = true;
	}
	
	foreach ($files as $file) {
	    $import = $standard_importer->import_standard($file, $other);
	    
	    if ($import['type']=="success") {
		if (strpos($file,'Math')) {
		    $message .= "Successfully imported Common Core Mathematics Standards. \n";
		} elseif (strpos($file,'ELA')) {
		    $message .= "Successfully imported Common Core English Language Arts Standards. \n";
		} elseif (strpos($file,'NGSS')) {
		    $message .= "Successfully imported Next Generation Science Standards. \n";
		} else {
		    $message .= "Successfully imported standards. \n";
		}
	    }
	    $type = urlencode($import['type']);
	}
	$message = urlencode($message);
    }

    wp_safe_redirect( admin_url("admin.php?page=import-standards&message=$message&type=$type"));
    exit;
}

//Initialize Setup Settings Tab
add_action( 'admin_init' , 'was_setup_settings' );
function was_setup_settings(){

	//Create Setup Section
	add_settings_section(
		'was_setup_settings',
		'',
		'was_setup_settings_callback',
		'standards-settings'
	);

	//Add Settings field for Importing Common Core State Standards
	add_settings_field(
		'was_import_ccss',
		'',
		'was_setup_settings_field',
		'standards-settings',
		'was_setup_settings',
		array(
			'uid' => 'was_import_ccss',
			'type' => 'checkbox',
			'value' => '1',
			'name' =>  __('Import Common Core State Standards', WAS_SLUG),
			'description' => __('Enable use of CCSS as an optional alignment option for resources.', WAS_SLUG)
		)
	);
	
	//Add Settings field for Importing California History-Social Science Standards
	add_settings_field(
		'was_import_chsss',
		'',
		'was_setup_settings_field',
		'standards-settings',
		'was_setup_settings',
		array(
			'uid' => 'was_import_chsss',
			'type' => 'checkbox',
			'value' => '1',
			'name' =>  __('California History-Social Science Standards', WAS_SLUG),
			'description' => __('Enable use of California History-Social Science Standards as an optional alignment option for resources.', WAS_SLUG)
		)
	);

        //Set API Secret for Url2PNG
	add_settings_field(
		'was_standard_slug',
		__("Standards Root Slug", WAS_SLUG),
		'was_setup_settings_field',
		'standards-settings',
		'was_setup_settings',
		array(
			'uid' => 'was_standard_slug',
			'type' => 'textbox',
                        'default' => "standards",
			'title' => __('Standards Root Slug', WAS_SLUG)
		)
	);

        register_setting( 'was_setup_settings' , 'was_import_ccss' );
	register_setting( 'was_setup_settings' , 'was_import_chsss' );
	register_setting( 'was_setup_settings' , 'was_standard_slug' );
}

//Setup Setting Callback
function was_setup_settings_callback(){

}

function was_setup_settings_field( $arguments ) {
	$selected = "";
	$size = "";
	$class = "";
	$disabled = "";
	$wrapper_class = "";
	$data_masked = "";

	$value = get_option($arguments['uid']);

	if (isset($arguments['masked'])){
		$data_masked = "data-hidden='".$value."'";
		$value = oer_mask_string($value, 4, 7);
	}

	if (isset($arguments['indent'])){
		if (isset($arguments['wrapper_class']))
			$wrapper_class = $arguments['wrapper_class'];
		echo '<div class="indent '.$wrapper_class.'">';
	}

	if (isset($arguments['class'])) {
		$class = $arguments['class'];
		$class = " class='".$class."' ";
	}

	if (isset($arguments['pre_html'])) {
		echo $arguments['pre_html'];
	}

	switch($arguments['type']){
		case "textbox":
			$size = 'size="50"';
			if (isset($arguments['title']))
				$title = $arguments['title'];
			echo '<label for="'.$arguments['uid'].'"><strong>'.$title.'</strong></label><input name="'.$arguments['uid'].'" id="'.$arguments['uid'].'" type="'.$arguments['type'].'" value="' . $value . '" ' . $size . ' ' .  $selected . ' ' . $data_masked . ' />';
			break;
		case "checkbox":
			$display_value = "";
			$selected = "";

			if ($value=="1" || $value=="on"){
				$selected = "checked='checked'";
				$display_value = "value='1'";
			} elseif ($value===false){
				$selected = "";
				if (isset($arguments['default'])) {
					if ($arguments['default']==true){
						$selected = "checked='checked'";
					}
				}
			} else {
				$selected = "";
			}

			if (isset($arguments['disabled'])){
				if ($arguments['disabled']==true)
					$disabled = " disabled";
			}

			echo '<input name="'.$arguments['uid'].'" id="'.$arguments['uid'].'" '.$class.' type="'.$arguments['type'].'" ' . $display_value . ' ' . $size . ' ' .  $selected . ' ' . $disabled . '  /><label for="'.$arguments['uid'].'"><strong>'.$arguments['name'].'</strong></label>';
			break;
		case "select":
			if (isset($arguments['name']))
				$title = $arguments['name'];
			echo '<label for="'.$arguments['uid'].'"><strong>'.$title.'</strong></label>';
			echo '<select name="'.$arguments['uid'].'" id="'.$arguments['uid'].'">';

			if (isset($arguments['options']))
				$options = $arguments['options'];

			foreach($options as $key=>$desc){
				$selected = "";
				if ($value===false){
					if ($key==$arguments['default'])
						$selected = " selected";
				} else {
					if ($key==$value)
						$selected = " selected";
				}
				$disabled = "";
				switch ($key){
					case 3:
						if(!shortcode_exists('wonderplugin_pdf'))
							$disabled = " disabled";
						break;
					case 4:
						if (!shortcode_exists('pdf-embedder'))
							$disabled = " disabled";
						break;
					case 5:
						if(!shortcode_exists('pdfviewer'))
							$disabled = " disabled";
						break;
					default:
						break;
				}
				echo '<option value="'.$key.'"'.$selected.''.$disabled.'>'.$desc.'</option>';
			}

			echo '<select>';
			break;
		case "textarea":
			echo '<label for="'.$arguments['uid'].'"><h3><strong>'.$arguments['name'];
			if (isset($arguments['inline_description']))
				echo '<span class="inline-desc">'.$arguments['inline_description'].'</span>';
			echo '</strong></h3></label>';
			echo '<textarea name="'.$arguments['uid'].'" id="'.$arguments['uid'].'" rows="10">' . $value . '</textarea>';
			break;
		default:
			break;
	}

	//Show Helper Text if specified
	if (isset($arguments['helper'])) {
		printf( '<span class="helper"> %s</span>' , $arguments['helper'] );
	}

	//Show Description if specified
	if( isset($arguments['description']) ){
		printf( '<p class="description">%s</p>', $arguments['description'] );
	}

	if (isset($arguments['indent'])){
		echo '</div>';
	}
}


add_action( 'wp_loaded', 'was_process_settings_form' );
function was_process_settings_form(){
    global $message, $type;
    if (isset($_REQUEST['settings-updated']) && (isset($_REQUEST['page']) && $_REQUEST['page']=="standards-settings")) {

        //Import CCSS Standards
        $import_ccss = get_option('was_import_ccss');
        if ($import_ccss) {
            $response = was_importDefaultStandards();
            if ($response) {
                $message .= $response["message"];
                $type .= $response["type"];
            }
        }
	
	//Import CHSSS Standards
        $import_chsss = get_option('was_import_chsss');
	if ($import_chsss){
	    $response = was_importCaliforniaHistoryStandards();
            if ($response) {
                $message .= $response["message"];
                $type .= $response["type"];
            }
	}

        // Standards slug Root
        $standard_root_slug = get_option('was_standard_slug');
        if (isset($standard_root_slug) && $standard_root_slug!==""){
            was_add_rewrites($standard_root_slug);
            //Trigger permalink reset
            flush_rewrite_rules();
            $message = "Permalink structure has been reset for standards root slug ".$standard_root_slug;
            $type = "success";
        }

        //Redirect to main settings page
        wp_redirect( admin_url( 'admin.php?page=standards-settings' ) );
        exit();
    }
}

add_action( "admin_footer" , "was_edit_standard_modal" );
function was_edit_standard_modal(){
    include_once(WAS_PATH."template/admin/modals/edit_standard_modal.php");
}

add_action( "admin_footer" , "was_add_standard_modal" );
function was_add_standard_modal(){
    include_once(WAS_PATH."template/admin/modals/add_standard_modal.php");
}

add_action('wp_ajax_get_standard_details', 'was_get_standard_details');
function was_get_standard_details(){
	$std_id = null;

	if (isset($_POST['std_id'])){
		$std_id = $_POST['std_id'];
	}

	if (!$std_id){
		echo "Invalid Standard ID";
		die();
	}

        $details = was_standard_details($std_id);
        echo json_encode($details);

	die();
}

add_action('wp_ajax_update_standard', 'was_update_standard');
function was_update_standard(){
    global $wpdb;
    $standard = null;
    $success = null;

    if (isset($_POST['details'])){
        $standard = $_POST['details'];
    }

    if (array_key_exists("standard_name", $standard)){
        $success = $wpdb->update(
            $wpdb->prefix."oer_core_standards",
            array(
                "standard_name" => sanitize_text_field($standard['standard_name']),
                "standard_url" => $standard['standard_url']
            ),
            array( "id" => $standard['id'] ),
            array(
                "%s",
                "%s"
            ),
            array( "%d" )
        );
    } elseif (array_key_exists("standard_title", $standard)) {
        $success = $wpdb->update(
            $wpdb->prefix."oer_sub_standards",
            array(
                "standard_title" => sanitize_text_field($standard['standard_title']),
                "url" => $standard['url']
            ),
            array( "id" => $standard['id'] ),
            array(
                "%s",
                "%s"
            ),
            array( "%d" )
        );
    } elseif (array_key_exists("standard_notation", $standard)) {
        $success = $wpdb->update(
            $wpdb->prefix."oer_standard_notation",
            array(
                "standard_notation" => sanitize_text_field($standard['standard_notation']),
                "description" => $standard['description'],
                "comment" => $standard['comment'],
                "url" => $standard['url']
            ),
            array( "id" => $standard['id'] ),
            array(
                "%s",
                "%s",
                "%s",
                "%s"
            ),
            array( "%d" )
        );
    }

    $response = array("success"=>$success,"standard"=>$standard);

    echo json_encode($response);

    die();
}

add_action('wp_ajax_add_standard', 'was_add_standard');
function was_add_standard(){
    global $wpdb;
    $standard = null;
    $success = null;
    $lastid = null;

    if (isset($_POST['details'])){
        $standard = $_POST['details'];
    }

    if (array_key_exists("standard_title", $standard)){
        $success = $wpdb->insert(
            $wpdb->prefix."oer_sub_standards",
            array(
                "parent_id" => $standard['parent_id'],
                "standard_title" => sanitize_text_field($standard['standard_title']),
                "url" => $standard['standard_url']
            ),
            array(
                "%s",
                "%s",
                "%s"
            )
        );
    } elseif (array_key_exists("standard_notation", $standard)) {
        $success = $wpdb->insert(
            $wpdb->prefix."oer_standard_notation",
            array(
                "parent_id" => $standard['parent_id'],
                "standard_notation" => sanitize_text_field($standard['standard_notation']),
                "description" => $standard['description'],
                "comment" => $standard['comment'],
                "url" => $standard['url']
            ),
            array(
                "%s",
                "%s",
                "%s",
                "%s",
                "%s"
            )
        );
    } elseif (array_key_exists("standard_name", $standard)){
        $success = $wpdb->insert(
            $wpdb->prefix."oer_core_standards",
            array(
                "standard_name" => sanitize_text_field($standard['standard_name']),
                "standard_url" => $standard['standard_url']
            ),
            array(
                "%s",
                "%s"
            )
        );
    }

    $lastid = $wpdb->insert_id;

    echo json_encode(array("success"=>$success, "id" => $lastid));

    die();
}

add_action('wp_ajax_load_admin_standards', 'was_load_admin_standards');
function was_load_admin_standards(){
	was_display_admin_standards();

	die();
}

add_action('wp_ajax_delete_standard', 'was_delete_standard');
function was_delete_standard(){
    global $wpdb;
    $standard_id = null;
    $success = null;

    if (isset($_POST['standard_id'])){
        $standard_id = $_POST['standard_id'];
    }

    if ($standard_id){
        $success = $wpdb->delete(
            $wpdb->prefix."oer_standard_notation",
            array("id" => $standard_id)
        );
    }

    echo $success;

    die();
}

add_action("wp_ajax_update_standard_position", "was_update_standard_position");
function was_update_standard_position(){
    global $wpdb;
    $standard_id = null;
    $pos = 0;
    $success = null;
    $table = null;
    $id = 0;

    if (isset($_POST['standard_id'])){
        $standard_id = $_POST['standard_id'];
    }

    if (isset($_POST['position'])){
        $pos = $_POST['position'];
    }


    if ($standard_id && $pos){
        $stds = explode("-", $standard_id);
        if (!empty($stds)){
            $table = $stds[0];
            $id = $stds[1];

            $success = $wpdb->update(
                $wpdb->prefix."oer_".$table,
                array(
                    "pos" => $pos
                ),
                array( "id" => $id ),
                array(
                    "%d"
                ),
                array( "%d" )
            );

        }
    }

    die();
}
?>
