=== Cool Video Gallery ===
Contributors: Praveen Rajan
Tags: video gallery,playlist,tinymce,videos,gallery,media,player,flash player,flash-player,skins,flash player skins,admin,post,pages,pictures,widgets,picture,video,cool-video-gallery,cool video gallery,ffmpeg,showcase,fancybox,preview image,upload,flv,mp4,mov,m4v,mp3,H.264,shortcode
Requires at least: 3.0.1
Tested up to: 4.5
Stable tag: 2.3
License: GPLv2

Cool Video Gallery is a Video Gallery plugin for WordPress with option to upload videos, add Youtube videos and manage them in multiple galleries. 

== Description ==

Cool Video Gallery is a Video Gallery plugin for WordPress with option to upload videos, attach media files, add Youtube videos and manage them in multiple galleries. Automatic preview image generation for uploaded videos using FFMPEG library available.
Option provided to upload images for video previews. Supports '.flv', '.mp4', '.mov', '.m4v' and '.mp3' video files presently. 

= Note =
* Video Player used by this plugin is <a href="https://www.jwplayer.com/" target="_blank">JW Player</a>. Agree to the Terms and Conditions of JW Player.

= Features =
* Supports H.264 (.mp4, .mov, .m4v), FLV (.flv) and MP3 (.mp3) files.
* Upload videos and manage videos in different galleries.
* Multiple video upload feature available.
* Automatic generation of preview images for videos using FFMPEG installed in webserver.
* Manual upload feature to upload preview image for videos if FFMPEG is not installed.
* Bulk deletion of videos/galleries.
* Option to add title/description for galleries.
* Playback feature for videos uploaded in a popup.
* Option to set width/height of preview images uploaded.
* Video player options like skin selection, default volume setting, autoplay feature and many other features available.
* Widgets for Slideshow and Showcase feature available.
* Shortcode feature integration for gallery/video with posts/pages. 
* Feature to scan gallery folders for newly added videos through FTP. 
* Feature to sort videos in a gallery.
* Play all videos in a gallery with navigation enabled in Fancybox popup. 
* Plugin Uninstall feature enabled.
* Google XML Video Sitemap generation feature integrated.
* Option to show single videos in page/post content using embed feature.
* Feature to show embed playlist of a complete gallery.
* TinyMCE integration implemented.
* Option to limit the no. of videos in widgets/showcase/slideshow added.
* Instructions/Details available in admin contextual help panel.
* Option to set Playlist size enabled.
* Youtube video support added.
* HTML5 support enabled using JW Player.
* Attach Media files from Library into Video Gallery.
* Shortcode generator feature for galleries and videos.
* Automatic scan functionality to add video to galleries if videos FTPed to gallery folder.
* Share option enable/disable feature via admin panel.
* Feature to publish a video as post added.
* Admin bar menu added for easy navigation.
* Exclude video in a gallery during playback option provided.
* Sort videos by Added Date integrated.
* Move video(s) from one gallery to another implemented.
* Randomize videos in a gallery through admin panel.
* I18n enabled.

If you find this plugin useful please provide your valuable ratings.

= Check out my other plugin =
* <a href="http://wordpress.org/extend/plugins/attachment-file-icons">Attachment File Icons (AF Icons)</a> - A plugin to display file type icons adjacent to files added to pages/posts/widgets. Feature to upload icons for different file types provided.

== Installation ==

1. Upload `cool-video-gallery` to the `/wp-content/plugins/` directory.
2. Activate the plugin through the `Plugins` menu in WordPress.
3. Add a gallery and upload some videos from the admin panel.
4. Use either `CVG Slideshow` or `CVG Showcase` widget to play slideshow of uploaded videos in a gallery. 
5. Go to your post/page and use TinyMCE button for adding videos to page/post content.
6. Inorder to use slideshow and showcase in custom templates created use the function `cvgShowCaseWidget(gid, limit)` and `cvgSlideShowWidget(gid, limit)` (where gid is gallery id and limit is the limit for videos).
7. Contextual Menu on Admin panel explains in detail about the plugin usage.

== Screenshots ==

1. Video Gallery as Showcase in Page and Slideshow in Sidebar Widget
2. Admin - CVG Overview
3. Admin - Add Gallery / Upload Videos / Attach Media / Add Youtube Videos
4. Admin - Gallery Details 
5. Admin - Google XML Video Sitemap Generation
6. Admin - Insert Gallery/Video into Posts/Pages
7. Admin - Generate Shortcode for Gallery
8. Admin - Generate Shortcode for Video

