<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ljouanneau
 * Date: 13-01-28
 * Time: 15:13
 * To change this template use File | Settings | File Templates.
 */
/*if(!class_exists('RW_Meta_Box'))
	require_once('rilwis-meta-box/meta-box.php');*/
// BANDS - Members
//$band_members = get_posts(array('post_type' => 'band_member', 'posts_per_page' => -1));
//$band_members_options = array();
//foreach($band_members as $band_member){
//	$band_members_options[$band_member->ID] = $band_member->post_title;
//}
//$meta_boxes[] = array(
//	// Meta box id, UNIQUE per meta box. Optional since 4.1.5
//	'id' => 'band-members',
//	// Meta box title - Will appear at the drag and drop handle bar. Required.
//	'title' => 'Band Members',
//	// Post types, accept custom post types as well - DEFAULT is array('post'). Optional.
//	'pages' => array( 'band' ),
//	// Where the meta box appear: normal (default), advanced, side. Optional.
//	'context' => 'normal',
//	// Order of meta box: high (default), low. Optional.
//	'priority' => 'high',
//	// List of meta fields
//	'fields' => array(
//		// SELECT BOX
//		array(
//			'name'     => 'Member',
//			'id'       => "band-members",
//			'type'     => 'select',
//			// Array of 'value' => 'Label' pairs for select box
//			'options'  => $band_members_options,
//			// Select multiple values, optional. Default is false.
//			'multiple' => true
//		)
//	)
//);

// SYNDICATION
$meta_boxes[] = array(
	'id' => 'syndication',
	'title' => 'Syndication',
	'pages' => array( 'syndication' ),
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array (
		array(
			'name'	=> 'Syndication id',
			'id'	=> "element-id",
			'type'	=> 'text'
		),
		array(
			'name'	=> 'Syndication url',
			'id'	=> "shared-link",
			'type'	=> 'text'
		),
		array(
            'name'	=> 'Author id',
            'id'	=> "author-id",
            'type'	=> 'text'
        ),
		array (
            'name'	=> 'Author name',
            'id'	=> "author-name",
            'type'	=> 'text'
        ),
        array (
            'name'  => 'Location',
            'id'    => "location",
            'type'  => 'text'
        )
	)
);

$meta_boxes[] = array(
	'id' => 'json',
	'title' => 'Json',
	'pages' => array('syndication'),
	'context' => 'normal',
	'priority' => 'high',
	'fields' => array(
		array(
            'name' => 'Result',
            'id' => 'json-result',
            'type' => 'textarea'
        )
	)
);

/********************* META BOX REGISTERING ***********************/

/**
 * Register meta boxes
 *
 * @return void
 */
if(!function_exists('register_meta_boxes')){
	include('rilwis-meta-box/meta-box.php');
	function register_meta_boxes()
	{
		// Make sure there's no errors when the plugin is deactivated or during upgrade
		if ( !class_exists( 'RW_Meta_Box' ) )
			return;

		global $meta_boxes;
		foreach ( $meta_boxes as $meta_box )
		{
			new RW_Meta_Box( $meta_box );
		}
	}
}
// Hook to 'admin_init' to make sure the meta box class is loaded before
// (in case using the meta box class in another plugin)
// This is also helpful for some conditionals like checking page template, categories, etc.
add_action( 'admin_init', 'register_meta_boxes' );