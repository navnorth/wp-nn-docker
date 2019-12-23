<?php
global $wpdb;

/** Check Child Standard Notation **/
if (!function_exists('check_child_standard')) {
    function check_child_standard($id)
    {
            global $wpdb;
            $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_standard_notation where parent_id = %s" , $id ) , ARRAY_A);
            return $results;
    }
}

/** Get Substandard Children **/
if (!function_exists('get_substandard_children')){
    function get_substandard_children($id)
    {
        global $wpdb;
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_sub_standards where parent_id = %s" , $id ) , ARRAY_A);
        return $results;
    }
}

// Get Title or Description of Standard or Notation
if (!function_exists('get_standard_label')) {
    function get_standard_label($slug){
        global $wpdb;

        $slugs = explode("-", $slug);
        $table_name = "oer_".$slugs[0];
        $id = $slugs[1];
        $standard = null;

        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. $table_name . " where id = %s" , $id ) , ARRAY_A);
        if (!empty($results)){
                foreach($results as $result) {
                        $standard = $result['description'];
                }
        }

        return $standard;
    }
}

/** Get Sub Standard **/
if (!function_exists('child_standards')){
    function child_standards($id, $display=false)
    {
        global $wpdb, $chck, $class;
        $collapse = " class='collapse'";
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_sub_standards where parent_id = %s ORDER by pos, id" , $id ) ,ARRAY_A);

        if(!empty($results))
        {
            if ($display==true)
                $collapse = "";

            echo "<div id='".$id."'".$collapse.">";
            echo "<ul>";
            $index = 1;

            foreach($results as $result)
            {
                $hiddenUp = "";
                $hiddenDown = "";
                $value = 'sub_standards-'.$result['id'];

                $id = 'sub_standards-'.$result['id'];
                $subchildren = get_substandard_children($id);
                $child = check_child_standard($id);

                if ($index==1){
                    $hiddenUp = "hidden-block";
                }
                if ($index == count($results)){
                    $hiddenDown = "hidden-block";
                }

                echo "<li class='was_sbstndard ". $class ."'>";
                echo "<input type='hidden' name='pos[]' class='std-pos' data-value='".$value."' data-count='".count($results)."' value='".$index."'>";
                if (!empty($subchildren)){
                    echo "<a data-toggle='collapse' data-target='#".$id.",#".$id."-1'>".stripslashes($result['standard_title'])."</a>";
                    echo '<span class="std-up std-icon '.$hiddenUp.'"><a href="#"><i class="fas fa-arrow-up"></i></a></span><span class="std-down std-icon '.$hiddenDown.'"><a href="#"><i class="fas fa-arrow-down"></i></a></span> <span class="std-edit"><a class="std-edit-icon" data-target="#editStandardModal" data-value="'.$id.'" data-stdid="'.$result['id'].'"><i class="far fa-edit"></i></a></span> <span class="std-add"><a data-target="#addStandardModal" class="std-add-icon" data-parent="'.$id.'"><i class="fas fa-plus"></i></a></span>';
                }

                if(empty($subchildren) && empty($child)) {
                    echo stripslashes($result['standard_title']);
                    echo '<span class="std-up std-icon '.$hiddenUp.'"><a href="#"><i class="fas fa-arrow-up"></i></a></span><span class="std-down std-icon '.$hiddenDown.'"><a href="#"><i class="fas fa-arrow-down"></i></a></span> <span class="std-edit"><a class="std-edit-icon" data-target="#editStandardModal" data-value="'.$id.'" data-stdid="'.$result['id'].'"><i class="far fa-edit"></i></a></span> <span class="std-add"><a data-target="#addStandardModal" class="std-add-icon" data-parent="'.$id.'"><i class="fas fa-plus"></i></a></span>';
		}

                $id = 'sub_standards-'.$result['id'];
                child_standards($id);

                if (empty($subchildren) && !empty($child)) {
                    echo "<a data-toggle='collapse' data-target='#".$id.",#".$id."-1'>".stripslashes($result['standard_title'])."</a>";
                    echo '<span class="std-up std-icon '.$hiddenUp.'"><a href="#"><i class="fas fa-arrow-up"></i></a></span><span class="std-down std-icon '.$hiddenDown.'"><a href="#"><i class="fas fa-arrow-down"></i></a></span> <span class="std-edit"><a class="std-edit-icon" data-target="#editStandardModal" data-value="'.$id.'" data-stdid="'.$result['id'].'"><i class="far fa-edit"></i></a></span> <span class="std-add"><a data-target="#addStandardModal" class="std-add-icon" data-parent="'.$id.'"><i class="fas fa-plus"></i></a></span>';
                    $sid = 'sub_standards-'.$result['id'];
                    child_standard_notations($sid);
                } elseif (!empty($subchildren) && !empty($child)) {
                    $sid = 'sub_standards-'.$result['id'];
                    child_standard_notations($sid, true);
                }
                echo "</li>";
                $index++;
            }
            echo "</ul>";
            echo "</div>";
        }
    }
}

