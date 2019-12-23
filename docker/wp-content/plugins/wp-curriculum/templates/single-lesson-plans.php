<?php
/**
 * The Template for displaying all single Curriculum
 */

/**
 * Enqueue the assets
 */
wp_enqueue_style('lesson-plan-load-fa', OER_LESSON_PLAN_URL.'assets/lib/font-awesome/css/all.min.css');
wp_enqueue_style('lesson-plan-bootstrap', OER_LESSON_PLAN_URL.'assets/lib/bootstrap-3.3.7/css/bootstrap.min.css');
wp_enqueue_script('lesson-plan-frontend', OER_LESSON_PLAN_URL.'assets/js/frontend/lesson-plan.js', array('jquery'), null, true);
wp_enqueue_script( 'jquery-ui-slider' );

get_header();

global $post;
global $wpdb;
$oer_sensitive = false;
$sensitive_material = null;

$post_meta_data = get_post_meta($post->ID );
$elements_orders = isset($post_meta_data['lp_order'][0]) ? unserialize($post_meta_data['lp_order'][0]) : array();

//Grade Level
$lp_grade = isset($post_meta_data['oer_lp_grades'][0])? unserialize($post_meta_data['oer_lp_grades'][0])[0]:"";
if ($lp_grade!=="pre-k" && $lp_grade!=="k")
    $lp_grade = "Grade ".$lp_grade;
    
// Download Copy
$oer_lp_download_copy_document = (isset($post_meta_data['oer_lp_download_copy_document'][0]) ? $post_meta_data['oer_lp_download_copy_document'][0] : '');
$oer_lp_standards = isset($post_meta_data['oer_lp_standards'][0])?$post_meta_data['oer_lp_standards'][0]:"";
$tags = get_the_terms($post->ID,"post_tag");
$authors = (isset($post_meta_data['oer_lp_authors'][0]) ? unserialize($post_meta_data['oer_lp_authors'][0]) : array());

// check if there is a resource with sensitive material set
$oer_resources = (isset($post_meta_data['oer_lp_primary_resources'][0]) ? unserialize($post_meta_data['oer_lp_primary_resources'][0]) : array());

if (isset($oer_resources['sensitive_material']))
    $sensitive_material = $oer_resources['sensitive_material'];
    
if (!empty($sensitive_material) && count($sensitive_material)>0) {
    $oer_sensitive = true;
}

$lp_type_set = (get_option('oer_lp_type_label'))?true:false;
$lp_type_enabled = (get_option('oer_lp_type_enabled'))?true:false;
$related_inquiry_set = (get_option('oer_lp_related_inquiry_set_label'))?true:false;
$related_inquiry_enabled = (get_option('oer_lp_related_inquiry_set_enabled'))?true:false;
$author_set = (get_option('oer_lp_authors_label'))?true:false;
$author_enabled = (get_option('oer_lp_authors_enabled'))?true:false;
$standards_set = (get_option('oer_lp_standards_label'))?true:false;
$standards_enabled = (get_option('oer_lp_standards_enabled'))?true:false;
$age_levels_set = (get_option('oer_lp_age_levels_label'))?true:false;
$age_levels_enabled = (get_option('oer_lp_age_levels_enabled'))?true:false;
$suggested_time_set = (get_option('oer_lp_suggested_instructional_time_label'))?true:false;
$suggested_time_enabled = (get_option('oer_lp_suggested_instructional_time_enabled'))?true:false;
$req_materials_set = (get_option('oer_lp_required_materials_label'))?true:false;
$req_materials_enabled = (get_option('oer_lp_required_materials_enabled'))?true:false;

