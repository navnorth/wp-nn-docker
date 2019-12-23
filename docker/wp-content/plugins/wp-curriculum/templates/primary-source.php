<?php

add_filter('body_class', function($classes){
    $classes[] = 'primary-source-template';
    return $classes;
});

get_header();

$back_url = "";
$source_id = 0;
$lp_prev_class = "";
$lp_next_class = "";
$prev_url = "";
$next_url = "";

// Back Button URL
$curriculum = get_query_var('curriculum');
$curriculum_details = get_page_by_path($curriculum, OBJECT, "lesson-plans");
$curriculum_id = $curriculum_details->ID;
if ($curriculum)
    $back_url = site_url("inquiry-sets/".$curriculum);

// Get Resource ID
$psource = get_query_var('source');
$sources = explode("-",$psource);
if ($sources)
    $source_id = $sources[count($sources)-1];

$resource = get_post($source_id);

// Get Featured Image Url
$featured_image_url = get_the_post_thumbnail_url($resource->ID, "full");
$resource_url = get_post_meta($resource->ID, "oer_resourceurl", true);
$oer_resource_url = get_the_permalink($resource->ID);
$youtube = oer_is_youtube_url($resource_url);
$isPDF = is_pdf_resource($resource_url);

// Get Curriculum Meta for Primary Sources
$post_meta_data = get_post_meta($curriculum_id);
$primary_resources = (isset($post_meta_data['oer_lp_primary_resources'][0]) ? unserialize($post_meta_data['oer_lp_primary_resources'][0]) : array());
$index = 0;
$teacher_info = "";
$student_info = "";
$embed = "";
$prev_url = null;
$next_url = null;
$right_class = "col-md-12";
$new_title = "";
$new_description = "";
$prev_title = "";
$next_title = "";
if (!empty($primary_resources) && lp_scan_array($primary_resources)) {
    if (!empty(array_filter($primary_resources['resource']))) {
        foreach ($primary_resources['resource'] as $resourceKey => $source) {
            if ($source==$resource->post_title){
                $new_title = (isset($primary_resources['title'][$resourceKey]) ? $primary_resources['title'][$resourceKey]: "");
                $new_description = (isset($primary_resources['description'][$resourceKey]) ? $primary_resources['description'][$resourceKey]: "");
                break;
            }
            $index++;
        }
        if (isset($primary_resources['resource'][$index-1])){
            $prev_resource = oer_lp_get_resource_details($primary_resources['resource'][$index-1]);
            $prev_title = (isset($primary_resources['title'][$index-1]) ? $primary_resources['title'][$index-1]: "");
            $prev_url = $back_url."/source/".sanitize_title($prev_resource->post_title)."-".$prev_resource->ID;
        }
        if (isset($primary_resources['resource'][$index+1])){
            $next_resource = oer_lp_get_resource_details($primary_resources['resource'][$index+1]);
            $next_title = (isset($primary_resources['title'][$index+1]) ? $primary_resources['title'][$index+1]: "");
            $next_url = $back_url."/source/".sanitize_title($next_resource->post_title)."-".$next_resource->ID;
        }
        if ($index==0)
            $lp_prev_class = "ps-nav-hidden";
        if ($index==count($primary_resources['resource'])-1)
            $lp_next_class = "ps-nav-hidden";
        if (isset($primary_resources['teacher_info']))
            $teacher_info = $primary_resources['teacher_info'][$index];
        if (isset($primary_resources['student_info']))
            $student_info = $primary_resources['student_info'][$index];
    }
}
if ($youtube || $isPDF)
    $featured_image_url = "";
