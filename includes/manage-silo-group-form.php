<?php 
global $wpdb;
$success_msg = false;

$id = '';
$action = '';
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
if(isset($_GET['action']) && $_GET['action'] == 'edit'){
	$id = $_GET['silo_group'];
	$action = $_GET['action'];
}

if(isset($_POST['submit_silo_group'])){
	$group_name = trim($_POST['group_name']);
	$parent_post = $_POST['parent_post'];
	$child_posts = $_POST['child_posts'];
    $child_post_link=$_POST['child_post_link'];
	$current_time = current_time('mysql');
	$parent_post_primary_keyword=$_POST['parent_post_primary_keyword'];
    $child_post_variation_keyword=array();
    $child_post_primary_keyword=array();
    for ($i = 0; $i < 10; $i++) {
        if (isset($_POST['child_post_variation_keyword'.$i])) {
            $child_post_variation_keyword_single = $_POST['child_post_variation_keyword'.$i];
            $child_post_variation_keyword[]= $child_post_variation_keyword_single;
        } else {
            break;
        }
    }
    $imploded_child_post_variation_keyword = implode(', ', $child_post_variation_keyword);
    for ($i = 0; $i < 10; $i++) {
        if (isset($_POST['child_post_primary_keyword'.$i])) {
            $child_post_primary_keyword_single = $_POST['child_post_primary_keyword'.$i];
            $child_post_primary_keyword[]= $child_post_primary_keyword_single;
        } else {
            break;
        }
    }
    $imploded_child_post_primary_keyword = implode(', ', $child_post_primary_keyword);
	if($action == 'edit'){
		$id = $_POST['id'];
		$action = $_POST['action'];
		
		$sql = "UPDATE {$wpdb->prefix}silo_groups SET group_name = '$group_name', created_at = '$current_time', parent_post = '$parent_post', child_posts = '$child_posts',parent_post_primary_keyword='$parent_post_primary_keyword',child_post_primary_keyword='$imploded_child_post_primary_keyword',child_post_variation_keyword='$imploded_child_post_variation_keyword',linking_post_id='$child_post_link' WHERE id = '$id'";
		if($wpdb->query($sql)){
			$success_msg = 'Silo updated successfully.';
		}
	} else {
		$sql = "INSERT into {$wpdb->prefix}silo_groups SET group_name = '$group_name', created_at = '$current_time', parent_post = '$parent_post', child_posts = '$child_posts'";
		if($wpdb->query($sql)){
			$success_msg = 'Silo added successfully.';
		}
	}
}

$group_name = '';
$parent		= 0;
$parent_post = 0;
$child_posts = array();
if($action == 'edit'){
	$getSql = "SELECT * FROM {$wpdb->prefix}silo_groups WHERE id = '$id'";	
	$results = $wpdb->get_results($getSql);
	if(count($results) > 0){
		$group_name = $results[0]->group_name;
		$parent 	= $results[0]->parent;
		$parent_post 	= $results[0]->parent_post;
		$child_posts 	= json_decode($results[0]->child_posts, true);
		$parent_post_primary_keyword_value = $results[0]->parent_post_primary_keyword;
        $child_post_primary_keyword_emploade= $results[0]->child_post_primary_keyword;
        $child_post_variation_keyword_emploade = $results[0]->child_post_variation_keyword;
        $child_post_primary_keyword_values = explode(",", $child_post_primary_keyword_emploade);
        $child_post_variation_keyword_values=explode(",", $child_post_variation_keyword_emploade);
        $child_post_link_value=$results[0]->linking_post_id;
	} else { 
		die("invalid resource.");
	}
}
?>
<?php if($success_msg): ?>
<div class="notice notice-success is-dismissible">
    <p><?php echo $success_msg; ?></p>
</div>
<?php endif; ?>


<?php
$args = array(
	'post_type'=> 'post',
	'orderby'    => 'ID',
	'post_status' => 'publish',
	'order'    => 'DESC',
	'posts_per_page' => -1 
);
$result = new WP_Query( $args );
$all_posts = array();
if ( $result-> have_posts() ) : 
?>
<?php while ( $result->have_posts() ) : $result->the_post(); ?> 
<?php
global $post;
$all_posts[] = array('label' => '#'.$post->ID.'-'.get_the_title($post->ID), 'value' => $post->ID); 
?>
<?php endwhile; ?>
<?php endif; wp_reset_postdata(); ?>

