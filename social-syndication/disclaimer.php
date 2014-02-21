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
<link rel="stylesheet" href="<?php bloginfo('wpurl'); ?>/wp-content/plugins/social-syndication/css/style.css">
<div class="wrap">

	<h2>Social Syndication</h2>
	<h3>Welcome to Social Syndication 2.0 !</h3>

	<?php $terms = get_terms('networks',array('hide_empty'=> false));?>

	<?php if(empty($terms)) : ?>

		<div id="message" class="updated" style="padding: 10px;">
			<form action="" method="POST">
				It appears that you have no <a href="edit-tags.php?taxonomy=networks&post_type=syndication">Network</a> set up.<br>
				<input type="submit" name="createNetwork" id="createNetwork" value="Create supported networks">
			</form>
		</div>

	<?php  else: ?>

		<div id="configuration_set" class="clear">

			<?php foreach($terms as $term):?>

				<div class="configuration_block <?php if(get_option('fetch_'.$term->slug) == 'on') echo ' active'; ?>">
					<div class="configuration_line">
						<span class="label"><?php echo $term->name; ?>:</span> <span class="data"><strong><?php if(get_option('fetch_'.$term->slug) == 'on') echo 'Active'; else echo 'Inactive'; ?></strong></span>
					</div>
					<div class="configuration_line">
						<span class="label">Search:</span>
					<span class="data">
						<?php $searchs = get_option('hashtag_'.$term->slug);
						if(!empty($searchs) && $searchs[0] != '') :
							$search_terms = '';
							foreach($searchs as $search) {
								$search_terms .= $search.',';
							}
							$search_terms = substr($search_terms,0,-1);
							echo $search_terms;
						else : ?>
							-
						<?php endif; ?>
					</span>
					</div>
				</div>

			<?php endforeach; ?>
			<div class="clear"></div>

		</div>

	<?php endif; ?>

	<?php $fetch_time = get_option('fetch_time'); ?>
	<?php if(isset($fetch_time) && $fetch_time != ''): ?>
		<div class="refresh">Refreshing every <strong>
				<?php if($fetch_time == '1m'): ?>Minute<?php endif; ?>
				<?php if($fetch_time == '5m'): ?>5 Minutes<?php endif; ?>
				<?php if($fetch_time == '10m'): ?>10 Minutes<?php endif; ?>
				<?php if($fetch_time == '30m'): ?>30 Minutes<?php endif; ?>
				<?php if($fetch_time == '1h'): ?>Hour<?php endif; ?>
				<?php if($fetch_time == '3h'): ?>3 Hours<?php endif; ?>
				<?php if($fetch_time == '1j'): ?>Day<?php endif; ?>
			</strong>
		</div>
	<?php endif; ?>
	>> <a href="admin.php?page=ss_settings">Settings</a>

</div>