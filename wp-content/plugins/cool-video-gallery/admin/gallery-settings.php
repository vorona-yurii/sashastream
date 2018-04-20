<?php 
/**
 * Section to display gallery settings.
 * @author Praveen Rajan
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) 
	die(__('You are not allowed to call this page directly.', 'cool-video-gallery')); 

//Loads WP default thickbox scripts
wp_enqueue_script('thickbox');
wp_enqueue_style('thickbox');
	
$cvg_core = new CvgCore();

//Section to save gallery settings
if(isset($_POST['update_admin_Settings'])) {

	// wp_nonce_field('cvg_gallery_settings_nonce','cvg_gallery_settings_nonce_csrf');
	if ( check_admin_referer( 'cvg_gallery_settings_nonce', 'cvg_gallery_settings_nonce_csrf' ) ) {
			
		if (!ctype_digit($_POST['options']['max_cvg_gallery']) || !ctype_digit($_POST['options']['max_vid_gallery'])) {

			$cvg_core->show_video_error( __('Enter valid parameters for settings.', 'cool-video-gallery') );

		}else {

			$options_old = get_option('cvg_settings');
			$options_new = $_POST['options'];
			$options_new = array_merge($options_old, $options_new);
			update_option('cvg_settings', $options_new);

			$cvg_core->show_video_message(__('Admin settings successfully updated.', 'cool-video-gallery'));
		}
	}
}
//Section to save gallery settings
if(isset($_POST['update_Settings'])) {

	// wp_nonce_field('cvg_gallery_settings_nonce','cvg_gallery_settings_nonce_csrf');
	if ( check_admin_referer( 'cvg_gallery_settings_nonce', 'cvg_gallery_settings_nonce_csrf' ) ) {
		
		if (!ctype_digit($_POST['options']['cvg_preview_height']) || !ctype_digit($_POST['options']['cvg_preview_width']) || !ctype_digit($_POST['options']['cvg_slideshow'])) {

			$cvg_core->show_video_error( __('Enter valid parameters for settings.', 'cool-video-gallery') );

		}else {

			$options_old = get_option('cvg_settings');
			$options_new = $_POST['options'];
			$options_new['cvg_slideshow'] = intval($options_new['cvg_slideshow']) * 1000;
			$options_new = array_merge($options_old, $options_new);
			update_option('cvg_settings', $options_new);

			$cvg_core->show_video_message(__('Settings successfully updated.', 'cool-video-gallery'));
		}
	}
}


$options = get_option('cvg_settings');

wp_enqueue_script ( 'postbox' );

add_meta_box ( 'cvg_settings_gallery_meta_box', __ ( 'Gallery / Video Settings', 'cool-video-gallery' ), 'gallery_settings_metabox', 'cvg_gallery_settings', 'left', 'core' );
add_meta_box ( 'cvg_settings_admin_meta_box', __ ( 'Admin Settings', 'cool-video-gallery' ), 'gallery_settings_admin_metabox', 'cvg_gallery_settings', 'right', 'core' );

function gallery_settings_admin_metabox() {
	
	$options = get_option('cvg_settings');
	?>
	<form method="post" action="<?php echo admin_url('admin.php?page=cvg-gallery-settings'); ?>">
	
			<div class="cvg-gallery-settings-left-pane">
				<h4><?php _e('No. of Galleries per page', 'cool-video-gallery');?>:</h4>
			</div>
			<div class="cvg-gallery-settings-right-pane">	
				<textarea class="number_validate" name="options[max_cvg_gallery]" COLS=10 ROWS=1><?php echo $options['max_cvg_gallery']?></textarea>
			</div>
			<br clear="all" />
			<br clear="all" />
			<div class="cvg-gallery-settings-left-pane">	
				<h4><?php _e('No. of Videos per page', 'cool-video-gallery');?>:</h4>
			</div>
			<div class="cvg-gallery-settings-right-pane">
				<textarea class="number_validate" name="options[max_vid_gallery]" COLS=10 ROWS=1><?php echo $options['max_vid_gallery']?></textarea>
			</div>
			<br clear="all" />
			<br clear="all" />
			<div class="cvg-gallery-settings-left-pane">	
				<h4><?php _e('Youtube API Key:', 'cool-video-gallery');?></h4>
			</div>
			<div class="cvg-gallery-settings-right-pane">
				<textarea name="options[cvg_youtubeapikey]" COLS=60 ROWS=1><?php echo $options['cvg_youtubeapikey']?></textarea>
				<br/>
				<i>(<?php _e('Create and save your Youtube API Key from Youtube. For more details visit ', 'cool-video-gallery');?><a href="https://developers.google.com/youtube/v3/" target="_blank"><?php _e('Youtube Data API', 'cool-video-gallery');?></a>)</i>
			</div>
			<br clear="all" />
			<br clear="all" />
			<p style="width:100%;height:2px;background-color:#EEEEEE;"></p>
			<p><b>FFMPEG Library Settings: </b></p>
			<div class="cvg-gallery-settings-left-pane">	
				<h4><?php _e('FFMPEG Path:', 'cool-video-gallery');?></h4>
				<i><a onclick="showHelpDialog();"  title="<?php _e('Steps to configure FFMPEG for CVG', 'cool-video-gallery');?>"><?php _e('Learn More', 'cool-video-gallery');?></a></i>
			</div>
			<div class="cvg-gallery-settings-right-pane">
				<textarea name="options[cvg_ffmpegpath]" COLS=100% ROWS=1><?php echo $options['cvg_ffmpegpath']?></textarea>
			</div>
			<br clear="all" />
			<br clear="all" />
			<div class="cvg-gallery-settings-left-pane">	
				<h4><?php _e('Thumbnail Resolution:', 'cool-video-gallery');?></h4>
			</div>
			<div class="cvg-gallery-settings-right-pane">
				<textarea class="number_validate" name="options[cvg_ffmpeg_preview_width]" COLS=10 ROWS=1><?php echo isset($options['cvg_ffmpeg_preview_width']) ? $options['cvg_ffmpeg_preview_width'] : "";?></textarea>
				<span>X</span>
				<textarea class="number_validate" name="options[cvg_ffmpeg_preview_height]" COLS=10 ROWS=1><?php echo isset($options['cvg_ffmpeg_preview_height']) ? $options['cvg_ffmpeg_preview_height'] : ""; ?></textarea>
				<i>(Width X Height) in pixel</i>
			</div>
			<br clear="all" />
			<br clear="all" />
			<div class="cvg-gallery-settings-left-pane">	
				<h4><?php _e('Thumbnail Seek Location:', 'cool-video-gallery');?></h4>
			</div>
			<div class="cvg-gallery-settings-right-pane">
				<textarea class="number_validate" name="options[cvg_ffmpeg_seek_hour]" COLS=3 ROWS=1><?php echo isset($options['cvg_ffmpeg_seek_hour']) ? $options['cvg_ffmpeg_seek_hour'] : "";?></textarea>
				<span style=" vertical-align: 10px;">:</span>
				<textarea class="number_validate" name="options[cvg_ffmpeg_seek_minute]" COLS=3 ROWS=1><?php echo isset($options['cvg_ffmpeg_seek_minute']) ? $options['cvg_ffmpeg_seek_minute'] : ""; ?></textarea>
				<span style=" vertical-align: 10px;">:</span>
				<textarea class="number_validate" name="options[cvg_ffmpeg_seek_second]" COLS=3 ROWS=1><?php echo isset($options['cvg_ffmpeg_seek_second']) ? $options['cvg_ffmpeg_seek_second'] : ""; ?></textarea>
				<i>(HH : mm : ss) Duration Specification</i>
			</div>
			<br clear="all" />
			<br clear="all" />
			<?php wp_nonce_field('cvg_gallery_settings_nonce','cvg_gallery_settings_nonce_csrf'); ?>
			<div class="submit">
				<input class="button-primary" type="submit" name="update_admin_Settings" value="<?php _e('Save Admin Settings', 'cool-video-gallery');?>"  />
			</div>
	</form>
	
	<?php 
	
}

function gallery_settings_metabox() {
	
	$options = get_option('cvg_settings');
	?>
	
	<form method="post" action="<?php echo admin_url('admin.php?page=cvg-gallery-settings'); ?>">
			<div class="cvg-gallery-settings-left-pane">	
				<h4><?php _e('Preview Image Resolution:', 'cool-video-gallery');?></h4>
			</div>
			<div class="cvg-gallery-settings-right-pane">
				<textarea class="number_validate" name="options[cvg_preview_width]" COLS=10 ROWS=1><?php echo $options['cvg_preview_width']?></textarea>
				<span>X</span>
				<textarea class="number_validate" name="options[cvg_preview_height]" COLS=10 ROWS=1><?php echo $options['cvg_preview_height']?></textarea>
				<i>(Width X Height) in pixel</i>
				<br/>
				<i>Note: Use parameters 'preview-width' and 'preview-height' in shortcodes to control default preview image width and height</i>
			</div>
			<br clear="all" />
			<br clear="all" />
			<div class="cvg-gallery-settings-left-pane">	
				<h4><?php _e('Slideshow Speed ', 'cool-video-gallery');?><i><?php _e('(in Seconds)', 'cool-video-gallery');?></i>:</h4>
			</div>
			<div class="cvg-gallery-settings-right-pane">
				<textarea class="number_validate" name="options[cvg_slideshow]" COLS=10 ROWS=1><?php echo intval($options['cvg_slideshow']) / 1000;?></textarea>
			</div>
			<br clear="all" />
			<br clear="all" />
			<div class="cvg-gallery-settings-left-pane">	
				<h4><?php _e('Enable Gallery Description:', 'cool-video-gallery');?></h4>
			</div>
			<div class="cvg-gallery-settings-right-pane">
				<input type="radio" id="description_gallery_yes" value="1" name="options[cvg_gallery_description]" <?php echo ($options['cvg_gallery_description'] == 1) ? 'checked' : '';?> /><label for="description_gallery_yes"><?php _e('Yes', 'cool-video-gallery');?></label>
				<input type="radio" id="description_gallery_no" value="0" name="options[cvg_gallery_description]" <?php echo ($options['cvg_gallery_description'] == 0) ? 'checked' : '';?> /><label for="description_gallery_no"><?php _e('No', 'cool-video-gallery');?></label>
			</div>
			<br clear="all" />
			<br clear="all" />
			<div class="cvg-gallery-settings-left-pane">	
				<h4><?php _e('Enable Video Title and Description:', 'cool-video-gallery');?></h4>
			</div>
			<div class="cvg-gallery-settings-right-pane">
				<input type="radio" id="description_yes" value="1" name="options[cvg_description]" <?php echo ($options['cvg_description'] == 1) ? 'checked' : '';?> /><label for="description_yes"><?php _e('Yes', 'cool-video-gallery');?></label>
				<input type="radio" id="description_no" value="0" name="options[cvg_description]" <?php echo ($options['cvg_description'] == 0) ? 'checked' : '';?> /><label for="description_no"><?php _e('No', 'cool-video-gallery');?></label>
			</div>
			<br clear="all" />
			<br clear="all" />	
			<div class="cvg-gallery-settings-left-pane">	
				<h4><?php _e('Randomize videos in gallery:', 'cool-video-gallery');?></h4>
			</div>
			<div class="cvg-gallery-settings-right-pane">
				<input type="radio" id="random_cvg_yes" value="1" name="options[cvg_random_video]" <?php echo (isset($options['cvg_random_video']) && $options['cvg_random_video'] == 1) ? 'checked' : '';?> /><label for="random_cvg_yes"><?php _e('Yes', 'cool-video-gallery');?></label>
				<input type="radio" id="random_cvg_no" value="0" name="options[cvg_random_video]" <?php echo (isset($options['cvg_random_video']) && $options['cvg_random_video'] == 0) ? 'checked' : '';?> /><label for="random_cvg_no"><?php _e('No', 'cool-video-gallery');?></label>
			</div>
			<br clear="all" />
			<br clear="all" />
			
			<?php wp_nonce_field('cvg_gallery_settings_nonce','cvg_gallery_settings_nonce_csrf'); ?>
			<div class="submit">
				<input class="button-primary" type="submit" name="update_Settings" value="<?php _e('Save Settings', 'cool-video-gallery');?>"  />
			</div>
	</form>	
			
	<?php 
}

?>
<div class="wrap">
	<h2><?php _e('Settings', 'cool-video-gallery'); ?></h2>
	<div id="dashboard-widgets-container" class="cvg-overview">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="post-body">
				<div id="dashboard-widgets-main-content">
					<div class="postbox-container" id="main-container"
						style="width: 100%;">
                            <?php do_meta_boxes('cvg_gallery_settings', 'left', ''); ?>
                    </div>
					<div class="postbox-container" id="side-container"
						style="width: 100%;">
                            <?php do_meta_boxes('cvg_gallery_settings', 'right', ''); ?>
                    </div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
        postboxes.add_postbox_toggles('cvg_gallery_settings');
        jQuery('.number_validate').keypress(function(e) {
			return e.charCode >= 48 && e.charCode <= 57;
		});
    });
	function showHelpDialog() {
		tb_show("FFMPEG Configuration", "#TB_inline?width=650&height=200&inlineId=ffmpeg_help&modal=false", false);
	}
</script>
<div style="display:none;">
	<div id="ffmpeg_help">
		<div>
		<?php _e('FFMPEG is required to generate thumbnail when uploading videos.', 'cool-video-gallery');?>
		<ul>
			<li>
				<?php _e('1. Visit FFMPEG to download library at ', 'cool-video-gallery');?><a target="_blank" href="https://ffmpeg.org/">https://ffmpeg.org/</a>
			</li>
			<li>
				<?php _e('2. Install the package to WebServer and note the path of FFMPEG library file.', 'cool-video-gallery');?>
			</li>
			<li>
				<?php _e('3. Save the path of FFMPEG library in Gallery Settings.', 'cool-video-gallery');?>
			</li>
			<li>
				<?php _e('4. Go to Overview menu of CVG to check if FFMPEG is installed properly.', 'cool-video-gallery');?>
			</li>
		</ul>
		<p><?php _e('Note: Agree to Terms and Conditions of FFMPEG Library at ', 'cool-video-gallery');?><a target="_blank" href="https://ffmpeg.org/legal.html">https://ffmpeg.org/legal.html</a></p>
		</div>
	</div>
</div>
