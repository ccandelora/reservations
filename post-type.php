<?
//create custom post types for Reservations
require_once(dirname(__FILE__) . '/meta-box-class/my-meta-box-class.php');

add_action( 'init', 'register_cpt_reservation' );
function register_cpt_reservation() {
	$labels = array( 
		'name' => _x( 'Reservations', 'reservation' ),
		'singular_name' => _x( 'Reservation', 'reservation' ),
		'add_new' => _x( 'Add New', 'reservation' ),
		'add_new_item' => _x( 'Add New Reservation', 'reservation' ),
		'edit_item' => _x( 'Edit Reservation', 'reservation' ),
		'new_item' => _x( 'New Reservation', 'reservation' ),
		'view_item' => _x( 'View Reservation', 'reservation' ),
		'search_items' => _x( 'Search Reservations', 'reservation' ),
		'not_found' => _x( 'No reservations found', 'reservation' ),
		'not_found_in_trash' => _x( 'No reservations found in Trash', 'reservation' ),
		'parent_item_colon' => _x( 'Parent Reservation:', 'reservation' ),
		'menu_name' => _x( 'Reservations', 'reservation' ),
	);
	$args = array( 
		'labels' => $labels,
		'hierarchical' => false,
		'description' => 'Rezervacije',
		'supports' => array( 'title', 'author'),
		'taxonomies' => array( 'Play Court' ),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 20,
		'menu_icon' => plugins_url('icon.svg', __FILE__),
		'show_in_nav_menus' => false,
		'publicly_queryable' => true,
		'exclude_from_search' => true,
		'has_archive' => false,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true,
		'capability_type' => 'post'
	);
	register_post_type( 'reservation', $args );
}

add_action( 'init', 'register_taxonomy_play_courts' );
function register_taxonomy_play_courts() {
	$labels = array( 
		'name' => _x( 'Play Courts', 'play_courts' ),
		'singular_name' => _x( 'Play Court', 'play_courts' ),
		'search_items' => _x( 'Search Play Courts', 'play_courts' ),
		'popular_items' => _x( 'Popular Play Courts', 'play_courts' ),
		'all_items' => _x( 'All Play Courts', 'play_courts' ),
		'parent_item' => _x( 'Parent Play Court', 'play_courts' ),
		'parent_item_colon' => _x( 'Parent Play Court:', 'play_courts' ),
		'edit_item' => _x( 'Edit Play Court', 'play_courts' ),
		'update_item' => _x( 'Update Play Court', 'play_courts' ),
		'add_new_item' => _x( 'Add New Play Court', 'play_courts' ),
		'new_item_name' => _x( 'New Play Court', 'play_courts' ),
		'separate_items_with_commas' => _x( 'Separate play courts with commas', 'play_courts' ),
		'add_or_remove_items' => _x( 'Add or remove Play Courts', 'play_courts' ),
		'choose_from_most_used' => _x( 'Choose from most used Play Courts', 'play_courts' ),
		'menu_name' => _x( 'Play Courts', 'play_courts' ),
	);
	$args = array( 
		'labels' => $labels,
		'public' => true,
		'show_in_nav_menus' => false,
		'show_ui' => true,
		'show_tagcloud' => false,
		'hierarchical' => false,
		'rewrite' => true,
		'query_var' => true
	);
	register_taxonomy( 'play_courts', array('reservation'), $args );
}

//custom meta
if (is_admin()){
	$prefix = 'reservations_';
	
	$config = array(
		'id' => 'reservations_meta_box',	// meta box id, unique per meta box
		'title' => 'Reservations Meta Box',	// meta box title
		'pages' => array('reservation'),	// post types, accept custom post types as well, default is array('post'); optional
		'context' => 'normal',				// where the meta box appear: normal (default), advanced, side; optional
		'priority' => 'high',				// order of meta box: high (default), low; optional
		'fields' => array(),				// list of meta fields (can be added by field arrays)
		'local_images' => false,			// Use local or hosted images (meta box images for add/remove)
		'use_with_theme' => false			//change path if used with theme set to true, false for a plugin or anything else for a custom path(default false).
	);
	
	$my_meta =	new AT_Meta_Box($config);
	$my_meta->addDate($prefix.'date_field_id',array('name'=> 'Date of reservation ', 'format' =>'yy-mm-dd'));
	$my_meta->addTime($prefix.'time_from_field_id',array('name'=> 'From '));
	$my_meta->addTime($prefix.'time_until_field_id',array('name'=> 'Until '));
	$my_meta->Finish();
}

?>