/** Get Standard Notation **/
if (!function_exists('child_standard_notations')) {
    function child_standard_notations($id, $continue = false)
    {
        global $wpdb, $class;

        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_standard_notation where parent_id = %s ORDER by pos, id" , $id ) , ARRAY_A);

        if(!empty($results))
        {
            $class = "was_standard_notation";

            if ($continue)
                $id = $id."-1";
            echo "<div id='".$id."' class='collapse'>";
            echo "<ul>";
            $index = 1;
            foreach($results as $result)
            {
                $hiddenUp = "";
                $hiddenDown = "";
                $id = 'standard_notation-'.$result['id'];
                $child = check_child_standard($id);
                $value = 'standard_notation-'.$result['id'];

                if ($index==1){
                    $hiddenUp = "hidden-block";
                }
                if ($index == count($results)){
                    $hiddenDown = "hidden-block";
                }

                echo "<li class='".$class."' data-target='#".$id."'>";
                echo "<input type='hidden' name='pos[]' class='std-pos' data-value='".$value."' data-count='".count($results)."' value='".$index."'>";
                if(!empty($child))
                {
                    echo "<a data-toggle='collapse' data-target='#".$id."'>".stripslashes($result['standard_notation'])."</a>";
                } else {
                    echo "<span class='was_stndrd_prefix'><strong>".stripslashes($result['standard_notation'])."</strong></span>";
                }

                echo "<div class='was_stndrd_desc'> ". stripslashes($result['description']);
                echo "</div>";
                echo '<span class="std-up std-icon '.$hiddenUp.'"><a href="#"><i class="fas fa-arrow-up"></i></a></span><span class="std-down std-icon '.$hiddenDown.'"><a href="#"><i class="fas fa-arrow-down"></i></a></span> <span class="std-edit std-icon"><a data-target="#editStandardModal" data-value="'.$id.'" data-stdid="'.$result['id'].'"><i class="far fa-edit"></i></a></span> <span class="std-add std-icon"><a data-target="#addStandardModal" class="std-add-icon" data-parent="'.$id.'"><i class="fas fa-plus"></i></a></span><span class="std-del std-icon"><a class="std-del-icon" data-stdid="'.$result['id'].'" data-value="'.$id.'"><i class="far fa-trash-alt"></i></a></span>';
                echo "</li>";

                child_standard_notations($id);
                $index++;
            }
            echo "</ul>";
            echo "</div>";
        }
    }
}

if (!function_exists('was_display_loader')){
    function was_display_loader(){
        ?>
        <div class="loader"><div class="loader-img"><div><img src="<?php echo WAS_URL; ?>images/loading.gif" align="center" valign="middle" /></div></div></div>
        <?php
    }
}

if (!function_exists('was_display_admin_standards')){
    function was_display_admin_standards(){
        global $wpdb;

        $results = $wpdb->get_results("SELECT * from " . $wpdb->prefix. "oer_core_standards",ARRAY_A);
        if ($results){
        ?>
        <ul class='was-standard-list'>
            <?php
            foreach($results as $row){
                $value = 'core_standards-'.$row['id'];
                ?>
                <li class='core-standard'>
                    <a data-toggle='collapse' data-id="<?php echo $row['id']; ?>" data-target='#core_standards-<?php echo $row['id']; ?>'><?php echo stripslashes(esc_html($row['standard_name'])); ?></a>
                        <span class="std-edit std-icon"><a data-target="#editStandardModal" class="std-edit-icon" data-value="<?php echo $value; ?>" data-stdid="<?php echo $row['id']; ?>"><i class="far fa-edit"></i></a></span>
                        <span class="std-add std-icon"><a data-target="#addStandardModal" class="std-add-icon" data-parent="<?php echo $value; ?>"><i class="fas fa-plus"></i></a></span>
                </li>
            <?php
                child_standards($value);
            }
            ?>
        </ul>
        <?php
        }
    }
}

if (!function_exists('was_display_admin_core_standards')){
    function was_display_admin_core_standards(){
        global $wpdb;

        $results = $wpdb->get_results("SELECT * from " . $wpdb->prefix. "oer_core_standards",ARRAY_A);
        if ($results){
        ?>
        <ul class='was-standard-list'>
            <?php
            foreach($results as $row){
                $value = 'core_standards-'.$row['id'];
                ?>
                <li class='core-standard'>
                    <a href="<?php echo admin_url("admin.php?page=wp-academic-standards&std=core_standards-".$row['id']); ?>" data-toggle='collapse' data-id="<?php echo $row['id']; ?>" data-target='#core_standards-<?php echo $row['id']; ?>'><?php echo stripslashes(esc_html($row['standard_name'])); ?></a>
                        <span class="std-edit std-icon"><a data-target="#editStandardModal" class="std-edit-icon" data-value="<?php echo $value; ?>" data-stdid="<?php echo $row['id']; ?>"><i class="far fa-edit"></i></a></span>
                </li>
            <?php
            }
            ?>
        </ul>
        <?php
        }
    }
}

if (!function_exists('was_selectable_admin_standards')){
    function was_selectable_admin_standards($post_id, $meta_key="oer_standard"){
        global $wpdb, $post;

        $standards = get_post_meta($post_id, $meta_key, true);

        $results = $wpdb->get_results("SELECT * from " . $wpdb->prefix. "oer_core_standards",ARRAY_A);
        if ($results){
             ?>
            <ul class='oer-standard-list'>
            <?php
              foreach($results as $row){
                $value = 'core_standards-'.$row['id'];
                ?>
                <li class='core-standard'>
                  <a data-toggle='collapse' data-target='#core_standards-<?php echo $row['id']; ?>'><?php echo stripslashes($row['standard_name']); ?></a>
                </li>
            <?php
                was_child_standards($value, $standards, $meta_key);
              }
        }
    }
}

/** Get Child Standards **/
if (!function_exists('was_child_standards')){
    function was_child_standards($id, $oer_standard, $meta_key="oer_standard") {
	global $wpdb, $chck, $class;

	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_sub_standards where parent_id = %s" , $id ) ,ARRAY_A);
	if(!empty($oer_standard))
	{
	    $stndrd_arr = explode(",",$oer_standard);
	}

	if(!empty($results))
	{
            echo "<div id='".$id."' class='collapse'>";
                echo "<ul>";
                foreach($results as $result)
                {
                    $value = 'sub_standards-'.$result['id'];
                    if(!empty($stndrd_arr))
                    {
                        if(in_array($value, $stndrd_arr))
                        {
                            $chck = 'checked="checked"';
                            $class = 'selected';
                        }
                        else
                        {
                            $chck = '';
                            $class = '';
                        }
                    }

                    $id = 'sub_standards-'.$result['id'];
                    $subchildren = get_substandard_children($id);
                    $child = check_child_standard($id);

                    echo "<li class='oer_sbstndard ". $class ."'>";

                    if (!empty($subchildren)){
                        echo "<a data-toggle='collapse' data-target='#".$id."'>".stripslashes($result['standard_title'])."</a>";
                    }

                    if(empty($subchildren) && empty($child)) {
                        echo "<input type='checkbox' ".$chck." name='".$meta_key."[]' value='".$value."' onclick='was_check_all(this)' >
                                <div class='oer_stndrd_desc'>".stripslashes($result['standard_title'])."</div>";
                    }

                    $id = 'sub_standards-'.$result['id'];
                    was_child_standards($id, $oer_standard, $meta_key);

                    if (!empty($child)) {
                        echo "<a data-toggle='collapse' data-target='#".$id."'>".stripslashes($result['standard_title'])."</a>";
                        $sid = 'sub_standards-'.$result['id'];
                        was_child_standard_notations($sid, $oer_standard, $meta_key);
                    }
                    echo "</li>";
                }
                echo "</ul>";
            echo "</div>";
	}
    }
}