if (have_posts()) : while (have_posts()) : the_post();
echo oer_breadcrumb_display();
?>
<div class="container">
    <div class="row lp-featured-section">
        <div class="row tc-lp-details-header">
            <h1 class="tc-lp-title"><?php echo the_title(); ?></h1>
        </div>
        <div class="row tc-lp-details-content">
            <div class="col-md-8 col-sm-12 curriculum-detail padding-left-0">
                <div class="tc-lp-details">
                    <?php if (($lp_type_set && $lp_type_enabled) || !$lp_type_set) { ?>
                    <div class="tc-lp-type">
                        <?php
                        $oer_lp_type = (isset($post_meta_data['oer_lp_type'][0]) ? $post_meta_data['oer_lp_type'][0] : '');
                        echo $oer_lp_type;
                        ?>
                    </div>
                    <?php } ?>
                    <div class="tc-lp-details-description">
                        <?php if (strlen($post->post_content)>230) : ?>
                        <div class="lp-excerpt"><?php echo oer_lp_content(230); ?></div>
                        <div class="lp-full-content"><?php echo the_content(); ?> <a href="javascript:void(0);" class="lp-read-less">(read less)</a></div>
                        <?php else : ?>
                        <div class="lp-content"><?php echo the_content(); ?></div>
                        <?php endif; ?>
                    </div>
                    <?php
                    $related_inquiry_sets = (isset($post_meta_data['oer_lp_related_inquiry_set'][0]) ? unserialize($post_meta_data['oer_lp_related_inquiry_set'][0]) : array());
                    $show_related_inquiry_sets = false;
                    foreach($related_inquiry_sets as $rset){
                        if ($rset!=="0"){
                            $show_related_inquiry_sets = true;
                            break;
                        }
                    }
                    if ($show_related_inquiry_sets) {
                        if (($related_inquiry_set && $related_inquiry_enabled) || !$related_inquiry_set) {
                    ?>
                    <div class="tc-related-inquiry-sets">
                        <h4 class="tc-related-inquiry-sets-heading clearfix">
                            <?php echo oer_lp_get_field_label('oer_lp_related_inquiry_set'); ?>
                        </h4>
                        <div class="tc-related-inquiry-sets-details clearfix">
                            <ul class="tc-related-inquiry-sets-list">
                            <?php
                            foreach($related_inquiry_sets as $inquiry_set) {
                                if ($inquiry_set!=="0") {
                                    $inquiry = oer_lp_get_inquiry_set_details($inquiry_set);
                                    $inquiry_link = get_permalink($inquiry_set);
                                    
                                    echo '<li><a href="'.$inquiry_link.'">'.$inquiry->post_title.'</a></li>';
                                }
                            } ?>
                            </ul>
                        </div>
                    </div>
                    <?php }
                    } ?>
                    <?php if (!empty($oer_lp_standards)) {
                         if (($standards_set && $standards_enabled) || !$standards_set) {
                    ?>
                    <div class="tc-lp-standards">
                        <h4 class="tc-lp-field-heading clearfix">
                            <?php echo oer_lp_get_field_label('oer_lp_standards'); ?>
                        </h4>
                        <div class="tc-lp-standards-details clearfix">
                            <ul class="tc-lp-standards-list">
                                <?php
                                $stds = array();
                                $standards = array();
                                $cstandard = null;
                                $oer_lp_standards = explode(",",$oer_lp_standards);
                                if (is_array($oer_lp_standards)):
                                    $current_std_id = "";
                                    foreach($oer_lp_standards as $standard){
                                        if (function_exists('oer_std_get_standard_by_notation')){
                                            $core_standard = oer_std_get_standard_by_notation($standard);
                                            if ($current_std_id!==$core_standard->id){
                                                if (!empty($standards) && !empty($cstandard)) {
                                                    $stds[] = array_merge(array("notation"=>$standards), $cstandard);
                                                }
                                                $standards = array();
                                                $current_std_id = $core_standard->id;
                                                $cstandard = array("core_standard_id"=>$core_standard->id,"core_standard_name"=>$core_standard->standard_name);
                                            }
                                            $standards[] = $standard;
                                        }
                                    }
                                    if (!empty($standards) && !empty($cstandard)) {
                                        $stds[] = array_merge(array("notation"=>$standards), $cstandard);
                                    }
                                    $cstd_id = array_column($stds,"core_standard_id");
                                    array_multisort($cstd_id,SORT_ASC,$stds);
                                    $standard_details = "";
                                    foreach($stds as $std){
                                        if (isset($std['core_standard_id'])) {
                                            echo "<li>";
                                                echo '<a class="lp-standard-toggle" data-toggle="collapse" href="#core-standard-'.$std['core_standard_id'].'">'.$std['core_standard_name'].' <i class="fas fa-caret-right"></i></a>';
                                            ?>
                                            <div class="collapse tc-lp-details-standard" id="core-standard-<?php echo $std['core_standard_id']; ?>">
                                            <?php
                                            if (is_array($std['notation'])) {
                                                echo "<ul class='tc-lp-notation-list'>";
                                                foreach ($std['notation'] as $notation) {
                                                    if (function_exists('was_standard_details'))
                                                        $standard_details = was_standard_details($notation);
                                                    if (!empty($standard_details)){
                                                        if (isset($standard_details->description))
                                                            echo "<li>".$standard_details->description."</li>";
                                                        else
                                                            echo "<li>".$standard_details->standard_title."</li>";
                                                    }
                                                }
                                                echo "</ul>";
                                            }
                                                echo "</div>";
                                            echo "</li>";
                                        }
                                    }
                                endif;
                                ?>
                            </ul>
                        </div>
                    </div>
                    <?php
                         }
                    } ?>
                    <?php
                        $post_terms = get_the_terms( $post->ID, 'resource-subject-area' );
                        if (!empty($post_terms)) {
                    ?>
                    <div class="tc-lp-subject-areas">
                       <h4 class="tc-lp-field-heading clearfix">
                            <?php _e("Subjects",OER_LESSON_PLAN_SLUG); ?>
                        </h4>
                       <div class="tc-lp-subject-details clearfix">
                            <ul class="tc-lp-subject-areas-list">
                                <?php
                                $i = 1;
                                $cnt = count($post_terms);
                                $moreCnt = $cnt - 2;
                                foreach($post_terms as $term){
                                    $subject_parent = get_term_parents_list($term->term_id,'resource-subject-area', array('separator' => ' <i class="fas fa-angle-double-right"></i> ', 'inclusive' => false));
                                    $subject = $subject_parent . '<a href="'.get_term_link($term->term_id).'">'.$term->name.'</a>';
                                    if ($i>2)
                                        echo '<li class="collapse lp-subject-hidden">'.$subject.'</li>';
                                    else
                                        echo '<li>'.$subject.'</li>';
                                    if (($i==2) && ($cnt>2))
                                        echo '<li><a class="see-more-subjects" data-toggle="collapse" data-count="'.$moreCnt.'" href=".lp-subject-hidden">SEE '.$moreCnt.' MORE +</a></li>';
                                    $i++;
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                    <?php } ?>
                    <?php if ($oer_sensitive) : ?>
                    <div class="tc-sensitive-material-section">
                        <p><i class="fal fa-exclamation-triangle"></i><span class="sensitive-material-text">Sensitive Material</span></p>
                        <button class="question-popup-button"><i class="fal fa-question-circle"></i></button>
                    </div>
                    <?php endif; ?>
                    <div id="tcHiddenFields" class="tc-hidden-fields collapse">
                        <?php
                        // Grade Level Display
                        $oer_lp_grade = oer_inquiry_set_grade_level($post->ID);
                        if (!empty($oer_lp_grade)){
                            ?>
                            <div class="form-field">
                                <span class="tc-lp-label">Grade Level:</span> <span class="tc-lp-value"><?php echo $oer_lp_grade; ?></span>
                            </div>
                            <?php
                        }
                        
                        // Appropriate Age Levels Display
                        if (($age_levels_set && $age_levels_enabled) || !$age_levels_set) {
                            $age_label = oer_lp_get_field_label('oer_lp_age_levels');
                            $age_levels = (isset($post_meta_data['oer_lp_age_levels'][0]) ? $post_meta_data['oer_lp_age_levels'][0] : "");
                            if (!empty($age_levels)){
                            ?>
                            <div class="form-field">
                                <span class="tc-lp-label"><?php echo $age_label; ?>:</span> <span class="tc-lp-value"><?php echo $age_levels; ?></span>
                            </div>
                            <?php
                            }
                        }
                        
                        // Suggested Instructional Time Display
                       if (($suggested_time_set && $suggested_time_enabled) || !$suggested_time_set) {
                            $suggested_label = oer_lp_get_field_label('oer_lp_suggested_instructional_time');
                            $suggested_time = (isset($post_meta_data['oer_lp_suggested_instructional_time'][0]) ? $post_meta_data['oer_lp_suggested_instructional_time'][0] : "");
                            if (!empty($suggested_time)){
                            ?>
                            <div class="form-field">
                                <span class="tc-lp-label"><?php echo $suggested_label; ?>:</span> <span class="tc-lp-value"><?php echo $suggested_time; ?></span>
                            </div>
                            <?php
                            }
                        }
                        
                         // Required Equipment Materials Display
                       if (($req_materials_set && $req_materials_enabled) || !$req_materials_set) {
                            $req_materials_label = oer_lp_get_field_label('oer_lp_required_materials');
                            $req_materials = (isset($post_meta_data['oer_lp_required_materials'][0]) ? $post_meta_data['oer_lp_required_materials'][0] : "");
                            if (!empty($req_materials)){
                            ?>
                            <div class="form-field">
                                <span class="tc-lp-label"><?php echo $req_materials_label; ?>:</span> <span class="tc-lp-value"><?php echo $req_materials; ?></span>
                            </div>
                            <?php
                            }
                        }
                        ?>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-12 featured-image padding-right-0">
                <?php the_post_thumbnail('inquiry-set-featured'); ?>
                <?php if (($author_set && $author_enabled) || !$author_set) { ?>
                <div class="tc-lp-authors-list">
                    <?php
                    $author_display = false;
                    foreach($authors as $author){
                        if (!empty($author[0])){
                            $author_display = true;
                            break;
                        }
                    }
                    if ($author_display){
                        ?>
                         <span class="lp-author-label"><?php echo oer_lp_get_field_label('oer_lp_authors'); ?></span>
                        <?php 
                        $aIndex = 0;
                        
                        foreach($authors['name'] as $author){
                            $author_url = $authors['author_url'][$aIndex];
                            if ($aIndex>0)
                                echo ", ";
                            if (isset($author_url))
                                echo "<span class='tc-lp-author'><a href='".$author_url."'>".$authors['name'][$aIndex]."</a></span>";
                            else
                                echo "<span class='tc-lp-author'>".$authors['name'][$aIndex]."</span>";
                                
                            $aIndex++;
                        }
                    } 
                    ?>
                </div>
                <?php } 
                $keywords = wp_get_post_tags($post->ID);
                if(!empty($keywords))
                {
                ?>
                <div class="tc-lp-keywords">
                    <div class="lp_keywords_container tagcloud">
                    <?php
                        foreach($keywords as $keyword)
                        {
                                echo "<span><a href='".esc_url(get_tag_link($keyword->term_id))."' class='button'>".ucwords($keyword->name)."</a></span>";
                        }
                    ?>
                    </div>
                </div>
                <?php } ?>
                <div class="tc-lp-controls">
                    <div class="sharethis-inline-share-buttons"></div>
                    <?php if ($oer_lp_download_copy_document): ?>
                    <a href="<?php echo $oer_lp_download_copy_document; ?>" target="_blank"><i class="fal fa-download"></i></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="see-more-row">
        <p class="center"><span><a id="see-more-link" class="see-more-link" role="button" data-toggle="collapse" href="#tcHiddenFields" aria-expanded="false" aria-controls="tcHiddenFields"><?php _e("SEE MORE +",OER_LESSON_PLAN_SLUG); ?></a></span></p>
    </div>
    <div class="row lp-primary-sources-row">
        <?php
        $primary_resources = (isset($post_meta_data['oer_lp_primary_resources'][0]) ? unserialize($post_meta_data['oer_lp_primary_resources'][0]) : array());
        if (!empty($primary_resources) && lp_scan_array($primary_resources)) {
            if (!empty(array_filter($primary_resources['resource']))) {
                foreach ($primary_resources['resource'] as $resourceKey => $resource) {
                    $resource = get_page_by_title($resource,OBJECT,"resource");
                    $url = get_post_meta($resource->ID, "oer_resourceurl", true);
                    $type = get_post_meta($resource->ID,"oer_mediatype")[0];
                    $resource_img = ''; 
                    $_hasimage = has_post_thumbnail($resource);
                    if($_hasimage) $resource_img = wp_get_attachment_image_url( get_post_thumbnail_id($resource), 'resource-thumbnail' );
                    $oer_authorname = get_post_meta($resource->ID, "oer_authorname", true);	
                    $oer_authorurl = get_post_meta($resource->ID, "oer_authorurl", true);	
                    $oer_authorname2 = get_post_meta($resource->ID, "oer_authorname2", true);	
                    $oer_authorurl2 = get_post_meta($resource->ID, "oer_authorurl2", true);
                    $sensitiveMaterial = (isset($primary_resources['sensitive_material'][$resourceKey]) ? $primary_resources['sensitive_material'][$resourceKey]: "");
                    $sensitiveMaterialValue = (isset($primary_resources['sensitive_material_value'][$resourceKey]) ? $primary_resources['sensitive_material_value'][$resourceKey]: "");
                    $title = (isset($primary_resources['title'][$resourceKey]) ? $primary_resources['title'][$resourceKey]: "");
                    
                    if ($sensitiveMaterialValue!=="")
                        $sensitiveMaterial = $sensitiveMaterialValue;
                ?>
                <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 padding-0">
                    <div class="media-image">
                        <div class="image-thumbnail">
                            <?php $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/source/".sanitize_title($resource->post_title)."-".$resource->ID); ?>
                            <a href="<?php echo $ps_url;  ?>">
                                <?php if($resource_img==''): $_avtr = getResourceIcon($type,$url); ?>	
                                  <div class="resource-avatar"><span class="dashicons <?php echo $_avtr; ?>"></span></div>	
                                <?php endif; ?>
                                <span class="resource-overlay"></span>
                                <?php if (!empty($type)): ?>
                                <span class="lp-source-type"><?php echo ucwords($type); ?></span>
                                <?php endif; ?>
                                <div class="resource-thumbnail" style="background: url('<?php echo $resource_img ?>') no-repeat center rgba(204,97,12,.1); background-size:cover;">
                                </div>
                            </a>
                            
                        </div>
                        <div class="lp-resource-title">
                          <b><?php echo $resource->post_title; ?></b>
                        </div>	
                        <div class="lp-resource-author">	
                          <?php if( $oer_authorname != ''):?>	
                            <div class="lp-resource-author_block"><a href="<?php echo $oer_authorurl; ?>" target="_new"><?php echo $oer_authorname; ?></a></div>	
                          <?php endif; ?>	
                          <?php /* if( $oer_authorname2 != ''):?>	
                            <div class="lp-resource-author_block"><a href=""><?php echo $oer_authorname2; ?></a></div>	
                          <?php endif;*/ ?>
                        </div>
                        <div class="lp-resource-excerpt"><?php echo oer_get_related_resource_content($resource->post_content, 75); ?></div>
                    </div>
                    <?php if ($sensitiveMaterial!=="" && $sensitiveMaterial!=="no"): ?>
                    <div class="sensitive-source">
                        <p><i class="fal fa-exclamation-triangle"></i></p>
                    </div>
                    <?php endif; ?>
                </div>
                <?php
                }
            }
        }
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
            foreach($elements_orders as $elementKey=>$order){
                if (!in_array($elementKey,$keys)){
                    if (strpos($elementKey, 'oer_lp_custom_editor_historical_background') !== false) {
                        $oer_lp_custom_editor = (isset($post_meta_data[$elementKey][0]) ? unserialize($post_meta_data[$elementKey][0]) : "");
                        if(!empty($oer_lp_custom_editor)) {
                            
                        ?>
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 padding-0">
                            <div class="media-image">
                                <div class="image-thumbnail">
                                    <?php  $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/module/".sanitize_title($oer_lp_custom_editor['title'])); ?>
                                    <a href="<?php echo $ps_url;  ?>">
                                        <div class="resource-avatar"><span class="dashicons dashicons-media-text"></span></div>
                                        <span class="resource-overlay"></span>
                                        <?php if (!empty($type)): ?>
                                        <span class="lp-source-type"><?php _e("Text", OER_LESSON_PLAN_SLUG); ?></span>
                                        <?php endif; ?>
                                        <div class="resource-thumbnail text-thumbnail"></div>
                                    </a>
                                </div>
                                <div class="lp-resource-title">
                                    <?php echo $oer_lp_custom_editor['title']; ?>
                                </div>
                            </div>
                        </div>
                        <?php
                        }
                    } elseif (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'oer_lp_custom_editor_') !== false) {
                        $oer_lp_custom_editor = (isset($post_meta_data[$elementKey][0]) ? unserialize($post_meta_data[$elementKey][0]) : "");
                        if(!empty($oer_lp_custom_editor)) {
                        ?>
                        <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 padding-0">
                            <div class="media-image">
                                <div class="image-thumbnail">
                                    <?php  $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/module/".sanitize_title($oer_lp_custom_editor['title'])); ?>
                                    <a href="<?php echo $ps_url;  ?>">
                                        <div class="resource-avatar"><span class="dashicons dashicons-media-text"></span></div>
                                        <span class="resource-overlay"></span>
                                        <?php if (!empty($type)): ?>
                                        <span class="lp-source-type"><?php _e("Text", OER_LESSON_PLAN_SLUG); ?></span>
                                        <?php endif; ?>
                                        <div class="resource-thumbnail text-thumbnail"></div>
                                    </a>
                                </div>
                                <div class="lp-resource-title">
                                    <?php echo $oer_lp_custom_editor['title']; ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    <?php } elseif (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'oer_lp_custom_text_list_') !== false) {
                    ?>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 padding-0">
                        <div class="media-image">
                            <div class="image-thumbnail">
                                <?php  $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/module/".sanitize_title("Text List")); ?>
                                <a href="<?php echo $ps_url;  ?>">
                                    <div class="resource-avatar"><span class="dashicons dashicons-media-text"></span></div>
                                    <span class="resource-overlay"></span>
                                    <?php if (!empty($type)): ?>
                                    <span class="lp-source-type"><?php _e("Text", OER_LESSON_PLAN_SLUG); ?></span>
                                    <?php endif; ?>
                                    <div class="resource-thumbnail text-thumbnail"></div>
                                </a>
                            </div>
                            <div class="lp-resource-title">
                                <?php _e("Text List", OER_LESSON_PLAN_SLUG); ?>
                            </div>
                        </div>
                    </div>
                    <?php } elseif (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'oer_lp_vocabulary_list_title_') !== false) {
                        $oer_lp_vocabulary_list_title = (isset($post_meta_data[$elementKey][0]) ? $post_meta_data[$elementKey][0] : "");
                        $oer_keys = explode('_', $elementKey); 
                        $listOrder = end($oer_keys);
                        $oer_lp_vocabulary_details = (isset($post_meta_data['oer_lp_vocabulary_details_'.$listOrder][0]) ? $post_meta_data['oer_lp_vocabulary_details_'.$listOrder][0] : "");
                        if (!empty($oer_lp_vocabulary_list_title)) { ?>
                        <div class="col-md-3 col-sm-3 padding-0">
                            <div class="media-image">
                                <div class="image-thumbnail">
                                    <?php  $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/module/".sanitize_title($oer_lp_vocabulary_list_title)); ?>
                                    <a href="<?php echo $ps_url;  ?>">
                                        <div class="resource-avatar"><span class="dashicons dashicons-media-text"></span></div>
                                        <span class="resource-overlay"></span>
                                        <?php if (!empty($type)): ?>
                                        <span class="lp-source-type"><?php _e("Text", OER_LESSON_PLAN_SLUG); ?></span>
                                        <?php endif; ?>
                                        <div class="resource-thumbnail text-thumbnail"></div>
                                    </a>
                                </div>
                                <div class="lp-resource-title">
                                    <?php echo $oer_lp_vocabulary_list_title; ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    <?php } elseif (isset($post_meta_data[$elementKey]) && strpos($elementKey, 'lp_oer_materials_list_') !== false) {?>
                    <div class="col-xs-12 col-sm-6 col-md-4 col-lg-3 padding-0">
                        <div class="media-image">
                            <div class="image-thumbnail">
                                <?php  $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/module/".sanitize_title($oer_lp_vocabulary_list_title)); ?>
                                <a href="<?php echo $ps_url;  ?>">
                                    <div class="resource-avatar"><span class="dashicons dashicons-media-text"></span></div>
                                    <span class="resource-overlay"></span>
                                    <?php if (!empty($type)): ?>
                                    <span class="lp-source-type"><?php _e("Text", OER_LESSON_PLAN_SLUG); ?></span>
                                    <?php endif; ?>
                                    <div class="resource-thumbnail text-thumbnail"></div>
                                </a>
                            </div>
                            <div class="lp-resource-title">
                                <?php _e("Materials", OER_LESSON_PLAN_SLUG); ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    }
                }
            }
        }
        ?>
    </div>
</div>
<?php
	// Display Activity Objects
 	endwhile; 
endif; 
get_footer();
