<?php
/*
Plugin Name: Circle Jerk Silo
Plugin URI: https://cnelindia.com
Description: This is a Circle Jerk Silo plugin.
Version: 1.0
Author: Siyaram Malav
Author URI:  https://cnelindia.com
*/

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}
/* ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); */
class Silo_List extends WP_List_Table {

	/** Class constructor */
	public function __construct() {

		parent::__construct( [
			'singular' => __( 'Silo', 'sp' ), //singular name of the listed records
			'plural'   => __( 'Silo', 'sp' ), //plural name of the listed records
			'ajax'     => false //does this table support ajax?
		] );

	}


	/**
	 * Retrieve customers data from the database
	 *
	 * @param int $per_page
	 * @param int $page_number
	 *
	 * @return mixed
	 */
	public static function get_silo_groups( $per_page = 5, $page_number = 1 ) {

		global $wpdb;

		$sql = "SELECT * FROM {$wpdb->prefix}silo_groups";

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		} else {
			$sql .= ' ORDER BY created_at DESC';
		}
			

		$sql .= " LIMIT $per_page";
		$sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page;


		$result = $wpdb->get_results( $sql, 'ARRAY_A' );

		return $result;
	}


	/**
	 * Delete a customer record.
	 *
	 * @param int $id customer ID
	 */
	public static function delete_silo_group( $id ) {
		global $wpdb;

		$wpdb->delete(
			"{$wpdb->prefix}silo_groups",
			[ 'id' => $id ],
			[ '%d' ]
		);
	}


	/**
	 * Returns the count of records in the database.
	 *
	 * @return null|string
	 */
	public static function record_count() {
		global $wpdb;

		$sql = "SELECT COUNT(*) FROM {$wpdb->prefix}silo_groups";

		return $wpdb->get_var( $sql );
	}


	/** Text displayed when no customer data is available */
	public function no_items() {
		_e( 'No silo groups avaliable.', 'sp' );
	}


	/**
	 * Render a column when no column specific method exist.
	 *
	 * @param array $item
	 * @param string $column_name
	 *
	 * @return mixed
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'group_name':
				return $item[ $column_name ];
			//case 'city':
				//return $item[ $column_name ];
			default:
				return print_r( $item, true ); //Show the whole array for troubleshooting purposes
		}
	}

	/**
	 * Render the bulk edit checkbox
	 *
	 * @param array $item
	 *
	 * @return string
	 */
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}


	/**
	 * Method for name column
	 *
	 * @param array $item an array of DB data
	 *
	 * @return string
	 */
	function column_name( $item ) {

		$delete_nonce = wp_create_nonce( 'hwe_delete_silo_group' );

		$title = '<strong>' . $item['group_name'] . '</strong>';

		$actions = [
			'edit' => sprintf( '<a href="?page=%s&silo_group=%s&action=%s">Edit</a>', 'manage_silo_group', absint( $item['id'] ), 'edit' ),
			'view' => sprintf( '<a href="?page=%s&silo_group=%s&action=%s">View</a>', 'manage_silo_group', absint( $item['id'] ), 'view' ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&silo_group=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];

		return $title . $this->row_actions( $actions );
	}


	/**
	 *  Associative array of columns
	 *
	 * @return array
	 */
	function get_columns() {
		$columns = [
			'cb'      => '<input type="checkbox" />',
			'name'    => __( 'Silo Name', 'sp' ),
			//'address' => __( 'Address', 'sp' ),
			//'city'    => __( 'City', 'sp' )
		];

		return $columns;
	}


	/**
	 * Columns to make sortable.
	 *
	 * @return array
	 */
	public function get_sortable_columns() {
		$sortable_columns = array(
			'group_name' => array( 'group_name', true ),
			//'city' => array( 'city', false )
		);

		return $sortable_columns;
	}

	/**
	 * Returns an associative array containing the bulk action
	 *
	 * @return array
	 */
	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
	}


	/**
	 * Handles data query and filter, sorting, and pagination.
	 */
	public function prepare_items() {

		$this->_column_headers = $this->get_column_info();

		/** Process bulk action */
		$this->process_bulk_action();

		$per_page     = $this->get_items_per_page( 'silo_groups_per_page', 5 );
		$current_page = $this->get_pagenum();
		$total_items  = self::record_count();

		$this->set_pagination_args( [
			'total_items' => $total_items, //WE have to calculate the total number of items
			'per_page'    => $per_page //WE have to determine how many items to show on a page
		] );

		$this->items = self::get_silo_groups( $per_page, $current_page );
	}

	public function process_bulk_action() {

		//Detect when a bulk action is being triggered...
		if ( 'delete' === $this->current_action() ) {

			// In our file that handles the request, verify the nonce.
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );

			if ( ! wp_verify_nonce( $nonce, 'hwe_delete_silo_group' ) ) {
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_silo_group( absint( $_GET['silo_group'] ) );

				// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
				// add_query_arg() return the current url
				//wp_redirect( esc_url_raw(add_query_arg(array('page' => 'silo_groups'), admin_url('admin.php'))) );
				?>
				<script type="text/javascript">
				window.location.href = "<?php echo esc_url_raw(add_query_arg(array('page' => 'silo_groups'), admin_url('admin.php'))) ?>";
				</script>
				<?php
				exit;
			}

		}

		// If the delete bulk action is triggered
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );

			// loop over the array of record IDs and delete them
			foreach ( $delete_ids as $id ) {
				self::delete_silo_group( $id );

			}

			// esc_url_raw() is used to prevent converting ampersand in url to "#038;"
		    // add_query_arg() return the current url
		    //wp_redirect( esc_url_raw(add_query_arg()) );
			?>
			<script type="text/javascript">
			window.location.href = "<?php echo esc_url_raw(add_query_arg(array('page' => 'silo_groups'), admin_url('admin.php'))) ?>";
			</script>
			<?php
			exit;
		}
	}

}