/** Get Standard Notation **/
if (!function_exists('was_child_standard_notations')) {
    function was_child_standard_notations($id, $oer_standard, $meta_key="oer_standard"){
	global $wpdb;

	$results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_standard_notation where parent_id = %s" , $id ) , ARRAY_A);

	if(!empty($oer_standard))
	{
		$stndrd_arr = explode(",",$oer_standard);
	}

	if(!empty($results))
	{
		echo "<div id='".$id."' class='collapse'>";
		echo "<ul>";
			foreach($results as $result)
			{
				$chck = '';
				$class = '';
				$id = 'standard_notation-'.$result['id'];
				$child = check_child_standard($id);
				$value = 'standard_notation-'.$result['id'];

				if(!empty($oer_standard))
				{
					if(in_array($value, $stndrd_arr))
					{
						$chck = 'checked="checked"';
						$class = 'selected';
					}
				}

				echo "<li class='".$class."'>";
				if(!empty($child))
				{
					echo "<a data-toggle='collapse' data-target='#".$id."'>".stripslashes($result['standard_notation'])."</a>";
				}

				if (empty($child))
					echo "<input type='checkbox' ".$chck." name='".$meta_key."[]' value='".$value."' onclick='was_check_myChild(this)'>";

				echo  stripslashes($result['standard_notation'])."
					<div class='oer_stndrd_desc'> ". stripslashes($result['description'])." </div>";

				was_child_standard_notations($id, $oer_standard,$meta_key);

				echo "</li>";
			}
		echo "</ul>";
		echo "</div>";
	}
    }
}

if (!function_exists('was_stylesheet_installed')){
    function was_stylesheet_installed($arr_styles)
    {
        global $wp_styles;

        foreach( $wp_styles->queue as $style )
        {
            foreach ($arr_styles as $css)
            {
                if (false !== strpos( $wp_styles->registered[$style]->src, $css ))
                    return true;
            }
        }
        return false;
    }
}

/**
 * Get Standards Count
 **/
if (!function_exists('was_core_standards_count')){
    function was_core_standards_count(){
            global $wpdb;
            $cnt = 0;

            $query = "SELECT count(*) FROM {$wpdb->prefix}oer_core_standards";

            $cnt = $wpdb->get_var($query);

            return $cnt;
    }
}

/**
 * Get Standards
 **/
if (!function_exists('was_core_standards')){
    function was_core_standards(){
            global $wpdb;

            $query = "SELECT * FROM {$wpdb->prefix}oer_core_standards";

            $standards = $wpdb->get_results($query);

            return $standards;
    }
}

/**
 * Get Resource Count By Standard
 **/
if (!function_exists('was_resource_count_by_standard')){
    function was_resource_count_by_standard($standard_id){

        $cnt = 0;

        $substandards = was_substandards($standard_id);

        if(count($substandards)>0){
                foreach($substandards as $substandard){
                        $cnt += was_resource_count_by_substandard($substandard->id);
                }
        }
        $notations = was_standard_notations($standard_id);

        if ($notations){
                foreach($notations as $notation){
                        $cnt += was_resource_count_by_notation($notation->id);
                }
        }
        return $cnt;
    }
}

/**
 * Get Resource Count By Sub-Standard
 **/
if (!function_exists('was_resource_count_by_substandard')){
    function was_resource_count_by_substandard($substandard_id){
        $cnt = 0;

        $child_substandards = was_substandards($substandard_id, false);

        if(count($child_substandards)>0){
            foreach($child_substandards as $child_substandard){
                $cnt += was_resource_count_by_substandard($child_substandard->id, false);
            }
        }
        $notations = was_standard_notations($substandard_id);

        if ($notations){
            foreach($notations as $notation){
                $cnt += was_resource_count_by_notation($notation->id);
            }
        }
        return $cnt;
    }
}

/**
 * Get Resource Count By Notation
 **/
if (!function_exists('was_resource_count_by_notation')){
    function was_resource_count_by_notation($notation_id){
        $cnt = 0;

        $notation = "standard_notation-".$notation_id;

        //later in the request
        $args = array(
                'post_type'  => 'resource', //or a post type of your choosing
                'posts_per_page' => -1,
                'meta_query' => array(
                        array(
                        'key' => 'oer_standard',
                        'value' => $notation,
                        'compare' => 'like'
                        )
                )
        );

        $query = new WP_Query($args);

        $cnt += count($query->posts);

        $child_notations = was_child_notations($notation_id);

        if ($child_notations){
                foreach ($child_notations as $child_notation){
                        $cnt += was_resource_count_by_notation($child_notation->id);
                }
        }

        return $cnt;
    }
}

/**
 * Get child standards of a core standard
 **/
if (!function_exists('was_substandards')) {
    function was_substandards($standard_id, $core=true){
        global $wpdb;

        if ($core)
                $std_id = "core_standards-".$standard_id;
        else
                $std_id = "sub_standards-".$standard_id;

        $substandards = array();

        $query = "SELECT * FROM {$wpdb->prefix}oer_sub_standards where parent_id='%s'";

        $substandards = $wpdb->get_results($wpdb->prepare($query, $std_id));

        return $substandards;
    }
}

/**
 * Get Standard Notations under a Sub Standard
 **/
if (!function_exists('was_standard_notations')){
    function was_standard_notations($standard_id){
        global $wpdb;

        $std_id = "sub_standards-".$standard_id;

        $notations = array();

        $query = "SELECT * FROM {$wpdb->prefix}oer_standard_notation where parent_id='%s'";

        $result = $wpdb->get_results($wpdb->prepare($query, $std_id));

        foreach ($result as $row){
                $notations[] = $row;
        }

        return $notations;
    }
}