<form method="post">
    <input type="hidden" value="<?php echo $id; ?>" name="id">
    <input type="hidden" value="<?php echo $action; ?>" name="action">
    <table class="form-table" role="presentation">
        <tbody>
            <tr class="form-field">
                <th scope="row"><label for="group_name">Silo Name<span style="color: red;">*</span> </label></th>
                <td><input name="group_name" type="text" id="group_name" value="<?php echo $group_name; ?>" required>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="parent_post">Parent Post</label></th>
                <td>
                    <input type="hidden" name="parent_post" id="parent_post" value="<?php echo $parent_post; ?>">
                    <button type="button" class="button hwe-choose_post hide-if-no-js">Choose Post</button>
                    <?php
				if(!empty($parent_post)){?>
                    <table class="table table-bordered silo">
                        <thead>
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th>Name</th>
                                <th>Primary Keyword</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo $parent_post?></td>
                                <td><?php echo get_the_title($parent_post)?></td>
                                <td><textarea name="parent_post_primary_keyword" class="parent_post_primary_keyword" rows="1" cols="20"><?php echo $parent_post_primary_keyword_value; ?></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <?php } ?>
                </td>
            </tr>
            <tr class="form-field">
                <th scope="row"><label for="child_posts">Child Posts</label></th>
                <td>
                <textarea style="display:none;" name="child_posts" id="child_posts"><?php echo json_encode($child_posts); ?></textarea>
					<button type="button" class="button hwe-choose_post hide-if-no-js">Choose Posts</button>
                    <?php if(!empty($child_posts)){?>
                    <table class="table table-bordered silo">
                        <thead>
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th>Name</th>
								<th>Primary Keyword</th>
                                <th>Variation Keyword</th>
                            </tr>
                        </thead>
                        <?php
                        $count=0;
                        sort($child_posts);
                foreach($child_posts as $child_post){
                ?>
               
                        <tbody>
                            <tr>
                                <td><input type="hidden" name="child_post" value="<?php echo $child_post?>"><?php echo $child_post?></td>
                                <td><?php echo get_the_title($child_post)?>
								<td>
                                    <textarea name="child_post_primary_keyword<?php echo $count ?>" id="child_post_primary_keyword" rows="1" cols="20"><?php echo $child_post_primary_keyword_values[$count]; ?></textarea>
                                </td>
                                <td><textarea name="child_post_variation_keyword<?php echo $count ?>" id="child_post_variation_keyword" rows="1" cols="20"><?php echo $child_post_variation_keyword_values[$count]; ?></textarea>
                                </td>
                            </tr>
                            <?php 
                                        $trimmedparentprimary = trim($parent_post_primary_keyword);
                                        $trimmedchildprimary = trim($child_post_primary_keyword_values[$count]);
                                        if(!empty($trimmedparentprimary)){
                                        if($trimmedparentprimary==$trimmedchildprimary && $count=='0'){
                                            $child_post_link_value=$child_post;
                                            $id = $_POST['id'];
                                            $sql = "UPDATE {$wpdb->prefix}silo_groups SET linking_post_id='$child_post_link_value' WHERE id = '$id'";
                                            if($wpdb->query($sql)){
                                                $success_msg = 'Silo updated successfully.';
                                            }
                                        }
                                        }
                            $count++; } ?>
                </td>
        </tbody>
    </table>
    <?php } ?>
    </td>
    </tr>
    <tr class="form-field">
                <th scope="row"><label for="silo_linking">Silo Link</label></th>
                <td>         
                    <textarea style="display:none;" name="child_post_link"
                        id="child_post_link"><?php echo $child_post_link_value; ?></textarea>
                    <button type="button" class="button hwe-choose_post hide-if-no-js">Choose Child Posts</button>
                    <?php
				if(!empty($child_post_link_value)){?>
                    <table class="table table-bordered silo">
                        <thead>
                            <tr>
                                <th style="width: 10%;">ID</th>
                                <th>Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo $child_post_link_value?></td>
                                <td><?php echo get_the_title($child_post_link_value)?></td>
                            </tr>
                        </tbody>
                    </table>
                    <?php } ?>
                </td>
    </tr>
    </tbody>
    </table>
    <p class="submit">
        <input type="submit" name="submit_silo_group" id="submit_silo_group" class="button button-primary"
            value="<?php if($action == 'edit') { echo 'Update'; }else { echo 'Add New'; }?> Silo">
    </p>
</form>
<?php include 'search-and-filter-post-model.php'; ?>