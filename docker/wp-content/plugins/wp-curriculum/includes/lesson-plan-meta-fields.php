<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;
global $wpdb;
global $oer_lp_default_structure;
global $oer_convert_info;
global $inquiryset_post;

$inquiryset_post = $post;
// Get all post meta for the post
$post_meta_data = get_post_meta($post->ID );
//echo "<pre>"; print_r(get_post_custom($post->ID, '', true ));

// Lesson activity data
$oer_lp_activity_title  = isset($post_meta_data['oer_lp_activity_title'][0]) ? unserialize($post_meta_data['oer_lp_activity_title'][0]) : array();
$oer_lp_activity_type   = isset($post_meta_data['oer_lp_activity_type'][0]) ? unserialize($post_meta_data['oer_lp_activity_type'][0]) : array();
$oer_lp_activity_detail = isset($post_meta_data['oer_lp_activity_detail'][0]) ? unserialize($post_meta_data['oer_lp_activity_detail'][0]) : array();

$elements_orders        = isset($post_meta_data['lp_order'][0]) ? unserialize($post_meta_data['lp_order'][0]) : array();
//was_selectable_admin_standards($post->ID, "oer_standard");
foreach ($elements_orders as $orderKey => $orderValue) {
    if (isset($post_meta_data[$orderKey]) && strpos($orderKey, 'oer_lp_custom_text_list_') !== false) {
      // print_r($post_meta_data[$orderKey]); echo  "<br/>";
    }
    //echo "Key -> " . $orderKey . "  value -> " . $orderValue ."<br/>";
}
$default = false;

// Check Metadata settings for label and if enabled
$author_set = (get_option('oer_lp_authors_label'))?true:false;
$author_enabled = (get_option('oer_lp_authors_enabled'))?true:false;
$primary_resources_set = (get_option('oer_lp_primary_resources_label'))?true:false;
$primary_resources_enabled = (get_option('oer_lp_primary_resources_enabled'))?true:false;
$materials_set = (get_option('lp_oer_materials_label'))?true:false;
$materials_enabled = (get_option('lp_oer_materials_enabled'))?true:false;
$iq_set = (get_option('oer_lp_iq_label'))?true:false;
$iq_enabled = (get_option('oer_lp_iq_enabled'))?true:false;
$req_materials_set = (get_option('oer_lp_required_materials_label'))?true:false;
$req_materials_enabled = (get_option('oer_lp_required_materials_enabled'))?true:false;
$history_bg_set = (get_option('oer_lp_custom_editor_historical_background_label'))?true:false;
$history_bg_enabled = (get_option('oer_lp_custom_editor_historical_background_enabled'))?true:false;
$type_set = (get_option('oer_lp_type_label'))?true:false;
$type_enabled = (get_option('oer_lp_type_enabled'))?true:false;
$standards_set = (get_option('oer_lp_standards_label'))?true:false;
$standards_enabled = (get_option('oer_lp_standards_enabled'))?true:false;