/**
 * Get Substandard(s) by Notation
 **/
if (!function_exists('was_substandards_by_notation')) {
    function was_substandards_by_notation($notation){
        global $wpdb;

        $std = null;

        $query = "SELECT * FROM {$wpdb->prefix}oer_standard_notation WHERE standard_notation = '%s'";

        $standard_notation = $wpdb->get_results($wpdb->prepare($query, $notation));

        if ($standard_notation){
            $substandard_id = $standard_notation[0]->parent_id;
            $std = was_hierarchical_substandards($substandard_id);
        }

        return $std;
    }
}

/**
 * Get Child Notations
 **/
if (!function_exists('was_child_notations')){
    function was_child_notations($notation_id){
        global $wpdb;

        $notation = "standard_notation-".$notation_id;

        $query = "SELECT * FROM {$wpdb->prefix}oer_standard_notation WHERE parent_id = '%s'";

        $standard_notations = $wpdb->get_results($wpdb->prepare($query, $notation));

        return $standard_notations;
    }
}

/**
 * Get Core Standard by standard or substandard ID
 **/
if (!function_exists('was_corestandard_by_standard')){
    function was_corestandard_by_standard($parent_id){
        global $wpdb;

        $standard = null;
        $parent = explode("-",$parent_id);
        if ($parent[0]=="sub_standards") {
                $query = "SELECT * FROM {$wpdb->prefix}oer_sub_standards WHERE id = '%s'";
                $substandards = $wpdb->get_results($wpdb->prepare($query, $parent[1]));

                foreach($substandards as $substandard){
                        $standard = was_corestandard_by_standard($substandard->parent_id);
                }
        } else {
                $query = "SELECT * FROM {$wpdb->prefix}oer_core_standards WHERE id = '%s'";
                $standards = $wpdb->get_results($wpdb->prepare($query, $parent[1]));
                foreach($standards as $std){
                        $standard = $std;
                }
        }

        return $standard;
    }
}

/**
 * Get Standard By Id
 **/
if (!function_exists('was_standard_by_id')){
    function was_standard_by_id($id){
        global $wpdb;

        $std = null;

        $query = "SELECT * FROM {$wpdb->prefix}oer_core_standards WHERE id = %d";

        $standards = $wpdb->get_results($wpdb->prepare($query,$id));

        foreach($standards as $standard){
                        $std = $standard;
        }

        return $std;
    }
}

/**
 * Get Standard By Slug
 **/
if (!function_exists('was_standard_by_slug')){
    function was_standard_by_slug($slug){
        global $wpdb;

        $std = null;

        $query = "SELECT * FROM {$wpdb->prefix}oer_core_standards";

        $standards = $wpdb->get_results($query);

        foreach($standards as $standard){
            if (sanitize_title($standard->standard_name)===$slug)
                $std = $standard;
        }

        return $std;
    }
}

/**
 * Get SubStandard By Slug
 **/
if (!function_exists('was_substandard_by_slug')){
    function was_substandard_by_slug($slug){
        global $wpdb;

        $std = null;

        $query = "SELECT * FROM {$wpdb->prefix}oer_sub_standards";

        $substandards = $wpdb->get_results($query);

        foreach($substandards as $substandard){
                if (sanitize_title($substandard->standard_title)===$slug)
                        $std = $substandard;
        }

        return $std;
    }
}

/**
 * Get Core Standard by Notation
 **/
if (!function_exists('was_standard_by_notation')) {
    function was_standard_by_notation($notation){
        global $wpdb;

        $std = null;

        $query = "SELECT * FROM {$wpdb->prefix}oer_standard_notation WHERE standard_notation = '%s'";

        $standard_notation = $wpdb->get_results($wpdb->prepare($query, $notation));

        if ($standard_notation){
            $substandard_id = $standard_notation[0]->parent_id;
            $substandard = was_parent_standard($substandard_id);

            if (strpos($substandard[0]['parent_id'],"core_standards")!==false){
                $pIds = explode("-",$substandard[0]['parent_id']);

                if (count($pIds)>1){
                    $parent_id=(int)$pIds[1];
                    $std = was_standard_by_id($parent_id);
                }
            }
        }

        return $std;
    }
}

/** Get Core Standard **/
if (!function_exists('was_core_standard')){
    function was_core_standard($id) {
            global $wpdb;
            $results = null;

            if ($id!=="") {
                    $stds = explode("-",$id);
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_core_standards where id = %s" , $stds[1] ) , ARRAY_A);
            }
            return $results;
    }
}

/** Get Parent Standard **/
if (!function_exists('was_parent_standard')){
    function was_parent_standard($standard_id) {
        global $wpdb, $_oer_prefix;

        $stds = explode("-",$standard_id);
        $table = $stds[0];

        $prefix = substr($standard_id,0,strpos($standard_id,"_")+1);

        $table_name = $wpdb->prefix.$_oer_prefix.$table;

        $id = $stds[1];
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $table_name. " where id = %s" , $id ) , ARRAY_A);

        foreach($results as $result) {

            $stdrds = explode("-",$result['parent_id']);
            $tbl = $stdrds[0];

            $tbls = array('sub_standards','standard_notation');

            if (in_array($tbl,$tbls)){
                $results = was_parent_standard($result['parent_id']);
            }

        }
        return $results;
    }
}

/**
 * Get Parent Sub Standard by Notation
 **/
if (!function_exists('was_substandard_by_notation')) {
    function was_substandard_by_notation($notation) {
        global $wpdb;

        $std = null;

        $query = "SELECT * FROM {$wpdb->prefix}oer_standard_notation WHERE standard_notation = '%s'";

        $substandards = $wpdb->get_results($wpdb->prepare($query, $notation));

        foreach($substandards as $substandard){
                $std = $substandard;
        }

        return $std;
    }
}

