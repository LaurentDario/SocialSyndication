<?php

/*
	Plugin Name: Social Syndication
	Plugin URI:
	Description: This plugin is generating posts from Twitter, Instagram and FlickR searches, in a new custom-post-type called "Syndication. Each syndication is tagged with its network and search keyword. Be sure to respect each API legal terms by linking images and posts to the original posts. The plugin is automatically deleting posts that are not available or deleted from the network.
	Version: 2.0
	Author: Laurent Jouanneau-Dario
	Author URI: http://www.sidleetechnologies.com
	License: GPL2
	Copyright 2013  Sid Lee Technologies  (email : contact@sidleetechnologies.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Add custom post type Syndication and custom taxonomy Networks
include('admin-content/ss-post-types.php');
// adding metaboxes for syndications posts
include_once('admin-content/metaboxes.php');
// DAT Class - clearing smileys and other **** from instagram and twitter feeds
include('admin-content/swag.php');
require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
require_once(ABSPATH . 'wp-admin' . '/includes/media.php');
require_once('admin-content/twitteroauth/twitteroauth.php');
ini_set('error_log', './error_log');
if(!class_exists('Social_Syndication'))
{
	class Social_Syndication
	{

		// List all the networks supported
		public $support = array('Twitter','Instagram','Flickr');

		/**
		 * Construct the plugin object
		 */
		public function __construct(){
			add_action('admin_init',array(&$this,'admin_init'));
			add_action('admin_menu',array(&$this,'add_menu'));
		}


		/**
		 * Activate the plugin
		 */
		public function activate(){}

		public function createTerms(){
			error_log('Creating');
			foreach($this->support as $support){
				wp_insert_term($support,'networks');
			}
		}

		/**
		 * Deactivate the plugin
		 */
		public static function deactivate(){}

		/**
		 * hook into WP's admin_init action hook
		 */
		public function admin_init()
		{
			// Set up the settings for this plugin
			$this->init_settings();

			// Possibly do additional admin_init tasks
		} // END public static function activate

		/**
		 * Initialize some custom settings
		 */
		public function init_settings()
		{
			// register the settings for this plugin
			$terms = get_terms('networks',array('hide_empty'=> false));
			foreach($terms as $term){
				register_setting('social_syndication-group', 'fetch_'.$term->slug);
				register_setting('social_syndication-group', 'status_'.$term->slug);
				register_setting('social_syndication-group', 'hashtag_'.$term->slug);
				register_setting('social_syndication-group', 'min_'.$term->slug);
			}
			register_setting('social_syndication-group', 'fetch_time');
		} // END public function init_custom_settings()

		/**
		 * add a menu
		 */
		public function add_menu()
		{
			global $settings_page;
			add_menu_page('Social Syndication', 'SocialSyn', 'edit_posts', 'social_syndication', array(&$this, 'plugin_disclaimer_page'));
			add_submenu_page('social_syndication', 'Settings', 'Settings', 'edit_posts', 'ss_settings', array(&$this, 'plugin_settings_page'));
			$terms = get_terms('networks',array('hide_empty'=> false));
			if(!empty($terms)){
				add_submenu_page('social_syndication', 'PiXels', 'PiXels', 'edit_posts', 'ss_pixel_ui', array(&$this, 'plugin_pixels_UI'));
			}


		} // END public function add_menu()

		/**
		 * Menu Callback
		 */
		public function plugin_settings_page()
		{
			if(!current_user_can('edit_posts'))
			{
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}

			// Render the settings template
			include(sprintf("%s/settings.php", dirname(__FILE__)));
		} // END public function plugin_settings_page()

		/**
		 * Menu Callback
		 */
		public function plugin_disclaimer_page()
		{
			if(!current_user_can('edit_posts'))
			{
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}

			// Render the settings template
			include(sprintf("%s/disclaimer.php", dirname(__FILE__)));
		} // END public function plugin_settings_page()

		/**
		 * Menu Callback
		 */
		public function plugin_pixels_UI()
		{
			if(!current_user_can('edit_posts'))
			{
				wp_die(__('You do not have sufficient permissions to access this page.'));
			}

			// Render the settings template
			include(sprintf("%s/pixels_interface.php", dirname(__FILE__)));
		} // END public function plugin_settings_page()

		/*
		 * Function fetchNetwork
		 *
		 * Fetch Twitter, Instagram or Facebook (?) for recent posts and create post from different json strings
		 *
		 * @param string network specify one network to fetch. If empty, fetching all registered networks
		 * @param int nb_fetched specify the number of post to fetch (not working for every network)
		 */
		public function fetchNetwork($network='all',$nb_fetched = 10){
			if($network == 'all'){
				// Fetching all selected feeds
				if(get_option('fetch_twitter') == 'on') $this->fetchNetwork('twitter');
				if(get_option('fetch_instagram') == 'on') $this->fetchNetwork('instagram');
				if(get_option('fetch_flickr') == 'on') $this->fetchNetwork('flickr');
			}//endif
			// getting last post
			switch($network){
				case 'twitter':
					require_once('admin-content/TwitterAPIExchange.php');
					// Twitter Feed
					if(get_option('min_twitter','') != ''){
						// Starting at the last element fetched
						$api_extra = '&since_id='.get_option('min_twitter');
					} else $api_extra = '';
					// getting new posts
					$searches = get_option('hashtag_twitter');
					foreach($searches as $search){

						$settings = array(
							'oauth_access_token'        => "14884997-jwdPV9jrvSPux5dRZYutpm4eossw35PT5B84MFnTJ",
							'oauth_access_token_secret' => "Z5ac2cUaIRydMSPzHTIPOv3FajVh2olUjmQ7SCQZk4c",
							'consumer_key'              => "mc8NMVJr4TV8gaHSE18Mw",
							'consumer_secret'           => "ANukeTbCl3q3hJdx5BIsqmYfd8xi593xNCMwxnMYAs"
						);

						$url    = 'http://api.twitter.com/1.1/search/tweets.json';
						$getField      = '?q=' . urlencode($search) . '&count=' . $nb_fetched . $api_extra;
						$requestMethod = 'GET';

						$twitter = new TwitterAPIExchange($settings);
						$tweets  = json_decode(
							$twitter->setGetfield($getField)
								->buildOauth($url, $requestMethod)
								->performRequest()
						);

						if (isset($tweets->errors)) {
							$this->app->error_log('Twitter API Error:');
							$this->app->error_log('Game : ' . $this->result->game . ' ||| Team : ' . $this->hashTag);
							$this->app->error_log(serialize($tweets->errors));

							return false;
						}

						$syndications = $tweets->statuses;

						// API Query
//						$syndications = json_decode($json); $request = array();
						$i = 0;
						foreach($syndications as $syndication){
							if($i == 0) update_option('min_twitter',$syndication->id_str);
							$request = array(
								'network' => $network,
								'post' => array(
									'post_content' => $this->YOLO($syndication->text),
									'post_date' => date('Y-m-d H:i:s',strtotime($syndication->created_at)),
									'post_title' => $network.'-'.$syndication->id_str,
									'post_type' => 'syndication',
									'post_status' => get_option('status_twitter')
								),
                                'language' => $syndication->metadata->iso_language_code,
								'syndication_string' => print_r($syndication,1),
								'element-id' => $syndication->id_str,
								'author-id' => $syndication->user->id_str,
								'author-name' => $syndication->user->name,
								'link' => 'https://twitter.com/'.$syndication->user->name.'/status/'.$syndication->id_str,
								'hashtag' => $search
							);
							// Creating Post
							$this->createPost($request);
							$i++;
						}
					}
				break;

				case 'instagram':
					// Instagram Feed
					$searches = get_option('hashtag_instagram');
					foreach($searches as $search){
						$ch = curl_init();
						// Configuration de l'URL et d'autres options
						curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/v1/tags/".urlencode($search)."/media/recent?client_id=996746c3057b469bb223814cc96319dc&min_tag_id=".get_option('min_instagram'));
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						// Récupération de l'URL et affichage sur le naviguateur
						$raw = curl_exec($ch);
						$syndications = json_decode($raw);
						// Fermeture de la session cURL
						curl_close($ch);
						if(!is_null($syndications->pagination->min_tag_id)) update_option('min_instagram',$syndications->pagination->min_tag_id);
						foreach($syndications->data as $syndication){
							if($syndication->caption == null) $syndication->caption->text = '';
							$request = array(
								'network' => $network,
								'post' => array(
									'post_content' => $this->YOLO($syndication->caption->text),
									'post_date' => date('Y-m-d H:i:s',$syndication->created_time),
									'post_title' => $network.'-'.$syndication->id,
									'post_type' => 'syndication',
									'post_status' => get_option('status_instagram'),
								),
								'syndication_string' => print_r($syndication,1),
								'element-id' => $syndication->id,
								'author-id' => $syndication->user->id,
								'author-name' => $syndication->user->username,
								'link' => $syndication->link,
								'image' => $syndication->images->standard_resolution->url,
								'hashtag' => $search
							);
                            if(isset($syndication->location->name) && !is_null($syndication->location->name)) $request['location'] = $syndication->location->name;
                            else $request['location'] = '';
							// Creating Post
							$this->createPost($request);
						}
					}
				break;

				case 'flickr':
					// FlickR Feed
					$searches = get_option('hashtag_flickr');
					$i = 0;
					foreach($searches as $search){
						$ch = curl_init();
						// Configuration de l'URL et d'autres options
						curl_setopt($ch, CURLOPT_URL, "http://api.flickr.com/services/rest/?method=flickr.photos.search&api_key=4e147022f22fd4b8d862c54837977c72&tags=".urlencode($search)."&format=json&nojsoncallback=1&min_upload_date=".get_option('min_flickr'));
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						// Récupération de l'URL et affichage sur le naviguateur
						$raw = curl_exec($ch);
						$syndications = json_decode($raw);
						// Fermeture de la session cURL
						curl_close($ch);
						foreach($syndications->photos->photo as $syndication){

							$ch = curl_init();
							// Configuration de l'URL et d'autres options
							curl_setopt($ch, CURLOPT_URL, "http://api.flickr.com/services/rest/?method=flickr.photos.getInfo&api_key=4e147022f22fd4b8d862c54837977c72&photo_id=".$syndication->id."&format=json&nojsoncallback=1");
							curl_setopt($ch, CURLOPT_HEADER, 0);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
							// Récupération de l'URL et affichage sur le naviguateur
							$raw = curl_exec($ch);
							$photo = json_decode($raw);
							// Fermeture de la session cURL
							curl_close($ch);



							$ch = curl_init();
							// Configuration de l'URL et d'autres options
							curl_setopt($ch, CURLOPT_URL, "http://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=4e147022f22fd4b8d862c54837977c72&photo_id=".$syndication->id."&format=json&nojsoncallback=1");
							curl_setopt($ch, CURLOPT_HEADER, 0);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
							// Récupération de l'URL et affichage sur le naviguateur
							$raw = curl_exec($ch);
							$photo_sizes = json_decode($raw);
							$found = false;
							$photo_size_url = '';
							foreach($photo_sizes->sizes->size as $photo_size){
								if($photo_size->width > 800 && !$found) {
									$photo_size_url = $photo_size->source;
									$found = true;
								}
							}
							// Fermeture de la session cURL
							curl_close($ch);
							$request = array(
								'network' => $network,
								'post' => array(
									'post_content' => $this->YOLO($photo->photo->description->_content),
									'post_date' => date('Y-m-d H:i:s',$photo->photo->dates->posted),
									'post_title' => $network.' : '.$photo->photo->title->_content,
									'post_type' => 'syndication',
									'post_status' => get_option('status_flickr'),
								),
								'syndication_string' => print_r($photo,1),
								'element-id' => $photo->photo->id,
								'author-id' => $photo->photo->owner->nsid,
								'author-name' => $photo->photo->owner->realname,
								'link' => $photo->photo->urls->url[0]->_content,
								'image' => $photo_size_url,
								'hashtag' => $search
							);
							if(($photo->photo->dates->posted) > get_option('min_flickr')){
								update_option('min_flickr',$photo->photo->dates->posted);
							}
							$i++;
							// Creating Post
							$this->createPost($request);
						}
					}
				break;
			}//end switch
		}//endFunction


		/*
		 * Function createPost
		 *
		 * Create a new post in Syndication from network feed (see fetchNetwork)
		 *
		 * @param array request Array created in fetchNetwork function
		 */
		public function createPost($request){
			// Adding post
            $post_id = wp_insert_post($request['post']);
            // WPML - Register Default language version
            if(in_array( 'sitepress-multilingual-cms/sitepress.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ))
                $this->registerLanguage($post_id);
			// Adding Taxonomy
			wp_set_object_terms($post_id,$request['network'],'networks');
			// Adding metas
			add_post_meta($post_id,'element-id',$request['element-id']);
			add_post_meta($post_id,'author-id',$request['author-id']);
			add_post_meta($post_id,'author-name',$request['author-name']);
			add_post_meta($post_id,'json-result',$request['syndication_string']);
			//add_post_meta($post_id,'search-key',get_option('hashtag_'.$request['network']));
			add_post_meta($post_id,'shared-link',$request['link']);
			wp_set_post_terms($post_id,sanitize_title($request['hashtag']),'search');

            if(isset($request['location']))
                add_post_meta($post_id,'location',$request['location']);
			if(isset($request['image'])) {
				add_post_meta($post_id,'img-link',$request['image']);
				require_once(ABSPATH . 'wp-admin' . '/includes/image.php');
				require_once(ABSPATH . 'wp-admin' . '/includes/file.php');
				require_once(ABSPATH . 'wp-admin' . '/includes/media.php');
				media_sideload_image( $request['image'], $post_id );
				$args = array(
					'post_type' => 'attachment',
					'numberposts' => 1,
					'post_status' => null,
					'post_parent' => $post_id
				);
				$attachments = get_posts($args);
				$attachment = $attachments[0];
				set_post_thumbnail( $post_id, $attachment->ID);
			}
		}

        // Register the post in WPML's default language
        private function registerLanguage($post_id){
            global $wpdb;
            $wpdb->query("update `{$wpdb->prefix}icl_translations` set language_code = '".icl_get_default_language()."' where element_id = ".$post_id.";");
        }

		private function buildBaseString($baseURI, $method, $params)
		{
			$r = array();
			ksort($params);
			foreach($params as $key=>$value){
				$r[] = "$key=" . rawurlencode($value);
			}
			return $method."&" . rawurlencode($baseURI) . '&' . rawurlencode(implode('&', $r));
		}

		private function buildAuthorizationHeader($oauth)
		{
			$r = 'Authorization: OAuth ';
			$values = array();
			foreach($oauth as $key=>$value)
				$values[] = "$key=\"" . rawurlencode($value) . "\"";
			$r .= implode(', ', $values);
			return $r;
		}

		public function clearUnavailable(){
			$terms = get_terms('networks');
			foreach($terms as $term){
				$posts = get_posts(array('networks' => $term->slug,'post_status' => 'published', 'post_type' => 'syndication'));
				if($term->slug == 'twitter'){
					foreach($posts as $post){
						$id = get_post_meta($post->ID,'element-id',true);

						$url = "http://api.twitter.com/1/statuses/show.json?id=".$id;

						$oauth_access_token = "14884997-jwdPV9jrvSPux5dRZYutpm4eossw35PT5B84MFnTJ";
						$oauth_access_token_secret = "Z5ac2cUaIRydMSPzHTIPOv3FajVh2olUjmQ7SCQZk4c";
						$consumer_key = "mc8NMVJr4TV8gaHSE18Mw";
						$consumer_secret = "ANukeTbCl3q3hJdx5BIsqmYfd8xi593xNCMwxnMYAs";

						$oauth = array( 'oauth_consumer_key' => $consumer_key,
							'oauth_nonce' => time(),
							'oauth_signature_method' => 'HMAC-SHA1',
							'oauth_token' => $oauth_access_token,
							'oauth_timestamp' => time(),
							'oauth_version' => '1.0');

						$base_info = $this->buildBaseString($url, 'GET', $oauth);
						$composite_key = rawurlencode($consumer_secret) . '&' . rawurlencode($oauth_access_token_secret);
						$oauth_signature = base64_encode(hash_hmac('sha1', $base_info, $composite_key, true));
						$oauth['oauth_signature'] = $oauth_signature;

						$header = array($this->buildAuthorizationHeader($oauth), 'Expect:');
						$options = array( CURLOPT_HTTPHEADER => $header,
							CURLOPT_HEADER => false,
							CURLOPT_URL => $url,
							CURLOPT_RETURNTRANSFER => true,
							CURLOPT_SSL_VERIFYPEER => false);

						$feed = curl_init();
						curl_setopt_array($feed, $options);
						$json = curl_exec($feed);
						curl_close($feed);

						$twitter_data = json_decode($json);
					}
				}
				else if($term->slug == 'instagram'){
					foreach($posts as $post){
						$id = get_post_meta($post->ID,'element-id',true);
						$ch = curl_init();
						// Configuration de l'URL et d'autres options
						curl_setopt($ch, CURLOPT_URL, "https://api.instagram.com/v1/media/$id?client_id=996746c3057b469bb223814cc96319dc");
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Host: api.twitter.com'));
						// Récupération de l'URL et affichage sur le naviguateur
						$raw = curl_exec($ch);
						$syndications = json_decode($raw);
						if(isset($syndications->meta->code) && $syndications->meta->code == 400) {
							wp_delete_post($post->ID,true);
						}
					}
				}
				else if($term->slug == 'flickr'){
					foreach($posts as $post){
						$id = get_post_meta($post->ID,'element-id',true);
						$ch = curl_init();
						// Configuration de l'URL et d'autres options
						curl_setopt($ch, CURLOPT_URL, "http://api.flickr.com/services/rest/?method=flickr.photos.getSizes&api_key=4e147022f22fd4b8d862c54837977c72&photo_id=".$id."&format=json&nojsoncallback=1");
						curl_setopt($ch, CURLOPT_HEADER, 0);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						// Récupération de l'URL et affichage sur le naviguateur
						$raw = curl_exec($ch);
						$photo = json_decode($raw);
						curl_close($ch);
						if(isset($photo->stat) && $photo->stat == 'fail'){
							wp_delete_post($post->ID,true);
						}
					}
				}
			}
			return false;
		}

		public function registerSearches(){
			$terms = get_terms('networks');
			foreach($terms as $term){
				$searches = get_option('hashtag_'.$term->slug);
				foreach($searches as $search){
					if(!term_exists(sanitize_title($search),'search')){
						wp_insert_term(sanitize_title($search),'search');
					}
				}
			}
		}

		/*
		 * Function YOLO
		 *
		 * Awesome function getting the SWAG off instagram and twitter feeds
		 *
		 * @param string text text to 'unSWAG'
		 * @return string 'unSWAGed' text
		 */
		public static function YOLO($text) {
			$clean = '';
			$clean = filter_var($text, FILTER_SANITIZE_STRING,FILTER_FLAG_STRIP_LOW);
			$clean = emoji_html_stripped($clean);
			$clean = trim($clean);
			return $clean;
		}

		public static function delete_unattached_images()
		{
			$query_images_args = array(
				'post_type' 		=> 'attachment',
				'post_mime_type' 	=>'image',
				'post_status' 		=> 'inherit',
				'post_parent'		=>0,
				'post_author'		=>0,
				'posts_per_page' 	=> -1,
			);

			$query_images = new WP_Query($query_images_args);
			foreach($query_images->posts as $image){
				wp_delete_post( $image->ID);
			}
			return true;
		}


		public function syndication_formatter($feed_type){
		//moved to a separate class
		}
	}
}

