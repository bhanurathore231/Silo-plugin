<div id="searchFilterPostsModal" class="modal">

  <div class="modal-content">
    <div class="modal-header">
      <span class="close">&times;</span>
      <h2>Choose Posts</h2>
    </div>
    <div class="modal-body">
		<div class="search-panel">
			<div class="form-group col-md-6">
				<button type="button" class="button button-primary" id="save_selected_posts">Save Checked</button>
				<input type="text" class="form-control" id="search_by_title" placeholder="Search by title" />
				<input type="hidden" id="popup_type" /> 
				<input type="hidden" id="silo_group_id" value="<?php echo $id; ?>" /> 
			</div>
			<div class="form-group col-md-4">
				<select class="form-control" id="filterByCategory">
					<option value="0">Filter by Category</option>
					<?php
					$categories = get_categories();
					foreach($categories as $category) {
					?>
					<option value="<?php echo $category->term_id; ?>"><?php echo $category->name; ?></option>
					<?php
					}
					?>
				</select>
			</div>
			<div class="form-group col-md-2">
				<button class="button button-primary" type="button" id="filter_post_list" style="line-height: 2;margin-top: -3px;">Filter</button>
			</div>	
		</div>
		<div class="datalist-wrapper">
			<!-- Loading overlay -->
			<!-- <div class="loading-overlay"><div class="overlay-content">Loading...</div></div> -->
			
			<!-- Data list container -->
			<div id="dataContainer">
				<table id="popup_post_list" class="table table-striped">
					<thead>
						<tr>
							<th style="border-right: 1px solid #ccc;text-align:center;"><input onclick="hweclickCheckedAll(this);" type="checkbox" class="clickCheckedAll" /></th>
							<th style="border-right: 1px solid #ccc;text-align:center;width:10%;">ID</th>
							<th>Post Title</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
    </div>
    <div class="modal-footer">
		<div class="pagination" style="text-align: center;">
			<button id="more_posts">Load More</button>	
		</div>
    </div>
  </div>
</div>
<script type="text/javascript">
var searchFilterPostsModal = document.getElementById("searchFilterPostsModal");

// Get the button that opens the modal
var hweChoosePostBTOne = document.getElementsByClassName("hwe-choose_post")[0];
var hweChoosePostBTTwo = document.getElementsByClassName("hwe-choose_post")[1];
var hweChoosePostBTThree = document.getElementsByClassName("hwe-choose_post")[2];

// Get the <span> element that closes the modal
var hweClose = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
hweChoosePostBTOne.onclick = function() {
	jQuery(document).find('.clickCheckedAll').prop('checked',false);
	jQuery(document).find('#popup_type').val('parent_post');
	
	jQuery('#search_by_title').val('');
	jQuery('#filterByCategory').prop('selectedIndex',0);
	jQuery("#popup_post_list tbody").empty();
	hwe_load_popup_posts();
  	searchFilterPostsModal.style.display = "block";
}

hweChoosePostBTTwo.onclick = function() {
	jQuery(document).find('.clickCheckedAll').prop('checked',false);
	jQuery(document).find('#popup_type').val('child_posts');

	jQuery('#search_by_title').val('');
	jQuery('#filterByCategory').prop('selectedIndex',0);
	jQuery("#popup_post_list tbody").empty();
	hwe_load_popup_posts();
  	searchFilterPostsModal.style.display = "block";
}
hweChoosePostBTThree.onclick = function() {
	jQuery(document).find('.clickCheckedAll').prop('checked',false);
	jQuery(document).find('#popup_type').val('child_post_link');

	jQuery('#search_by_title').val('');
	jQuery('#filterByCategory').prop('selectedIndex',0);
	jQuery("#popup_post_list tbody").empty();
	hwe_load_popup_posts();
  	searchFilterPostsModal.style.display = "block";
}
// When the user clicks on <span> (x), close the modal
hweClose.onclick = function() {
  searchFilterPostsModal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == searchFilterPostsModal) {
    searchFilterPostsModal.style.display = "none";
  }
}
var ajaxUrl = "<?php echo admin_url('admin-ajax.php')?>";
var page = 1; // What page we are on.
var ppp = 10; // Post per page

jQuery('#filter_post_list').click(function(){
	page = 1;
	jQuery("#popup_post_list tbody").empty();
	hwe_load_popup_posts();
});

jQuery(document).on("click", "#more_posts", function() {
	page++;
	hwe_load_popup_posts();
});


 // Do currentPage + 1, because we want to load the next page

function hwe_load_popup_posts(keyword = null, category = null) {
	var keyword = jQuery('#search_by_title').val();
	var category = jQuery('#filterByCategory').val();
	var popup_type = jQuery('#popup_type').val();
	var silo_group_id = jQuery('#silo_group_id').val();
	var parent_post = jQuery('#parent_post').val();
	var child_posts = jQuery('#child_posts').val();
	var child_post_link = jQuery('#child_post_link').val();
	
	jQuery("#more_posts").attr("disabled",true); // Disable the button, temp.
	jQuery.post(ajaxUrl, {
		action:"hwe_get_popup_posts",
		pages: page,
		ppp: ppp,
		q: keyword,
		cat: category,
		popup_type: popup_type,
		silo_group_id: silo_group_id,
		parent_post: parent_post,
		child_posts: child_posts,
		child_post_link:child_post_link
	}).success(function(posts){
		if(posts != 0){
			jQuery("#popup_post_list tbody").append(posts); // CHANGE THIS!
			jQuery("#more_posts").attr("disabled",false);
		} 
		
	});
}


function hweclickCheckedAll(t){
	if(jQuery(t).is(":checked")){
		jQuery(document).find('.popup_post_ids').prop('checked',true);
	} else {
		jQuery(document).find('.popup_post_ids').prop('checked', false); 
	}
}

jQuery(document).find('.clickCheckedAll').prop('checked',false);
jQuery('#search_by_title').val('');

jQuery(document).on("click", "#save_selected_posts", function() {
	var $selectedPopupPost = [];
	jQuery(".popup_post_ids:checked").each(function(){
		$selectedPopupPost.push(jQuery(this).val());
	});

	var popup_type = jQuery('#popup_type').val();

	if(popup_type == 'child_posts'){
		jQuery('#child_posts').val(JSON.stringify($selectedPopupPost));
	}else if(popup_type == 'child_post_link'){
		jQuery('#child_post_link').val($selectedPopupPost[0]);
	}else {
		jQuery('#parent_post').val($selectedPopupPost[0]);
	}

	alert('Saved!');
});

</script>	