// Get Hierarchical Substandards
if (!function_exists('was_hierarchical_substandards')){
    function was_hierarchical_substandards($substandard_id){
        $substandard=null;
        $substandards = null;
        $hierarchy = "";
        $ids = explode("-",$substandard_id);
        if (strpos($substandard_id,"sub_standards")!==false) {
            do {

                $substandard = was_substandard_details($ids[1]);
                $ids = explode("-", $substandard['parent_id']);
                $substandards[] = $substandard;

            } while(strpos($substandard['parent_id'],"sub_standards")!==false);
        }

        return $substandards;
    }
}

// Get Hierarchical Notations
if (!function_exists('was_hierarchical_notations')){
    function was_hierarchical_notations($notation_id){
        $notation=null;
        $notations = array();
        $hierarchy = "";
        $ids = explode("-",$notation_id);
        if (strpos($notation_id,"standard_notation")!==false) {
            do {
                $notation = was_notation_details($ids[1]);
                $ids = explode("-", $notation[0]['parent_id']);
                $notations[] = $notation;
            } while(strpos($notation[0]['parent_id'],"standard_notation")!==false);
        }
        return $notations;
    }
}

// Get Notation Details
if (!function_exists('was_notation_details')){
    function was_notation_details($notation_id){
        global $wpdb;
        $notations = null;
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_standard_notation where id = %s" , $notation_id  ) , ARRAY_A);
        foreach ($results as $row){
            $notations = $row;
        }
        return $notations;
    }
}

// Get Substandard Details
if (!function_exists('was_substandard_details')){
    function was_substandard_details($substandard_id){
        global $wpdb;
        $substandards = null;
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_sub_standards where id = %s" , $substandard_id  ) , ARRAY_A);
        foreach ($results as $row){
            $substandards = $row;
        }
        return $substandards;
    }
}

/**
 * Get Resources by notation
 **/
if (!function_exists('was_resources_by_notation')) {
    function was_resources_by_notation($notation_id) {

        $notation = "standard_notation-".$notation_id;

        //later in the request
        $args = array(
            'post_type'  => 'resource', //or a post type of your choosing
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                'key' => 'oer_standard',
                'value' => $notation,
                'compare' => 'like'
                )
            )
        );

        $query = new WP_Query($args);

        return $query->posts;
    }
}

if (!function_exists('was_custom_styles')) {
    function was_custom_styles(){
        ?>
        <style type="text/css">
            .substandards-template #content ul.oer-substandards > li:not(:active),
            .standards-template #content ul.oer-standards > li,
            .substandards-template #content ul.oer-notations > li,
            .notation-template #content ul.oer-subnotations > li { background:url(<?php echo WAS_URL."/images/arrow-right.png"; ?>) no-repeat top left; padding-left:28px; }
        </style>
        <?php
    }
}

if (!function_exists('was_show_setup_settings')){
    function was_show_setup_settings() {
            global $message, $type;
            ?>
    <div class="plugin-body">
        <div class="was-plugin-row">
            <div class="oer-row">
                <?php _e("These are the settings of WP Academic Standards.", WAS_SLUG); ?>
                <div class="oer-import-row">
                <h2 class="hidden"></h2>
                <?php if ($message) { ?>
                <div class="notice notice-<?php echo esc_attr($type); ?> is-dismissible">
                    <p><?php echo $message; ?></p>
                </div>
                <?php } ?>
                </div>
            </div>
        </div>
        <div class="was-plugin-row">
            <form method="post" class="was_settings_form" action="options.php"  onsubmit="return wasShowLoader(this)">
                <?php settings_fields("was_setup_settings"); ?>
                <?php do_settings_sections("standards-settings"); ?>
                <?php submit_button('Save', 'primary setup-continue'); ?>
            </form>
        </div>
    </div>
    <?php
    }
}

//Import Default CCSS
if (!function_exists('was_importDefaultStandards')){
    function was_importDefaultStandards() {
        $files = array(
            WAS_PATH."samples/CCSS_Math.xml",
            WAS_PATH."samples/CCSS_ELA.xml",
            WAS_PATH."samples/NGSS.xml"
            );
        foreach ($files as $file) {
            $import = was_importStandards($file);
            if ($import['type']=="success") {
                if (strpos($file,'Math')) {
                            $message .= "Successfully imported Common Core Mathematics Standards. \n";
                } else {
                            $message .= "Successfully imported Common Core English Language Arts Standards. \n";
                }
            }
            $type = $import['type'];
        }
        $response = array( 'message' => $message, 'type' => $type );
        return $response;
    }
}

//Import California History Standards
if (!function_exists('was_importCaliforniaHistoryStandards')){
    function was_importCaliforniaHistoryStandards() {
        $files = array(
	    WAS_PATH."samples/D10002A5.xml"
            );
        foreach ($files as $file) {
            $import = was_importStandards($file);
            if ($import['type']=="success") {
                $message .= "Successfully imported California History-Social Science Standards. \n";
            }
            $type = $import['type'];
        }
        $response = array( 'message' => $message, 'type' => $type );
        return $response;
    }
}

