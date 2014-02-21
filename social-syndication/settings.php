<?php
ini_set('display_errors', 'On');
error_reporting(E_ALL);

global $social_syndication;
if(isset($_GET['settings-updated']) && $_GET['settings-updated'] == true) {
//    $social_syndication->fetchNetwork();
	wp_unschedule_event(wp_next_scheduled('ss_fetch'),'ss_fetch');
	wp_clear_scheduled_hook('ss_fetch');
	syndication_schedule();
	$social_syndication->registerSearches();
}
// Creating supported terms
if(isset($_POST['createNetwork'])){
	$social_syndication->createTerms();
	header('Location:options-general.php?page=social_syndication');
}

?>
<link rel="stylesheet" href="<?php bloginfo('wpurl'); ?>/wp-content/plugins/social-syndication/css/style.css">
<script>
	jQuery(document).ready(function($){
        $('body').on('click','.addMore',function(){
			var fieldset = $(this).parent(), searchfield = fieldset.find('input[type=text]').first(), addMore = $(this), clone = searchfield.clone();
			var deleteField = $('<div class="delete"></div><br>');
            addMore.before(clone.val(''));
			clone.after(deleteField);
        });
		$('body').on('click','.delete',function(){
			$(this).prev().remove();
			$(this).next().remove();
			$(this).remove();
		});
	});
</script>
<div class="wrap">

    <h2>Social Syndication</h2>

		<?php $terms = get_terms('networks',array('hide_empty'=> false));?>

		<?php if(empty($terms)) : ?>

			<div id="message" class="updated" style="padding: 10px;">
				<form action="" method="POST">
					It appears that you have no <a href="edit-tags.php?taxonomy=networks&post_type=syndication">Network</a> set up.<br>
					<input type="submit" name="createNetwork" id="createNetwork" value="Create supported networks">
				</form>
			</div>

		<?php endif; ?>

	<form method="post" action="options.php">
		<?php @settings_fields('social_syndication-group'); ?>
		<?php @do_settings_fields('social_syndication-group'); ?>

		<?php foreach($terms as $term) : ?>

        <fieldset style="width:200px; margin: 0 15px 15px 0; float: left;"<?php if(!in_array(strtolower($term->slug),array_map('strtolower', $social_syndication->support))) :?> disabled="disabled"<?php endif; ?>>
			<?php if(!in_array(strtolower($term->slug),array_map('strtolower', $social_syndication->support))) :?><p class="warning"><?php echo $term->name; ?> is not yet supported.</p><?php endif; ?>
            <legend><?php echo $term->name; ?></legend>
            <input type="checkbox" name="fetch_<?php echo $term->slug; ?>" id="fetch_<?php echo $term->slug; ?>" <?php if(get_option('fetch_'.$term->slug) == 'on') echo 'checked'; ?>><label for="fetch_<?php echo $term->slug; ?>">Fetch</label><br>
            <label for="status_<?php echo $term->slug; ?>">Save as </label>
            <select name="status_<?php echo $term->slug; ?>" id="status_<?php echo $term->slug; ?>">
				<?php $statuses = get_post_statuses(); foreach($statuses as $slug => $status): ?>
                <option value="<?php echo $slug; ?>"<?php if(get_option('status_'.$term->slug) == $slug): ?> selected="selected"<?php endif; ?>><?php echo $status; ?></option>
				<?php endforeach; ?>
            </select><br>
			<label for="hashtag_<?php echo $term->slug; ?>">Search</label><br>
           	<?php $searchs = get_option('hashtag_'.$term->slug); $i=0; if(!empty($searchs)) : foreach($searchs as $search) : $i++ ?>
            	<input type="text" name="hashtag_<?php echo $term->slug; ?>[]" id="hashtag_<?php echo $term->slug; ?>[]" value="<?php echo $search; ?>"><?php if($i > 1) : ?><div class="delete"></div><?php endif; ?><br>
			<?php endforeach; else : ?>
            <input type="text" name="hashtag_<?php echo $term->slug; ?>[]" id="hashtag_<?php echo $term->slug; ?>[]" value=""><br>
			<?php endif; ?>
			<a href="#" class="addMore">+ Add more searches</a>
        </fieldset>

		<?php endforeach; ?>

		<div class="clear"></div>

        <label for="fetch_time">Time between each fetch</label>
        <select name="fetch_time" id="fetch_time">
            <option value="1m" <?php if(get_option('fetch_time') == '1m') echo 'selected'; ?>>1 Minute</option>
            <option value="5m" <?php if(get_option('fetch_time') == '5m') echo 'selected'; ?>>5 Minutes</option>
            <option value="10m" <?php if(get_option('fetch_time') == '10m') echo 'selected'; ?>>10 Minutes</option>
            <option value="30m" <?php if(get_option('fetch_time') == '30m') echo 'selected'; ?>>30 Minutes</option>
            <option value="1h" <?php if(get_option('fetch_time') == '1h') echo 'selected'; ?>>1 Hour</option>
            <option value="3h" <?php if(get_option('fetch_time') == '3h') echo 'selected'; ?>>3 Hours</option>
            <option value="1j" <?php if(get_option('fetch_time') == '1j') echo 'selected'; ?>>1 Day</option>
        </select>

		<?php @submit_button(); ?>
    </form>
</div>