== Changelog ==

= 2.3 =
* Fancybox reverted back to 1.3.4 per WordPress Guidelines.

= 2.2 =
* JWPlayer version 7.3.6 included.
* Fancybox version 2.1.5 included and supports better configurations.
* Responsive video player in embed.
* Improved VideoPlayer Share option.
* Admin panel config fixes.
* Shortcode optimization and improvements.
* Redesign of plugin images.
* Improved support for FFMPEG library for thumbnail creation.

= 2.1 =
* Design issues fixed.
* More image format supported in preview image of videos. Few image format will not be displayed as player preview image as they are not supported by JWPlayer.
* WP deprecated function issues fixed.
* Gallery/Video Shortcode modified.
* Google XML Sitemap generation updated.
* Publish gallery as post added.
* I18n enabled.
* Playback support for Youtube Videos using Youtube IFrame support if Web Browser does not support Flash playback. This will not work if playlist feature of JWPlayer is used. 

= 2.0 =
* Command Injection Fix.
* Design issue fixes.
* TinyMCE editor issue fix
* Shortcode parsing issue fix.

= 1.9 =
* CSRF Vulnerability Fix.

= 1.8 =
* Admin bar menu added for easy navigation.
* Exclude video in a gallery during playback option provided.
* Sort videos by Added Date integrated.
* Move video(s) from one gallery to another impletmented.
* Randomize videos in a gallery through admin panel.
* Contextual help menu changes made during deprecation of used functionality.
* Video title field made to accept long text.
* Few PHP warning messages removed in admin/front end.

= 1.7 =
* Feature to add title for Videos added.
* Shortcode generator option for galleries and videos included in admin panel to ease out operation.
* Feature to publish a video as post added. 
* Automatic scan and add videos to gallery if video file is already FTPed to respective gallery folder.
* Share option enable/disable feature through admin panel.
* Fix for jQuery UI Sortable library issue in Gallery Sort page (WP 3.5 specific)
* Minor fixes for admin panel UI. 
* WP included JavaScript/CSS files used instead of external JavaScript/CSS.  
* Plugin Menu modified to make it compact.
* Minor issues fixed in Google Sitemap and Playlist XML files.
* Database collation type modified to allow saving of Non-English characters.
* FFMPEG issue on Linux OS fixed.

= 1.6 =
* Media file from Library support added.
* Youtube media adding option changed. Now accepts Youtube Video ID.
* Video description positioned restored to below video items.
* Version Upgrade issues of v1.5 fixed. 
* Embedding issue for Youtube videos fixed.
* Video Sharing plugin enabled - Default plugin <a href="http://www.longtailvideo.com/addons/plugins/47/Viral" target="_blank">Viral</a> for JW Player.
* Issue with PHP extension and settings for Youtube Vide adding diagonized and warning messages provided properly.

= 1.5 =
* Youtube video support added.
* Fancybox replaces Shadowbox functionalities. 
* iOS support enabled with HTML5.
* Playlist size settings enabled. 
* Autohide feature enabled for navigation controls in Fancybox.
* Instruction steps provided for addition of skins to plugin.
* Fixed Google XML Video Sitemap issues fixed.
* FFMPEG library issues fixed.
* Few features like full screen, zoom crop of preview image, embed feature, Shadowbox are removed since they are no longer supported.
* About Author page added to Plugin admin page.

= 1.4 =
* Embed feature added single video and playlist.
* TinyMCE integration implemented.
* Fix for preview image manipulations.
* Option to limit the no. of videos.

= 1.3 =
* '.mov' and '.mp3' media file supports added.
* Added patch for thumbnail generation.
* Added uninstall option for plugin.
* Added fix for plugin upgrade issue.

= 1.2 =
* Added feature to sort videos in a gallery
* Navigation feature enabled in shadowbox popup to move acrosss videos in current gallery selected.
* Issue with 'jpeg/jpg' extension thumbnail fixed. '.png' image files currently accepted for thumbnail images.

= 1.1 =
* Added feature to scan video gallery folder and add newly added videos through FTP access.
* Shortcode feature added to support video gallery in post/page content.

= 1.0 =
* Initial version  

== Upgrade Notice ==

= 2.2 =
Upgrade includes JWPlayer v7.3.6. In order to use this player, users have to register at jwplayer.com to obtain license key for JW Player 7(free for non-commerical).