<?php
/*
Plugin Name: Cool Video Gallery
Description: Cool Video Gallery, a video gallery plugin to manage video galleries. Feature to upload videos, attach Youtube videos, media files from library and group them into galleries is available. Option to play videos using Fancybox. Supports '.flv', '.mp4', '.m4v', '.mov' and '.mp3' files playback. 
Version: 2.3
Author: Praveen Rajan
Text Domain: cool-video-gallery
Domain Path: /languages
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
	
	Copyright 2016  Praveen Rajan
	
	Cool Video Gallery is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 2 of the License, or
	any later version.
	 
	Cool Video Gallery is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	GNU General Public License for more details.
	 
	You should have received a copy of the GNU General Public License
	along with Cool Video Gallery. If not, write to the Free Software Foundation, Inc.,
 	51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 	
 	Please see license.txt for the full license.
*/
?>
<?php
global $wp_version;
if (version_compare ( $wp_version, "3.0", "<" )) { 
	wp_die("This plugin requires WordPress version 3.0.1 or higher.");
}

if ( !class_exists('CoolVideoGallery') ) {
	/**
	 * Class declaration for cool video gallery
	 * @author Praveen Rajan
	 */
	class CoolVideoGallery{
		
		var $plugin_url;
		var $plugin_third_party_url;
		var $table_gallery;
		var $table_videos;
		var $default_gallery_path;
		var $winabspath;
		var $video_id;
		var $cvg_version = '2.3';
		
		var $video_type_upload;
		var $video_type_youtube;
		
		var $allowed_extension;
		
		protected static $instance = NULL;
		
		/**
		 * Constructor class for CoolVideoGallery
		 */
		function CoolVideoGallery(){
			
			$this->plugin_url = plugin_dir_url( __FILE__ );
			$this->plugin_third_party_url = $this->plugin_url . 'third_party_lib/';
			
			$this->table_gallery = '';
			$this->table_videos = '';
			$this->video_id = '';
			
			$this->video_type_upload = "upload";
			$this->video_type_youtube = "youtube";
			$this->video_type_media = "media";
			 
			/**
			 * Video file types supported.
			 * 
			 * @Warning: Editing/Adding can cause the plugin to malfunction. Do it at your own risk :)
			 */
			$this->allowed_extension = array('mp4', 'flv', 'MP4', 'FLV', 'mov', 'MOV', 'mp3', 'MP3', 'm4v', 'M4V');
			
			if (function_exists('is_multisite') && is_multisite()) {
				$this->default_gallery_path = get_option('upload_path') . '/video-gallery/' ;
			}else{
				$this->default_gallery_path =  'wp-content/uploads/video-gallery/';
			}
		
			$this->winabspath =  str_replace("\\", "/", ABSPATH);
			
			$this->cvg_load_dependencies();
			
			//adds scripts and css stylesheets
			add_action('wp_print_scripts', array(&$this, 'cvg_gallery_script'));
			
			//adds admin menu options to manage
			add_action('admin_menu', array(&$this, 'cvg_admin_menu'));
			
			//adds admin menu options at topbar to manage
			if ( is_admin() ) 
				add_action( 'admin_bar_menu', array(&$this, 'cvg_admin_bar_menu'), 100 );
			
			//adds contextual help for all menus of plugin
			add_action('admin_init',  array(&$this, 'cvg_add_contextual_help'));
			 
			//adds player options to head
			add_action('wp_head', array(&$this, 'cvg_add_player_header'));
	 		add_action('admin_head', array(&$this, 'cvg_add_player_header'));
	 		
	 		// dashboard widget
	 		add_action('wp_dashboard_setup', array(&$this,'cvg_custom_dashboard_widgets'));
	 		
	 		// add shortcodes
	 		add_shortcode('cvg-gallery', array(&$this,'cvg_gallery_shortcode') );
	 		add_shortcode('cvg-video', array(&$this,'cvg_video_shortcode') );
	 		
	 		// check plugin upgrades
	 		add_action( 'plugins_loaded', array(&$this,'cvg_plugin_loaded'));
	 		
	 		// Additional links on the plugin page
	 		add_filter('plugin_row_meta', array(&$this, 'cvg_add_plugin_page_links'), 10, 2);
		}
		
		/**
		 * Returns an instance of CoolVideoGallery plugin class
		 */
		public static function get_instance() {
		
			// create an object
			NULL === self::$instance and self::$instance = new self;
		
			return self::$instance; // return the object
		}
		
		/**
		 * Function to install cool video gallery plugin
		 */
		function cvg_install(){
			global $wpdb;
			
			if (function_exists('is_multisite') && is_multisite()) {
				// check if it is a network activation - if so, run the activation function for each blog id
				if (isset($_GET['networkwide']) && ($_GET['networkwide'] == 1)) {
					
					$old_blog = $wpdb->blogid;
					// Get all blog ids
					$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs", null));
					foreach ($blogids as $blog_id) {
						switch_to_blog($blog_id);
						$this->_cvg_activate();
					}
					switch_to_blog($old_blog);
					return;
				}
			}
			$this->_cvg_activate();
		}		
		
		/**
		 * Function to create database for plugin.
		 */
		function _cvg_activate() {
			
			global $wpdb;
			
	        $sub_name_gallery = 'cvg_gallery';
	        $sub_name_videos = 'cvg_videos';
	        
	        $this->table_gallery  = $wpdb->prefix . $sub_name_gallery;
	        $this->table_videos = $wpdb->prefix . $sub_name_videos;
	        
			if($wpdb->get_var("SHOW TABLES LIKE '$this->table_gallery'") != $this->table_gallery) {
			
				$sql = "CREATE TABLE " . $this->table_gallery . " (
						 	  `gid` bigint(20) NOT NULL auto_increment,
							  `name` varchar(255) NOT NULL,
							  `path` mediumtext,
							  `title` mediumtext,
							  `galdesc` mediumtext,
							  `author` bigint(20) NOT NULL default '0',
							  PRIMARY KEY  (`gid`)
						) CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				dbDelta($sql);
			}
			
			if($wpdb->get_var("SHOW TABLES LIKE '$this->table_videos'") !=  $this->table_videos) {
				
					$sql_video = "CREATE TABLE " .  $this->table_videos . " (
							 		`pid` bigint( 20  ) NOT NULL AUTO_INCREMENT  ,
									`galleryid` bigint( 20 ) NOT NULL DEFAULT '0',
									`filename` varchar( 255 ) NOT NULL ,
									`thumb_filename` varchar( 255 ) NOT NULL ,
									`video_title` mediumtext NULL,
									`description` mediumtext,
									`sortorder` BIGINT( 20 ) NOT NULL DEFAULT '0',
									`alttext` mediumtext,
									`videodate` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
									`meta_data` longtext,
									`video_type` varchar( 20 ) NOT NULL DEFAULT '". $this->video_type_upload ."',
									`exclude` tinyint(5) NOT NULL DEFAULT '0',
									PRIMARY KEY ( `pid` )
							) CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
					require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
					dbDelta($sql_video);
			}
			
			 $installed_ver = get_option( "cvg_version" );
			 
			 // For version 1.2
			 if (version_compare($installed_ver, '1.3', '<')) {
			 	$sql_update = "ALTER TABLE " .  $this->table_videos . " ADD `sortorder` BIGINT( 20 ) NOT NULL DEFAULT '0' AFTER `description`" ;
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			 	$wpdb->query($sql_update);
			 }
				
			// Section to save gallery settings.
			$options = get_option ( 'cvg_settings' );
			
			$options = array ();
			$options ['max_cvg_gallery'] = 10;
			$options ['max_vid_gallery'] = 10;
			$options ['cvg_preview_height'] = 100;
			$options ['cvg_preview_width'] = 100;
			$options ['cvg_preview_quality'] = 70;
			$options ['cvg_zc'] = 0;
			$options ['cvg_slideshow'] = 7000;
			$options ['cvg_description'] = 1;
			$options ['cvg_gallery_description'] = 1;
			$options ['cvg_ffmpegpath'] = '/Applications/ffmpegX.app/Contents/Resources/ffmpeg';
			$options ['cvg_random_video'] = 0;
			$options ['cvg_youtubeapikey'] = '';
			$options ['cvg_ffmpeg_preview_width'] = '100';
			$options ['cvg_ffmpeg_preview_height'] = '100';
			$options ['cvg_ffmpeg_seek_hour'] = '00';
			$options ['cvg_ffmpeg_seek_minute'] = '00';
			$options ['cvg_ffmpeg_seek_second'] = '10';
			
			update_option ( 'cvg_settings', $options );
			
			// Section to save player settings.
			$options_player = get_option ( 'cvg_player_settings' );
			
			$options_player = array ();
			$options_player ['cvgplayer_jwplayer_key'] = '';
			$options_player ['cvgplayer_width'] = 400;
			$options_player ['cvgplayer_height'] = 400;
			$options_player ['cvgplayer_skin'] = '';
			$options_player ['cvgplayer_volume'] = 70;
			$options_player ['cvgplayer_fullscreen'] = 1;
			$options_player ['cvgplayer_autoplay'] = 1;
			$options_player ['cvgplayer_mute'] = 0;
			$options_player ['cvgplayer_auto_close_single'] = 1;
			$options_player ['cvgplayer_stretching'] = 'fill';
			$options_player ['cvgplayer_share_option'] = 0;
			
			update_option ( 'cvg_player_settings', $options_player );
			
			update_option('cvg_version', $this->cvg_version);
		}
		
		/**
		 * Function to deactivate plugin
		 */
		function cvg_deactivate_empty() {
		
		}
		
		/**
		 * Function to uninstall plugin
		 */
		function cvg_uninstall(){
			
			global $wpdb;
			
			if (function_exists('is_multisite') && is_multisite()) {

				$old_blog = $wpdb->blogid;
				// Get all blog ids
				$blogids = $wpdb->get_col($wpdb->prepare("SELECT blog_id FROM $wpdb->blogs", null));
				foreach ($blogids as $blog_id) {
					switch_to_blog($blog_id);
					$this->_cvg_deactivate();
				}
				switch_to_blog($old_blog);
				return;
			}
			
			$this->_cvg_deactivate();
		}
		
		/**
		 * Function to delete tables of plugins
		 */
		function _cvg_deactivate() {
			
			global $wpdb;
			$sub_name_gallery = 'cvg_gallery';
	        $sub_name_videos = 'cvg_videos';
	        
	        $this->table_gallery  = $wpdb->prefix . $sub_name_gallery;
	        $this->table_videos = $wpdb->prefix . $sub_name_videos;
	        
		  	$wpdb->query("DROP TABLE $this->table_gallery");
		  	$wpdb->query("DROP TABLE $this->table_videos");
		  	
		  	if (function_exists('is_multisite') && is_multisite()) {
				$gallery_path = get_option('upload_path') . '/video-gallery/' ;
			}else{
				$gallery_path =  'wp-content/uploads/video-gallery/';
			}
			
			$cvg_core = new CvgCore();
			$cvg_core->deleteDir( ABSPATH . $gallery_path ); 
			
		}
		
		/**
		 * Function to update databse during function upgrade
		 */
		function cvg_plugin_loaded() {
		
			global $wpdb;
				
			$sub_name_gallery = 'cvg_gallery';
			$sub_name_videos = 'cvg_videos';
			 
			$this->table_gallery  = $wpdb->prefix . $sub_name_gallery;
			$this->table_videos = $wpdb->prefix . $sub_name_videos;
			 
			$installed_ver = get_option( "cvg_version" );
			
			if (version_compare($installed_ver, '1.5', '<')) {
					
				$sql_update = "ALTER TABLE " .  $this->table_videos . " ADD `video_type` varchar( 20 ) NOT NULL DEFAULT '". $this->video_type_upload . "' AFTER `meta_data`" ;
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				$wpdb->query($wpdb->prepare($sql_update, null));
			}
				
			if (version_compare($installed_ver, '1.7', '<')) {
					
				$sql_update = "ALTER TABLE " .  $this->table_videos . " ADD `video_title` varchar( 20 ) NULL AFTER `thumb_filename`" ;
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				$wpdb->query($wpdb->prepare($sql_update, null));
			}
				
			if (version_compare($installed_ver, '1.8', '<')) {
					
				$sql_update = "ALTER TABLE " .  $this->table_videos . " ADD `exclude` tinyint(5) NOT NULL DEFAULT '0' AFTER `video_type`" ;
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
				$wpdb->query($wpdb->prepare($sql_update, null));
					
				$sql_update_video_text = "ALTER TABLE " .  $this->table_videos . " MODIFY `video_title` mediumtext" ;
				$wpdb->query($wpdb->prepare($sql_update_video_text, null));
			}
				
			$sql_collation_update_videos = "ALTER TABLE " . $this->table_videos . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$wpdb->query($sql_collation_update_videos);
			
			$sql_collation_update_gallery = "ALTER TABLE " .  $this->table_gallery . " CONVERT TO CHARACTER SET utf8 COLLATE utf8_unicode_ci;";
			$wpdb->query($sql_collation_update_gallery);
			
			if($installed_ver != $this->cvg_version) {
					
				update_option('cvg_version', $this->cvg_version);
			}

			// Load text domain
			load_plugin_textdomain('cool-video-gallery', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
			
			// Add ffmpeg extra params 
			$options = get_option('cvg_settings');
			if(!isset($options['cvg_ffmpeg_preview_width']) || !isset($options['cvg_ffmpeg_preview_height'])) {
			
				$options['cvg_ffmpeg_preview_width'] = '100';
				$options['cvg_ffmpeg_preview_height'] = '100';
			}
			if(!isset($options['cvg_ffmpeg_seek_hour']) || !isset($options['cvg_ffmpeg_seek_minute']) || !isset($options['cvg_ffmpeg_seek_second'])) {
			
				$options['cvg_ffmpeg_seek_hour'] = '00';
				$options['cvg_ffmpeg_seek_minute'] = '00';
				$options['cvg_ffmpeg_seek_second'] = '10';
			}
			update_option('cvg_settings', $options);
		}
		
		/**
		 * Function to add main menu and submenus to admin panel
		 */
		function cvg_admin_menu() {
			
			$parent_slug = "cvg-gallery-overview";
			
			add_menu_page(__('Video Gallery Overview', 'cool-video-gallery'), __('Video Gallery', 'cool-video-gallery'), 'manage_options', $parent_slug , array( $this, 'gallery_overview'), $this->plugin_url .'images/video_small.png');
			
			add_submenu_page( $parent_slug, __('Video Gallery Overview', 'cool-video-gallery'), __('Overview', 'cool-video-gallery'), 'manage_options', 'cvg-gallery-overview',array($this, 'gallery_overview'));
			add_submenu_page( $parent_slug, __('Add Gallery / Upload Videos', 'cool-video-gallery'), __('Add Gallery / Videos', 'cool-video-gallery'), 'manage_options', 'cvg-gallery-add',array($this, 'gallery_add'));
			add_submenu_page( $parent_slug, __('Manage Video Gallery', 'cool-video-gallery'), __('Manage Gallery', 'cool-video-gallery'), 'manage_options', 'cvg-gallery-manage',array($this, 'gallery_manage'));
			add_submenu_page( $parent_slug, __('Settings', 'cool-video-gallery'), __('Settings', 'cool-video-gallery'), 'manage_options', 'cvg-gallery-settings',array($this, 'gallery_settings'));
			add_submenu_page( $parent_slug, __('Video Player Settings', 'cool-video-gallery'), __('Video Player Settings', 'cool-video-gallery'), 'manage_options', 'cvg-player-settings',array($this, 'player_settings'));
			add_submenu_page( $parent_slug, __('CVG Video Sitemap', 'cool-video-gallery'), __('Google Video Sitemap', 'cool-video-gallery'), 'manage_options', 'cvg-video-sitemap',array($this, 'video_sitemap'));
			add_submenu_page( $parent_slug, __('CVG Uninstall', 'cool-video-gallery'), __('Uninstall CVG', 'cool-video-gallery'), 'manage_options', 'cvg-plugin-uninstall',array($this, 'uninstall_plugin'));
		}
		
		/**
		 * Function to add admin_bar_menu at top.
		 */
		function cvg_admin_bar_menu() {
		
			global $wp_admin_bar;
		
			$wp_admin_bar->add_menu( array( 'id' => 'cvg-menu', 'title' => __( 'CVG', 'cool-video-gallery'), 'href' => admin_url('admin.php?page=cvg-gallery-overview') ) );
			$wp_admin_bar->add_menu( array( 'parent' => 'cvg-menu', 'id' => 'cvg-menu-add-gallery-video', 'title' => __('Add Gallery / Videos', 'cool-video-gallery'), 'href' => admin_url('admin.php?page=cvg-gallery-add') ) );
			$wp_admin_bar->add_menu( array( 'parent' => 'cvg-menu', 'id' => 'cvg-menu-manage-gallery', 'title' => __('Manage Gallery', 'cool-video-gallery'), 'href' => admin_url('admin.php?page=cvg-gallery-manage') ) );
			$wp_admin_bar->add_menu( array( 'parent' => 'cvg-menu', 'id' => 'cvg-menu-gallery-settings', 'title' => __('Settings', 'cool-video-gallery'), 'href' => admin_url('admin.php?page=cvg-gallery-settings') ) );
			$wp_admin_bar->add_menu( array( 'parent' => 'cvg-menu', 'id' => 'cvg-menu-player-settings', 'title' => __('Video Player Settings', 'cool-video-gallery'), 'href' => admin_url('admin.php?page=cvg-player-settings') ) );
			$wp_admin_bar->add_menu( array( 'parent' => 'cvg-menu', 'id' => 'cvg-menu-google-sitemap', 'title' => __('Google Video Sitemap', 'cool-video-gallery'), 'href' => admin_url('admin.php?page=cvg-video-sitemap') ) );
			$wp_admin_bar->add_menu( array( 'parent' => 'cvg-menu', 'id' => 'cvg-menu-uninstall', 'title' => __('Uninstall', 'cool-video-gallery'), 'href' => admin_url('admin.php?page=cvg-plugin-uninstall') ) );
		}
		
		function cvg_add_plugin_page_links($links, $file) {
			
			if($file == plugin_basename( basename(dirname(__FILE__)).'/'.basename(__FILE__))) {
				$links[] = '<a href="https://wordpress.org/support/plugin/cool-video-gallery/">' . __('Support', 'sitemap') . '</a>';
			}
			return $links;
		}
		
		/**
		 * Function to add contextual help for each menu of plugin page.
		 */
		function cvg_add_contextual_help(){
			
			$help_array = array('toplevel_page_cvg-gallery-overview', 'video-gallery_page_cvg-gallery-add', 'video-gallery_page_cvg-gallery-manage', 'video-gallery_page_cvg-gallery-details', 'video-gallery_page_cvg-gallery-sort', 'video-gallery_page_cvg-gallery-settings', 'video-gallery_page_cvg-player-settings', 'video-gallery_page_cvg-plugin-uninstall', 'video-gallery_page_cvg-video-sitemap' );
			foreach($help_array as $help) {
				
				add_filter( 'contextual_help', array(&$this, 'cvg_contextual_help') , $help, 2);
			}	
		}
		
		/**
		 * Function to add contextual help for each menu
		 * 
		 * @param $contextual_help - Contextual Help
		 * @param $screen_id - Screen Id
		 */
		function cvg_contextual_help( $contextual_help, $screen_id) {
			
			$help_content = "";
			$screen_title = "";
			
			switch($screen_id) {
					case 'toplevel_page_cvg-gallery-overview':
										$help_content = '<p>An overview about the total number of galleries and videos added using this plugin is shown here. Server information is also provided to show the maximum file upload limit of PHP. Inaddition to this it shows whether <b>FFMPEG</b> is installed in the webserver. Preview images are automatically generated for videos added if FFMPEG is installed. Otherwise images should be manually uploaded for videos added.</p>';
										$help_content .= '<p><b>Instructions to use <i>Cool Video Gallery</i>:</b></p>';
										$help_content .= '<p><ol><li> Add a gallery and upload some videos from the admin panel to that gallery.</li>'.
														 '<li>Use either `<b>CVG Slideshow</b>` or `<b>CVG Showcase</b>` widget to play slideshow of uploaded videos in a gallery.</li>'.	
														 '<li>Go to your post/page and enter the tag `<b>[cvg-video videoid=</b>vid<b>]</b>` (where vid is video id) to add video '.
														 'or enter the tag `<b>[cvg-gallery galleryid=</b>gid<b>]</b>` (where gid is gallery id) to add a complete gallery.</li>'.			
														 '<li>Inorder to use slideshow and showcase in custom templates created use the function `<b>cvgShowCaseWidget(</b>gid<b>)</b>` and `<b>cvgSlideShowWidget(</b>gid<b>)</b>` (where gid is gallery id).</li></ol></p>';
										$help_content .= '<p><b>Shortcode configuration keys available: </b></p>';
										$help_content .= '<p><ol><li>`galleryid`: Id of Gallery to present as Showcase / Slideshow / Playlist</li><li>`videoid`: Id of Video to present as Embed / Popup</li><li>`limit`: Number of Videos to be shown in a Gallery</li><li>`width`: Width of Video Player</li><li>`height`: Height of Video Player</li><li>`preview-width`: Width of Preview Thumbnail</li><li>`preview-height`: Height of Preview Thumbnail</li>';
										$help_content .= '<li>`mode`: Mode to present Gallery or Video(s). Different options available for mode: <ol type="a" style="list-style-type: lower-alpha"><li>`showcase`: List all Videos</li><li>`slideshow`: Show Videos as Slideshow</li><li>`playlist`: Show Videos as Playlist / Embedded</li><li>`embed`: Show Video as Embedded</li></ol></li>';
										$help_content .= '</ol></p>';
										
										$screen_title = 'Overview';
										
										break;
					case 'video-gallery_page_cvg-gallery-add':
										$help_content = '<p>This page provides three tabs to add gallery, upload videos and add Youtube videos. <ul><li>`Add new gallery` tab provides option to add new video galleries.</li><li>`Upload videos` tab provides option to upload mulitple videos to a selected gallery.</li><li>`Attach Media` tab provides option to attach media from library to video galleries.</li><li>`Youtube Videos` tab lets you add Youtube videos to a gallery.</li></p>';
										$screen_title = 'Add Gallery / Videos';
										
										break;	
					case 'video-gallery_page_cvg-gallery-manage':
					
										if(isset($_GET['gid']) && !isset($_GET['order'])) {
											$help_content = '<p>Displays the details of a particular gallery. <ul><li>Name and description of the gallery can be updated</li><li>Lists all video(s) in a gallery</li><li>Bulk deletion and sorting of videos available</li>';
											$help_content .= '<li>Scan gallery folder for manually uploaded video(s) <i><a onclick="showHelpDialogForScanVideos();">Learn More</a></i></li><li>Upload preview thumbnail for video(s)</li><li>Option to exclude video(s) from gallery</li><li>Publish individual video(s) as post</li><li>Generate shortcode for video(s)</li><li>Move video(s) from one gallery to another</li></ul></p>';
											$screen_title = 'Gallery Details';
											
										}else if(isset($_GET['order'])){
											$help_content = '<p>Options to sort videos in a gallery. <ul><li>Sort by Video ID, Name or Date</li><li>Drag-drop to change video order</li></ul></p>';
											$screen_title = 'Sort Gallery';
											
										}else {
											$help_content = '<p>Lists the different galleries created and provide details like <ul><li>Total number of videos</li><li>Author of gallery</li><li>Description of gallery</li><li>Option to publish a gallery</li><li>Option to delete a gallery</li><li>Generate shortcode for gallery</li></ul>Option provided to perform bulk deletion of galleries.</p>';
											$screen_title = 'Manage Gallery';
											
										}
										
										break;
					case 'video-gallery_page_cvg-gallery-settings':
										$help_content = '<p>Shows the different options available for listing and managing a gallery.</p>';
										$screen_title = 'Gallery Settings';
										
										break;	
					case 'video-gallery_page_cvg-player-settings':
										$help_content = '<p>Options to manage different options of video player is provided here.</p>';
										$screen_title = 'Player Settings';
										
										break;
					case 'video-gallery_page_cvg-video-sitemap':
										$help_content = '<p>Option to generate Google XML Sitemap for Videos.</p>';
										$screen_title = 'Video Sitemap';
										
										break;						
					case 'video-gallery_page_cvg-plugin-uninstall':
										$help_content = '<p>Option to uninstall this plugin. Please backup all data before uninstall.</p>';
										$screen_title = 'Plugin Uninstall';
										
										break;	
															
				}

			$screen = get_current_screen();

			$help_array = array('toplevel_page_cvg-gallery-overview', 'video-gallery_page_cvg-gallery-add', 'video-gallery_page_cvg-gallery-manage', 'video-gallery_page_cvg-gallery-details', 'video-gallery_page_cvg-gallery-sort', 'video-gallery_page_cvg-gallery-settings', 'video-gallery_page_cvg-player-settings', 'video-gallery_page_cvg-plugin-uninstall', 'video-gallery_page_cvg-video-sitemap');
			
			if(in_array($screen->base, $help_array)) {
			
				$screen->add_help_tab( array(
			        'id'      => $screen_id,
			        'title'   => __( $screen_title, 'cool-video-gallery' ),
			        'content' => __($help_content, 'cool-video-gallery'),
			    ));
			    return $contextual_help;
			}
		}
		
		/**
		 * Function to include gallery overview page
		 */
		function gallery_overview() {
			include('admin/gallery-overview.php');
		}

		/**
		 * Function to include gallery add page
		 */
		function gallery_add() {
			include('admin/gallery-add.php');
		}
		
		/**
		 * Function to include gallery manage page
		 */
		function gallery_manage() {
			
			if(isset($_GET['gid']) && !isset($_GET['order'])) {
				
				include('admin/gallery-details.php');
				
			}else if(isset($_GET['order'])){
			
				include('admin/gallery-sort.php');
				
			}else {
				
				include('admin/gallery-manage.php');
			}
		}
		
		/**
		 * Function to include gallery settings page
		 */
		function gallery_settings() {
			include('admin/gallery-settings.php');
		}
		
		/**
		 * Function to include player settings page
		 */
		function player_settings() {
			include('admin/player-settings.php');	
		}
		
		/**
		 * Function to include video xml sitemap page
		 */
		function video_sitemap() {
			include('admin/video-sitemap.php');	
		}
		
		/**
		 * Function to include plugin uninstall page
		 */
		function uninstall_plugin(){
			include('admin/plugin-uninstall.php');	
		} 
		
		/**
		 * Function to include plugin description in WordPress Admin dashboard page
		 */
		function cvg_custom_dashboard_widgets(){
			
			wp_add_dashboard_widget( 'cvg_admin_section', __('Cool Video Gallery' , 'cool-video-gallery'), array(&$this, 'cvg_gallery_admin_notices'));
		}
		
		/**
		 * Display CVG overview in WordPress dashboard
		 */
		function cvg_gallery_admin_notices() {
			
			$cvg_core = new CvgCore();
			$cvg_core->gallery_overview();
		}
		/**
		 * Function to include scripts
		 */
		function cvg_gallery_script() {
			
			echo "<!-- Cool Video Gallery Script starts here -->";
			
			wp_enqueue_script('jquery');
			wp_enqueue_script('jquery.slideshow', $this->plugin_third_party_url . 'jquery.utils/jquery.slideshow.js', 'jquery');
			wp_enqueue_script('jquery.stripslashes', $this->plugin_third_party_url . 'jquery.utils/jquery.stripslashes.js', 'jquery');
			wp_enqueue_style('cvg-styles', $this->plugin_url . 'css/cvg-styles.css', '');

			echo "<!-- Cool Video Gallery Script ends here -->";
		}
		
		/**
		 * Function to load required files.
		 */	
		function cvg_load_dependencies() {
			
			require_once('lib/video-db.php');
			require_once('lib/core.php');
			require_once('lib/youtube.php');
			require_once('widgets/widgets.php');	
			require_once('tinymce/tinymce.php');
		}
		
		/**
		 * Shortcode Function to render gallery.
		 * 
		 * @param $arguments - input arguments.
		 * @return Gallery.
		 */
		 function cvg_gallery_shortcode($arguments) {
			
			$output = '';
			
			$gallery_id = $arguments ['galleryid'];
			
			if (isset ( $arguments ['limit'] ))
				$limit = $arguments ['limit'];
			else
				$limit = 0;
			
			$mode = $arguments ['mode'];
			
			$cvg_core = new CvgCore();
			$output = $cvg_core->video_show_gallery ( $arguments, "main" );
			
			return $output;
		}
		
		/**
		 * Shotcode Function to render videos
		 * @param $arguments - input arguments.
		 * @return video
		 */
		function cvg_video_shortcode($arguments) {
			
			return $this -> cvg_video_render($arguments);
		}
		
		/**
		 * Function to render video player.
		 * 
		 * @param $arguments - input arguments.
		 * @return player code.
		 */
		function cvg_video_render($arguments){
			
			$output = '';
			
			$cvg_videodb = new CvgVideoDB();
			$video_details = $cvg_videodb->find_video($arguments['videoid']);
			
			if(!is_array($video_details))
				return __('[Video not found]', 'cool-video-gallery');
			
			$options = get_option('cvg_settings');
			$options_player = get_option('cvg_player_settings');
			
			if (isset ( $arguments ['preview-width'] ))
				$thumb_width = $arguments ['preview-width'];
			else 
				$thumb_width = $options ['cvg_preview_width'];
			
			if (isset ( $arguments ['preview-height'] ))
				$thumb_height = $arguments ['preview-height'];
			else
				$thumb_height = $options ['cvg_preview_height'];
			
			if (isset ( $arguments ['width'] ))
				$player_width = $arguments ['width'];
			else
				$player_width = $options_player ['cvgplayer_width'];
			
			if (isset ( $arguments ['height'] ))
				$player_height = $arguments ['height'];
			else
				$player_height = $options_player ['cvgplayer_height'];
			
			if ($options_player ['cvgplayer_autoplay'] == 1)
				$autoplay = "true";
			else
				$autoplay = "false";
			
			if ($options_player ['cvgplayer_mute'] == 1)
				$mute = "true";
			else
				$mute = "false";
										
			$video = array();
			
			if($video_details[0]->video_type == $this->video_type_upload) {
					
				// Upload file type		
				$video['filename'] = site_url()  . '/' . $video_details[0]->path . '/' . $video_details[0]->filename;
				$video['thumb_filename'] =  $video_details[0]->path . '/thumbs/' . $video_details[0]->thumb_filename;
			
				if(!file_exists(ABSPATH . '/' .$video['thumb_filename']))
					$video['thumb_filename']  = WP_CONTENT_URL .  '/plugins/' . dirname( plugin_basename(__FILE__)) . '/images/default_video.png';
				else 
					$video['thumb_filename'] =	site_url() . '/' . $video['thumb_filename'];
			
			}else if($video_details[0]->video_type == $this->video_type_youtube){
				
				// Youtube file type
				$video['filename'] =  $video_details[0]->filename;
				$video['thumb_filename'] =  $video_details[0]->thumb_filename;
				
			}else if($video_details[0]->video_type == $this->video_type_media) {
				
				// Media file type
				$video['filename'] =  $video_details[0]->filename;
				$video['thumb_filename'] =  $video_details[0]->path . '/thumbs/' . $video_details[0]->thumb_filename;
			
				if(!file_exists(ABSPATH . '/' .$video['thumb_filename']))
					$video['thumb_filename']  = WP_CONTENT_URL .  '/plugins/' . dirname( plugin_basename(__FILE__)) . '/images/default_video.png';
				else 
					$video['thumb_filename'] =	site_url() . '/' . $video['thumb_filename'];
			
			}			
			
			if($options['cvg_description'] == 1)			
				$video['title'] = $video_details[0]->video_title;
			else 
				$video['title'] = '';
				
			$video['name']	= $video_details[0]->name;
			$video['description'] = $video_details[0]->description;	
			
			if ( !array_key_exists('filename', $video) ){
				return __('Error: Required parameter "filename" is missing!', 'cool-video-gallery');
			}
			
			if(isset($arguments['mode']) && ($arguments['mode'] == "playlist" || $arguments['mode'] == "embed")) {
				
				//Embed section for a video
				$video_display = '<div style="max-width:'.$player_width.'px;height:'.$player_height.'px;width: 100%;display: inline-block;"><span id="mediaplayer_vid_'.$arguments['videoid'].'">';
				$video_display .= '</span>';
				
				if($options['cvg_description'] == 1)
					$video_display .= '<span class="cvg-playlist-title-outside" style="max-width:'.$player_width.'px;"><label>'. stripslashes($video['description']).'</label></span>';
				
				echo "<!-- Cool Video Gallery Script starts here -->";
				?>
					<script type="text/javascript">
					jQuery(document).ready(function(){
						jwplayer("<?php echo "mediaplayer_vid_".$arguments['videoid']; ?>").setup({
							"file" : "<?php echo $video['filename'];?>",
							"volume" : "<?php echo $options_player['cvgplayer_volume']; ?>",
							"width" : "<?php echo $player_width; ?>",
							"height" : "<?php echo $player_height; ?>",
							"image" : "<?php echo $video['thumb_filename'] ; ?>",
							"autostart" : "<?php echo $autoplay;?>",
							"mute" : "<?php echo $mute; ?>",

							<?php if($options_player['cvgplayer_share_option'] == 1) { // Enable Share ?>			
							"sharing" : {
								"heading": "<?php if($options['cvg_description'] == 1) { echo $video ['title']; }else { echo ""; }?>",
								"sites": ['twitter','email']
							},
							<?php } ?>
							
							"stretching" : "<?php echo $options_player['cvgplayer_stretching']; ?>",
							"skin" : "<?php echo $options_player['cvgplayer_skin']; ?>",
							"title" : "<?php if($options['cvg_description'] == 1) { echo $video ['title']; }else { echo ""; }?>",
							"width" : "100%"
						});
						jwplayer("<?php echo "mediaplayer_vid_".$arguments['videoid']; ?>").onReady(function() {
							jQuery("#<?php echo "mediaplayer_vid_".$arguments['videoid']; ?>").css('display', 'inline-block', 'important');
							jQuery("#<?php echo "mediaplayer_vid_".$arguments['videoid']; ?>").addClass('cvg-single-video-wrapper');
						});
					});
					</script>
				<?php 
				
				echo "<!-- Cool Video Gallery Script ends here -->";
				
				$video_display .= '</div>';
				
				return $video_display;
				
			}else {
				
				// Single video display
				if($options['cvg_description'] == 1) {
					$description = stripslashes ( $video ['description'] ) ;
				}else {
					$description = "";
				}
				$output .= '<span class="cvg-single-video-wrapper" style="width:' . $thumb_width . 'px;height:' . $thumb_height .'px;"><span style="width:' . $thumb_width . 'px;height:' . $thumb_height .'px;">';
				$output .= '<a href="' . $video ['filename'] . '" title="' . $description . '"  rel="fancy_cvg_video_' . $arguments ['videoid'] . '">';
				$output .= '<img src="' . $video ['thumb_filename'] . '" style="width:' . $thumb_width . 'px;height:' . $thumb_height . 'px;" ';
				$output .= 'alt="' . __ ( 'Click to Watch Video', 'cool-video-gallery' ) . '" id="fancy_cvg_video_preview_' . $arguments ["videoid"] . '"  />';
				$output .= '</a>';
				$output .= '</span>';
				
				if($options['cvg_description'] == 1) {
					$output .= '<label class="cvg-single-video-wrapper-title" style="width:' . $thumb_width . 'px;">' . stripcslashes($video ['title']) . '</label>';
				}
				
				$output .= '</span>';
				
				echo "<!-- Cool Video Gallery Script starts here -->";
				?>
				<script type="text/javascript">
					jQuery(document).ready(function() {
						jQuery("a[rel=fancy_cvg_video_<?php echo $arguments['videoid'];?>]").fancybox({
							'content' : '<div id="video_fancy_cvg_video_<?php echo $arguments['videoid'];?>" style="overflow:hidden;"></div>',
							'width' : parseInt("<?php echo $player_width; ?>") ,
							'height' : "<?php echo $player_height; ?>",
							'titlePosition' : 'inside',
							'autoScale' : false,
							'autoDimensions' : false,
							'margin' : 0,
							'padding' : 15,
							'transitionIn' : 'none',
							'transitionOut' : 'none',
							'centerOnScroll' : false,
							'titleFormat' : function(title, currentArray, currentIndex, currentOpts) {
								return  (this.title.length > 0) ?  this.title : '' ;
							},
							'onComplete' : function() {

								jwplayer('video_fancy_cvg_video_<?php echo $arguments['videoid'];?>').setup({
									'file' : this.href,
									"autostart" : "<?php echo $autoplay;?>",
									"volume" : "<?php echo $options_player['cvgplayer_volume']; ?>",
									"width" : "<?php echo $player_width; ?>",
									"height" : "<?php echo $player_height; ?>",
									"image" :  jQuery('#fancy_cvg_video_preview_<?php echo $arguments["videoid"];?>').attr('src') ,
									"mute" : "<?php echo $mute; ?>",

									<?php if($options_player['cvgplayer_share_option'] == 1) { // Enable Share ?>			
									"sharing" : {
										"heading": "<?php if($options['cvg_description'] == 1) { echo $video ['title']; }else { echo ""; }?>",
										"sites": ['twitter','email']
									},
									<?php } ?>
										
									"stretching" : "<?php echo $options_player['cvgplayer_stretching']; ?>",
									"skin" : "<?php echo $options_player['cvgplayer_skin']; ?>",
									"title" : "<?php if($options['cvg_description'] == 1) { echo $video ['title']; }else { echo ""; }?>",
									"width" : "100%",
									"height" : "100%"
								});
								jwplayer('video_fancy_cvg_video_<?php echo $arguments['videoid'];?>').onComplete(function() {
									<?php 
										if ($options_player['cvgplayer_auto_close_single']) {
										?>
											jQuery.fancybox.close();
										<?php 
										}
									?>
								});
							}
						});
					});
				</script>
				<?php
				echo "<!-- Cool Video Gallery Script ends here -->";
			}
			return $output;
		} 
		
		/**
		 * Function to add players files to header.
		 * 
		 * @return script and styles for video player
		 */		
		function cvg_add_player_header(){
			
			$options_player = get_option('cvg_player_settings');
			echo "<!-- Cool Video Gallery Script starts here -->";
			
			wp_enqueue_script('jwplayer', $this->plugin_third_party_url . 'jwplayer_7.3.6/jwplayer.js', '');
			wp_enqueue_script('jquery.fancybox', $this->plugin_third_party_url . 'fancybox_1.3.4/jquery.fancybox-1.3.4.pack.js', 'jquery');
			wp_enqueue_style('jquery.fancybox', $this->plugin_third_party_url . 'fancybox_1.3.4/jquery.fancybox-1.3.4.css', 'jquery');
			
			?>
			<script type="text/javascript">
				jQuery(document).ready(function(){

					// Set JWPlayer License Key
					jwplayer.key="<?php echo isset($options_player['cvgplayer_jwplayer_key']) ?  $options_player['cvgplayer_jwplayer_key'] : "";?>";
				});
			</script>
			<!-- Cool Video Gallery Script ends here -->
			<?php
		}
	}
}else {
	exit ("Class CoolVideoGallery already declared!");
}

// create new instance of the class
$CoolVideoGallery = new CoolVideoGallery();

if (isset($CoolVideoGallery)){
	
	register_activation_hook( basename(dirname(__FILE__)).'/'.basename(__FILE__), array(&$CoolVideoGallery,'cvg_install') );
	register_deactivation_hook(basename(dirname(__FILE__)).'/'.basename(__FILE__),  array(&$CoolVideoGallery,'cvg_deactivate_empty'));
			
}
?>