<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
<?php 
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
global $wpdb;
$id = '';
if(isset($_GET['action']) && $_GET['action'] == 'view'){
	$id = $_GET['silo_group'];
}
$group_name = '';
$group_keyword = '';
$parent_post = 0;
$child_posts = array();
	$getSql = "SELECT * FROM {$wpdb->prefix}silo_groups WHERE id = '$id'";	
	$results = $wpdb->get_results($getSql);
	if(count($results) > 0){
		$group_name = $results[0]->group_name;
		$group_keyword=$results[0]->group_keywords;
		$parent_post 	= $results[0]->parent_post;
		$child_posts 	= $results[0]->child_posts;
        $child_posts_replace=str_replace(array('[',']','"'),'',$child_posts);
        $child_posts_name=explode(',',$child_posts_replace);
		$parent_post_primary_keyword_value = $results[0]->parent_post_primary_keyword;
		$child_post_primary_keyword_emploade= $results[0]->child_post_primary_keyword;
        $child_post_variation_keyword_emploade = $results[0]->child_post_variation_keyword;
        $child_post_primary_keyword_values = explode(",", $child_post_primary_keyword_emploade);
        $child_post_variation_keyword_values=explode(",", $child_post_variation_keyword_emploade);
		$linking_post_id=$results[0]->linking_post_id;
	} else {
		die("invalid resource.");
	}
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
<?php if(!empty($parent_post_primary_keyword_value) or !empty($child_post_primary_keyword_emploade)){ ?>
<table class="form-table" role="presentation">
		<tbody>
		<tr>
		<th scope="row"><label for="group_name">Silo Name</label></th>
		</tr>
			<tr class="form-field">
				<td><?php echo $group_name; ?></td>
			</tr>
			<tr>
			<th scope="row"><label for="group_name">Link Status</label></th>
		</tr>
			<tr class="form-field">
				<?php if(!empty($linking_post_id) && !empty($parent_post_primary_keyword_value)){?>
				<td><button type="button" class="btn btn-warning">Keyword Linked <?php echo 'ID:'.$linking_post_id;?></button></td>
				<?php }else{ ?>
				<td><button type="button" class="btn btn-secondary disabled">Not Linked</button></td>	
					<?php }?>
			</tr>
			<tr>
			<th scope="row"><label for="parent_post">Parent Post</label></th>
		</tr>
			<tr class="form-field">
				<td>
                <?php if(!empty($parent_post)){?>
                <table class="table table-bordered silo">
				<thead>
				<tr>
					<th style="width: 4%;">ID</th>
					<th>Name</th> 
					<th>Primary Keyword</th>
					<th>Linked Child</th>
					<th>ID</th>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td><?php echo $parent_post; ?></td>
					<td><?php echo get_the_title($parent_post);?></td>
					<td><?php echo $parent_post_primary_keyword_value; ?></td>
					<td><?php if($linking_post_id){?>
						<button type="button" class="btn btn-success">Keyword Linked</button>
						<?php }else if(empty($parent_post_primary_keyword_value)){?>
						<button type="button" class="btn btn-secondary disabled">Not Linked</button>
						<?php }?>
					</td>
					<td><?php echo $linking_post_id;?></td>
				</tr>
				</tbody>
				</table>
                <?php } ?>
                </td>
			</tr>
			<tr>
			<th scope="row"><label for="child_posts">Child Posts</label></th>
		</tr>
			<tr class="form-field">
				<td>
                <?php if(!empty($child_posts_name)){?>
                <table class="table table-bordered silo">
				<thead>
				<tr>
					<th style="width: 4%;">ID</th>
					<th>Name</th> 
					<th>Linked Parent</th> 
					<th>Primary Keyword</th>
					<th>Linked Forward</th>
					<th style="width: 4%;">ID</th>
					<th>Variation Keyword</th>
					<th>Linked Backward</th>
					<th style="width: 4%;">ID</th>
				</tr>
				</thead>
                <?php
				$count =0;
				$checkprimarykeyword=array();
				$checkvariantkeyword=array();
				sort($child_posts_name);
                foreach($child_posts_name as $child_post_name){
					$childprimarypostid='';
					$childvariantpostid='';
					$trimmedparentprimary = trim($parent_post_primary_keyword_value);
					$trimmedchildprimary = trim($child_post_primary_keyword_values[$count]);
					foreach($child_posts_name as $single_child_post_name ){
						$singlechildprimary = trim($child_post_primary_keyword_values[$count]);
						if($trimmedchildprimary==$singlechildprimary){
							$forward_postid=$checkprimarykeyword[$trimmedchildprimary];
							//print_r($forward_postid);
							$child_posts_name['forward_post_id'];
						}
					}
					$trimmedchildvariation = trim($child_post_variation_keyword_values[$count]);
					if (array_key_exists($trimmedchildprimary, $checkprimarykeyword))
					{
						$childprimarypostid=$checkprimarykeyword[$trimmedchildprimary];
						$checkprimarykeyword[$trimmedchildprimary]=$child_post_name;
					}
					else
					{
						$checkprimarykeyword[$trimmedchildprimary]=$child_post_name;
					}
					if (array_key_exists($trimmedchildvariation, $checkvariantkeyword))
					{
						$childvariantpostid=$checkvariantkeyword[$trimmedchildvariation];
						$checkvariantkeyword[$trimmedchildvariation]=$child_post_name;
					}
					else
					{
						$checkvariantkeyword[$trimmedchildvariation]=$child_post_name;
					}
                ?>
				<tbody>
				<tr>
					<td><?php echo $child_post_name?></td>
					<td><?php echo get_the_title($child_post_name)?>
					<td><?php if($trimmedparentprimary==$trimmedchildprimary && $count =='0'){?>
						<button type="button" class="btn btn-success">Keyword Linked</button>
						<?php }elseif($trimmedparentprimary==$trimmedchildprimary){?>
							<button type="button" class="btn btn-success">Keyword Linked</button>
						<?php }else{?>
						<button type="button" class="btn btn-secondary disabled">Not Linked</button>
						<?php }?>
					</td>
					<td><?php echo $child_post_primary_keyword_values[$count]; ?></td>
					<td><?php if(!empty($childprimarypostid)){?>
						<button type="button" class="btn btn-success">Keyword Linked</button>
					<?php }else{?>
						<button type="button" class="btn btn-secondary disabled">Not Linked</button>
						<?php }?>
					</td>
					<td><?php echo $childprimarypostid?></td>
					<td><?php echo $child_post_variation_keyword_values[$count]; ?></td>
					<td><?php if(!empty($childvariantpostid)){?>
						<button type="button" class="btn btn-success">Keyword Linked</button>
					<?php }else{?>
						<button type="button" class="btn btn-secondary disabled">Not Linked</button>
						<?php }?></td>
					<td><?php echo $childvariantpostid;?></td>
				</tr>
                <?php $count++;} ?>
				</td>
				</tbody>
				</table>
                <?php } ?>
			</tr>
		</tbody>
	</table>
	<?php }else{
		$allchildpostname=array();
		$parentpostname=get_post_field('post_name',$parent_post);
		foreach($child_posts_name as $childpostnames){
			$childpostname=get_post_field('post_name',$childpostnames);
			$allchildpostname[]=$childpostname;
		}
		$keywordVariations = $allchildpostname;
		$mainKeyword = $parentpostname;
		$uniqueTitles = array();
		foreach ($keywordVariations as $variation) {
			$uniqueTitles[] = str_replace($group_name, $mainKeyword, $variation);
		}
		function generateInlineCTAs($targetPageURL, $titles) {
			$output = '';
			$output .= '<table class="table table-bordered silo withoutkeyword">
			<thead>
			<tr>
				<th>Unique Titles</th> 
				<th>Linked</th> 
			</tr>
			</thead>
			<tbody>';
			$keywordCounts = array_count_values($titles);
			$uniqueKeyword = null;
			foreach ($keywordCounts as $keyword => $count) {
				if ($count === 1) {
					$uniqueKeyword = $keyword;
					break;
				}
			}
			$post = get_page_by_path($uniqueKeyword, OBJECT, 'post');
			$post_id = $post->ID;
			$targetPageURL = home_url('/') . $uniqueKeyword;
				$output .= '
				<tr>
				<td>
				' . $uniqueKeyword . '
				</td>
				<td>
				<a href="' . $targetPageURL . '">' . $post_id . '</a>
				</td>
				</tr>';
			$output .= '</tbody>
				</table>';
			return $output;
		}
		
		$inlineCTAs = generateInlineCTAs($targetPageURL, $uniqueTitles);
		
		echo $inlineCTAs;
	}?>
	<div>
	<a class="btn btn-primary" style="float: right;" href="?page=manage_silo_group&silo_group=<?php echo $id; ?>&action=edit">Edit Silo</a>
	</div>
    <?php include 'search-and-filter-post-model.php'; ?>