<?php
/*
 * Template Name: Standard Page Template
 */
add_filter( 'body_class','standards_body_classes' );
function standards_body_classes( $classes ) {
 
    $classes[] = 'standards-template';
     
    return $classes;
     
}

get_header();

global $wp_query;
$standard_name_slug = $wp_query->query_vars['standard'];
$standard = was_standard_by_slug($standard_name_slug);
$sub_standards = was_substandards($standard->id);

$root_slug = get_option('was_standard_slug');
if (!isset($root_slug) || $root_slug==""){
    $root_slug ="standards";
}
?>
<div class="oer-backlink">
    <a class="backlink-btn" href="<?php echo home_url($root_slug); ?>"><?php _e("<i class='fa fa-angle-double-left'></i> Back to Standards",WAS_SLUG); ?></a>
</div>
<div class="oer-cntnr">
	<section id="primary" class="site-content">
		<div id="content" class="standards-display" role="main">
		    <div class="oer-allftrdrsrc">
			<div class="oer-snglrsrchdng"><h2><?php printf(__("%s", WAS_SLUG), $standard->standard_name); ?></h2></div>
			<div class="oer-allftrdrsrccntr">
			    <?php if ($sub_standards) {  ?>
			    <ul class="oer-standards">
				<?php foreach($sub_standards as $sub_standard) {
				    $cnt = was_resource_count_by_substandard($sub_standard->id);
				    $slug = $root_slug."/".$standard_name_slug."/".sanitize_title($sub_standard->standard_title);
				?>
				<li><a href="<?php echo home_url($slug); ?>"><i class="fa fa-plus"></i> <?php echo $sub_standard->standard_title; ?></a> <span class="res-count"><?php echo $cnt; ?></span></li>
				<?php } ?>
			    </ul>
			    <?php } ?>
			</div>
		    </div>
		</div><!-- #content -->
	</section><!-- #primary -->
</div>
<?php
get_footer();
?>