require_once 'classes/class-show-post-ids.php';

class Circle_Jerk_Silo_Plugin {

	// class instance
	static $instance;

	// customer WP_List_Table object
	public $silo_groups_obj;
	public $posts_obj;

	// class constructor
	public function __construct() {
		add_filter( 'set_screen_option', [ __CLASS__, 'set_screen' ], 10, 3 );
		add_filter( 'set_screen_option', [ __CLASS__, 'set_screen_for_show_post_ids' ], 10, 3 );
		add_action( 'admin_menu', [ $this, 'plugin_menu' ] );
		add_action("wp_ajax_hwe_get_popup_posts", [ $this, 'hwe_get_popup_posts_data' ] );
	}


	public static function set_screen( $status, $option, $value ) {
		return $value;
	}
	
	public static function set_screen_for_show_post_ids( $status, $option, $value ) {
		return $value;
	}

	public function plugin_menu() {

		$hook = add_menu_page(
			'All Silo',
			'All Silo',
			'manage_options',
			'silo_groups',
			[ $this, 'all_silo_group_list_page' ]
		);
		
		add_action( "load-$hook", [ $this, 'screen_option' ] );
		
		add_submenu_page( 'silo_groups', 'Add Silo', 'Add New', 'manage_options', 'manage_silo_group', [ $this, 'manage_silo_group_form_page' ]);
		
		$hook2 = add_submenu_page( 'silo_groups', 'Show Post IDs', 'Show Post IDs', 'manage_options', 'show_post_ids', [ $this, 'show_post_ids_list_page' ]);
		add_action( "load-$hook2", [ $this, 'screen_option_show_post_ids' ] );
		//print_r($posts_obj);
		//add_submenu_page( 'silo_groups', 'Show Post IDs', 'Show Post IDs', 'manage_options', 'show_post_ids', [ $this, 'show_post_ids_list_page' ]);
		

	}

	function title_filter( $where, &$wp_query ){
		global $wpdb;
		if ( $search_term = $wp_query->get( 'search_prod_title' ) ) {
			$where .= ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( $wpdb->esc_like( $search_term ) ) . '%\'';
		}
		return $where;
	}

	public function all_silo_group_list_page() {
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">All Silo</h1>
			<a href="?page=manage_silo_group" class="page-title-action">Add New</a>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2" style="width:100%;">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->silo_groups_obj->prepare_items();
								$this->silo_groups_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}

	public function hwe_get_popup_posts_data(){
		
		$pages = $_POST["pages"];
		$ppp = $_POST["ppp"];
		$search_prod_title = $_POST["q"];
		$cat = $_POST["cat"];
		$popup_type = $_POST["popup_type"];
		$silo_group_id = $_POST["silo_group_id"];
		$parent_post = $_POST["parent_post"];
		$jsonData = stripslashes(html_entity_decode($_POST["child_posts"]));
		$child_posts = json_decode($jsonData, true);
		$child_post_link =$_POST["child_post_link"];
		
		header("Content-Type: text/html");
	
		$args = array(
			'post_type' => 'post',
			'posts_per_page' => $ppp,
			'orderby'    => 'ID',
			'post_status' => 'publish',
			'order'    => 'DESC',
			'search_prod_title' => $search_prod_title,
			'paged' => $pages,
		);
		
		if(!empty($cat)){
			$args['cat'] = $cat;
		}
		add_filter( 'posts_where', [ $this, 'title_filter' ], 10, 2 );
		$posts = new WP_Query( $args );
		remove_filter( 'posts_where', [ $this, 'title_filter' ], 10, 2 );
		

		if ( $posts-> have_posts() ) : 
			while ( $posts->have_posts() ) : $posts->the_post();
			global $post;
			?>
			<tr>
				<td style="border-right: 1px solid #ccc;text-align:center;">
					<input class="popup_post_ids" type="checkbox" value="<?php echo $post->ID; ?>" name="post_ids[]"
					<?php
					if($popup_type == 'parent_post'){
						if($parent_post == $post->ID){
							echo 'checked="checked"';	
						}
					}else if($popup_type == 'child_post_link'){
						if($child_post_link==$post->ID){
							echo 'checked="checked"';	
						}
					}else {
						if(in_array($post->ID, $child_posts)){
							echo 'checked="checked"';	
						}
					} 
					?>
					/>
				</td>
				<td style="border-right: 1px solid #ccc;text-align:center;"><?php echo $post->ID; ?></td>
				<td><?php echo get_the_title($post->ID); ?></td>
			</tr>
			<?php
			endwhile;
		endif;
		wp_reset_postdata();
	}
	