/** Import Standards **/
if (!function_exists('was_importStandards')){
    function was_importStandards($file){
	global $wpdb;

	$time = time();
	$date = date($time);

	//Set Maximum Excution Time
	ini_set('max_execution_time', 0);
	set_time_limit(0);

	// Log start of import process
	debug_log("Academic Standards Importer: Start Bulk Import of Standards");

	if( isset($file) ){
            try {

                $filedetails = pathinfo($file);

                $filename = $filedetails['filename']."-".$date;

                $doc = new DOMDocument();
                $doc->preserveWhiteSpace = FALSE;
                $doc->load( $file );

                $StandardDocuments = $doc->getElementsByTagName('StandardDocument');

                $xml_arr = array();
                $m = 0;
                foreach( $StandardDocuments as $StandardDocument)
                {
                        $url = $StandardDocuments->item($m)->getAttribute('rdf:about');
                        $titles = $StandardDocuments->item($m)->getElementsByTagName('title');
                        $core_standard[$url]['title'] = $titles->item($m)->nodeValue;
                }

                $Statements = $doc->getElementsByTagName('Statement');
                $i = 0;
                foreach( $Statements as $Statement)
                {
                    $statementNotations = $Statements->item($i)->getElementsByTagName('statementNotation');
                    if($statementNotations->length == 1)
                    {
                        $url = $Statements->item($i)->getAttribute('rdf:about');
                        $isChildOfs = $Statements->item($i)->getElementsByTagName('isChildOf');
                        $descriptions = $Statements->item($i)->getElementsByTagName('description');
                        for($j = 0; $j < sizeof($statementNotations); $j++)
                        {
                            $standard_notation[$url]['ischild'] = $isChildOfs->item($j)->getAttribute('rdf:resource');
                            $standard_notation[$url]['title'] = $statementNotations->item($j)->nodeValue;
                            $standard_notation[$url]['description'] = $descriptions->item($j)->nodeValue;
                        }
                    }
                    else
                    {
                        $descriptions = $Statements->item($i)->getElementsByTagName('description');
                        $url = $Statements->item($i)->getAttribute('rdf:about');
                        $isChildOfs = $Statements->item($i)->getElementsByTagName('isChildOf');
                        $k = 0;
                        foreach( $descriptions as $description)
                        {
                            $xml_arr[$url]['ischild'] = $isChildOfs->item($k)->getAttribute('rdf:resource');
                            $xml_arr[$url]['title'] = $descriptions->item($k)->nodeValue;
                            $k++;
                        }
                    }
                    $i++;
                }

                // Get Core Standard
                foreach($core_standard as $cskey => $csdata)
                {
                    $url = $cskey;
                    $title = $csdata['title'];
                    $results = $wpdb->get_results( $wpdb->prepare( "SELECT id from " . $wpdb->prefix. "oer_core_standards where standard_name = %s" , $title ));
                    if(empty($results))
                    {
                        $wpdb->get_results( $wpdb->prepare( 'INSERT INTO ' . $wpdb->prefix. 'oer_core_standards values("", %s , %s)' , $title , $url ));
                    }
                }
                // Get Core Standard

                // Get Sub Standard
                foreach($xml_arr as $key => $data)
                {
                    $url = esc_url_raw($key);
                    $ischild = $data['ischild'];
                    $title = sanitize_text_field($data['title']);
                    $parent = '';

                    $rsltset = $wpdb->get_results( $wpdb->prepare( "select id from " . $wpdb->prefix. "oer_core_standards where standard_url=%s" , $ischild ));
                    if(!empty($rsltset))
                    {
                        $parent = "core_standards-".$rsltset[0]->id;
                    }
                    else
                    {
                        $rsltset_sec = $wpdb->get_results( $wpdb->prepare( "select id from " . $wpdb->prefix. "oer_sub_standards where url=%s" , $ischild ));
                        if(!empty($rsltset_sec))
                        {
                            $parent = 'sub_standards-'.$rsltset_sec[0]->id;
                        }
                    }

                    $res = $wpdb->get_results( $wpdb->prepare( "SELECT id from " . $wpdb->prefix. "oer_sub_standards where parent_id = %s && url = %s" , $parent , $url ));
                    if(empty($res))
                    {
                        $wpdb->get_results( $wpdb->prepare( 'INSERT INTO ' . $wpdb->prefix. 'oer_sub_standards values("", %s, %s, %s, 0)' , $parent , $title , $url ));
                    }
                }
                // Get Sub Standard

                // Get Standard Notation
                foreach($standard_notation as $st_key => $st_data)
                {
                    $url = esc_url_raw($st_key);
                    $ischild = $st_data['ischild'];
                    $notation = sanitize_text_field($st_data['title']);
                    $description = sanitize_text_field($st_data['description']);
                    $parent = '';

                    $rsltset = $wpdb->get_results( $wpdb->prepare( "select id from " . $wpdb->prefix. "oer_sub_standards where url=%s" , $ischild ));
                    if(!empty($rsltset))
                    {
                        $parent = 'sub_standards-'.$rsltset[0]->id;
                    }
                    else
                    {
                        $rsltset_sec = $wpdb->get_results( $wpdb->prepare( "select id from " . $wpdb->prefix. "oer_standard_notation where url=%s" , $ischild ));
                        if(!empty($rsltset_sec))
                        {
                            $parent = 'standard_notation-'.$rsltset_sec[0]->id;
                        }
                    }

                    $res = $wpdb->get_results( $wpdb->prepare( "SELECT id from " . $wpdb->prefix. "oer_standard_notation where standard_notation = %s && parent_id = %s && url = %s" , $notation , $parent , $url ));
                    if(empty($res))
                    {
                        //$description = preg_replace("/[^a-zA-Z0-9]+/", " ", html_entity_decode($description))
                        $description = esc_sql($description);
                        $wpdb->get_results( $wpdb->prepare( 'INSERT INTO ' . $wpdb->prefix. 'oer_standard_notation values("", %s, %s, %s, "", %s, 0)' , $parent , $notation , $description , $url ));
                    }
                }

            } catch(Exception $e) {
                $response = array(
                'message' => $e->getMessage(),
                'type' => 'error'
                );
                // Log any error during import process
                debug_log($e->getMessage());
                return $response;
            }
            // Log Finished Import
            debug_log("Academic Standards Importer: Finished Bulk Import of Standards");
            // Get Standard Notation
            $response = array(
                    'message' => 'successful',
                    'type' => 'success'
            );
            return $response;
	}
    }
}

//Check if Standard Exists
if (!function_exists('was_isStandardExisting')){
    function was_isStandardExisting($standard) {
        global $wpdb;

        $response = false;
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT id from " . $wpdb->prefix. "oer_core_standards where standard_name like %s" , '%'.$standard.'%'));
        if(!empty($results))
            $response = true;

        return $response;
    }
}

