<?php
/*
 * Template Name: Notation Page Template
 */
add_filter( 'body_class','standards_body_classes' );
function standards_body_classes( $classes ) {
 
    $classes[] = 'notation-template';
     
    return $classes;
     
}

get_header();

global $wp_query;
$upnotations = null;
$upstandards = null;
$end_upnote = "";
$end_html = "";

$standard_slug = $wp_query->query_vars['standard'];
$parent_slug = $wp_query->query_vars['substandard'];
$notation_slug = $wp_query->query_vars['notation'];
$notation = was_substandard_by_notation($notation_slug);

if (strpos($notation->parent_id,"standard_notation")!==false){
    $upnotations = was_hierarchical_notations($notation->parent_id);
}

if ($upnotations){
    foreach($upnotations as $upnotation) {
	if (strpos($upnotation['parent_id'],"sub_standards")!==false){
	    $upstandards = was_hierarchical_substandards($upnotation['parent_id']);
	    $upstandards = array_reverse($upstandards);
	}
    }
} else {
    if (strpos($notation->parent_id,"sub_standards")!==false){
	$upstandards = was_hierarchical_substandards($notation->parent_id);
	$upstandards = array_reverse($upstandards);
    }
}

$subnotations = was_child_notations($notation->id);
$substandards = was_substandards_by_notation($notation_slug);
$standard = was_standard_by_notation($notation_slug);
$resources = was_resources_by_notation($notation->id);

$root_slug = get_option('was_standard_slug');
if (!isset($root_slug) || $root_slug==""){
    $root_slug ="standards";
}
?>
<div class="oer-backlink">
    <a class="backlink-btn" href="<?php echo home_url($root_slug.'/'.$standard_slug.'/'.$parent_slug); ?>"><?php _e("<i class='fa fa-angle-double-left'></i> Back", WAS_SLUG); ?></a>
</div>
<div class="oer-cntnr">
	<section id="primary" class="site-content">
		<div id="content" class="standards-display" role="main">
		    <div class="oer-allftrdrsrc">
			<div class="oer-snglrsrchdng"><h2><?php printf(__("%s", WAS_SLUG), '<a href="'.home_url($root_slug."/".sanitize_title($standard->standard_name)).'">'.$standard->standard_name.'</a>'); ?></h2></div>
			<div class="oer-allftrdrsrccntr-notation">
			    <ul class="oer-standard">
			    <?php  if ($upstandards){
				foreach($upstandards as $upstandard) {
				    $slug = $root_slug."/".sanitize_title($standard->standard_name)."/".sanitize_title($upstandard['standard_title']);
				?>
				<li>
				    <ul class="oer-hsubstandards">
					<li><a href="<?php echo home_url($slug); ?>"><?php echo $upstandard['standard_title']; ?></a></li>
				<?php
				$end_html .= '</ul>
					</li>';
				}
			    }
			    if ($upnotations) {
				foreach($upnotations as $upnotation){
				    $upnote_slug = $upnotation['standard_notation'];
				    ?>
				    <li class="upnotation">
					<ul class="oer-notations">
					    <li><a href="<?php echo $upnote_slug; ?>"><strong><?php echo $upnotation['standard_notation']; ?></strong> <?php echo $upnotation['description']; ?></a></li>
					
				    <?php
				    $end_upnote .= '</ul>
				    </li>';
				}
			    }
			    if ($notation) {  ?>
				<li>
				    <ul class="oer-notations">
					<li>
					    <h4><strong><i class="fa fa-minus"></i> <?php echo $notation->standard_notation; ?></strong> <?php echo $notation->description; ?></h4>
					</li>
					<?php if (!empty($subnotations)) { ?>
					<li>
					    <ul class="oer-subnotations">
						<?php
						foreach($subnotations as $subnotation) {
						    $cnt = was_resource_count_by_notation($subnotation->id);
						    $subnote_slug = $subnotation->standard_notation;
						?>
						<li>
						    <a href="<?php echo $subnote_slug; ?>"><strong><?php echo $subnotation->standard_notation; ?></strong> <?php echo $subnotation->description; ?></a>  <span class="res-count"><?php echo $cnt; ?></span>
						</li>
						<?php } ?>
					    </ul>
					</li>    
					<?php } ?>
				    </ul>
				</li>
			    <?php }
			    if ($end_html)
				echo $end_html;
			    if ($end_upnote)
				echo $end_upnote;
			    ?>
			    </ul>
			</div>
			<div class="oer_standard_resources">
			    <?php if ($resources) { ?>
				<h4><?php _e("Resources:", WAS_SLUG); ?></h4>
				<ul class="oer-resources">
				    <?php foreach($resources as $resource) { ?>
				    <li><a href="<?php echo get_the_permalink($resource->ID); ?>"><?php echo $resource->post_title; ?></a></li>
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