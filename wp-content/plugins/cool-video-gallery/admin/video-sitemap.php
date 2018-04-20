<?php
/**
 * Section to generate video sitemap
 * @author Praveen Rajan
 */
if (preg_match ( '#' . basename ( __FILE__ ) . '#', $_SERVER ['PHP_SELF'] ))
	die ( __('You are not allowed to call this page directly.', 'cool-video-gallery') );

$cvg_core = new CvgCore ();

if (isset ( $_POST ['generatexml'] )) {
	if(isset($_POST['cvg_sitemapaction'])) {
	
		// wp_nonce_field('cvg_video_sitemap_nonce','cvg_video_sitemap_nonce_csrf');
		if (check_admin_referer ( 'cvg_video_sitemap_nonce', 'cvg_video_sitemap_nonce_csrf' )) {
			$cvg_core->xml_sitemap ();
		}
	}else {
		$cvg_core->show_video_error(__('No posts/pages selected for video sitemap.', 'cool-video-gallery'));
	}
}
?>
<div class="wrap">
	<h2><?php _e('Google Video Sitemap Generator', 'cool-video-gallery'); ?></h2>
	<div id="dashboard-widgets-wrap">
		<form method="post" id="editvideositemap"
			action="<?php echo admin_url('admin.php?page=cvg-video-sitemap'); ?>">
			<div style="width: 100%;" class="postbox-container">
				<div class="postbox">
					<p style="padding-left: 15px;">
						<?php
							$message = sprintf( 'Generate your Google Video Sitemap here. For more details check <a href="%1$s" target="_blank">Google Video Sitemap Guide</a><br/><br/>Select pages/posts containing videos that need to be included in video sitemap generated. Video sitemap will be saved at <b>%2$s</b>.', "https://developers.google.com/webmasters/videosearch/sitemaps", ABSPATH);
							_e($message, 'cool-video-gallery');
							_e("<br/><i style='color:red;'>Note: FFMPEG library is required to collect video duration information. Generating video sitemap without FFMPEG will result in incorrect video duration.</i>", 'cool-video-gallery');
						?>
					</p>
				<?php wp_nonce_field('cvg_video_sitemap_nonce','cvg_video_sitemap_nonce_csrf'); ?>
					<div style="padding-left: 15px;">
				
						<table class="form-table"> 
						<tr valign="top"> 
							<th scope="row"><?php _e('Video Sitemap File name', 'cool-video-gallery') ;?>:</th> 
							<td><input type="text" size="35" name="cvg_sitemapname" value="" style="width:94%;"/><br />
							<i>( <?php _e('Allowed characters for file names are', 'cool-video-gallery') ;?>: a-z, A-Z, 0-9, -, _ )</i></td>
						</tr>
						</table>
						<input type="submit" class="button-primary" name="generatexml"
							value="<?php _e("Generate Video Sitemap", 'cool-video-gallery'); ?>" />
					</div>
					<div class="cvg-clear"></div>
					<h4 style="padding-left: 15px;"><?php echo _e('Pages / Posts', 'cool-video-gallery'); ?></h4>
					<div>
						<table class="widefat">
						<thead>
						<tr>
							<th scope="col" width="5%">
								<input type="checkbox" onclick="checkAll(document.getElementById('editvideositemap'));" name="checkall"/>
							</th>
							<th scope="col" width="10%"><b><?php _e('ID', 'cool-video-gallery'); ?></b></th>
							<th scope="col" width="55%"><b><?php _e('Post/Page Title', 'cool-video-gallery'); ?></b></th>
							<th scope="col" width="10%"><b><?php _e('Type', 'cool-video-gallery'); ?></b></th>
							<th scope="col" width="10%"><b><?php _e('No of Galleries', 'cool-video-gallery'); ?></b></th>
							<th scope="col" width="10%"><b><?php _e('No of Videos', 'cool-video-gallery'); ?></b></th>
						</tr>
						</thead>
						<tbody>
							<?php
							$args = array('post_type' => array( 'post', 'page' ), 'numberposts' => -1 );
							$posts = get_posts($args);
							$pattern = get_shortcode_regex();
							$empty_posts = true;
							
							foreach ($posts as $post) {
								
								if (   preg_match_all( '/'. $pattern .'/s', $post->post_content, $matches )
										&& array_key_exists( 2, $matches )
										&& (in_array( 'cvg-video', $matches[2] ) || in_array( 'cvg-gallery', $matches[2] )) )
								{
									$empty_posts = false;
									$class = ( !isset($class) || $class == '' ) ? 'class="alternate"' : '';
									$post_id = $post->ID;
									$name = $post->post_title;
									
									$cvg_shortcodes = $matches[0];
									$gallery_count = 0;
									$video_count = 0;
									
									foreach ($cvg_shortcodes as $cvg_shortcode) {
											
										$cvg_shortcode_params = shortcode_parse_atts($cvg_shortcode);
										
										// Gallery Present
										if(isset($cvg_shortcode_params['galleryid'])) {
											$gallery_count++;
											
											$limit_by = 0;
											if(isset($cvg_shortcode_params['limit']))
												$limit_by  = ( $cvg_shortcode_params['limit'] > 0 ) ? $cvg_shortcode_params['limit'] : 0;
											
											$videolist = $cvg_core->cvg_videodb->get_gallery($cvg_shortcode_params['galleryid'], false, 'sortorder', 'ASC', $limit_by);
											$video_count = $video_count + count($videolist);
										}
										// Video Present
										if(isset($cvg_shortcode_params['videoid'])) {
												
											$video_count++;
										}
									}
									?>
									<tr id="post-<?php echo $post_id ?>" <?php echo $class; ?> >
										<th>
											<input name="cvg_sitemapaction[]" type="checkbox" value="<?php echo $post_id ?>" />
										</th>
										<td><?php echo $post_id; ?></td>
										<td><a href="<?php echo admin_url('post.php?post='.$post_id.'&action=edit'); ?>" target="_blank" title="<?php _e("Click to edit", 'cool-video-gallery');?>"><?php echo $name; ?></a></td>
										<td><?php echo ucfirst($post->post_type); ?></td>
										<td><?php echo $gallery_count; ?></td>
										<td><?php echo $video_count; ?></td>
									</tr>
									<?php 
								}
							}
							if($empty_posts) { 
								echo '<tr><td colspan="7" align="center"><strong>' . __('No posts/pages found with videos', 'cool-video-gallery') . '</strong></td></tr>';
							}
							?>		
						</tbody>
						</table>
					</div>
				</div>
			</div>
		</form>
	</div>
	<script type="text/javascript"> 
	function checkAll(form)	{
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox") {
				if(form.elements[i].name == "cvg_sitemapaction[]") {
					if(form.elements[i].checked == true)
						form.elements[i].checked = false;
					else
						form.elements[i].checked = true;
				}
			}
		}
	}
	</script>
</div>