?>
<div class="lesson_plan_meta_wrapper">
    <div class="row">
        <div class="col-md-3">
            <ul class="list-group">
                <?php
                if (!empty($elements_orders) || isset($oer_lp_default_structure)) {
                    // Set order of modules
                    if (isset($oer_lp_default_structure) && empty($elements_orders)){
                        $default = true;
                        $index = 0;
                        foreach($oer_lp_default_structure as $module){
                            $index++;
                            $elements_orders[$module] = $index;
                        }
                    }
                    foreach ($elements_orders as $elementKey => $value) {
                        if($elementKey == 'lp_introduction_order') {?>
                            <li class="list-group-item">
                                <a href="#oer-lp-introduction-group" title="Introduction">Introduction</a>
                            </li>
                        <?php } elseif ($elementKey == 'lp_authors_order') {
                            if (($author_set && $author_enabled) || !$author_set) {
                            ?>
                            <li class="list-group-item">
                                <a href="#oer-lp-authors" title="Lesson Times">
                                    <?php
                                    echo oer_lp_get_field_label('oer_lp_authors');
                                    ?>
                                </a>
                            </li>
                        <?php }
                            } elseif ($elementKey == 'lp_primary_resources') {
                            if (is_oer_plugin_installed()){
                                if (($primary_resources_set && $primary_resources_enabled) || !$primary_resources_set) {
                            ?>
                            <li class="list-group-item">
                                <a href="#oer-lp-primary-resources" title="Primary Resources">
                                    <?php
                                    echo oer_lp_get_field_label('oer_lp_primary_resources');
                                    ?>
                                </a>
                            </li>
                        <?php }
                            }
                        } elseif ($elementKey == 'lp_oer_materials') {
                            if (($materials_set && $materials_enabled) || !$materials_set) {
                            ?>
                            <li class="list-group-item">
                                <a href="#oer-lp-materials" class="js-scroll-trigger" title="Materials">
                                <?php
                                echo oer_lp_get_field_label('lp_oer_materials');
                                ?>
                                </a>
                            </li>
                        <?php }
                        } elseif ($elementKey == 'lp_iq') {
                            if (($iq_set && $iq_enabled) || !$iq_set) {
                            ?>
                            <li class="list-group-item">
                                <a href="#oer-lp-iq" title="Investigative Question"><?php echo oer_lp_get_field_label('oer_lp_iq'); ?></a>
                            </li>
                        <?php }
                        } elseif ($elementKey == 'lp_required_materials') {
                            if (($req_materials_set && $req_materials_enabled) || !$req_materials_set) {
                            ?>
                            <li class="list-group-item">
                                <a href="#oer-lp-required-materials" title="Required Materials"><?php echo oer_lp_get_field_label('oer_lp_required_materials');  ?></a>
                            </li>
                        <?php }
                        } elseif ($elementKey == 'lp_lesson_times_order') {?>
                            <li class="list-group-item">
                                <a href="#oer-lp-times-group" title="Lesson Times">Lesson Times</a>
                            </li>
                        <?php } elseif ($elementKey == 'lp_standard_order') {
                             if (($standards_set && $standards_enabled) || !$standards_set) { ?>
                            <li class="list-group-item">
                                <a href="#oer-lp-standards-group" title="Standards and Objectives"><?php echo oer_lp_get_field_label('oer_lp_standards');  ?></a>
                            </li>
                        <?php }
                        } elseif ($elementKey == 'lp_activities_order') {?>
                            <li class="list-group-item">
                                <a href="#oer-lp-activities-group" title="Activities in this Lesson">Activities in this Lesson</a>
                                <ul class="list-group sidebar-lesson-activities-title">
                                    <?php
                                    if(!empty($oer_lp_activity_title)) {
                                        foreach ($oer_lp_activity_title as $key => $item) { ?>
                                            <li class="list-group-item">
                                                <strong>-</strong>
                                                <a href="#lp-ac-item-<?php echo $key;?>" title="<?php echo $item; ?>"><?php echo $item; ?></a>
                                            </li>
                                        <?php } ?>
                                    <?php } else {
                                        for ($i = 0; $i < 5; $i++) { ?>
                                            <li class="list-group-item">
                                                <strong>-</strong>
                                                <a href="#lp-ac-item-<?php echo $i;?>" title="Unnamed Activity">Unnamed Activity</a>
                                            </li>
                                        <?php }?>
                                    <?php }?>
                                </ul>
                            </li>
                        <?php } elseif ($elementKey == 'lp_summative_order') {?>
                            <li class="list-group-item">
                                <a href="#oer-lp-summative-group" title="Summative Assessment">Summative Assessment</a>
                            </li>
                        <?php } elseif (strpos($elementKey, 'oer_lp_custom_editor_teacher_background') !== false) {?>
                            <li class="list-group-item">
                                <a href="#oer-lp-custom-editor-group-teacher-background" title="Teacher Background">Teacher Background</a>
                            </li>
                        <?php } elseif (strpos($elementKey, 'oer_lp_custom_editor_student_background') !== false) {?>
                            <li class="list-group-item">
                                <a href="#oer-lp-custom-editor-group-student-background" title="Student Background">Student Background</a>
                            </li>
                       <?php }
                    }
                    if ($default==true)
                        $elements_orders = array();
                } else { ?>
                    <li class="list-group-item">
                        <a href="#oer-lp-introduction-group" title="Introduction">Introduction</a>
                    </li>
                    <?php if (($author_set && $author_enabled) || !$author_set) { ?>
                    <li class="list-group-item">
                        <a href="#oer-lp-authors" title="Authors"><?php echo oer_lp_get_field_label('oer_lp_authors'); ?></a>
                    </li>
                    <?php } ?>
                    <?php if (is_oer_plugin_installed()){ ?>
                    <?php if (($primary_resources_set && $primary_resources_enabled) || !$primary_resources_set) { ?>
                    <li class="list-group-item">
                        <a href="#oer-lp-primary-resources" title="Primary Resources"><?php echo oer_lp_get_field_label('oer_lp_primary_resources'); ?></a>
                    </li>
                    <?php } ?>
                    <?php } ?>
                    <?php if (($materials_set  && $materials_enabled ) || !$materials_set ) { ?>
                    <li class="list-group-item">
                        <a href="#oer-lp-materials" title="Materials"><?php echo oer_lp_get_field_label('lp_oer_materials'); ?></a>
                    </li>
                    <?php } ?>
                    <?php if (($iq_set && $iq_enabled) || !$iq_set) { ?>
                    <li class="list-group-item">
                        <a href="#oer-lp-iq" title="Investigative Question"><?php echo oer_lp_get_field_label('oer_lp_iq'); ?></a>
                    </li>
                    <?php } ?>
                    <?php if (($req_materials_set && $req_materials_enabled) || !$req_materials_set) { ?>
                     <li class="list-group-item">
                        <a href="#oer-lp-required-materials" title="Required Materials"><?php echo oer_lp_get_field_label('oer_lp_required_materials'); ?></a>
                    </li>
                     <?php } ?>
                    <li class="list-group-item">
                        <a href="#oer-lp-times-group" title="Lesson Times">Lesson Times</a>
                    </li>
                    <?php if (($standards_set && $standards_enabled) || !$standards_set) { ?>
                    <li class="list-group-item">
                        <a href="#oer-lp-standards-group" title="Standards and Objectives"><?php echo oer_lp_get_field_label('oer_lp_standards');  ?></a>
                    </li>
                    <?php } ?>
                    <li class="list-group-item">
                        <a href="#oer-lp-activities-group" title="Activities in this Lesson">Activities in this Lesson</a>
                        <ul class="list-group sidebar-lesson-activities-title">
                            <?php
                            if(!empty($oer_lp_activity_title)) {
                                foreach ($oer_lp_activity_title as $key => $item) { ?>
                                    <li class="list-group-item">
                                        <strong>-</strong>
                                        <a href="#lp-ac-item-<?php echo $key;?>" title="<?php echo $item; ?>"><?php echo $item; ?></a>
                                    </li>
                                <?php } ?>
                            <?php } else {
                                for ($i = 0; $i < 5; $i++) { ?>
                                    <li class="list-group-item">
                                        <strong>-</strong>
                                        <a href="#lp-ac-item-<?php echo $i;?>" title="Unnamed Activity">Unnamed Activity</a>
                                    </li>
                                <?php }?>
                            <?php }?>
                        </ul>
                    </li>
                    <li class="list-group-item">
                        <a href="#oer-lp-summative-group" title="Summative Assessment">Summative Assessment</a>
                    </li>
                <?php }?>
            </ul>
        </div>
        <div class="col-md-8" id="oer-lp-sortable">
            <!-- Details Module -->
            <div class="panel panel-default lp-element-wrapper oer-lp-details-group" id="oer-lp-details-group">
                <div class="panel-heading">
                    <h3 class="panel-title lp-module-title">
                        <?php _e("Details", OER_LESSON_PLAN_SLUG); ?>
                    </h3>
                </div>
                <div class="panel-body">
                    <?php
                    $oer_lp_type = isset($post_meta_data['oer_lp_type'][0]) ? $post_meta_data['oer_lp_type'][0] : "";
                    $oer_lp_type_other = isset($post_meta_data['oer_lp_type_other'][0]) ? $post_meta_data['oer_lp_type_other'][0] : "";
                    $xclass = " hidden";
                    if (($type_set && $type_enabled) || !$type_set) {
                        if ($oer_lp_type=="Other")
                            $xclass = "";
                        
                    ?>
                    <div class="form-group">
                        <label for="oer_lp_type"><?php echo oer_lp_get_field_label('oer_lp_type'); ?></label>
                        <select name="oer_lp_type" id="oer_lp_type" class="form-control">
                            <?php echo oer_lp_get_curriculum_type($oer_lp_type); ?>
                        </select>
                    </div>
                    <div class="form-group other-type-group<?php echo $xclass; ?>">
                        <label for="oer_lp_type_other"><?php echo oer_lp_get_field_label('oer_lp_type_other'); ?></label>
                        <input type="text"
                               class="form-control"
                               name="oer_lp_type_other"
                               placeholder="Curriculum Type"
                               value="<?php echo isset($oer_lp_type_other) ? $oer_lp_type_other : "";?>"
                        >
                    </div>
                    <?php } ?>
                </div>
            </div>
            <!--For Introduction-->
            <?php if (!empty($elements_orders)) {
                foreach ($elements_orders as $elementKey => $value) {
                    if($elementKey == 'lp_introduction_order') {?>
                        <div class="panel panel-default lp-element-wrapper oer-lp-introduction-group" id="oer-lp-introduction-group">
                            <input type="hidden" name="lp_order[lp_introduction_order]" class="element-order" value="<?php echo $value;?>">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php _e("Introduction", OER_LESSON_PLAN_SLUG); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <?php
                                $oer_lp_introduction = isset($post_meta_data['oer_lp_introduction'][0]) ? $post_meta_data['oer_lp_introduction'][0] : "";
                                wp_editor( $oer_lp_introduction,
                                    'oer-lp-introduction',
                                    $settings = array(
                                        'textarea_name' => 'oer_lp_introduction',
                                        'media_buttons' => true,
                                        'textarea_rows' => 10,
                                        'drag_drop_upload' => true,
                                        'teeny' => true,
                                    )
                                );
                                ?>
                            </div>
                        </div>
                    <?php } elseif ($elementKey == 'lp_authors_order') {
                        if (($author_set && $author_enabled) || !$author_set) { 
                        ?>
                        <?php
                        $authors = (isset($post_meta_data['oer_lp_authors'][0]) ? unserialize($post_meta_data['oer_lp_authors'][0]) : array());
                        if(!empty($authors)) { ?>
                            <div class="panel panel-default lp-element-wrapper oer-lp-authors-group" id="oer-lp-authors">
                                <input type="hidden" name="lp_order[lp_authors_order]" class="element-order" value="<?php echo $value;?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php echo oer_lp_get_field_label('oer_lp_authors'); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="panel-group">
                                        <?php
                                        if (isset($authors['name']) && !empty($authors['name'])) {
                                            foreach ( $authors['name']as $authorKey => $authorName) { ?>
                                                <div class="panel panel-default lp-author-element-wrapper" id="author-<?php echo $authorKey;?>">
                                                    <div class="panel-heading">
                                                        <h3 class="panel-title lp-module-title">
                                                            <?php _e("Author", OER_LESSON_PLAN_SLUG); ?>
                                                            <span class="lp-sortable-handle">
                                                                <i class="fa fa-arrow-down author-reorder-down" aria-hidden="true"></i>
                                                                <i class="fa fa-arrow-up author-reorder-up" aria-hidden="true"></i>
                                                            </span>
                                                            <span class="btn btn-danger btn-sm lp-remove-author"
                                                                  title="Delete"
                                                            ><i class="fa fa-trash"></i> </span>
                                                        </h3>
                                                    </div>
                                                    <div class="panel-body">
                                                        <div class="row lp-authors-element-row">
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <input type="text"
                                                                           class="form-control"
                                                                           name="oer_lp_authors[name][]"
                                                                           placeholder="Name"
                                                                           value="<?php echo $authorName;?>"
                                                                    >
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <input type="text"
                                                                           class="form-control"
                                                                           name="oer_lp_authors[role][]"
                                                                           placeholder="Role"
                                                                           value="<?php echo isset($authors['role'][$authorKey]) ? $authors['role'][$authorKey] : "";?>"
                                                                    >
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <input type="text"
                                                                           class="form-control"
                                                                           name="oer_lp_authors[author_url][]"
                                                                           placeholder="Author URL"
                                                                           value="<?php echo isset($authors['author_url'][$authorKey]) ? $authors['author_url'][$authorKey] : "";?>"
                                                                    >
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <input type="text"
                                                                           class="form-control"
                                                                           name="oer_lp_authors[institution][]"
                                                                           placeholder="Institution"
                                                                           value="<?php echo isset($authors['institution'][$authorKey]) ? $authors['institution'][$authorKey] : "";?>"
                                                                    >
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <div class="form-group">
                                                                    <input type="text"
                                                                           class="form-control"
                                                                           name="oer_lp_authors[institution_url][]"
                                                                           placeholder="Institution URL"
                                                                           value="<?php echo isset($authors['institution_url'][$authorKey]) ? $authors['institution_url'][$authorKey] : "";?>"
                                                                    >
                                                                </div>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <input type="hidden"
                                                                       name="oer_lp_authors[author_pic][]"
                                                                       value="<?php echo isset($authors['author_pic'][$authorKey]) ? $authors['author_pic'][$authorKey] : "";?>"
                                                                >
                                                                <?php
                                                                $image = (isset($authors['author_pic'][$authorKey]) ? $authors['author_pic'][$authorKey] : "");
                                                                if(empty($image)) {
                                                                    $image = OER_LESSON_PLAN_URL . "assets/images/lp-oer-person-placeholder.png";
                                                                }
                                                                ?>
                                                                <img src="<?php echo $image;?>"
                                                                    class="img-circle lp-oer-person-placeholder"
                                                                    width="50px"
                                                                    height="50px"/>
                                                            </div>
                                                        </div><!-- /.row -->
                                                    </div>
                                                </div>
                                            <?php }
                                        }
                                        ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button"
                                                    id="lp-add-more-author"
                                                    class="btn btn-default lp-add-more-author"
                                            ><i class="fa fa-plus"></i> Add More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php }
                        } ?>
                    <?php } elseif ($elementKey == 'lp_primary_resources') {
                        if (is_oer_plugin_installed()){
                            if (($primary_resources_set && $primary_resources_enabled) || !$primary_resources_set) { 
                        ?>
                        <!-- Primary Sources -->
                        <div class="panel panel-default lp-element-wrapper oer-lp-primary-resources" id="oer-lp-primary-resources">
                            <input type="hidden" name="lp_order[lp_primary_resources]" class="element-order" value="<?php echo $value;?>">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php echo oer_lp_get_field_label('oer_lp_primary_resources'); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="panel-group lp-primary-resource-element-panel">
                                    <?php
                                    $posts = get_posts([
                                        'post_type' => 'resource',
                                        'post_status' => 'publish',
                                        'numberposts' => -1,
                                        'orderby' => 'title',
                                        'order'    => 'ASC'
                                    ]);
                                    $primary_resources = (isset($post_meta_data['oer_lp_primary_resources'][0]) ? unserialize($post_meta_data['oer_lp_primary_resources'][0]) : array());
                                    if (count($primary_resources) && isset($primary_resources['resource'])) {
                                        foreach ($primary_resources['resource'] as $resourceKey => $resource) { ?>
                                            <?php
                                            $sensitiveMaterial = (isset($primary_resources['sensitive_material'][$resourceKey]) ? $primary_resources['sensitive_material'][$resourceKey]: "");
                                            $sensitiveMaterialValue = (isset($primary_resources['sensitive_material_value'][$resourceKey]) ? $primary_resources['sensitive_material_value'][$resourceKey]: "");
                                            if ($sensitiveMaterialValue!=="")
                                                $sensitiveMaterial = $sensitiveMaterialValue;
                                                
                                            $teacherInfo = (isset($primary_resources['teacher_info'][$resourceKey]) ? $primary_resources['teacher_info'][$resourceKey]: "");
                                            $studentInfo = (isset($primary_resources['student_info'][$resourceKey]) ? $primary_resources['student_info'][$resourceKey]: "");
                                            ?>
                                            <div class="panel panel-default lp-primary-resource-element-wrapper">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title lp-module-title">
                                                        <?php _e("Resource", OER_LESSON_PLAN_SLUG); ?>
                                                        <span class="lp-sortable-handle">
                                                            <i class="fa fa-arrow-down resource-reorder-down" aria-hidden="true"></i>
                                                            <i class="fa fa-arrow-up resource-reorder-up" aria-hidden="true"></i>
                                                        </span>
                                                        <span class="btn btn-danger btn-sm lp-remove-source"
                                                              title="Delete"
                                                              <?php echo ((count($primary_resources) == 1) ? 'disabled="disabled"' : '');?>
                                                        ><i class="fa fa-trash"></i> </span>
                                                    </h3>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <select name="oer_lp_primary_resources[resource][]" class="form-control">
                                                                    <option value="">Select Resource</option>
                                                                    <?php
                                                                    if (count($posts)) {
                                                                        foreach ($posts as $post) {
                                                                            ?>
                                                                            <option value="<?php echo $post->post_title;?>" <?php echo ((htmlspecialchars($resource) == $post->post_title) ? 'selected="selected"' : "");?>><?php echo $post->post_title;?></option>
                                                                        <?php }
                                                                    }
                                                                    ?>
                                                                </select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-5">
                                                            <div class="checkbox pull-right">
                                                                <label>
                                                                    <input type="hidden"
                                                                           name="oer_lp_primary_resources[sensitive_material_value][]"
                                                                           value="<?php echo (($sensitiveMaterial == 'yes')? 'yes' : 'no'); ?>"
                                                                    >
                                                                    <input type="checkbox"
                                                                           name="oer_lp_primary_resources[sensitive_material][]"
                                                                           value="yes"
                                                                           <?php echo (($sensitiveMaterial == 'yes')? 'checked="checked"' : '');?>
                                                                    >
                                                                    Sensitive Material
                                                                </label>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group">
                                                        <?php if ($oer_convert_info): ?>
                                                        <label>Title</label>
                                                        <input type="text"
                                                            class="form-control"
                                                            name="oer_lp_primary_resources[title][]"
                                                            placeholder="Resource Title"
                                                            value="<?php echo isset($primary_resources['title'][$resourceKey]) ? $primary_resources['title'][$resourceKey] : "";?>">
                                                        <?php else: ?>
                                                        <label>Teacher Information</label>
                                                        <?php wp_editor( $teacherInfo,
                                                           'oer-lp-resource-teacher-' . $resourceKey,
                                                           $settings = array(
                                                               'textarea_name' => 'oer_lp_primary_resources[teacher_info][]',
                                                               'media_buttons' => true,
                                                               'textarea_rows' => 6,
                                                               'drag_drop_upload' => true,
                                                               'teeny' => true,
                                                           )
                                                       ); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="form-group">
                                                        <?php if ($oer_convert_info):
                                                        $resource_description = (isset($primary_resources['description'][$resourceKey]) ? $primary_resources['description'][$resourceKey]: "");
                                                        ?>
                                                        <label>Description</label>
                                                        <?php wp_editor( $resource_description,
                                                            'oer-lp-resource-student-' . $resourceKey,
                                                            $settings = array(
                                                                'textarea_name' => 'oer_lp_primary_resources[description][]',
                                                                'media_buttons' => true,
                                                                'textarea_rows' => 6,
                                                                'drag_drop_upload' => true,
                                                                'teeny' => true,
                                                            )
                                                        ); ?>
                                                        <?php else: ?>
                                                        <label>Student Information</label>
                                                        <?php wp_editor( $studentInfo,
                                                            'oer-lp-resource-student-' . $resourceKey,
                                                            $settings = array(
                                                                'textarea_name' => 'oer_lp_primary_resources[student_info][]',
                                                                'media_buttons' => true,
                                                                'textarea_rows' => 6,
                                                                'drag_drop_upload' => true,
                                                                'teeny' => true,
                                                            )
                                                        ); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                    } else {?>
                                        <div class="panel panel-default lp-primary-source-element-wrapper">
                                            <div class="panel-heading">
                                                <h3 class="panel-title lp-module-title">
                                                    <?php _e("Resource", OER_LESSON_PLAN_SLUG); ?>
                                                    <span class="lp-sortable-handle">
                                                    <i class="fa fa-arrow-down resource-reorder-down" aria-hidden="true"></i>
                                                    <i class="fa fa-arrow-up resource-reorder-up" aria-hidden="true"></i>
                                                </span>
                                                    <span class="btn btn-danger btn-sm lp-remove-source"
                                                          title="Delete"
                                                          disabled="disabled"
                                                    ><i class="fa fa-trash"></i> </span>
                                                </h3>
                                            </div>
                                            <div class="panel-body">
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                    <?php
                                                    $posts = get_posts([
                                                        'post_type' => 'resource',
                                                        'post_status' => 'publish',
                                                        'numberposts' => -1,
                                                        'orderby' => 'title',
                                                        'order'    => 'ASC'
                                                    ]);
                                                    ?>
                                                    <select name="oer_lp_primary_resources[resource][]" class="form-control">
                                                        <option>Select Resource</option>
                                                        <?php
                                                        if (count($posts)) {
                                                            foreach ($posts as $post) {
                                                                echo '<option value="'.$post->post_title.'">'.$post->post_title.'</option>';
                                                            }
                                                        }
                                                        ?>
                                                    </select>
                                                </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="checkbox pull-right">
                                                        <label>
                                                            <input type="checkbox" name="oer_lp_primary_resources[sensitive_material][]" value="yes">
                                                            Sensitive Material
                                                        </label>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Teacher Information</label>
                                                    <?php wp_editor( '',
                                                        'oer-lp-resource-teacher-1',
                                                        $settings = array(
                                                            'textarea_name' => 'oer_lp_primary_resources[teacher_info][]',
                                                            'media_buttons' => true,
                                                            'textarea_rows' => 6,
                                                            'drag_drop_upload' => true,
                                                            'teeny' => true,
                                                        )
                                                    ); ?>
                                                </div>
                                                <div class="form-group">
                                                    <label>Student Information</label>
                                                    <?php wp_editor( '',
                                                        'oer-lp-resource-student-1',
                                                        $settings = array(
                                                            'textarea_name' => 'oer_lp_primary_resources[student_info][]',
                                                            'media_buttons' => true,
                                                            'textarea_rows' => 6,
                                                            'drag_drop_upload' => true,
                                                            'teeny' => true,
                                                        )
                                                    ); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php }?>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button"
                                                id="lp-add-more-resource"
                                                class="btn btn-default lp-add-more-resource"
                                        ><i class="fa fa-plus"></i> Add More</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php }
                        }
                    } elseif ($elementKey == 'lp_oer_materials') {
                        if (($materials_set && $materials_enabled) || !$materials_set) { 
                        ?>
                        <div class="panel panel-default lp-element-wrapper" id="oer-lp-materials">
                            <input type="hidden" name="lp_order[lp_oer_materials]" class="element-order" value="3">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php echo oer_lp_get_field_label('lp_oer_materials'); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i></span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="panel-group lp-materials-container" id="lp-materials-container">
                                    <?php
                                    $materials = (isset($post_meta_data['lp_oer_materials'][0]) ? unserialize($post_meta_data['lp_oer_materials'][0]) : array());
                                    if (!empty($materials['url'])) {
                                        foreach ($materials['url'] as $materialKey => $material) {?>
                                            <?php
                                            $file_response = get_file_type_from_url($material);
                                            ?>
                                            <div class="panel panel-default lp-material-element-wrapper">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title lp-module-title">
                                                        <span class="lp-sortable-handle">
                                                            <i class="fa fa-arrow-down material-reorder-down" aria-hidden="true"></i>
                                                            <i class="fa fa-arrow-up material-reorder-up" aria-hidden="true"></i>
                                                        </span>
                                                        <span class="btn btn-danger btn-sm lp-remove-material" title="Delete"><i class="fa fa-trash"></i></span>
                                                    </h3>
                                                    </div>
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <input type="text"
                                                                   class="form-control"
                                                                   name="lp_oer_materials[url][]"
                                                                   placeholder="URL"
                                                                   value="<?php echo $material;?>"
                                                            >
                                                            <div class="input-group-addon oer-lp-material-icon"
                                                                 title="<?php echo isset($file_response['title']) ? $file_response['title'] : "";?>"
                                                            ><?php echo isset($file_response['icon']) ? $file_response['icon'] : "";?></div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <input type="text"
                                                               class="form-control"
                                                               name="lp_oer_materials[title][]"
                                                               placeholder="Title"
                                                               value="<?php echo $materials['title'][$materialKey]?>"
                                                        >
                                                    </div>
                                                    <div class="form-group">
                                                        <textarea class="form-control"
                                                                  name="lp_oer_materials[description][]"
                                                                  rows="6"
                                                                  placeholder="Description"
                                                        ><?php echo $materials['description'][$materialKey]?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                    }?>
                                </div>
                                <button type="button"
                                        id="lp-add-materials"
                                        class="btn btn-default lp-add-materials"
                                ><i class="fa fa-plus"></i> Add Materials</button>
                            </div>
                        </div>
                    <?php }
                    } elseif ($elementKey == 'lp_iq') {
                        if (($iq_set && $iq_enabled) || !$iq_set) { 
                        ?>
                        <!--Investigative Question Module-->
                        <?php
                        $oer_lp_iq  = isset($post_meta_data['oer_lp_iq'][0]) ? unserialize($post_meta_data['oer_lp_iq'][0]) : array();
                        ?>
                        <div class="panel panel-default lp-element-wrapper oer-lp-iq" id="oer-lp-iq">
                            <input type="hidden" name="lp_order[lp_iq]" class="element-order" value="<?php echo $value;?>">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php echo oer_lp_get_field_label('oer_lp_iq'); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label>Investigative Question</label>
                                    <input type="text"
                                           name="oer_lp_iq[question]"
                                           maxlength="512"
                                           class="form-control"
                                           placeholder="Investigative Question"
                                           value="<?php echo (isset($oer_lp_iq['question']) ? $oer_lp_iq['question'] : "")?>"
                                    >
                                </div>
                                <div class="form-group">
                                    <label>Framework Excerpt</label>
                                    <?php wp_editor( (isset($oer_lp_iq['excerpt']) ? $oer_lp_iq['excerpt'] : ""),
                                        'oer_lp_iq_excerpt',
                                        $settings = array(
                                            'textarea_name' => 'oer_lp_iq[excerpt]',
                                            'media_buttons' => true,
                                            'textarea_rows' => 6,
                                            'drag_drop_upload' => true,
                                            'teeny' => true,
                                        )
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    <?php }
                    } elseif ($elementKey == 'lp_required_materials') {
                        if (($req_materials_set && $req_materials_enabled) || !$req_materials_set) { 
                        ?>
                        <!--Required Equipment Materials Module-->
                        <?php
                        $oer_lp_required_materials  = isset($post_meta_data['oer_lp_required_materials'][0]) ? $post_meta_data['oer_lp_required_materials'][0] : array();
                        ?>
                        <div class="panel panel-default lp-element-wrapper oer-lp-required-materials" id="oer-lp-required-materials">
                            <input type="hidden" name="lp_order[lp_required_materials]" class="element-order" value="<?php echo $value;?>">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php _e("Required Equipment Materials", OER_LESSON_PLAN_SLUG); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="form-group">
                                    <label>Required Equipment Materials</label>
                                    <?php wp_editor( (isset($oer_lp_required_materials) ? $oer_lp_required_materials : ""),
                                        'oer_lp_required_materials',
                                        $settings = array(
                                            'textarea_name' => 'oer_lp_required_materials',
                                            'media_buttons' => true,
                                            'textarea_rows' => 6,
                                            'drag_drop_upload' => true,
                                            'teeny' => true,
                                        )
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    <?php }
                    } elseif ($elementKey == 'lp_lesson_times_order') {?>
                        <!--For Lesson Times-->
                        <div class="panel panel-default lp-element-wrapper oer-lp-times-group" id="oer-lp-times-group">
                            <input type="hidden" name="lp_order[lp_lesson_times_order]" class="element-order" value="<?php echo $value;?>">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php _e("Lesson Times", OER_LESSON_PLAN_SLUG); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <?php
                                $oer_lp_times_label  = isset($post_meta_data['oer_lp_times_label'][0]) ? unserialize($post_meta_data['oer_lp_times_label'][0]) : array();
                                $oer_lp_times_number = isset($post_meta_data['oer_lp_times_number'][0]) ? unserialize($post_meta_data['oer_lp_times_number'][0]) : array();
                                $oer_lp_times_type   = isset($post_meta_data['oer_lp_times_type'][0]) ? unserialize($post_meta_data['oer_lp_times_type'][0]) : array();
                                ?>

                                <?php
                                /**
                                 * Check if lesson time data available the show the value pre fill
                                 */
                                if(!empty($oer_lp_times_label)){
                                    foreach ($oer_lp_times_label as $key => $item){?>
                                        <div class="row lp-time-element-row">
                                            <div class="col-md-5">
                                                <div class="form-group">
                                                    <input type="text"
                                                           class="form-control"
                                                           name="oer_lp_times_label[]"
                                                           value="<?php echo $item;?>"
                                                           placeholder="label">
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <input type="text"
                                                           class="form-control"
                                                           name="oer_lp_times_number[]"
                                                           value="<?php echo isset($oer_lp_times_number[$key]) ? $oer_lp_times_number[$key] : '';?>"
                                                           placeholder="40">
                                                </div>
                                            </div>
                                            <div class="col-md-2">
                                                <div class="form-group">
                                                    <select name="oer_lp_times_type[]" class="form-control">
                                                        <option value="minutes" <?php echo (isset($oer_lp_times_type[$key]) ? oer_lp_show_selected('minutes', $oer_lp_times_type[$key]) : '');?>>Minute(s)</option>
                                                        <option value="hours" <?php echo (isset($oer_lp_times_type[$key]) ? oer_lp_show_selected('hours', $oer_lp_times_type[$key]) : '');?>>Hour(s)</option>
                                                        <option value="days" <?php echo (isset($oer_lp_times_type[$key]) ? oer_lp_show_selected('days', $oer_lp_times_type[$key]) : '');?>>Days(s)</option>
                                                        <option value="class_periods" <?php echo (isset($oer_lp_times_type[$key]) ? oer_lp_show_selected('class_periods', $oer_lp_times_type[$key]) : '');?>>Class Period(s)</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <button type="button"
                                                            class="btn btn-danger remove-time-element"
                                                        <?php if(count($oer_lp_times_label) == 1) echo 'disabled="disabled"';?>
                                                    ><i class="fa fa-trash"></i> </button>
                                                </div>
                                            </div>
                                        </div><!-- /.row -->
                                    <?php }?>
                                <?php } else {?>
                                    <div class="row lp-time-element-row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="oer_lp_times_label[]" placeholder="label">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="oer_lp_times_number[]" placeholder="40">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <select name="oer_lp_times_type[]" class="form-control">
                                                    <option value="minutes">Minute(s)</option>
                                                    <option value="hours">Hour(s)</option>
                                                    <option value="days">Days(s)</option>
                                                    <option value="class_periods">Class Period(s)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <button type="button"
                                                        class="btn btn-danger remove-time-element"
                                                        disabled="disabled"
                                                ><i class="fa fa-trash"></i> </button>
                                            </div>
                                        </div>
                                    </div><!-- /.row -->
                                <?php }?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button"
                                                class="btn btn-default lp-add-time-element"
                                        ><i class="fa fa-plus"></i> Add Time Element</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } elseif ($elementKey == 'lp_standard_order') { ?>
                        <!--For Standards and Objectives -->
                        <div class="panel panel-default lp-element-wrapper oer-lp-standards-group" id="oer-lp-standards-group">
                            <input type="hidden" name="lp_order[lp_standard_order]" class="element-order" value="<?php echo $value;?>">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php _e("Standards and Objectives", OER_LESSON_PLAN_SLUG); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <?php if (is_standards_plugin_installed()) {
                                    if (($standards_set && $standards_enabled) || !$standards_set) { ?>
                                <h4 class="page-title-inner"><?php _e("Standards", OER_LESSON_PLAN_SLUG); ?></h4>

                                <div id="selected-standard-wrapper">
                                    <?php
                                    $standards = (isset($post_meta_data['oer_lp_standards'][0])? $post_meta_data['oer_lp_standards'][0] : "");
                                    get_standard_notations_from_ids($standards, true);
                                    ?>
                                </div>
                                <input type="hidden" name="oer_lp_standards" value="<?php echo $standards;?>">
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button"
                                                id="lp-select-standard"
                                                class="btn btn-primary"
                                        >Select Standards</button>
                                    </div>
                                </div>
                                <?php }
                                } ?>
                                <div class="row">
                                    <div class="col-md-12">
                                        <h4>Related Instructional Objectives (<span title="Students will be able to...">SWBAT...</span>)</h4>
                                    </div>

                                    <?php
                                    $oer_lp_related_objective  = isset($post_meta_data['oer_lp_related_objective'][0]) ? unserialize($post_meta_data['oer_lp_related_objective'][0]) : array();
                                    if(!empty($oer_lp_related_objective)) {
                                        foreach ( $oer_lp_related_objective as $key => $item) { ?>
                                            <div class="lp-related-objective-row" id="lp-related-objective-row">
                                                <div class="col-md-9">
                                                    <div class="form-group">
                                                        <input type="text"
                                                               class="form-control"
                                                               name="oer_lp_related_objective[]"
                                                               value="<?php echo $item;?>"
                                                        >
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button"
                                                            class="btn btn-danger lp-remove-related-objective"
                                                        <?php if(count($oer_lp_related_objective) == 1) echo 'disabled="disabled"';?>
                                                    ><i class="fa fa-trash"></i> </button>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    <?php } else { ?>
                                        <div class="lp-related-objective-row" id="lp-related-objective-row">
                                            <div class="col-md-9">
                                                <div class="form-group">
                                                    <input type="text"
                                                           class="form-control"
                                                           name="oer_lp_related_objective[]"
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button"
                                                        class="btn btn-danger lp-remove-related-objective"
                                                        disabled="disabled"
                                                ><i class="fa fa-trash"></i> </button>
                                            </div>
                                        </div>
                                    <?php } ?>
                                </div><!-- /.row -->
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button"
                                                class="btn btn-default lp-add-related-objective"
                                        ><i class="fa fa-plus"></i> Add Objective</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } elseif ($elementKey == 'lp_activities_order') {?>
                        <!--Activities in this lesson-->
                        <div class="panel panel-default lp-element-wrapper oer-lp-activities-group" id="oer-lp-activities-group">
                            <input type="hidden" name="lp_order[lp_activities_order]" class="element-order" value="<?php echo $value;?>">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php _e("Activities in this Lesson", OER_LESSON_PLAN_SLUG); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="panel-group" id="lp-ac-inner-panel">
                                    <?php
                                    if(!empty($oer_lp_activity_title)) {
                                        foreach ($oer_lp_activity_title as $key => $item) { ?>
                                            <div class="panel panel-default lp-ac-item" id="lp-ac-item-<?php echo $key;?>">
                                                <!--<input type="hidden" name="lp_activity_order[lp_activities_order]" class="element-activity-order" value="">-->
                                                <!--<span class="lp-inner-sortable-handle">
                                                    <i class="fa fa-arrow-down activity-reorder-down" aria-hidden="true"></i>
                                                    <i class="fa fa-arrow-up activity-reorder-up" aria-hidden="true"></i>
                                                </span>-->
                                                <div class="panel-heading">
                                                    <h3 class="panel-title lp-module-title">
                                                        <span class="lp-sortable-handle">
                                                            <i class="fa fa-arrow-down activity-reorder-down" aria-hidden="true"></i>
                                                            <i class="fa fa-arrow-up activity-reorder-up" aria-hidden="true"></i>
                                                        </span>
                                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                                    </h3>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label>Activity Title</label>
                                                        <input type="text"
                                                               name="oer_lp_activity_title[]"
                                                               class="form-control"
                                                               placeholder="Activity Title"
                                                               value="<?php echo $item; ?>"
                                                        >
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-8">
                                                            <label for="activity-title">Activity Type</label>
                                                            <select name="oer_lp_activity_type[]" class="form-control">
                                                                <option value=""> - Activity Type -</option>
                                                                <option value="hooks_set" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('hooks_set', $oer_lp_activity_type[$key]) : "");?>>Hooks / Set</option>
                                                                <option value="lecture" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('lecture', $oer_lp_activity_type[$key]) : "");?>>Lecture</option>
                                                                <option value="demonstration" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('demonstration', $oer_lp_activity_type[$key]) : "");?>>Demo / Modeling</option>
                                                                <option value="independent_practice" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('independent_practice', $oer_lp_activity_type[$key]) : "");?>>Independent Practice</option>
                                                                <option value="guided_practice" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('guided_practice', $oer_lp_activity_type[$key]) : "");?>>Guided Practice</option>
                                                                <option value="check_understanding" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('check_understanding', $oer_lp_activity_type[$key]) : "");?>>Check Understanding</option>
                                                                <option value="lab_shop" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('lab_shop', $oer_lp_activity_type[$key]) : "");?>>Lab / Shop</option>
                                                                <option value="group_work" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('group_work', $oer_lp_activity_type[$key]) : "");?>>Group Work</option>
                                                                <option value="projects" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('projects', $oer_lp_activity_type[$key]) : "");?>>Projects</option>
                                                                <option value="assessment" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('assessment', $oer_lp_activity_type[$key]) : "");?>>Formative Assessment</option>
                                                                <option value="closure" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('closure', $oer_lp_activity_type[$key]) : "");?>>Closure</option>
                                                                <option value="research" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('research', $oer_lp_activity_type[$key]) : "");?>>Research / Annotate</option>
                                                                <option value="other" <?php echo (isset($oer_lp_activity_type[$key]) ? oer_lp_show_selected('other', $oer_lp_activity_type[$key]) : "");?>>Other</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <?php
                                                        $content = isset($oer_lp_activity_detail[$key]) ? $oer_lp_activity_detail[$key] : "";
                                                        wp_editor( $content,
                                                            'oer-lp-activity-detail-'.$key,
                                                            $settings = array(
                                                                'textarea_name' => 'oer_lp_activity_detail[]',
                                                                'media_buttons' => true,
                                                                'textarea_rows' => 10,
                                                                'drag_drop_upload' => true,
                                                                'teeny' => true,
                                                            )
                                                        ); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                    <?php }?>
                                </div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="button"
                                                class="btn btn-default lp-add-ac-item"
                                                data-url="<?php echo admin_url('admin-index.php')?>"
                                        ><i class="fa fa-plus"></i> Add Activity</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } elseif ($elementKey == 'lp_summative_order') {?>
                        <!--Summative Assessment-->
                        <div class="panel panel-default lp-element-wrapper oer-lp-summative-group" id="oer-lp-summative-group">
                            <input type="hidden" name="lp_order[lp_summative_order]" class="element-order" value="<?php echo $value;?>">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php _e("Summative Assessment", OER_LESSON_PLAN_SLUG); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <h4><?php _e("Assessment Type(s):", OER_LESSON_PLAN_SLUG); ?></h4>
                                <div class="row">
                                    <?php
                                    $oer_lp_assessment_type = (isset($post_meta_data['oer_lp_assessment_type'][0]) ? unserialize($post_meta_data['oer_lp_assessment_type'][0]) : array());
                                    // Prepare array for the Assessment options checkboxes
                                    $assessment_options = array(
                                        'demonstrations' => 'Demonstrations',
                                        'interviews' => 'Interviews',
                                        'journals' => 'Journals',
                                        'observations' => 'Observations',
                                        'portfolios' => 'Portfolios',
                                        'projects' => 'Projects',
                                        'rubrics' => 'Rubrics',
                                        'surveys' => 'surveys',
                                        'teacher_made_test' => 'Teacher-Made Test',
                                        'writing_samples' => 'Writing Samples',
                                    );

                                    foreach ($assessment_options as $key => $assessment_option) { ?>
                                        <div class="col-md-3">
                                            <div class="checkbox oer-summative-checkbox">
                                                <label>
                                                    <input name="oer_lp_assessment_type[]"
                                                           type="checkbox"
                                                           value="<?php echo $key;?>"
                                                        <?php echo oer_lp_show_selected($key, $oer_lp_assessment_type, 'checkbox')?>
                                                    > <?php echo $assessment_option; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php }?>
                                </div>
                                <div class="row">
                                    <?php
                                    $oer_lp_other_assessment_type = (isset($post_meta_data['oer_lp_other_assessment_type'][0]) ? $post_meta_data['oer_lp_other_assessment_type'][0] : '');
                                    ?>
                                    <div class="form-group col-md-8">
                                        <label><?php _e("Other", OER_LESSON_PLAN_SLUG); ?></label>
                                        <input type="text"
                                               name="oer_lp_other_assessment_type"
                                               class="form-control"
                                               placeholder="Other Assessment Type(s)"
                                               value="<?php echo $oer_lp_other_assessment_type;?>"
                                        >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php
                                    $oer_lp_assessment = (isset($post_meta_data['oer_lp_assessment'][0]) ? $post_meta_data['oer_lp_assessment'][0] : '');
                                    wp_editor( $oer_lp_assessment,
                                        'oer-lp-other-assessment',
                                        $settings = array(
                                            'textarea_name' => 'oer_lp_assessment',
                                            'media_buttons' => true,
                                            'textarea_rows' => 10,
                                            'drag_drop_upload' => true,
                                            'teeny' => true,
                                        )
                                    ); ?>
                                </div>
                            </div>
                        </div>
                    <?php } elseif ($elementKey == 'oer_lp_custom_editor_teacher_background' || $elementKey == 'oer_lp_custom_editor_student_background') {
                        $group_id = 'oer-lp-custom-editor-group-'.$key;
                        if ($elementKey == 'oer_lp_custom_editor_teacher_background')
                            $group_id = 'oer-lp-custom-editor-group-teacher-background';
                        else
                            $group_id = 'oer-lp-custom-editor-group-student-background';
                        $oer_lp_custom_editor = (isset($post_meta_data[$elementKey][0]) ? unserialize($post_meta_data[$elementKey][0]) : "");
                        ?>
                        <div class="panel panel-default lp-element-wrapper oer-lp-introduction-group" id="<?php echo $group_id; ?>">
                            <input type="hidden" name="lp_order[<?php echo $elementKey;?>]" class="element-order" value="<?php echo $value;?>" value="1">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php echo $oer_lp_custom_editor['title']; ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                 <div class="form-group">
                                    <label>Title</label>
                                    <input type="text" name="<?php echo $elementKey; ?>[title]" maxlength="512" class="form-control" placeholder="Text Module Title" value="<?php echo $oer_lp_custom_editor['title']; ?>" />
                                </div>
                                <div class="form-group">
                                <?php
                                wp_editor( $oer_lp_custom_editor['description'],
                                    'oer-lp-custom-editor-'.$value,
                                    $settings = array(
                                        'textarea_name' => $elementKey."[description]",
                                        'media_buttons' => true,
                                        'textarea_rows' => 10,
                                        'drag_drop_upload' => true,
                                        'teeny' => true,
                                    )
                                );
                                ?>
                                </div>
                            </div>
                        </div>
                    <?php } elseif (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'oer_lp_custom_editor_') !== false) {?>
                        <?php
                        if ($elementKey!=="oer_lp_custom_editor_teacher_background" && $elementKey!=="oer_lp_custom_editor_student_background") {
                        $oer_lp_custom_editor = (isset($post_meta_data[$elementKey][0]) ? unserialize($post_meta_data[$elementKey][0]) : "");
                        ?>
                            <div class="panel panel-default lp-element-wrapper oer-lp-introduction-group" id="oer-lp-custom-editor-group-<?php echo $key; ?>">
                                <input type="hidden" name="lp_order[<?php echo $elementKey;?>]" class="element-order" value="<?php echo $value;?>" value="1">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php echo $oer_lp_custom_editor['title']; ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                     <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="oer_lp_custom_editor_<?php echo $value; ?>[title]" maxlength="512" class="form-control" placeholder="Text Module Title" value="<?php echo $oer_lp_custom_editor['title']; ?>" />
                                    </div>
                                    <div class="form-group">
                                    <?php
                                    wp_editor( $oer_lp_custom_editor['description'],
                                        'oer-lp-custom-editor-'.$value,
                                        $settings = array(
                                            'textarea_name' => "oer_lp_custom_editor_" . $value ."[description]",
                                            'media_buttons' => true,
                                            'textarea_rows' => 10,
                                            'drag_drop_upload' => true,
                                            'teeny' => true,
                                        )
                                    );
                                    ?>
                                    </div>
                                </div>
                            </div>
                    <?php }
                    } elseif (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'oer_lp_custom_text_list_') !== false) {?>
                        <?php
                        $oer_lp_custom_text_list = (isset($post_meta_data[$elementKey][0]) ? unserialize($post_meta_data[$elementKey][0]) : array());
                        //echo "<pre>"; echo $elementKey; print_r($post_meta_data[$elementKey]);
                        if (!empty($oer_lp_custom_text_list)) {
                            foreach ($oer_lp_custom_text_list as $key => $list) { ?>
                                <div class="panel panel-default lp-element-wrapper" id="oer-lp-text-list-group-<?php echo $key;?>">
                                    <input type="hidden" name="lp_order[<?php echo $elementKey;?>]" class="element-order" value="<?php echo $value;?>">
                                    <div class="panel-heading">
                                        <h3 class="panel-title lp-module-title">
                                            <?php _e("Text List", OER_LESSON_PLAN_SLUG); ?>
                                            <span class="lp-sortable-handle">
                                                <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                                <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                            </span>
                                            <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                        </h3>
                                    </div>
                                    <div class="panel-body">
                                        <div class="row">
                                            <div class="lp-text-list-row" id="lp-text-list-row<?php echo $key;?>">
                                                <div class="col-md-9">
                                                    <div class="form-group">
                                                        <input type="text"
                                                               class="form-control"
                                                               name="<?php echo $elementKey;?>[]"
                                                               value="<?php echo $list;?>"
                                                        >
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <button type="button"
                                                            class="btn btn-danger lp-remove-text-list"
                                                            disabled="disabled"
                                                    ><i class="fa fa-trash"></i> </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                        }
                        ?>
                    <?php } elseif (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'lp_oer_materials_list_') !== false) {?>
                        <div class="panel panel-default lp-element-wrapper" id="oer-lp-materials-<?php echo $value;?>">
                            <input type="hidden" name="<?php echo $elementKey?>" class="element-order" value="<?php echo $value?>">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php _e("Materials", OER_LESSON_PLAN_SLUG); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i></span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <div class="panel-group lp-materials-container" id="lp-materials-container-<?php echo $value;?>">
                                    <?php
                                    $materials = (isset($post_meta_data[$elementKey][0]) ? unserialize($post_meta_data[$elementKey][0]) : array());
                                    if (!empty($materials['url'])) {
                                        foreach ($materials['url'] as $materialKey => $material) {?>
                                            <?php
                                            $file_response = get_file_type_from_url($material);
                                            ?>
                                            <div class="panel panel-default lp-material-element-wrapper">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title lp-module-title">
                                                        <span class="lp-sortable-handle">
                                                            <i class="fa fa-arrow-down material-reorder-down" aria-hidden="true"></i>
                                                            <i class="fa fa-arrow-up material-reorder-up" aria-hidden="true"></i>
                                                        </span>
                                                        <span class="btn btn-danger btn-sm lp-remove-material" title="Delete"><i class="fa fa-trash"></i></span>
                                                    </h3>
                                                </div>
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <div class="input-group">
                                                            <input type="text"
                                                                   class="form-control"
                                                                   name="<?php echo $elementKey;?>[url][]"
                                                                   placeholder="URL"
                                                                   value="<?php echo $material;?>">
                                                            <div class="input-group-addon oer-lp-material-icon"
                                                                 title="<?php echo isset($file_response['title']) ? $file_response['title'] : "";?>"
                                                            ><?php echo isset($file_response['icon']) ? $file_response['icon'] : "";?></div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <input type="text"
                                                               class="form-control"
                                                               name="<?php echo $elementKey;?>[title][]"
                                                               placeholder="Title"
                                                               value="<?php echo $materials['title'][$materialKey]?>">
                                                    </div>
                                                    <div class="form-group">
                                                        <textarea class="form-control"
                                                                  name="<?php echo $elementKey;?>[description][]"
                                                                  rows="6"
                                                                  placeholder="Description"
                                                        ><?php echo $materials['description'][$materialKey]?></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                    }?>
                                </div>
                                <button type="button"
                                        id="lp-add-materials"
                                        class="btn btn-default lp-add-materials"
                                ><i class="fa fa-plus"></i> Add Materials</button>
                            </div>
                        </div>
                    <?php } elseif (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'oer_lp_vocabulary_list_title_') !== false) {?>
                        <?php
                        $oer_lp_vocabulary_list_title = (isset($post_meta_data[$elementKey][0]) ? $post_meta_data[$elementKey][0] : "");
                        $oer_keys = explode('_', $elementKey);
                        $listOrder = end($oer_keys);
                        $oer_lp_vocabulary_details = (isset($post_meta_data['oer_lp_vocabulary_details_'.$listOrder][0]) ? $post_meta_data['oer_lp_vocabulary_details_'.$listOrder][0] : "");
                        ?>
                            <div class="panel panel-default lp-element-wrapper" id="oer-lp-vocabulary-list-group-<?php echo $key;?>">
                                <input type="hidden" name="lp_order[<?php echo $elementKey?>]" class="element-order" value="<?php echo $value;?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php _e("Vocabulary List", OER_LESSON_PLAN_SLUG); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <input type="text"
                                               class="form-control"
                                               name="<?php echo $elementKey;?>"
                                               value="<?php echo $oer_lp_vocabulary_list_title;?>"
                                        >
                                    </div>
                                    <div class="form-group">
                                        <textarea class="form-control" name="oer_lp_vocabulary_details_<?php echo $listOrder;?>" rows="6"><?php echo $oer_lp_vocabulary_details;?></textarea>
                                    </div>
                                </div>
                            </div>
                    <?php }
                }
            } else { ?>
                <?php
                 // Set order of modules
                if (!empty($oer_lp_default_structure)){
                    $index=0;
                    foreach($oer_lp_default_structure as $module){
                        $index++;
                        if ($module=="lp_introduction_order") {
                            ?>
                            <!-- Introduction Module -->
                            <div class="panel panel-default lp-element-wrapper oer-lp-introduction-group" id="oer-lp-introduction-group">
                                <input type="hidden" name="lp_order[lp_introduction_order]" class="element-order" value="<?php echo $index; ?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php _e("Introduction", OER_LESSON_PLAN_SLUG); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <?php
                                    $oer_lp_introduction = isset($post_meta_data['oer_lp_introduction'][0]) ? $post_meta_data['oer_lp_introduction'][0] : "";
                                    wp_editor( $oer_lp_introduction,
                                        'oer-lp-introduction',
                                        $settings = array(
                                            'textarea_name' => 'oer_lp_introduction',
                                            'media_buttons' => true,
                                            'textarea_rows' => 10,
                                            'drag_drop_upload' => true,
                                            'teeny' => true,
                                        )
                                    );
                                    ?>
                                </div>
                            </div>
                            <?php
                        } elseif ($module=="lp_authors_order"){
                            if (($author_set && $author_enabled) || !$author_set) { 
                            ?>
                            <!--Authors-->
                            <div class="panel panel-default lp-element-wrapper oer-lp-authors-group" id="oer-lp-authors">
                                <input type="hidden" name="lp_order[lp_authors_order]" class="element-order" value="<?php echo $index; ?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php echo oer_lp_get_field_label('oer_lp_authors'); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="panel-group">
                                        <div class="panel panel-default lp-author-element-wrapper">
                                            <div class="panel-heading">
                                                <h3 class="panel-title lp-module-title">
                                                    <?php _e("Author", OER_LESSON_PLAN_SLUG); ?>
                                                    <span class="lp-sortable-handle">
                                                        <i class="fa fa-arrow-down author-reorder-down" aria-hidden="true"></i>
                                                        <i class="fa fa-arrow-up author-reorder-up" aria-hidden="true"></i>
                                                    </span>
                                                    <span class="btn btn-danger btn-sm lp-remove-author"
                                                          title="Delete"
                                                          disabled="disabled"
                                                    ><i class="fa fa-trash"></i> </span>
                                                </h3>
                                            </div>
                                            <div class="panel-body">
                                                <div class="row lp-authors-element-row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="oer_lp_authors[name][]" placeholder="Name">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="oer_lp_authors[role][]" placeholder="Role">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="oer_lp_authors[author_url][]" placeholder="Author URL">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="oer_lp_authors[institution][]" placeholder="Institution">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <input type="text" class="form-control" name="oer_lp_authors[institution_url][]" placeholder="Institution URL">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <input type="hidden" name="oer_lp_authors[author_pic][]">
                                                        <img src="<?php echo OER_LESSON_PLAN_URL;?>assets/images/lp-oer-person-placeholder.png"
                                                            class="img-circle lp-oer-person-placeholder"
                                                            width="50px"
                                                            height="50px"/>
                                                    </div>
            
                                                </div><!-- /.row -->
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button"
                                                    id="lp-add-more-author"
                                                    class="btn btn-default lp-add-more-author"
                                            ><i class="fa fa-plus"></i> Add More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }
                        } elseif ($module=="lp_primary_resources"){
                            if (is_oer_plugin_installed()){
                                if (($primary_resources_set && $primary_resources_enabled) || !$primary_resources_set) { 
                            ?>
                            <!-- Primary Sources -->
                            <div class="panel panel-default lp-element-wrapper oer-lp-primary-resources" id="oer-lp-primary-resources">
                                <input type="hidden" name="lp_order[lp_primary_resources]" class="element-order" value="<?php echo $index; ?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php echo oer_lp_get_field_label('oer_lp_primary_resources'); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="panel-group lp-primary-resource-element-panel">
                                        <div class="panel panel-default lp-primary-resource-element-wrapper">
                                            <div class="panel-heading">
                                                <h3 class="panel-title lp-module-title">
                                                    <?php _e("Resource", OER_LESSON_PLAN_SLUG); ?>
                                                    <span class="lp-sortable-handle">
                                                    <i class="fa fa-arrow-down resource-reorder-down" aria-hidden="true"></i>
                                                    <i class="fa fa-arrow-up resource-reorder-up" aria-hidden="true"></i>
                                                </span>
                                                    <span class="btn btn-danger btn-sm lp-remove-source"
                                                          title="Delete"
                                                          disabled="disabled"
                                                    ><i class="fa fa-trash"></i> </span>
                                                </h3>
                                            </div>
                                            <div class="panel-body">
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Resource</label>
                                                            <?php
                                                            $posts = get_posts([
                                                                'post_type' => 'resource',
                                                                'post_status' => 'publish',
                                                                'numberposts' => -1,
                                                                'orderby' => 'title',
                                                                'order'    => 'ASC'
                                                            ]);
                                                            ?>
                                                            <select name="oer_lp_primary_resources[resource][]" class="form-control">
                                                                <option>Select Resource</option>
                                                                <?php
                                                                if (count($posts)) {
                                                                    foreach ($posts as $post) {
                                                                        echo '<option value="'.$post->post_title.'">'.$post->post_title.'</option>';
                                                                    }
                                                                }
                                                                ?>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="checkbox pull-right">
                                                            <label>
                                                                <input type="checkbox" name="oer_lp_primary_resources[sensitive_material][]" value="yes">
                                                                Sensitive Material
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Teacher Information</label>
                                                    <?php wp_editor( '',
                                                        'oer-lp-resource-teacher-1',
                                                        $settings = array(
                                                            'textarea_name' => 'oer_lp_primary_resources[teacher_info][]',
                                                            'media_buttons' => true,
                                                            'textarea_rows' => 6,
                                                            'drag_drop_upload' => true,
                                                            'teeny' => true,
                                                        )
                                                    ); ?>
                                                </div>
                                                <div class="form-group">
                                                    <label>Student Information</label>
                                                    <?php wp_editor( '',
                                                        'oer-lp-resource-student-1',
                                                        $settings = array(
                                                            'textarea_name' => 'oer_lp_primary_resources[student_info][]',
                                                            'media_buttons' => true,
                                                            'textarea_rows' => 6,
                                                            'drag_drop_upload' => true,
                                                            'teeny' => true,
                                                        )
                                                    ); ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button"
                                                    id="lp-add-more-resource"
                                                    class="btn btn-default lp-add-more-resource"
                                            ><i class="fa fa-plus"></i> Add More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php }
                            }
                        } elseif ($module=="lp_oer_materials"){
                            if (($materials_set && $materials_enabled) || !$materials_set) { 
                            ?>
                            <!--Materials module-->
                            <div class="panel panel-default lp-element-wrapper" id="oer-lp-materials">
                                <input type="hidden" name="lp_order[lp_oer_materials]" class="element-order" value="<?php echo $index; ?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php echo oer_lp_get_field_label('lp_oer_materials'); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i></span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="panel-group lp-materials-container" id="lp-materials-container">
                                    </div>
                                    <button type="button"
                                            id="lp-add-materials"
                                            class="btn btn-default lp-add-materials"
                                    ><i class="fa fa-plus"></i> Add Materials</button>
                                </div>
                            </div>
                            <?php }
                        } elseif ($module=="lp_iq"){
                            if (($iq_set && $iq_enabled) || !$iq_set) {    
                            ?>
                            <!--Investigative Question Module-->
                            <div class="panel panel-default lp-element-wrapper oer-lp-iq" id="oer-lp-iq">
                                <input type="hidden" name="lp_order[lp_iq]" class="element-order" value="<?php echo $index; ?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php echo oer_lp_get_field_label('oer_lp_iq'); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label>Investigative Question</label>
                                        <input type="text"
                                               name="oer_lp_iq[question]"
                                               maxlength="512"
                                               class="form-control"
                                               placeholder="Investigative Question"
                                        >
                                    </div>
                                    <div class="form-group">
                                        <label>Framework Excerpt</label>
                                        <?php wp_editor( '',
                                            'oer_lp_iq_excerpt',
                                            $settings = array(
                                                'textarea_name' => 'oer_lp_iq[excerpt]',
                                                'media_buttons' => true,
                                                'textarea_rows' => 6,
                                                'drag_drop_upload' => true,
                                                'teeny' => true,
                                            )
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                            <?php }
                        } elseif ($module=="lp_required_materials"){
                            if (($req_materials_set && $req_materials_enabled) || !$req_materials_set) {    
                            ?>
                            <!--Required Equipment Materials Module-->
                            <div class="panel panel-default lp-element-wrapper oer-lp-required-materials" id="oer-lp-required-materials">
                                <input type="hidden" name="lp_order[lp_required_materials]" class="element-order" value="<?php echo $index; ?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">                                        
                                        <?php echo oer_lp_get_field_label('oer_lp_required_materials'); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="form-group">
                                        <label>Required Equipment Materials</label>
                                        <?php wp_editor( '',
                                            'oer_lp_required_materials',
                                            $settings = array(
                                                'textarea_name' => 'oer_lp_required_materials',
                                                'media_buttons' => true,
                                                'textarea_rows' => 6,
                                                'drag_drop_upload' => true,
                                                'teeny' => true,
                                            )
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                            <?php }
                        } elseif ($module=="lp_lesson_times_order"){
                            ?>
                            <!--For Lesson Times-->
                            <div class="panel panel-default lp-element-wrapper oer-lp-times-group" id="oer-lp-times-group">
                                <input type="hidden" name="lp_order[lp_lesson_times_order]" class="element-order" value="<?php echo $index; ?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php _e("Lesson Times", OER_LESSON_PLAN_SLUG); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row lp-time-element-row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="oer_lp_times_label[]" placeholder="label">
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="oer_lp_times_number[]" placeholder="40">
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <select name="oer_lp_times_type[]" class="form-control">
                                                    <option value="minutes">Minute(s)</option>
                                                    <option value="hours">Hour(s)</option>
                                                    <option value="days">Days(s)</option>
                                                    <option value="class_periods">Class Period(s)</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <button type="button"
                                                        class="btn btn-danger remove-time-element"
                                                        disabled="disabled"
                                                ><i class="fa fa-trash"></i> </button>
                                            </div>
                                        </div>
                                    </div><!-- /.row -->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button"
                                                    class="btn btn-default lp-add-time-element"
                                            ><i class="fa fa-plus"></i> Add Time Element</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        } elseif ($module=="lp_standard_order"){
                            ?>
                            <!--For Standards and Objectives -->
                            <div class="panel panel-default lp-element-wrapper oer-lp-standards-group" id="oer-lp-standards-group">
                                <input type="hidden" name="lp_order[lp_standard_order]" class="element-order" value="<?php echo $index; ?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php _e("Standards and Objectives", OER_LESSON_PLAN_SLUG); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <?php if (is_standards_plugin_installed()) {
                                        if (($standards_set && $standards_enabled) || !$standards_set) { ?>
                                    <h4 class="page-title-inner"><?php _e("Standards", OER_LESSON_PLAN_SLUG); ?></h4>
                                    <div id="selected-standard-wrapper">
                                        <p><?php _e("You have not selected any academic standards", OER_LESSON_PLAN_SLUG); ?></p>
                                    </div>
                                    <input type="hidden" name="oer_lp_standards">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button"
                                                    id="lp-select-standard"
                                                    class="btn btn-primary"
                                            >Select Standards</button>
                                        </div>
                                    </div>
                                    <?php }
                                    } ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h4>Related Instructional Objectives (<span title="Students will be able to...">SWBAT...</span>)</h4>
                                        </div>
                                        <div class="lp-related-objective-row" id="lp-related-objective-row">
                                            <div class="col-md-9">
                                                <div class="form-group">
                                                    <input type="text"
                                                           class="form-control"
                                                           name="oer_lp_related_objective[]"
                                                    >
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <button type="button"
                                                        class="btn btn-danger lp-remove-related-objective"
                                                        disabled="disabled"
                                                ><i class="fa fa-trash"></i> </button>
                                            </div>
                                        </div>
                                    </div><!-- /.row -->
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button"
                                                    class="btn btn-default lp-add-related-objective"
                                            ><i class="fa fa-plus"></i> Add Objective</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        } elseif ($module=="lp_activities_order"){
                            ?>
                            <!--Activities in this lesson-->
                            <div class="panel panel-default lp-element-wrapper oer-lp-activities-group" id="oer-lp-activities-group">
                                <input type="hidden" name="lp_order[lp_activities_order]" class="element-order" value="<?php echo $index; ?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php _e("Activities in this Lesson", OER_LESSON_PLAN_SLUG); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="panel-group" id="lp-ac-inner-panel">
                                        <?php
                                        for ($i = 0; $i < 5; $i++) { ?>
                                            <div class="panel panel-default lp-ac-item" id="lp-ac-item-<?php echo $i;?>">
                                                <div class="panel-heading">
                                                    <h3 class="panel-title lp-module-title">
                                                        <span class="lp-sortable-handle">
                                                            <i class="fa fa-arrow-down activity-reorder-down" aria-hidden="true"></i>
                                                            <i class="fa fa-arrow-up activity-reorder-up" aria-hidden="true"></i>
                                                        </span>
                                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                                    </h3>
                                                </div>
            
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label>Activity Title</label>
                                                        <input type="text" name="oer_lp_activity_title[]" class="form-control" placeholder="Activity Title">
                                                    </div>
                                                    <div class="row">
                                                        <div class="form-group col-md-8">
                                                            <label for="activity-title">Activity Type</label>
                                                            <select name="oer_lp_activity_type[]" class="form-control">
                                                                <option value=""> - Activity Type -</option>
                                                                <option value="hooks_set">Hooks / Set</option>
                                                                <option value="lecture">Lecture</option>
                                                                <option value="demonstration">Demo / Modeling</option>
                                                                <option value="independent_practice">Independent Practice</option>
                                                                <option value="guided_practice">Guided Practice</option>
                                                                <option value="check_understanding">Check Understanding</option>
                                                                <option value="lab_shop">Lab / Shop</option>
                                                                <option value="group_work">Group Work</option>
                                                                <option value="projects">Projects</option>
                                                                <option value="assessment">Formative Assessment</option>
                                                                <option value="closure">Closure</option>
                                                                <option value="research">Research / Annotate</option>
                                                                <option value="other">Other</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="form-group">
                                                        <?php wp_editor( '',
                                                            'oer-lp-activity-detail-'.$i,
                                                            $settings = array(
                                                                'textarea_name' => 'oer_lp_activity_detail[]',
                                                                'media_buttons' => true,
                                                                'textarea_rows' => 10,
                                                                'drag_drop_upload' => true,
                                                                'teeny' => true,
                                                            )
                                                        ); ?>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php }
                                        ?>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button"
                                                    class="btn btn-default lp-add-ac-item"
                                                    data-url="<?php echo admin_url('admin-index.php')?>"
                                            ><i class="fa fa-plus"></i> Add Activity</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php
                        } elseif ($module=="lp_summative_order"){
                            ?>
                            <!--Summative Assessment-->
                            <div class="panel panel-default lp-element-wrapper oer-lp-summative-group" id="oer-lp-summative-group">
                            <input type="hidden" name="lp_order[lp_summative_order]" class="element-order" value="<?php echo $index; ?>">
                            <div class="panel-heading">
                                <h3 class="panel-title lp-module-title">
                                    <?php _e("Summative Assessment", OER_LESSON_PLAN_SLUG); ?>
                                    <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                    </span>
                                    <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                </h3>
                            </div>
                            <div class="panel-body">
                                <h4><?php _e("Assessment Type(s):", OER_LESSON_PLAN_SLUG); ?></h4>
                                <div class="row">
                                    <?php
                                    $oer_lp_assessment_type = (isset($post_meta_data['oer_lp_assessment_type'][0]) ? unserialize($post_meta_data['oer_lp_assessment_type'][0]) : array());
                                    // Prepare array for the Assessment options checkboxes
                                    $assessment_options = array(
                                        'demonstrations' => 'Demonstrations',
                                        'interviews' => 'Interviews',
                                        'journals' => 'Journals',
                                        'observations' => 'Observations',
                                        'portfolios' => 'Portfolios',
                                        'projects' => 'Projects',
                                        'rubrics' => 'Rubrics',
                                        'surveys' => 'surveys',
                                        'teacher_made_test' => 'Teacher-Made Test',
                                        'writing_samples' => 'Writing Samples',
                                    );
            
                                    foreach ($assessment_options as $key => $assessment_option) { ?>
                                        <div class="col-md-3">
                                            <div class="checkbox oer-summative-checkbox">
                                                <label>
                                                    <input name="oer_lp_assessment_type[]"
                                                           type="checkbox"
                                                           value="<?php echo $key;?>"
                                                        <?php echo oer_lp_show_selected($key, $oer_lp_assessment_type, 'checkbox')?>
                                                    > <?php echo $assessment_option; ?>
                                                </label>
                                            </div>
                                        </div>
                                    <?php }?>
                                </div>
                                <div class="row">
                                    <?php
                                    $oer_lp_other_assessment_type = (isset($post_meta_data['oer_lp_other_assessment_type'][0]) ? $post_meta_data['oer_lp_other_assessment_type'][0] : '');
                                    ?>
                                    <div class="form-group col-md-8">
                                        <label><?php _e("Other", OER_LESSON_PLAN_SLUG); ?></label>
                                        <input type="text"
                                               name="oer_lp_other_assessment_type"
                                               class="form-control"
                                               placeholder="Other Assessment Type(s)"
                                               value="<?php echo $oer_lp_other_assessment_type;?>"
                                        >
                                    </div>
                                </div>
                                <div class="form-group">
                                    <?php
                                    $oer_lp_assessment = (isset($post_meta_data['oer_lp_assessment'][0]) ? $post_meta_data['oer_lp_assessment'][0] : '');
                                    wp_editor( $oer_lp_assessment,
                                        'oer-lp-other-assessment',
                                        $settings = array(
                                            'textarea_name' => 'oer_lp_assessment',
                                            'media_buttons' => true,
                                            'textarea_rows' => 10,
                                            'drag_drop_upload' => true,
                                            'teeny' => true,
                                        )
                                    ); ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        } elseif($module=="oer_lp_custom_editor_teacher_background"){
                            ?>
                            <div class="panel panel-default lp-element-wrapper oer-lp-introduction-group" id="oer-lp-custom-editor-group-teacher-background">
                                <input type="hidden" name="lp_order[<?php echo $module; ?>]" class="element-order" value="<?php echo $index;?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php _e("Teacher Background", OER_LESSON_PLAN_SLUG); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                     <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="<?php echo $module; ?>[title]" maxlength="512" class="form-control" placeholder="Text Module Title" />
                                    </div>
                                    <div class="form-group">
                                    <?php
                                    wp_editor( '',
                                        'oer-lp-custom-editor-teacher-background',
                                        $settings = array(
                                            'textarea_name' => "" . $module ."[description]",
                                            'media_buttons' => true,
                                            'textarea_rows' => 10,
                                            'drag_drop_upload' => true,
                                            'teeny' => true,
                                        )
                                    );
                                    ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        } elseif($module=="oer_lp_custom_editor_historical_background"){
                            ?>
                            <div class="panel panel-default lp-element-wrapper oer-lp-introduction-group" id="oer-lp-custom-editor-group-historical-background">
                                <input type="hidden" name="lp_order[<?php echo $module; ?>]" class="element-order" value="<?php echo $index;?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php _e("Historical Background", OER_LESSON_PLAN_SLUG); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                     <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="<?php echo $module; ?>[title]" maxlength="512" class="form-control" placeholder="" />
                                    </div>
                                    <div class="form-group">
                                    <?php
                                    wp_editor( '',
                                        'oer-lp-custom-editor-historical-background',
                                        $settings = array(
                                            'textarea_name' => "" . $module ."[description]",
                                            'media_buttons' => true,
                                            'textarea_rows' => 10,
                                            'drag_drop_upload' => true,
                                            'teeny' => true,
                                        )
                                    );
                                    ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                         } elseif($module=="oer_lp_custom_editor_student_background"){
                            ?>
                            <div class="panel panel-default lp-element-wrapper oer-lp-introduction-group" id="oer-lp-custom-editor-group-student-background">
                                <input type="hidden" name="lp_order[<?php echo $module; ?>]" class="element-order" value="<?php echo $index;?>">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php _e("Student Background", OER_LESSON_PLAN_SLUG); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                     <div class="form-group">
                                        <label>Title</label>
                                        <input type="text" name="<?php echo $module; ?>[title]" maxlength="512" class="form-control" placeholder="Text Module Title" />
                                    </div>
                                    <div class="form-group">
                                    <?php
                                    wp_editor( '',
                                        'oer-lp-custom-editor-student-background',
                                        $settings = array(
                                            'textarea_name' => "" . $module ."[description]",
                                            'media_buttons' => true,
                                            'textarea_rows' => 10,
                                            'drag_drop_upload' => true,
                                            'teeny' => true,
                                        )
                                    );
                                    ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                } else {
                ?>
                <div class="panel panel-default lp-element-wrapper oer-lp-introduction-group" id="oer-lp-introduction-group">
                    <input type="hidden" name="lp_order[lp_introduction_order]" class="element-order" value="1">
                    <div class="panel-heading">
                        <h3 class="panel-title lp-module-title">
                            <?php _e("Introduction", OER_LESSON_PLAN_SLUG); ?>
                            <span class="lp-sortable-handle">
                                <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                            </span>
                            <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <?php
                        $oer_lp_introduction = isset($post_meta_data['oer_lp_introduction'][0]) ? $post_meta_data['oer_lp_introduction'][0] : "";
                        wp_editor( $oer_lp_introduction,
                            'oer-lp-introduction',
                            $settings = array(
                                'textarea_name' => 'oer_lp_introduction',
                                'media_buttons' => true,
                                'textarea_rows' => 10,
                                'drag_drop_upload' => true,
                                'teeny' => true,
                            )
                        );
                        ?>
                    </div>
                </div>
                <!--Authors-->
                <div class="panel panel-default lp-element-wrapper oer-lp-authors-group" id="oer-lp-authors">
                    <input type="hidden" name="lp_order[lp_authors_order]" class="element-order" value="2">
                    <div class="panel-heading">
                        <h3 class="panel-title lp-module-title">
                            <?php _e("Authors", OER_LESSON_PLAN_SLUG); ?>
                            <span class="lp-sortable-handle">
                                <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                            </span>
                            <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="panel-group">
                            <div class="panel panel-default lp-author-element-wrapper">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php _e("Author", OER_LESSON_PLAN_SLUG); ?>
                                        <span class="lp-sortable-handle">
                                            <i class="fa fa-arrow-down author-reorder-down" aria-hidden="true"></i>
                                            <i class="fa fa-arrow-up author-reorder-up" aria-hidden="true"></i>
                                        </span>
                                        <span class="btn btn-danger btn-sm lp-remove-author"
                                              title="Delete"
                                              disabled="disabled"
                                        ><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row lp-authors-element-row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="oer_lp_authors[name][]" placeholder="Name">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="oer_lp_authors[role][]" placeholder="Role">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="oer_lp_authors[author_url][]" placeholder="Author URL">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="oer_lp_authors[institution][]" placeholder="Institution">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <input type="text" class="form-control" name="oer_lp_authors[institution_url][]" placeholder="Institution URL">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="hidden" name="oer_lp_authors[author_pic][]">
                                            <img src="<?php echo OER_LESSON_PLAN_URL;?>assets/images/lp-oer-person-placeholder.png"
                                                 class="img-circle lp-oer-person-placeholder"
                                                 width="50px"
                                                 height="50px"/>
                                        </div>

                                    </div><!-- /.row -->
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button"
                                        id="lp-add-more-author"
                                        class="btn btn-default lp-add-more-author"
                                ><i class="fa fa-plus"></i> Add More</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php if (is_oer_plugin_installed()){ ?>
                <!-- Primary Sources -->
                <div class="panel panel-default lp-element-wrapper oer-lp-primary-resources" id="oer-lp-primary-resources">
                    <input type="hidden" name="lp_order[lp_primary_resources]" class="element-order" value="3">
                    <div class="panel-heading">
                        <h3 class="panel-title lp-module-title">
                            <?php _e("Primary Resources", OER_LESSON_PLAN_SLUG); ?>
                            <span class="lp-sortable-handle">
                                <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                            </span>
                            <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="panel-group lp-primary-resource-element-panel">
                            <div class="panel panel-default lp-primary-resource-element-wrapper">
                                <div class="panel-heading">
                                    <h3 class="panel-title lp-module-title">
                                        <?php _e("Resource", OER_LESSON_PLAN_SLUG); ?>
                                        <span class="lp-sortable-handle">
                                        <i class="fa fa-arrow-down resource-reorder-down" aria-hidden="true"></i>
                                        <i class="fa fa-arrow-up resource-reorder-up" aria-hidden="true"></i>
                                    </span>
                                        <span class="btn btn-danger btn-sm lp-remove-source"
                                              title="Delete"
                                              disabled="disabled"
                                        ><i class="fa fa-trash"></i> </span>
                                    </h3>
                                </div>
                                <div class="panel-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Resource</label>
                                                <?php
                                                $posts = get_posts([
                                                    'post_type' => 'resource',
                                                    'post_status' => 'publish',
                                                    'numberposts' => -1,
                                                    'orderby' => 'title',
                                                    'order'    => 'ASC'
                                                ]);
                                                ?>
                                                <select name="oer_lp_primary_resources[resource][]" class="form-control">
                                                    <option value="">Select Resource</option>
                                                    <?php
                                                    if (count($posts)) {
                                                        foreach ($posts as $post) {
                                                            echo '<option value="'.$post->post_title.'">'.$post->post_title.'</option>';
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="checkbox pull-right">
                                                <label>
                                                    <input type="checkbox" name="oer_lp_primary_resources[sensitive_material][]" value="yes">
                                                    Sensitive Material
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Teacher Information</label>
                                        <?php wp_editor( '',
                                            'oer-lp-resource-teacher-1',
                                            $settings = array(
                                                'textarea_name' => 'oer_lp_primary_resources[teacher_info][]',
                                                'media_buttons' => true,
                                                'textarea_rows' => 6,
                                                'drag_drop_upload' => true,
                                                'teeny' => true,
                                            )
                                        ); ?>
                                    </div>
                                    <div class="form-group">
                                        <label>Student Information</label>
                                        <?php wp_editor( '',
                                            'oer-lp-resource-student-1',
                                            $settings = array(
                                                'textarea_name' => 'oer_lp_primary_resources[student_info][]',
                                                'media_buttons' => true,
                                                'textarea_rows' => 6,
                                                'drag_drop_upload' => true,
                                                'teeny' => true,
                                            )
                                        ); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button"
                                        id="lp-add-more-resource"
                                        class="btn btn-default lp-add-more-resource"
                                ><i class="fa fa-plus"></i> Add More</button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                <!--Materials module-->
                <div class="panel panel-default lp-element-wrapper" id="oer-lp-materials">
                    <input type="hidden" name="lp_order[lp_oer_materials]" class="element-order" value="4">
                    <div class="panel-heading">
                        <h3 class="panel-title lp-module-title">
                            <?php _e("Materials", OER_LESSON_PLAN_SLUG); ?>
                            <span class="lp-sortable-handle">
                                <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                            </span>
                            <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i></span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="panel-group lp-materials-container" id="lp-materials-container">
                        </div>
                        <button type="button"
                                id="lp-add-materials"
                                class="btn btn-default lp-add-materials"
                        ><i class="fa fa-plus"></i> Add Materials</button>
                    </div>
                </div>
                <!--Investigative Question Module-->
                <div class="panel panel-default lp-element-wrapper oer-lp-iq" id="oer-lp-iq">
                    <input type="hidden" name="lp_order[lp_iq]" class="element-order" value="4">
                    <div class="panel-heading">
                        <h3 class="panel-title lp-module-title">
                            <?php _e("Investigative Question", OER_LESSON_PLAN_SLUG); ?>
                            <span class="lp-sortable-handle">
                                <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                            </span>
                            <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="form-group">
                            <label>Investigative Question</label>
                            <input type="text"
                                   name="oer_lp_iq[question]"
                                   maxlength="512"
                                   class="form-control"
                                   placeholder="Investigative Question"
                            >
                        </div>
                        <div class="form-group">
                            <label>Framework Excerpt</label>
                            <?php wp_editor( '',
                                'oer_lp_iq_excerpt',
                                $settings = array(
                                    'textarea_name' => 'oer_lp_iq[excerpt]',
                                    'media_buttons' => true,
                                    'textarea_rows' => 6,
                                    'drag_drop_upload' => true,
                                    'teeny' => true,
                                )
                            ); ?>
                        </div>
                    </div>
                </div>
                <!--For Lesson Times-->
                <div class="panel panel-default lp-element-wrapper oer-lp-times-group" id="oer-lp-times-group">
                    <input type="hidden" name="lp_order[lp_lesson_times_order]" class="element-order" value="5">
                    <div class="panel-heading">
                        <h3 class="panel-title lp-module-title">
                            <?php _e("Lesson Times", OER_LESSON_PLAN_SLUG); ?>
                            <span class="lp-sortable-handle">
                                <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                            </span>
                            <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="row lp-time-element-row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="oer_lp_times_label[]" placeholder="label">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="oer_lp_times_number[]" placeholder="40">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <select name="oer_lp_times_type[]" class="form-control">
                                        <option value="minutes">Minute(s)</option>
                                        <option value="hours">Hour(s)</option>
                                        <option value="days">Days(s)</option>
                                        <option value="class_periods">Class Period(s)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <button type="button"
                                            class="btn btn-danger remove-time-element"
                                            disabled="disabled"
                                    ><i class="fa fa-trash"></i> </button>
                                </div>
                            </div>
                        </div><!-- /.row -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button"
                                        class="btn btn-default lp-add-time-element"
                                ><i class="fa fa-plus"></i> Add Time Element</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--For Standards and Objectives -->
                <div class="panel panel-default lp-element-wrapper oer-lp-standards-group" id="oer-lp-standards-group">
                    <input type="hidden" name="lp_order[lp_standard_order]" class="element-order" value="6">
                    <div class="panel-heading">
                        <h3 class="panel-title lp-module-title">
                            <?php _e("Standards and Objectives", OER_LESSON_PLAN_SLUG); ?>
                            <span class="lp-sortable-handle">
                                <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                            </span>
                            <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <?php if (is_standards_plugin_installed()) { ?>
                        <h4 class="page-title-inner"><?php _e("Standards", OER_LESSON_PLAN_SLUG); ?></h4>
                        <div id="selected-standard-wrapper">
                            <p><?php _e("You have not selected any academic standards", OER_LESSON_PLAN_SLUG); ?></p>
                        </div>
                        <input type="hidden" name="oer_lp_standards" value="">
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button"
                                        id="lp-select-standard"
                                        class="btn btn-primary"
                                >Select Standards</button>
                            </div>
                        </div>
                        <?php } ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h4>Related Instructional Objectives (<span title="Students will be able to...">SWBAT...</span>)</h4>
                            </div>
                            <div class="lp-related-objective-row" id="lp-related-objective-row">
                                <div class="col-md-9">
                                    <div class="form-group">
                                        <input type="text"
                                               class="form-control"
                                               name="oer_lp_related_objective[]"
                                        >
                                    </div>
                                </div>
                                <div class="col-md-1">
                                    <button type="button"
                                            class="btn btn-danger lp-remove-related-objective"
                                            disabled="disabled"
                                    ><i class="fa fa-trash"></i> </button>
                                </div>
                            </div>
                        </div><!-- /.row -->
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button"
                                        class="btn btn-default lp-add-related-objective"
                                ><i class="fa fa-plus"></i> Add Objective</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Activities in this lesson-->
                <div class="panel panel-default lp-element-wrapper oer-lp-activities-group" id="oer-lp-activities-group">
                    <input type="hidden" name="lp_order[lp_activities_order]" class="element-order" value="7">
                    <div class="panel-heading">
                        <h3 class="panel-title lp-module-title">
                            <?php _e("Activities in this Lesson", OER_LESSON_PLAN_SLUG); ?>
                            <span class="lp-sortable-handle">
                                <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                                <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                            </span>
                            <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                        </h3>
                    </div>
                    <div class="panel-body">
                        <div class="panel-group" id="lp-ac-inner-panel">
                            <?php
                            for ($i = 0; $i < 5; $i++) { ?>
                                <div class="panel panel-default lp-ac-item" id="lp-ac-item-<?php echo $i;?>">
                                    <div class="panel-heading">
                                        <h3 class="panel-title lp-module-title">
                                            <span class="lp-sortable-handle">
                                                <i class="fa fa-arrow-down activity-reorder-down" aria-hidden="true"></i>
                                                <i class="fa fa-arrow-up activity-reorder-up" aria-hidden="true"></i>
                                            </span>
                                            <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                                        </h3>
                                    </div>

                                    <div class="panel-body">
                                        <div class="form-group">
                                            <label>Activity Title</label>
                                            <input type="text" name="oer_lp_activity_title[]" class="form-control" placeholder="Activity Title">
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-md-8">
                                                <label for="activity-title">Activity Type</label>
                                                <select name="oer_lp_activity_type[]" class="form-control">
                                                    <option value=""> - Activity Type -</option>
                                                    <option value="hooks_set">Hooks / Set</option>
                                                    <option value="lecture">Lecture</option>
                                                    <option value="demonstration">Demo / Modeling</option>
                                                    <option value="independent_practice">Independent Practice</option>
                                                    <option value="guided_practice">Guided Practice</option>
                                                    <option value="check_understanding">Check Understanding</option>
                                                    <option value="lab_shop">Lab / Shop</option>
                                                    <option value="group_work">Group Work</option>
                                                    <option value="projects">Projects</option>
                                                    <option value="assessment">Formative Assessment</option>
                                                    <option value="closure">Closure</option>
                                                    <option value="research">Research / Annotate</option>
                                                    <option value="other">Other</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <?php wp_editor( '',
                                                'oer-lp-activity-detail-'.$i,
                                                $settings = array(
                                                    'textarea_name' => 'oer_lp_activity_detail[]',
                                                    'media_buttons' => true,
                                                    'textarea_rows' => 10,
                                                    'drag_drop_upload' => true,
                                                    'teeny' => true,
                                                )
                                            ); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php }
                            ?>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <button type="button"
                                        class="btn btn-default lp-add-ac-item"
                                        data-url="<?php echo admin_url('admin-index.php')?>"
                                ><i class="fa fa-plus"></i> Add Activity</button>
                            </div>
                        </div>
                    </div>
                </div>
                <!--Summative Assessment-->
                <div class="panel panel-default lp-element-wrapper oer-lp-summative-group" id="oer-lp-summative-group">
                <input type="hidden" name="lp_order[lp_summative_order]" class="element-order" value="8">
                <div class="panel-heading">
                    <h3 class="panel-title lp-module-title">
                        <?php _e("Summative Assessment", OER_LESSON_PLAN_SLUG); ?>
                        <span class="lp-sortable-handle">
                            <i class="fa fa-arrow-down reorder-down" aria-hidden="true"></i>
                            <i class="fa fa-arrow-up reorder-up" aria-hidden="true"></i>
                        </span>
                        <span class="btn btn-danger btn-sm lp-remove-module" title="Delete"><i class="fa fa-trash"></i> </span>
                    </h3>
                </div>
                <div class="panel-body">
                    <h4><?php _e("Assessment Type(s):", OER_LESSON_PLAN_SLUG); ?></h4>
                    <div class="row">
                        <?php
                        $oer_lp_assessment_type = (isset($post_meta_data['oer_lp_assessment_type'][0]) ? unserialize($post_meta_data['oer_lp_assessment_type'][0]) : array());
                        // Prepare array for the Assessment options checkboxes
                        $assessment_options = array(
                            'demonstrations' => 'Demonstrations',
                            'interviews' => 'Interviews',
                            'journals' => 'Journals',
                            'observations' => 'Observations',
                            'portfolios' => 'Portfolios',
                            'projects' => 'Projects',
                            'rubrics' => 'Rubrics',
                            'surveys' => 'surveys',
                            'teacher_made_test' => 'Teacher-Made Test',
                            'writing_samples' => 'Writing Samples',
                        );

                        foreach ($assessment_options as $key => $assessment_option) { ?>
                            <div class="col-md-3">
                                <div class="checkbox oer-summative-checkbox">
                                    <label>
                                        <input name="oer_lp_assessment_type[]"
                                               type="checkbox"
                                               value="<?php echo $key;?>"
                                            <?php echo oer_lp_show_selected($key, $oer_lp_assessment_type, 'checkbox')?>
                                        > <?php echo $assessment_option; ?>
                                    </label>
                                </div>
                            </div>
                        <?php }?>
                    </div>
                    <div class="row">
                        <?php
                        $oer_lp_other_assessment_type = (isset($post_meta_data['oer_lp_other_assessment_type'][0]) ? $post_meta_data['oer_lp_other_assessment_type'][0] : '');
                        ?>
                        <div class="form-group col-md-8">
                            <label><?php _e("Other", OER_LESSON_PLAN_SLUG); ?></label>
                            <input type="text"
                                   name="oer_lp_other_assessment_type"
                                   class="form-control"
                                   placeholder="Other Assessment Type(s)"
                                   value="<?php echo $oer_lp_other_assessment_type;?>"
                            >
                        </div>
                    </div>
                    <div class="form-group">
                        <?php
                        $oer_lp_assessment = (isset($post_meta_data['oer_lp_assessment'][0]) ? $post_meta_data['oer_lp_assessment'][0] : '');
                        wp_editor( $oer_lp_assessment,
                            'oer-lp-other-assessment',
                            $settings = array(
                                'textarea_name' => 'oer_lp_assessment',
                                'media_buttons' => true,
                                'textarea_rows' => 10,
                                'drag_drop_upload' => true,
                                'teeny' => true,
                            )
                        ); ?>
                    </div>
                </div>
            </div>
            <?php }
            }?>

            <?php if (in_array('lp_add_module', $oer_lp_default_structure)) { ?>
            <!--Add Extra Module-->
            <div class="row">
                <div class="col-md-12">
                    <button type="button"
                            id="lp-create-dynamic-module"
                            class="btn btn-default lp-create-dynamic-module"
                    ><i class="fa fa-plus"></i> Add Module</button>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>