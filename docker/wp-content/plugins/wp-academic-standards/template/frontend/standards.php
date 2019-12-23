<?php
/*
 * Template Name: Main Standards Page Template
 */
add_filter( 'body_class','standards_body_classes' );
function standards_body_classes( $classes ) {
 
    $classes[] = 'oer-standards';
     
    return $classes;
     
}

get_header();

$std_count = was_core_standards_count();
$standards = was_core_standards();

$root_slug = get_option('was_standard_slug');
if (!isset($root_slug) || $root_slug==""){
    $root_slug ="standards";
}
?>
<div class="oer-cntnr">
	<section id="primary" class="site-content">
		<div id="content" class="standards-display" role="main">
		    <div class="oer-allftrdrsrc">
			<div class="oer-snglrsrchdng"><h1><?php printf(__("%d Academic Standards", WAS_SLUG), $std_count); ?></h1></div>
			<div class="oer-allftrdrsrccntr">
			    <?php if ($standards) {  ?>
			    <ul class="oer-standards">
				<?php foreach($standards as $standard) {
				    $cnt = was_resource_count_by_standard($standard->id);
				    $slug = $root_slug."/".sanitize_title($standard->standard_name);
				?>
				<li><a href="<?php echo home_url($slug); ?>"><i class="fa fa-plus"></i> <?php echo $standard->standard_name; ?></a> <span class="res-count"><?php echo $cnt; ?></span></li>
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