// Display Selected Standards on frontend
if (!function_exists('was_display_selected_standards')){
    function was_display_selected_standards($standard_meta_key="oer_standard"){
        global $post, $wpdb, $_oer_prefix;

        $oer_standard = get_post_meta($post->ID, $standard_meta_key, true);

        $standards = explode(",", $oer_standard);
        $oer_standards = array();

        foreach ($standards as $standard) {
            if ($standard!=""){
                $stds = was_parent_standard($standard);
                foreach($stds as $std){
                    $core_std = was_core_standard($std['parent_id']);
                    $oer_standards[] = array(
                    'id' => $standard,
                    'core_id' => $core_std[0]['id'],
                    'core_title' => $core_std[0]['standard_name']
                     );
                }
            }
        }

        foreach ($oer_standards as $key => $row) {
            $core[$key]  = $row['core_id'];
        }

        if (!empty($oer_standards))
            array_multisort($core, SORT_ASC, $oer_standards);

        if(!empty($oer_standards))
        {
        ?>
            <div class="alignedStandards">
            <h2><?php _e("Standards Alignment", WAS_SLUG) ?></h2>
            <div class="oer_meta_container">
                <div class="oer_stndrds_notn">
                <?php
                    if(!empty($oer_standards))
                    {
                    ?>
                        <?php
                        $displayed_core_standards = array();
                        foreach($oer_standards as $o_standard) {

                            if (!in_array($o_standard['core_id'],$displayed_core_standards)){
                                echo "<div class='oer-core-title'><h4><strong>".$o_standard['core_title']."</strong></h4></div>";
                                $displayed_core_standards[] = $o_standard['core_id'];
                            }

                            $oer_standard =$o_standard['id'];
                            $stnd_arr = explode(",", $oer_standard);

                            for($i=0; $i< count($stnd_arr); $i++)
                            {
                                $table = explode("-",$stnd_arr[$i]);

                                $table_name = $wpdb->prefix.$_oer_prefix.$table[0];

                                $id = $table[1];

                                $res = $wpdb->get_row( $wpdb->prepare("select * from $table_name where id=%d" , $id ), ARRAY_A);

                                echo "<div class='oer_sngl_stndrd'>";
                                    if (strpos($table_name,"sub_standards")>0) {
                                        echo "<span class='oer_sngl_description'>".$res['standard_title']."</span>";
                                    } else {
                                        echo "<span class='oer_sngl_notation'>".$res['standard_notation']."</span>";
                                        echo "<span class='oer_sngl_description'>".$res['description']."</span>";
                                    }
                                echo "</div>";
                            }
                        }
                    }
                ?>
                </div>

            </div>
        </div>
        <?php }
    }
}

if (!function_exists('was_pre_search_block')){
    function was_pre_search_block($iteration){
        for ($i=0;$i<$iteration;$i++){
            echo "<li><ul>";
        }
    }
}

if (!function_exists('was_post_search_block')){
    function was_post_search_block($iteration){
        for ($i=0;$i<$iteration;$i++){
            echo "</ul><li>";
        }
    }
}

if (!function_exists('was_search_standards')){
    function was_search_standards($post_id, $keyword, $meta_key="oer_standard") {
        global $wpdb;

        $all_results = array();
        $results = null;
        $chck = null;
        $core_standard = false;
        $sub_standard = false;
        $standard_notation = false;

        $standards = get_post_meta($post_id, $meta_key, true);
        $selected_standards = explode(",",$standards);

        $search = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_standard_notation where description like %s" , '%'.$keyword.'%'));
        if (!empty($search)){
            foreach($search as $row){
                $results[]= $row;
            }
        }

        if (!empty($results)){
            foreach ($results as $res){
                $parent = was_get_parent($res->parent_id);
                $all_results[] = array("parent"=>$parent,"notation"=>$res);
            }
        }

        usort($all_results, "was_sort_search_results");
        $added = array();
        if (!empty($all_results)){
            echo "<div id='search-results-list'>";
            echo "<ul class='search-standards-list'>";
            foreach($all_results as $sresult) {
                $chck = "";
                $ancestors = 0;
                $parents = $sresult['parent'];
                if (is_array($parents)){
                    foreach ($parents as $parent){
                        if (property_exists($parent,"standard_name")){
                            if (!in_array($parent->standard_name,$added)) {
                                echo "<li>".$parent->standard_name."</li>";
                                $core_standard = true;
                                $added[] = $parent->standard_name;
                            }
                        }
                        if (property_exists($parent,"standard_title")){
                            $ancestors++;
                            if (!in_array($parent->standard_title, $added)){
                                was_pre_search_block($ancestors);
                                echo "<li>".$parent->standard_title."</li>";
                                was_post_search_block($ancestors);
                                $added[] = $parent->standard_title;
                                $sub_standard = true;
                            }
                        }
                        if (property_exists($parent,"standard_notation")){
                            $ancestors++;
                            if (!in_array($parent->standard_notation, $added)){
                                was_pre_search_block($ancestors);
                                echo "<li><strong>".$parent->standard_notation."</strong> ".$parent->description."</li>";
                                was_post_search_block($ancestors);
                                $added[] = $parent->standard_notation;
                                $standard_notation = true;
                            }
                        }
                    }
                }
                $notation = $sresult['notation'];
                if ($notation){
                    $ancestors++;
                    $value = "standard_notation-".$notation->id;
                    if (in_array($value, $selected_standards)){
                        $chck = "checked='checked'";
                    }
                    was_pre_search_block($ancestors);
                    echo "<li><input type='checkbox' ".$chck." name='".$meta_key."[]' value='".$value."' onclick='was_check_all(this)' >".$notation->standard_notation." <div class='oer_stndrd_desc'>".$notation->description."</div></li>";
                    was_post_search_block($ancestors);
                }
            }
            echo "</ul>";
            echo "</div>";
        }
    }
}

if (!function_exists('was_get_parent')){
    function was_get_parent($parent_id){
        global $wpdb;

        $table ="";
        $id = 0;
        $results = null;
        $ids = explode("-",$parent_id);
        if (is_array($ids)){
            if (isset($ids[0]))
                $table = $ids[0];
            if (isset($ids[1]))
                $id = $ids[1];
        }

        if ($table!=="" && $id!==0) {
            $parent = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_".$table." where id=%d" , ''.$id.''));
            if (!empty($parent)){
                foreach($parent as $row){
                    if ($table!=="core_standards"){
                        $results = was_get_parent($row->parent_id);
                        $results[]= $row;
                    }
                    else
                        $results[] = $row;
                }
            }
        }
        return $results;
    }
}

