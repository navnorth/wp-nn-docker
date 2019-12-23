<?php

add_filter('body_class', function($classes){
    $classes[] = 'primary-source-template';
    return $classes;
});

get_header();
$module_title = "";
$module_content = "";
$modules = array();
$back_url = "";
$source_id = 0;
$lp_prev_class = "";
$lp_next_class = "";
$prev_url = "";
$next_url = "";
$current_index = 0;

$post_meta_data = get_post_meta($post->ID );
$elements_orders = isset($post_meta_data['lp_order'][0]) ? unserialize($post_meta_data['lp_order'][0]) : array();

// Back Button URL
$curriculum = get_query_var('curriculum');
$curriculum_details = get_page_by_path($curriculum, OBJECT, "lesson-plans");
$curriculum_id = $curriculum_details->ID;
if ($curriculum)
    $back_url = site_url("inquiry-sets/".$curriculum);

// Get Resource ID
$module_slug = get_query_var('module');
if (!empty($elements_orders)) {
    $keys = array(
        "lp_introduction_order",
        "lp_primary_resources",
        "lp_lesson_times_order",
        "lp_industries_order",
        "lp_standard_order",
        "lp_activities_order",
        "lp_summative_order",
        "lp_authors_order",
        "lp_iq",
        "lp_oer_materials"
    );
    $eIndex = 0;
    foreach($elements_orders as $elementKey=>$order){
        if (!in_array($elementKey,$keys)){
            if (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'oer_lp_vocabulary_list_title_') === false) 
                $module = (isset($post_meta_data[$elementKey][0]) ? unserialize($post_meta_data[$elementKey][0]) : "");
            
            if (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'oer_lp_custom_text_list_') !== false){
                $module['title'] = "Text List";
                $module['description'] = $module[0];
            } elseif (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'oer_lp_vocabulary_list_title_') !== false) {
                $oer_lp_vocabulary_list_title = (isset($post_meta_data[$elementKey][0]) ? $post_meta_data[$elementKey][0] : "");
                $oer_keys = explode('_', $elementKey); 
                $listOrder = end($oer_keys);
                $oer_lp_vocabulary_details = (isset($post_meta_data['oer_lp_vocabulary_details_'.$listOrder][0]) ? $post_meta_data['oer_lp_vocabulary_details_'.$listOrder][0] : "");
                $module['title'] = $oer_lp_vocabulary_list_title;
                $module['description'] = $oer_lp_vocabulary_details;
            } elseif (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'lp_oer_materials_list_') !== false) {
                $module['title'] = "Materials";
                $html = "";
                $i = 0;
                foreach($module['url'] as $url){
                    $img = get_file_type_from_url($url, "fa-4x");
                    $html .= "<div class='row clear input-group'>";
                    if ($img['title']=="Image")
                        $html .= "<div class='col-md-4'><img src='".$url."'></div>";
                    else
                        $html .= "<div class='col-md-4' style='display:flex;align-items:center;justify-content:center;'>".$img['icon']."</div>";
                    $html .= "<div class='col-md-8'>".$module['description'][$i]."</div>";
                    $html .= "</div>";
                    $i++;
                }
                $module['description'] = $html;
            }
            if (sanitize_title($module['title'])==$module_slug){
                $module_title = $module['title'];
                if (isset($module['description']))
                    $module_content = $module['description'];
                $current_index = $eIndex;
            }
            $modules[] = $module;
            $eIndex++;
        }
    }
}

// Get Curriculum Meta for Primary Sources
$post_meta_data = get_post_meta($curriculum_id);
$primary_resources = (isset($post_meta_data['oer_lp_primary_resources'][0]) ? unserialize($post_meta_data['oer_lp_primary_resources'][0]) : array());
$index = 0;
$prev_url = null;
$next_url = null;
$cnt = count($primary_resources['resource']);
if (!empty($primary_resources) && lp_scan_array($primary_resources)) {
    if (!empty(array_filter($primary_resources['resource']))) {
        if ($current_index==0) {
            if (isset($primary_resources['resource'][$cnt-1])){
                $prev_resource = oer_lp_get_resource_details($primary_resources['resource'][$cnt-1]);
                $prev_url = $back_url."/source/".sanitize_title($prev_resource->post_title)."-".$prev_resource->ID;
            }
        } else {
            if (isset($modules[$current_index-1])){
                $prev_resource = $modules[$current_index-1];
                $prev_url = $back_url."/module/".sanitize_title($prev_resource['title']);
            }
        }
    }
}
if (isset($modules[$current_index+1])){
    $next_resource = $modules[$current_index+1];
    $next_url = $back_url."/module/".sanitize_title($next_resource['title']);
}
?>
<div class="lp-nav-block"><a class="back-button" href="<?php echo $back_url; ?>"><i class="fas fa-arrow-left"></i><?php echo $curriculum_details->post_title; ?></a></div>
<div class="row ps-details-row">
    <?php
    $resource_meta = null;
    $subject_areas = null;
    ?>
    <div class="ps-details col-md-12 col-sm-12">
        <div class="ps-info">
            <h1 class="ps-info-title"><?php echo $module_title; ?></h1>
            <div class="ps-info-description">
                <?php echo $module_content; ?>
            </div>
        </div>
    </div>
