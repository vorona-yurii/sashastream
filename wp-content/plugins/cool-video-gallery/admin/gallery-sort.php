<?php 
/**
 * Section to sort videos in a gallery
 * @author Praveen Rajan
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) 
	die(__('You are not allowed to call this page directly.', 'cool-video-gallery')); 

$cvg_core = new CvgCore();
$cvg_videodb = new CvgVideoDB();

//Loads WP default scripts
wp_enqueue_script('jquery-ui-core');
wp_enqueue_script('jquery-ui-widget');
wp_enqueue_script('jquery-ui-mouse');
wp_enqueue_script('jquery-ui-sortable');
wp_enqueue_style('jquery.ui.all', trailingslashit(WP_PLUGIN_URL . '/' . dirname(dirname(plugin_basename(__FILE__)))) . 'third_party_lib/jquery.utils/jquery.ui.all.css', 'jquery');

if (isset ($_POST['updateSortOrder']))  {
	
	// wp_nonce_field('cvg_gallery_sort_nonce','cvg_gallery_sort_nonce_csrf');
	if ( check_admin_referer( 'cvg_gallery_sort_nonce', 'cvg_gallery_sort_nonce_csrf' ) ) {
		
		$sort_video = $cvg_videodb->sort_gallery($_POST['sortOrder']);
		
		if($sort_video) {
			$cvg_core->show_video_message(__('Sort order updated successfully!', 'cool-video-gallery'));
		}else {
			$cvg_core->show_video_error(__('Error updating sort order.', 'cool-video-gallery'));
		} 
	}
}
$gid = $_GET['gid'];
$orderBy = isset($_GET['order']) ? $_GET['order'] : 'sortorder';

$cool_video_gallery = CoolVideoGallery::get_instance();

//Section if no gallery is selected.
if(!isset($gid) || $gid == "") { 
	?>
	<div class="wrap">
		<h2><?php _e('Sort Gallery Videos', 'cool-video-gallery');?></h2>
		<div class="cvg-clear"></div>
	   	<p>
			<?php _e('Choose your gallery at', 'cool-video-gallery');?> <a class="button rbutton" href="<?php echo admin_url('admin.php?page=cvg-gallery-manage');?>"><?php _e('Manage Gallery', 'cool-video-gallery') ?></a>
		</p>
		<?php 	$cvg_core->show_video_error( __('Please select a gallery to sort videos', 'cool-video-gallery') ); ?>
	</div> 
<?php 	
}else {
	$options = get_option('cvg_settings');
	$gallery = $cvg_videodb->find_gallery($gid);
	
	if (!$gallery)  
		$cvg_core->show_video_error(__('Gallery not found.'));
	
	if ($gallery) { 
			
			$videolist = $cvg_videodb->get_gallery($gid, true, $orderBy, 'asc');
			$act_author_user = get_userdata( (int) $gallery->author );
			$base_url = admin_url('admin.php?page=cvg-gallery-manage&gid=' . $_GET['gid'] . '&order=') ;
			?>
			<script type="text/javascript">
			jQuery(function() {
				jQuery( "#sortable" ).sortable({
					placeholder: "ui-state-highlight",
					cursor:  "crosshair",
					opacity: 0.6,
					update: function(event, ui) { 
			   			var result = jQuery('#sortable').sortable('toArray');
			   			jQuery('#sortOrder').val('');
			   			jQuery('#sortOrder').val(result); 
					},
					start: function(e, ui){
				        ui.placeholder.height(ui.item.height());
				        ui.placeholder.width(ui.item.width());
				    }
				});
				jQuery( "#sortable" ).disableSelection();
					  
			});
			</script>
			<div class="wrap">
				<h2>
				<?php 
					printf(
						__( 'Sort Videos in Gallery: %s', 'cool-video-gallery'),
						$gallery->name
						);
				?>
				</h2>
				<div class="cvg-clear"></div>
				<form id="updatevideos" method="POST" action="<?php echo $base_url; ?>" accept-charset="utf-8">
					<?php wp_nonce_field('cvg_gallery_sort_nonce','cvg_gallery_sort_nonce_csrf'); ?>
					<div class="tablenav">
						<div class="alignleft actions">
							<a class="button" href="<?php echo admin_url('admin.php?page=cvg-gallery-manage&gid=' . $_GET['gid']); ?>"><?php _e('Back to Gallery', 'cool-video-gallery'); ?></a>
							<input type="submit" name="updateSortOrder" class="button-primary action"  value="<?php _e('Update Sort Order', 'cool-video-gallery');?>" />
						</div>
					</div>	
					<ul class="subsubsub">
						<li><?php _e('Sort By', 'cool-video-gallery') ?> :</li>
						<li><a href="<?php echo $base_url . 'pid'; ?>" <?php if ($orderBy == 'pid') echo 'class="current"'; ?>><?php _e('Video ID', 'cool-video-gallery') ?></a> |</li>
						<li><a href="<?php echo $base_url . 'filename'; ?>"  <?php if ($orderBy == 'filename') echo 'class="current"'; ?>><?php _e('Video Name', 'cool-video-gallery') ?></a> |</li>
						<li><a href="<?php echo $base_url . 'videodate'; ?>"  <?php if ($orderBy == 'videodate') echo 'class="current"'; ?>><?php _e('Video Date', 'cool-video-gallery') ?></a></li>
					</ul>
					<div class="cvg-clear"></div>
					<?php
						if($videolist) {
							
							$options = get_option('cvg_settings');
							$thumb_width = $options['cvg_preview_width'];
							$thumb_height = $options['cvg_preview_height'];

							 
							$html_output = '<ul id="sortable">';
							$pid_list = '';
							foreach($videolist as $video) {
								$pid = $video->pid;
								
								$video_name = $video->filename;
								
								if($video->video_type == $cool_video_gallery->video_type_upload) {
										
									$video_thumb_filename = $video->thumb_filename;
									$video_thumb_url = site_url() . '/' .  $video->path . '/thumbs/' . $video_thumb_filename;
									
									if(!file_exists(ABSPATH . $video->path . '/thumbs/' .$video->thumb_filename))
										$video_thumb_url  = WP_CONTENT_URL .  '/plugins/' . dirname(dirname( plugin_basename(__FILE__))) . '/images/default_video.png';
								}else if($video->video_type == $cool_video_gallery->video_type_youtube){
										
									$video_thumb_url =  $video->thumb_filename;
								}else if($video->video_type == $cool_video_gallery->video_type_media){
									
									$video_thumb_filename = $video->thumb_filename;
									$video_thumb_url = site_url() . '/' .  $video->path . '/thumbs/' . $video_thumb_filename;
									
									if(!file_exists(ABSPATH . $video->path . '/thumbs/' .$video->thumb_filename))
										$video_thumb_url  = WP_CONTENT_URL .  '/plugins/' . dirname(dirname( plugin_basename(__FILE__))) . '/images/default_video.png';
								}else {
									
									$video_thumb_filename = $video->thumb_filename;
									$video_thumb_url = site_url() . '/' .  $video->path . '/thumbs/' . $video_thumb_filename;
									
									if(!file_exists(ABSPATH . $video->path . '/thumbs/' .$video->thumb_filename))
										$video_thumb_url  = WP_CONTENT_URL .  '/plugins/' . dirname(dirname( plugin_basename(__FILE__))) . '/images/default_video.png';
								}  
								
								$output =  '<div class="cvg-gallery-sort-outer"><img src="' ;
								$output .= $video_thumb_url; 
								$output .=  '" style="width:' . $thumb_width . 'px;height:' . $thumb_height . 'px;" alt="preview"/></div>';
								$video_title = mb_strimwidth($video->video_title, 0, 15, '...');
								$output .= '<div class="cvg-clear"></div><div style="text-align:center;width:'. $thumb_width .'px;">'. $video_title  . '</div>';

								$html_output .= '<li class="ui-state-default" id="' . $pid . '">';
								$html_output .= $output;
								$html_output .= '</li>';
								$pid_list .= $pid . ',';
							}
							$html_output .= '</ul>';
							
							echo $html_output;
						}
						
						$pid_list = substr($pid_list, 0, (strlen($pid_list) - 1));
					?>	
			
				<input type="hidden" value="<?php echo $pid_list;?>" name="sortOrder" id="sortOrder" />
			</form>
		</div>
		
	<?php } ?>	
<?php } ?>