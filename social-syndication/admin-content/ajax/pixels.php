<?php
/**
 * Created by JetBrains PhpStorm.
 * User: ljouanneau
 * Date: 13-06-03
 * Time: 14:24
 * To change this template use File | Settings | File Templates.
 */

/** Loads the WordPress Environment and Template */
require_once(__DIR__.'/../../../../../wp-load.php' );

if(!isset($_POST['action']) || !isset($_POST['post']))
	die('Wrong parameters');

if($_POST['action'] == 'delete'){
	$delete = wp_delete_post($_POST['post'],true);
	if($delete === false)
		die('0');
	else
		die('1');
}
else if($_POST['action'] == 'publish'){
	$current_post = get_post( $_POST['post'], 'ARRAY_A' );
	$current_post['post_status'] = 'publish';
	$publish = wp_update_post($current_post);
	if($publish === 0)
		die('0');
	else
		die('1');
}

?>