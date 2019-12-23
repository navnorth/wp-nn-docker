<?php
/*
 * Template Name: Substandard Page Template
 */
add_filter( 'body_class','standards_body_classes' );
function standards_body_classes( $classes ) {
 
    $classes[] = 'substandards-template';
     
    return $classes;
     
}

get_header();

global $wp_query;
$end_html = "";
$output_html = "";

$parent_slug = $wp_query->query_vars['standard'];
$standard_name_slug = $wp_query->query_vars['substandard'];
$standard = was_substandard_by_slug($standard_name_slug);

$parent_id = 0;
if (strpos($standard->parent_id,"core_standards")!==false){
    $pIds = explode("-",$standard->parent_id);
    if (count($pIds)>1)
	$parent_id=(int)$pIds[1];
    
    $core_standard = was_standard_by_id($parent_id);
} else {
    $core_standard = was_corestandard_by_standard($standard->parent_id);
}

$parent_substandards = was_hierarchical_substandards($standard->parent_id);
$sub_standards = was_substandards($standard->id, false);
$notations = was_standard_notations($standard->id);

$root_slug = get_option('was_standard_slug');
if (!isset($root_slug) || $root_slug==""){
    $root_slug ="standards";
}
?>
<div class="oer-backlink">
    <a class="backlink-btn" href="<?php echo home_url($root_slug.'/'.$parent_slug); ?>"><?php _e("<i class='fa fa-angle-double-left'></i> Back",WAS_SLUG); ?></a>
</div>
<div class="oer-cntnr">
	<section id="primary" class="site-content">
		<div id="content" class="standards-display" role="main">
		    <div class="oer-allftrdrsrc">
			<div class="oer-snglrsrchdng"><h2><?php printf(__("%s", WAS_SLUG), '<a href="'.home_url($root_slug."/".sanitize_title($core_standard->standard_name)).'">'.$core_standard->standard_name.'</a>'); ?></h2></div>
			<div class="oer-allftrdrsrccntr">
			    <ul class="oer-standard">
				<?php if ($parent_substandards) { 
				    echo "<li>";
				    
				    $cnt = count($parent_substandards);
				    $index = 1;
				    
				    foreach($parent_substandards as $parent_substandard){
					
					$slug = $root_slug."/".sanitize_title($core_standard->standard_name)."/".sanitize_title($parent_substandard['standard_title']);
					
					if ($cnt>1) { ?>
					    <ul class="oer-substandards">
						<li>
						    <a href="<?php echo home_url($slug); ?>"><i class="fa fa-plus"></i> <?php echo $parent_substandard['standard_title']; ?></a>
						</li>
					<?php
					    $end_html .= '</ul>';
					} else { ?>
					    <li>
						<a href="<?php echo home_url($slug); ?>"><?php echo $parent_substandard['standard_title']; ?></a>
					    </li>
					<?php
					}
					$index++;
				    } 
				    $end_html .= '</li>';
				}
				?>
				<li><?php if ($parent_substandards) { ?>
					<ul class="oer-hsubstandards">
					    <li><?php echo $standard->standard_title; ?></li>
					
					<?php
					$output_html .= '</ul>';
					$output_html .= '</li>';
				    } else
					echo '<i class="fa fa-minus"></i> '.$standard->standard_title;
				    
				    if ($sub_standards) {  ?>
					<ul class="oer-substandards">
					    <?php foreach($sub_standards as $sub_standard) {
						 $cnt = was_resource_count_by_substandard($sub_standard->id);
						$slug = $root_slug."/".sanitize_title($core_standard->standard_name)."/".sanitize_title($sub_standard->standard_title);
					    ?>
					    <li><a href="<?php echo home_url($slug); ?>"><i class="fa fa-plus"></i> <?php echo $sub_standard->standard_title; ?></a> <span class="res-count"><?php echo $cnt; ?></span></li>
					    <?php } ?>
					</ul>
				    <?php }
				    if ($notations) {  ?>
					<ul class="oer-notations">
					    <?php foreach($notations as $notation) {
						$cnt = was_resource_count_by_notation($notation->id);
						$slug = $root_slug."/".sanitize_title($core_standard->standard_name)."/".$standard_name_slug."/".$notation->standard_notation;
					    ?>
					    <li><a href="<?php echo home_url($slug); ?>"><strong><?php echo $notation->standard_notation; ?></strong> <?php echo $notation->description; ?></a> <span class="res-count"><?php echo $cnt; ?></span></li>
					    <?php } ?>
					</ul>
				    <?php } 
				    if ($output_html)
					echo $output_html;
				    ?>
				</li>
				<?php
				if ($end_html)
				    echo $end_html;
				?>
			    </ul>
			</div>
		    </div>
		</div><!-- #content -->
	</section><!-- #primary -->
</div>
<?php
get_footer();
?>