if(class_exists('Social_Syndication'))
{
	// Installation and uninstall hooks
	register_activation_hook(__FILE__, array('Social_Syndication', 'activate'));
	register_deactivation_hook(__FILE__, array('Social_Syndication', 'deactivate'));

	// instantiate the plugin class
	$social_syndication = new Social_Syndication();
	if(isset($social_syndication))
	{
		// Add the settings link to the plugins page
		function plugin_settings_link($links)
		{
			$settings_link = '<a href="options-general.php?page=social_syndication">Settings</a>';
			array_unshift($links, $settings_link);
			return $links;
		}

		$plugin = plugin_basename(__FILE__);
		add_filter("plugin_action_links_$plugin", 'plugin_settings_link');
	}


	// Initiating cron job
	add_action('init','syndication_schedule');
	function syndication_schedule(){
		if(! wp_next_scheduled('ss_fetch')){
			wp_schedule_event(time(), get_option('fetch_time'), 'ss_fetch');
		}
	}

	add_action('ss_fetch', 'fetcher');
	function fetcher() {
		$social_syndication = new Social_Syndication();
		$social_syndication->fetchNetwork();
		$social_syndication->delete_unattached_images();
		$social_syndication->clearUnavailable();
	}

	/* Create custom cron interval */
	add_filter( 'cron_schedules', 'fetch_cron_schedules');
	function fetch_cron_schedules(){
		return array(
			'1m' => array(
				'interval' => 60,
				'display' => 'In every Mintue'
			),
			'5m' => array(
				'interval' => 60 * 5,
				'display' => 'In every five Mintues'
			),
			'10m' => array(
				'interval' => 60 * 10,
				'display' => 'In every ten Mintues'
			),
			'30m' => array(
				'interval' => 60 * 30,
				'display' => 'In every 30 Mintues'
			),
			'1h' => array(
				'interval' => 60 * 60,
				'display' => 'In every Hour'
			),
            '3h' => array(
                'interval' => 60 * 60 * 3,
                'display' => 'In every 3 Hours'
            ),
			'1j' => array(
				'interval' => 60 * 60 * 24,
				'display' => 'In every Day'
			)
		);
	}
}