</div>
<div class="ps-related-sources lp-primary-sources-row">
    <div class="lp-ps-nav-left-block <?php echo $lp_prev_class; ?> col-md-6 col-sm-12">
        <?php if (!empty($prev_resource)):
        $resource_img = wp_get_attachment_image_url( get_post_thumbnail_id($prev_resource), 'resource-thumbnail' );
        ?>
        <a class="lp-ps-nav-left" href="<?php echo $prev_url; ?>" data-activetab="" data-id="<?php echo $index-1; ?>" data-count="<?php echo count($primary_resources['resource']); ?>" data-curriculum="<?php echo $curriculum_id; ?>" data-prevsource="<?php echo $primary_resources['resource'][$index-1]; ?>">
            <span class="col-md-3">&nbsp;</span>
            <span class="nav-media-icon"><i class="fas fa-arrow-left fa-2x"></i></span>
            <span class="nav-media-image col-md-8">
                <span class="nav-image-thumbnail col-md-4">
                    <?php if (!empty($resource_img)):
                    if (is_object($prev_resource))
                        $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/source/".sanitize_title($prev_resource->post_title)."-".$prev_resource->ID);
                    else
                        $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/module/".sanitize_title($prev_resource['title']));
                    ?>
                    <div class="resource-thumbnail" style="background: url('<?php echo $resource_img ?>') no-repeat center rgba(204,97,12,.1); background-size:cover;"></div>
                    <?php else: ?>
                    <div class="resource-thumbnail" style="background: rgba(204,97,12,.1); background-size:cover; display:flex; align-items:center; justify-content: center;"><i class="fa fa-file-text-o fa-4x"></i></div>
                    <?php endif; ?>
                </span>
                <span class="nav-lp-resource-title col-md-8">
                    <?php
                    if (is_object($prev_resource))
                        echo $prev_resource->post_title;
                    else
                        echo $prev_resource['title'];
                    ?>
                </span>
            </span>
        </a>
        <?php endif; ?>
    </div>
    <div class="lp-ps-nav-right-block <?php echo $lp_next_class; ?> col-md-6 col-sm-12">
        <?php if (!empty($next_resource)):
        $resource_img = wp_get_attachment_image_url( get_post_thumbnail_id($next_resource), 'resource-thumbnail' );
        ?>
        <a class="lp-ps-nav-right" href="<?php echo $next_url; ?>" data-activetab="" data-id="<?php echo $index+1; ?>" data-count="<?php echo count($primary_resources['resource']); ?>" data-curriculum="<?php echo $curriculum_id; ?>" data-nextsource="<?php echo $primary_resources['resource'][$index+1]; ?>">
            <span class="nav-media-image col-md-8">
                <span class="nav-image-thumbnail col-md-4">
                    <?php if (!empty($resource_img)):
                    $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/module/".sanitize_title($next_resource['title']));
                    ?>
                    <div class="resource-thumbnail" style="background: url('<?php echo $resource_img ?>') no-repeat center rgba(204,97,12,.1); background-size:cover;"></div>
                    <?php else: ?>
                    <div class="resource-thumbnail" style="background: rgba(204,97,12,.1); background-size:cover; display:flex; align-items:center; justify-content: center;"><i class="fa fa-file-text-o fa-4x"></i></div>
                    <?php endif; ?>
                </span>
                <span class="nav-lp-resource-title col-md-8">
                    <?php
                    if (is_object($next_resource))
                        echo $next_resource->post_title;
                    else
                        echo $next_resource['title'];
                    ?>
                </span>
            </span>
            <span class="nav-media-icon"><i class="fas fa-arrow-right fa-2x"></i></span>
            <span class="col-md-3">&nbsp;</span>
        </a>
        <?php endif; ?>
    </div>
</div>
<div class="lp-ajax-loader" role="status">
    <div class="lp-ajax-loader-img">
        <img src="<?php echo OER_LESSON_PLAN_URL."/assets/images/load.gif"; ?>" />
    </div>
</div>
<?php
get_footer();
?>