<?php
// Register Syndication Post Type
add_action( 'init', 'register_cpt_syndication' );

function register_cpt_syndication() {

	$labels = array(
		'name' => _x( 'Syndications', 'syndication' ),
		'singular_name' => _x( 'Syndication', 'syndication' ),
		'add_new' => _x( 'Add New', 'syndication' ),
		'add_new_item' => _x( 'Add New Syndication', 'syndication' ),
		'edit_item' => _x( 'Edit Syndication', 'syndication' ),
		'new_item' => _x( 'New Syndication', 'syndication' ),
		'view_item' => _x( 'View Syndication', 'syndication' ),
		'search_items' => _x( 'Search Syndications', 'syndication' ),
		'not_found' => _x( 'No syndications found', 'syndication' ),
		'not_found_in_trash' => _x( 'No syndications found in Trash', 'syndication' ),
		'parent_item_colon' => _x( 'Parent Syndication:', 'syndication' ),
		'menu_name' => _x( 'Syndications', 'syndication' ),
	);

	$args = array(
		'labels' => $labels,
		'hierarchical' => false,

		'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
		'taxonomies' => array( 'networks' ),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 5,
		'menu_icon' => plugins_url().'/social-syndication/img/icon-16.png',
		'show_in_nav_menus' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'has_archive' => true,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => array( 'slug' => 'syndication' ),
		'capability_type' => 'post'
	);

	register_post_type( 'syndication', $args );
}



add_action( 'admin_head', 'wpt_syndication_icons' );
function wpt_syndication_icons() {
	?>
	<style type="text/css" media="screen">
		#icon-edit.icon32-posts-syndication {background: url('<?php echo plugins_url(); ?>/social-syndication/img/icon-32.png') no-repeat;}
	</style>
<?php }


// Register  Networks Taxonomy
add_action( 'init', 'register_taxonomy_networks' );

function register_taxonomy_networks() {

	$labels = array(
		'name' => _x( 'Networks', 'networks' ),
		'singular_name' => _x( 'Network', 'networks' ),
		'search_items' => _x( 'Search Networks', 'networks' ),
		'popular_items' => _x( 'Popular Networks', 'networks' ),
		'all_items' => _x( 'All Networks', 'networks' ),
		'parent_item' => _x( 'Parent Network', 'networks' ),
		'parent_item_colon' => _x( 'Parent Network:', 'networks' ),
		'edit_item' => _x( 'Edit Network', 'networks' ),
		'update_item' => _x( 'Update Network', 'networks' ),
		'add_new_item' => _x( 'Add New Network', 'networks' ),
		'new_item_name' => _x( 'New Network', 'networks' ),
		'separate_items_with_commas' => _x( 'Separate networks with commas', 'networks' ),
		'add_or_remove_items' => _x( 'Add or remove networks', 'networks' ),
		'choose_from_most_used' => _x( 'Choose from the most used networks', 'networks' ),
		'menu_name' => _x( 'Networks', 'networks' ),
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_ui' => true,
		'show_tagcloud' => true,
		'hierarchical' => true,

		'rewrite' => true,
		'query_var' => true
	);

	register_taxonomy( 'networks', array('syndication'), $args );
}

// Register Search Taxonomy
add_action( 'init', 'register_taxonomy_search' );

function register_taxonomy_search() {

	$labels = array(
		'name' => _x( 'Search', 'search' ),
		'singular_name' => _x( 'Search', 'search' ),
		'search_items' => _x( 'Search Search', 'search' ),
		'popular_items' => _x( 'Popular Search', 'search' ),
		'all_items' => _x( 'All Search', 'search' ),
		'parent_item' => _x( 'Parent Search', 'search' ),
		'parent_item_colon' => _x( 'Parent Search:', 'search' ),
		'edit_item' => _x( 'Edit Search', 'search' ),
		'update_item' => _x( 'Update Search', 'search' ),
		'add_new_item' => _x( 'Add New Search', 'search' ),
		'new_item_name' => _x( 'New Search', 'search' ),
		'separate_items_with_commas' => _x( 'Separate search with commas', 'search' ),
		'add_or_remove_items' => _x( 'Add or remove Search', 'search' ),
		'choose_from_most_used' => _x( 'Choose from most used Search', 'search' ),
		'menu_name' => _x( 'Search', 'search' ),
	);

	$args = array(
		'labels' => $labels,
		'public' => true,
		'show_in_nav_menus' => true,
		'show_ui' => true,
		'show_tagcloud' => true,
		'show_admin_column' => false,
		'hierarchical' => false,

		'rewrite' => true,
		'query_var' => true
	);

	register_taxonomy( 'search', array('syndication'), $args );
}
?>