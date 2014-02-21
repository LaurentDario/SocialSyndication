<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

global $social_syndication;
// Creating supported terms
if(isset($_POST['createNetwork'])){
	$social_syndication->createTerms();
	header('Location:options-general.php?page=social_syndication');
}

?>
<link rel="stylesheet" href="<?php echo plugins_url(); ?>/social-syndication/css/style.css">
<script src="<?php echo plugins_url(); ?>/social-syndication/js/jquery.isotope.min.js"></script>
<script>
	var plugin_folder = '<?php echo plugins_url().'/social-syndication/';?>';

	jQuery(document).ready(function($){

		$('#pixels_grid').isotope({
			// options
			itemSelector: '.pixels_element',
			masonry: {
				columnWidth: 50
			},
			getSortData : {
				// ...
				status : function ( $elem ) {
					return $elem.attr('data-status');
				}
			}
		});
		$('#pixels_grid').isotope({
			sortBy : 'status',
			sortAscending : false,
			filter: '.pending,.draft,.private'
		});

		$('.filter').on('click',function(){
			$('#pixels_grid').isotope({ filter : $(this).attr('data-filter') });
		});

		$('#pixels_grid').on('click','.pixels_element',function(){
			$(this).toggleClass('active');
			$('#pixels_grid').isotope( 'reLayout' );
		});

		$('.pixels_element').on('click','.publish',function(){
			$pixels_element = $(this).parent().parent();
			if($pixels_element.hasClass('publishing')) return false;
			$pixels_element.addClass('publishing');
			var request = jQuery.ajax({
				url : plugin_folder + "admin-content/ajax/pixels.php",
				type : "POST",
				data: {
					action: 'publish',
					post: $pixels_element.attr('data-id')
				}
			});
			request.done(function(msg) {
				$pixels_element.removeClass('draft');
				$pixels_element.removeClass('private');
				$pixels_element.removeClass('pending-review');
				$pixels_element.removeClass('active');
				$pixels_element.removeClass('publishing');
				$pixels_element.addClass('publish');
				$('#pixels_grid').isotope( 'reLayout' );
			});
		});


		$('.pixels_element').on('click','.delete-ss',function(){
			$pixels_element = $(this).parent().parent();
			if($pixels_element.hasClass('deleting')) return false;
			$pixels_element.addClass('deleting');
			var request = jQuery.ajax({
				url : plugin_folder + "admin-content/ajax/pixels.php",
				type : "POST",
				data: {
					action: 'delete',
					post: $pixels_element.attr('data-id')
				}
			});
			request.done(function(msg) {
				$pixels_element.remove();
				$('#pixels_grid').isotope( 'reLayout' );
			});
		});
	});
</script>
<div class="wrap">

	<h2>PiXels</h2>
	<a href="#" class="filter" data-filter="*">All</a>
	<a href="#" class="filter" data-filter=".publish">Published</a>
	<a href="#" class="filter" data-filter=".pending,.draft,.private">Waiting</a>

	<?php $terms = get_terms('networks',array('hide_empty'=> false));?>

	<?php if(empty($terms)) : ?>

		<div id="message" class="updated" style="padding: 10px;">
			<form action="" method="POST">
				It appears that you have no <a href="edit-tags.php?taxonomy=networks&post_type=syndication">Network</a> set up.<br>
				<input type="submit" name="createNetwork" id="createNetwork" value="Create supported networks">
			</form>
		</div>

	<?php endif; ?>

	<div id="pixels_grid">
		<?php

		$args = array(
			'numberposts'	=> 300,
			'post_type'		=> 'syndication',
			'post_status'	=> 'any',
			'orderby'		=> 'post_status'
		);
		$syndications = get_posts($args);
		foreach($syndications as $syndication): $networks = wp_get_post_terms($syndication->ID,'networks'); $network = $networks[0]; ?>
			<div class="pixels_element <?php echo $network->slug; ?> <?php echo $syndication->post_status; ?>" data-id="<?php echo $syndication->ID; ?>" data-status="<?php echo $syndication->post_status; ?>">
				<div class="overlay publishing">Publishing</div>
				<div class="overlay deleting">Deleting</div>
				<?php if($network->slug == 'instagram' || $network->slug == 'flickr') : ?>
					<?php echo get_the_post_thumbnail($syndication->ID); ?>
				<?php endif; ?>
				<div class="excerpt"><?php echo $syndication->post_content; ?></div>
				<div class="moderate">
					<div class="publish"></div>
					<div class="delete-ss"></div>
					<div class="original clear">
						From <a href="<?php echo get_post_meta($syndication->ID,'shared-link',true); ?>" target="_blank"><?php echo get_post_meta($syndication->ID,'author-name',true); ?> - <?php echo $network->name; ?></a>
						<br><a href="post.php?post=<?php echo $syndication->ID; ?>&action=edit" target="_blank">See Blog Post</a>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>

</div>