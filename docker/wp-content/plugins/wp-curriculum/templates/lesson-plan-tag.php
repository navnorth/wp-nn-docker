<?php

$tag = get_query_var('topic');
$term = get_term_by("slug",$tag);
add_filter('body_class', function($classes){
    $classes[] = 'topic-template';
    return $classes;
});

get_header();

$front_page_id = get_option( 'page_on_front' );
$tag_name = ucwords(oer_lp_title_from_slug($tag));
$args = array(
    "showposts" => -1,
    "post_type" => "lesson-plans",
    "tax_query" => array(
        array(
            "taxonomy" => "post_tag",
            "field" => "name",
            "terms" => array($tag_name)
        )
    ),
    "orderby" => "title",
    "order" => "ASC"
    );

$inquiry_sets = get_posts( $args );
?>

<div id="main">
    <div class="inquiry-set-header-wrapper">
        <h4 class="inquiry-sets-by-tag-header"><?php _e("Inquiry Sets", OER_LESSON_PLAN_SLUG); ?>: <span id="tag-name"><?php echo $tag_name; ?></span></h4>
    </div>
    <div id="tc-topics">
        <div class="tc-topic-wrapper">
            <?php if (is_array($inquiry_sets) && count($inquiry_sets)>0) {
                foreach($inquiry_sets as $inquiry_set){
                    $link = get_permalink($inquiry_set->ID);
                    $default_thumbnail_url = get_template_directory_uri()."/images/default-image.png";
                    $thumbnail_url = get_the_post_thumbnail_url($inquiry_set->ID);
                    if (!$thumbnail_url)
                        $thumbnail_url = $default_thumbnail_url;
                        
                    $grade_level = oer_inquiry_set_grade_level($inquiry_set->ID);
            ?>
            <div class="col-md-4 col-sm-6 padding-0">
            <a href="<?php echo $link; ?>" class="wp-block-wp-curriculum-inquiry-set-thumbnail-block lp-tc-related-inquiry-block-link">
                <div class="lp-tc-related-inquiry-blocks-padding">
                    <div class="media-image">
                        <div class="image-thumbnail">
                            <div class="image-section">
                                <img src="<?php echo $thumbnail_url; ?>" alt="" class="img-thumbnail-square img-responsive img-loaded">
                            </div>
                        </div>
                    </div>
                    <div class="lp-tc-related-inquiry-grades">
                        <span><?php echo $grade_level; ?></span>
                    </div>
                    <div class="lp-tc-related-inquiry-set-description">
                        <h4><?php echo $inquiry_set->post_title; ?></h4>
                    </div>
                </div>
            </a>
            </div>
            <?php }
            } ?>
        </div>
    </div>
</div>

<?php
get_footer();
?>