if (function_exists('oer_get_resource_metadata')){
    $resource_meta = oer_get_resource_metadata($resource->ID);
}
if (empty($next_resource)){
    $modules = oer_lp_modules($post->ID);
    if (isset($modules[0])){
        $lp_next_class = "";
        $next_resource = $modules[0];
        $next_url = $back_url."/module/".sanitize_title($next_resource['title']);
    }
}
$type = get_post_meta($resource->ID,"oer_mediatype");
$type = $type[0];
?>
<?php echo oer_breadcrumb_display($resource); ?>
<div class="lp-nav-block"><a class="back-button" href="<?php echo $back_url; ?>"><i class="fas fa-arrow-left"></i><?php echo $curriculum_details->post_title; ?></a></div>
<div class="row ps-details-row">
    <?php if (!empty($featured_image_url) || $youtube || $isPDF) {
        $right_class = "col-md-8";
    ?>
    <div class="ps-media-image col-md-4 col-sm-12" data-curid="<?php echo $index; ?>">
        <?php if ($youtube): ?>
        <div class="ps-youtube-video">
            <?php
                echo '<div class="youtubeVideoWrapper">';
                if (function_exists('oer_generate_youtube_embed_code'))
                    $embed = oer_generate_youtube_embed_code($resource_url);
                echo $embed;
                echo '</div>';
            ?>
        </div>
        <?php elseif ($isPDF): ?>
        <div class="ps-pdf-block">
            <?php
                echo '<div class="psPDFWrapper">';
                if (function_exists('oer_display_pdf_embeds'))
                    oer_display_pdf_embeds($resource_url);
                echo '</div>';
            ?>
        </div>
        <?php else: ?>
        <div class="ps-image-block">
           <img src="<?php echo $featured_image_url; ?>" alt="<?php echo $resource->post_title; ?>" />
        </div>
        <?php if ($type=="website"): ?>
        <span class="ps-expand"><a href="<?php echo $resource_url; ?>" class="lp-expand-img" target="_blank"><i class="fas fa-external-link-alt"></i></a></span>
        <?php endif; ?>
        <?php endif; ?>
        <div class="lp-center">
            <?php if (isset($oer_resource_url)) { ?>
            <div class="ps-meta-group ps-resource-url">
                <a href="<?php echo $oer_resource_url; ?>" class="tc-view-button" target="_blank"><?php _e("View Original", OER_LESSON_PLAN_SLUG); ?></a>
            </div>
            <?php } ?>
        </div>
    </div>
    <?php
    }
    $resource_meta = null;
    $subject_areas = null;
    ?>
    <div class="ps-details <?php echo $right_class; ?> col-sm-12">
        <div class="ps-info">
            <h1 class="ps-info-title"><?php
            if (!empty($new_title))
                echo $new_title;
            else
                echo $resource->post_title;
            ?></h1>
            <div class="ps-info-description">
                <?php
                if (!empty($new_description))
                    echo $new_description;
                else
                    echo $resource->post_content;
                ?>
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
                    <?php if ($resource_img!=""):
                    $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/source/".sanitize_title($prev_resource->post_title)."-".$prev_resource->ID);
                    ?>
                    <div class="resource-thumbnail" style="background: url('<?php echo $resource_img ?>') no-repeat center rgba(204,97,12,.1); background-size:cover;"></div>
                    <?php else: ?>
                    <?php
                     $prev_resource_url = get_post_meta($prev_resource->ID, "oer_resourceurl", true);
                     $prev_resource_type = get_post_meta($prev_resource->ID,"oer_mediatype")[0];
                    ?>
                    <div class="navigation-avatar"><span class="dashicons <?php echo getResourceIcon($prev_resource_type,$prev_resource_url); ?>"></span></div>
                    <?php endif; ?>
                </span>
                <span class="nav-lp-resource-title col-md-8">
                    <?php
                    if (!empty($prev_title))
                        echo $prev_title;
                    else
                        echo $prev_resource->post_title;
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
                      if (is_object($next_resource))
                          $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/source/".sanitize_title($next_resource->post_title)."-".$next_resource->ID);
                      else
                          $ps_url = site_url("inquiry-sets/".sanitize_title($post->post_name)."/module/".sanitize_title($next_resource['title']));
                      ?>
                      <div class="resource-thumbnail" style="background: url('<?php echo $resource_img ?>') no-repeat center rgba(204,97,12,.1); background-size:cover;"></div>
                    <?php else: ?>
                      <?php
                       $next_resource_url = get_post_meta($next_resource->ID, "oer_resourceurl", true);
                       $next_resource_type = get_post_meta($next_resource->ID,"oer_mediatype")[0];
                      ?>
                      <div class="navigation-avatar"><span class="dashicons <?php echo getResourceIcon($next_resource_type,$next_resource_url); ?>"></span></div>
                    <?php endif; ?>
                </span>
                <span class="nav-lp-resource-title col-md-8">
                    <?php
                     if (is_object($next_resource)){
                        if (!empty($next_title))
                            echo $next_title;
                        else
                            echo $next_resource->post_title;
                     }
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