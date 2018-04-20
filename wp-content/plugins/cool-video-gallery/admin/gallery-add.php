<?php
/**
 * Section to add gallery and upload videos
 * @author Praveen Rajan
 */

if (preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF']))
	die(__('You are not allowed to call this page directly.', 'cool-video-gallery'));

wp_enqueue_script('jquery.ui.tabs', trailingslashit(WP_PLUGIN_URL . '/' . dirname(dirname(plugin_basename(__FILE__)))) . 'third_party_lib/jquery.utils/jquery.ui.tabs.js', 'jquery');
wp_enqueue_script('jquery.multifile', trailingslashit(WP_PLUGIN_URL . '/' . dirname(dirname(plugin_basename(__FILE__)))) . 'third_party_lib/jquery.utils/jquery.multifile.js', 'jquery');
wp_enqueue_style('jquery.ui.tabs', trailingslashit(WP_PLUGIN_URL . '/' . dirname(dirname(plugin_basename(__FILE__)))) . 'third_party_lib/jquery.utils/jquery.ui.tabs.css', 'jquery');

$cvg_core = new CvgCore();

if ($_SERVER ['REQUEST_METHOD'] == 'POST' && empty ( $_POST ) && empty ( $_FILES ) && $_SERVER ['CONTENT_LENGTH'] > 0) {
	
	$temp_file_size = intval ( $cvg_core->wp_convert_bytes_to_kb ( $_SERVER ['CONTENT_LENGTH'] ) );
	$max_upload_size = $cvg_core->get_max_size ();
	
	if ($temp_file_size > $max_upload_size) {
		$cvg_core->show_video_error ( __ ( 'File upload size limit exceeded.', 'cool-video-gallery' ) );
	}
}

//Section on submitting data.
if (!empty($_POST)) {
	
	$cvg_core->processor();
}
?>
<div class="wrap">
	<h2><?php _e('Add Gallery / Videos', 'cool-video-gallery');?></h2>
	<?php $tabs = $cvg_core->tabs_order();?>
	<!-- Section to display tabs -->
	<div id="cvg_add_tab">
		<ul id="tabs">
			<?php
			foreach ($tabs as $tab_key => $tab_name) {
				echo "\n\t\t<li><a href='#$tab_key'>" . __($tab_name) . "</a></li>";
			}
			?>
		</ul>
		<?php
		foreach ($tabs as $tab_key => $tab_name) {
			echo "\n\t<div id='$tab_key'>\n";
			$function_name = 'tab_' . $tab_key;
			$cvg_core->$function_name();
			echo "\n\t</div>";
		}
		?>
	</div>
</div><!-- wrap -->
<script type="text/javascript">
	jQuery(document).ready(function(){
		jQuery('#videofiles').MultiFile({
			STRING: {
				remove:'[<?php  _e('remove', 'cool-video-gallery');?>]',
				denied:'<?php _e('File type not permitted.', 'cool-video-gallery');?>',
				duplicate:'<?php _e('This file has already been selected: ', 'cool-video-gallery');?>$file'
			},
			accept : 'mp4,flv,MP4,FLV,mov,MOV,MP3,mp3,m4v,M4V'
		});
		jQuery('#uploadvideo_btn').click(function(){
			if(jQuery.trim(jQuery('#galleryselect').val()) == 0) {
				alert('<?php _e('Please choose a gallery.', 'cool-video-gallery');?>');
			}else {
				jQuery('#uploadvideo_form').submit();
			}
		});
		jQuery('#addvideo_btn').click(function(){
			if(jQuery.trim(jQuery('#galleryselect_add').val()) == 0) {
				alert('<?php _e('Please choose a gallery.', 'cool-video-gallery');?>');
			}else {
				jQuery('#addvideo_form').submit();
			}
		});
		jQuery('#addmedia_btn').click(function(){
			if(jQuery.trim(jQuery('#galleryselect_media').val()) == 0) {
				alert('<?php _e('Please choose a gallery.', 'cool-video-gallery');?>');
			}else if(jQuery.trim(jQuery('#mediaselect_add').val()) == 0) {
				alert('<?php _e('Please choose a media file.', 'cool-video-gallery');?>');
			}else {
				jQuery('#addmedia_form').submit();
			}
		});
		jQuery('#cvg_add_tab').tabs({
			fxFade : true,
			fxSpeed : 'fast'
		});
	});
</script>