if (!function_exists('was_sort_search_results')){
    function was_sort_search_results($a, $b){
        $ret = $a['parent'][0]->id - $b['parent'][0]->id;
        if ($ret == 0){
            $ret = $a['parent'][1]->id - $b['parent'][1]->id;
            if ($ret == 0){
                if (isset($a['parent'][2]) && isset($a['parent'][2])) {
                    $ret = $a['parent'][2]->id - $b['parent'][2]->id;
                }
            }
        }
        return $ret;
    }
}

if (!function_exists('was_standard_details')){
    function was_standard_details($stdid){
        global $wpdb;

        $rec = null;

        $stds = explode("-", $stdid);
        $table = $stds[0];
        $id = $stds[1];

        $result = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_".$table." where id=%d" , $id));
        if (!empty($result)){
            foreach($result as $row){
                $rec = $row;
            }
        }
        return $rec;
    }
}

if (!function_exists('was_admin_delete_standard')){
    function was_admin_delete_standard($standard_id) {
        global $wpdb;

        $results = $wpdb->get_results("SELECT * from " . $wpdb->prefix. "oer_core_standards where id=".$standard_id."",ARRAY_A);
        if ($results){
            foreach($results as $row){
                $value = 'core_standards-'.$row['id'];

                was_admin_delete_substandards($value);
            }
        }
        $wpdb->delete($wpdb->prefix."oer_core_standards", array("id"=>$standard_id));
    }
}

if (!function_exists('was_admin_delete_substandards')){
    function was_admin_delete_substandards($parent_id){
        global $wpdb;

        $subs = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_sub_standards where parent_id = %s" , $parent_id ) ,ARRAY_A);

        foreach($subs as $sub)
        {
            $value = 'sub_standards-'.$sub['id'];

            $id = 'sub_standards-'.$sub['id'];
            $subchildren = get_substandard_children($id);
            $child = check_child_standard($id);

            if (!empty($subchildren))
                was_admin_delete_substandards($id);

            if (empty($subchildren) && !empty($child)) {
                $sid = 'sub_standards-'.$sub['id'];
                was_admin_delete_standard_notations($sid);
            } elseif (!empty($subchildren) && !empty($child)) {
                $sid = 'sub_standards-'.$sub['id'];
                was_admin_delete_standard_notations($sid, true);
            }
        }

        $wpdb->delete($wpdb->prefix."oer_sub_standards", array("parent_id"=>$parent_id));
    }
}

if (!function_exists('was_admin_delete_standard_notations')){
    function was_admin_delete_standard_notations($parent_id){
        global $wpdb;

        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $wpdb->prefix. "oer_standard_notation where parent_id = %s" , $parent_id ) , ARRAY_A);

        if(!empty($results))
        {
            foreach($results as $result)
            {
                $id = 'standard_notation-'.$result['id'];
                $child = check_child_standard($id);
                $value = 'standard_notation-'.$result['id'];

                if ($child)
                    was_admin_delete_standard_notations($id);
            }
        }

        $wpdb->delete($wpdb->prefix."oer_standard_notation", array("parent_id"=>$parent_id));
    }
}

if (!function_exists('was_admin_delete_standards')){
    function was_admin_delete_standards($standards){
        $stds = explode(",", $standards);
        foreach($stds as $std){
            was_admin_delete_standard($std);
        }
    }
}

if (!function_exists('was_search_imported_standards')){
    function was_search_imported_standards($imported_standards, $standard){
	foreach($imported_standards as $index => $imported_standard){
	    if ($imported_standard['other_title']==$standard)
		return $index;
	}
	return false;
    }
}

if (!function_exists('debug_log')){
    // Log Debugging
    function debug_log($message) {
	error_log($message);
    }
}

/**
 * Get Core Standard by Notation
 **/
if (!function_exists('oer_std_get_standard_by_notation')){
    function oer_std_get_standard_by_notation($notation){
        global $wpdb;
        
        $std = null;
        $notations = explode("-", $notation);
        $table = "oer_".$notations[0];
        $notation_id = $notations[1];
        
        $query = "SELECT * FROM {$wpdb->prefix}".$table." WHERE id = '%s'";
        $standard_notation = $wpdb->get_results($wpdb->prepare($query, $notation_id));
        
        if ($standard_notation){
            $substandard_id = $standard_notation[0]->parent_id;
            $substandard = oer_std_get_parent_standard($substandard_id);
            
            if (strpos($substandard[0]['parent_id'],"core_standards")!==false){
                $pIds = explode("-",$substandard[0]['parent_id']);
                
                if (count($pIds)>1){
                    $parent_id=(int)$pIds[1];
                    $std = oer_std_get_standard_by_id($parent_id);
                }
            }
        }
        
        return $std;
    }
}

/** Get Parent Standard **/
if (!function_exists('oer_std_get_parent_standard')) {
    function oer_std_get_parent_standard($standard_id) {
        global $wpdb, $_oer_prefix;
        
        $stds = explode("-",$standard_id);
        $table = $stds[0];
        
        $prefix = substr($standard_id,0,strpos($standard_id,"_")+1);
        
        $table_name = $wpdb->prefix.$_oer_prefix.$table;
        
        $id = $stds[1];
        $results = $wpdb->get_results( $wpdb->prepare( "SELECT * from " . $table_name. " where id = %s" , $id ) , ARRAY_A);
        
        foreach($results as $result) {
    
            $stdrds = explode("-",$result['parent_id']);
            $tbl = $stdrds[0];
            
            $tbls = array('sub_standards','standard_notation');
            
            if (in_array($tbl,$tbls)){
                $results = oer_std_get_parent_standard($result['parent_id']);
            }
    
        }
        return $results;
    }
}

/**
 * Get Standard By Id
 **/
if (!function_exists('oer_std_get_standard_by_id')){
    function oer_std_get_standard_by_id($id){
        global $wpdb;
        
        $std = null;
        
        $query = "SELECT * FROM {$wpdb->prefix}oer_core_standards WHERE id = %d";
        
        $standards = $wpdb->get_results($wpdb->prepare($query,$id));
        
        foreach($standards as $standard){
                $std = $standard;
        }
        
        return $std;
    }
}