	public function show_post_ids_list_page() {
		ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
		?>
		<div class="wrap">
			<h1 class="wp-heading-inline">Show Post Ids</h1>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2" style="width:100%;">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->posts_obj->prepare_items();
								$this->posts_obj->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
	}
	
	public function manage_silo_group_form_page() {
		$action_label = '';
		if(($_GET['action']=='edit') && !empty($_GET['action'])){
			$action_label = 'Edit';
			?>
			<div class="wrap">
			<h1 class="wp-heading-inline"><a href="<?php echo esc_url_raw(add_query_arg(array('page' => 'silo_groups'), admin_url('admin.php'))); ?>" style="text-decoration:none;">&#x2190; </a><?php echo $action_label; ?> Silo</h1>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2" style="width:100%;">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">

							<?php include 'includes/manage-silo-group-form.php'; ?>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
		}elseif(($_GET['action']=='view') && !empty($_GET['action'])){
			$action_label = 'View';
			?>
			<div class="wrap">
				<h1 class="wp-heading-inline"><a href="<?php echo esc_url_raw(add_query_arg(array('page' => 'silo_groups'), admin_url('admin.php'))); ?>" style="text-decoration:none;">&#x2190; </a><?php echo $action_label; ?> Silo</h1>
				<div id="poststuff">
					<div id="post-body" class="metabox-holder columns-2" style="width:100%;">
						<div id="post-body-content">
							<div class="meta-box-sortables ui-sortable">
								<?php include 'includes/view-silo-group.php'; ?>
							</div>
						</div>
					</div>
					<br class="clear">
				</div>
			</div>
			<?php
		}else {
			$action_label = 'Add';
			?>
			<div class="wrap">
			<h1 class="wp-heading-inline"><a href="<?php echo esc_url_raw(add_query_arg(array('page' => 'silo_groups'), admin_url('admin.php'))); ?>" style="text-decoration:none;">&#x2190; </a><?php echo $action_label; ?> Silo</h1>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2" style="width:100%;">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">

							<?php include 'includes/manage-silo-group-form.php'; ?>
						</div>
					</div>
				</div>
				<br class="clear">
			</div>
		</div>
	<?php
		}
	}
	

	/**
	 * Screen options
	 */
	public function screen_option() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Silo',
			'default' => 20,
			'option'  => 'silo_groups_per_page'
		];

		add_screen_option( $option, $args );

		$this->silo_groups_obj = new Silo_List();
	}
	
	public function screen_option_show_post_ids() {

		$option = 'per_page';
		$args   = [
			'label'   => 'Posts',
			'default' => 20,
			'option'  => 'posts_per_page'
		];

		add_screen_option( $option, $args );

		$this->posts_obj = new ShowPostIds_List();
	}


	/** Singleton instance */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

}


add_action( 'plugins_loaded', function () {
	Circle_Jerk_Silo_Plugin::get_instance();
} );


function circle_jerk_silo_adds_to_the_head() { 
	wp_register_script( 'circle-jerk-silo-js', plugin_dir_url( __FILE__ ) . 'js/script.js', array('jquery'),'',true  ); 
    wp_register_style( 'circle-jerk-silo-css', plugin_dir_url( __FILE__ ) . 'css/style.css','','', 'screen' ); 
    
	wp_localize_script( 'circle-jerk-silo-js', 'circle_jerk_silo_object',
		array( 
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'loading_image' => plugin_dir_url( __FILE__ ).'images/loading.gif'
		)
	);
  
	wp_enqueue_script( 'circle-jerk-silo-js' ); 
    wp_enqueue_style( 'circle-jerk-silo-css' ); 
}
add_action( 'admin_enqueue_scripts', 'circle_jerk_silo_adds_to_the_head' ); 
?>
