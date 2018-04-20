<?php 
/**
 * Section to display video player settings
 * @author Praveen Rajan
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) 
	die(__('You are not allowed to call this page directly.', 'cool-video-gallery')); 

//Loads WP default thickbox scripts
wp_enqueue_script('thickbox');
wp_enqueue_style('thickbox');

$cvg_core = new CvgCore();

wp_enqueue_script ( 'postbox' );

add_meta_box ( 'cvg_player_admin_meta_box', __ ( 'JWPlayer Settings', 'cool-video-gallery' ), 'player_settings_admin_metabox', 'cvg_player_settings', 'left', 'core' );

if(isset($_POST['update_CVGSettings'])){
	
	// wp_nonce_field('cvg_player_settings_nonce','cvg_player_settings_nonce_csrf');
	if ( check_admin_referer( 'cvg_player_settings_nonce', 'cvg_player_settings_nonce_csrf' ) ) {
		
		$options_player = $_POST['options_player'];
		update_option('cvg_player_settings', $options_player);
		
		$cvg_core->show_video_message(__('Player settings successfully updated.', 'cool-video-gallery'));
	}
}

$options_player = get_option('cvg_player_settings');

function player_settings_admin_metabox() {
	
	$cvg_core = new CvgCore();
	$options_player = get_option('cvg_player_settings');
	?>
	<form method="post" action="<?php echo admin_url('admin.php?page=cvg-player-settings'); ?>">
		
		<div class="cvg-gallery-settings-left-pane">	
			<h4><?php _e('JWPlayer License Key:', 'cool-video-gallery');?></h4>
		</div>
		<div class="cvg-gallery-settings-right-pane">
			<textarea name="options_player[cvgplayer_jwplayer_key]" COLS=60 ROWS=1><?php echo isset($options_player['cvgplayer_jwplayer_key']) ?  $options_player['cvgplayer_jwplayer_key'] : "";?></textarea>
			<br/>
			<i>(<?php _e('Register with JWPlayer and save your JWPlayer License Key here. For more details visit ', 'cool-video-gallery');?><a href="https://www.jwplayer.com/" target="_blank"><?php _e('JWPlayer', 'cool-video-gallery');?></a>)</i>
			<br/>
			<i>You here by agree to <a href="https://www.jwplayer.com/tos/" target="_blank">Terms of Service</a> of JW Player</i>
		</div>
		<br clear="all" />
		<br clear="all" />
		<div class="cvg-gallery-settings-left-pane">
			<h4><?php _e('Video Player Resolution:', 'cool-video-gallery');?></h4>
		</div>
		<div class="cvg-gallery-settings-right-pane">	
			<textarea name="options_player[cvgplayer_width]" COLS=10 ROWS=1><?php echo $options_player['cvgplayer_width']?></textarea>
			<span>X</span>
			<textarea name="options_player[cvgplayer_height]" COLS=10 ROWS=1><?php echo $options_player['cvgplayer_height']?></textarea>
			<i>(Width X Height) in pixel</i>
		</div>
		<br clear="all" />
		<br clear="all" />
		<div class="cvg-gallery-settings-left-pane">	
			<h4><?php _e('Choose skin for video player:', 'cool-video-gallery');?>
				<i><a onclick="showHelpDialog();" id="skin_help_popup"  title="<?php _e('Steps to install new skin to CVG', 'cool-video-gallery');?>"><?php _e('Learn More', 'cool-video-gallery');?></a></i>
			</h4>	
		</div>
		<?php 

		$skins = $cvg_core->get_dir_skin( dirname(dirname(__FILE__)) . "/third_party_lib/jwplayer_7.3.6/skins/", ".css", "", false);
		
		$option = '<option value="">No Skin</option>';
		foreach ($skins as $value){
			$option .= '<option value="' . $value . '" '; 
			if ($options_player['cvgplayer_skin'] ==  $value ){
				$option .=  'SELECTED >' . $value .'</option>';
			}else{
				$option .=  '>' . $value .'</option>';
			}
		}
		?>
		<div class="cvg-gallery-settings-right-pane">		
			<select name="options_player[cvgplayer_skin]">
				<?php echo $option;?>				
			</select>
		</div>	
		<br clear="all" />
		<br clear="all" />
		<div class="cvg-gallery-settings-left-pane">	
			<h4><?php _e('Default Volume:', 'cool-video-gallery');?></h4>
		</div>			
		<div class="cvg-gallery-settings-right-pane">		
			<textarea name="options_player[cvgplayer_volume]" COLS=10 ROWS=1><?php echo $options_player['cvgplayer_volume']?></textarea>
		</div>
		<br clear="all" />
		<br clear="all" />
		<div class="cvg-gallery-settings-left-pane">	
			<h4><?php _e('Autoplay: ', 'cool-video-gallery');?></h4>
		</div>	
		<div class="cvg-gallery-settings-right-pane">
				<label for="autoplay_true"><input type="radio" id="autoplay_true" name="options_player[cvgplayer_autoplay]" value="1" <?php if ($options_player['cvgplayer_autoplay']) { _e('checked="checked"'); }?>/><?php _e('True', 'cool-video-gallery');?></label>
				<label for="autoplay_false"><input type="radio" id="autoplay_false" name="options_player[cvgplayer_autoplay]" value="0" <?php if (!$options_player['cvgplayer_autoplay']) { _e('checked="checked"'); }?>/><?php _e('False', 'cool-video-gallery');?></label>
		</div>	
		<br clear="all" />
		<br clear="all" />
		<div class="cvg-gallery-settings-left-pane">	
			<h4><?php _e('Mute Volume: ', 'cool-video-gallery');?></h4>
		</div>	
		<div class="cvg-gallery-settings-right-pane">
				<label for="mute_true"><input type="radio" id="mute_true" name="options_player[cvgplayer_mute]" value="1" <?php if ($options_player['cvgplayer_mute']) { _e('checked="checked"'); }?>/><?php _e('True', 'cool-video-gallery');?></label>
				<label for="mute_false"><input type="radio" id="mute_false" name="options_player[cvgplayer_mute]" value="0" <?php if (!$options_player['cvgplayer_mute']) { _e('checked="checked"'); }?>/><?php _e('False', 'cool-video-gallery');?></label>
		</div>	
		<br clear="all" />
		<br clear="all" />
		<div class="cvg-gallery-settings-left-pane">	
			<h4><?php _e('Auto close video popup on completion: ', 'cool-video-gallery');?></h4>
		</div>	
		<div class="cvg-gallery-settings-right-pane">
				<label for="auto_close_single_true"><input type="radio" id="auto_close_single_true" name="options_player[cvgplayer_auto_close_single]" value="1" <?php if ($options_player['cvgplayer_auto_close_single']) { _e('checked="checked"'); }?>/><?php _e('True', 'cool-video-gallery');?></label>
				<label for="auto_close_single_false"><input type="radio" id="auto_close_single_false" name="options_player[cvgplayer_auto_close_single]" value="0" <?php if (!$options_player['cvgplayer_auto_close_single']) { _e('checked="checked"'); }?>/><?php _e('False', 'cool-video-gallery');?></label>
		</div>	
		<br clear="all" />
		<br clear="all" />
		<div class="cvg-gallery-settings-left-pane">	
			<h4><?php _e('Enable Share: ', 'cool-video-gallery');?></h4>
		</div>	
		<div class="cvg-gallery-settings-right-pane">
				<label for="share_option_true"><input type="radio" id="share_option_true" name="options_player[cvgplayer_share_option]" value="1" <?php if ($options_player['cvgplayer_share_option']) { _e('checked="checked"'); }?>/><?php _e('True', 'cool-video-gallery');?></label>
				<label for="share_option_false"><input type="radio" id="share_option_false" name="options_player[cvgplayer_share_option]" value="0" <?php if (!$options_player['cvgplayer_share_option']) { _e('checked="checked"'); }?>/><?php _e('False', 'cool-video-gallery');?></label>
		</div>	
		<br clear="all" />
		<br clear="all" />
		<div class="cvg-gallery-settings-left-pane">	
			<h4><?php _e('Video Display: ', 'cool-video-gallery');?></h4>
		</div>	
		<div class="cvg-gallery-settings-right-pane">	
				<label for="display_none"><input type="radio" id="display_none" name="options_player[cvgplayer_stretching]" value="none" <?php if ($options_player['cvgplayer_stretching'] == "none") { _e('checked="checked"'); }?>/><?php _e('None', 'cool-video-gallery');?></label>
				<label for="display_exactfit"><input type="radio" id="display_exactfit" name="options_player[cvgplayer_stretching]" value="exactfit" <?php if ($options_player['cvgplayer_stretching'] == "exactfit") { _e('checked="checked"'); }?>/><?php _e('Exact Fit', 'cool-video-gallery');?></label>
				<label for="display_uniform"><input type="radio" id="display_uniform" name="options_player[cvgplayer_stretching]" value="uniform" <?php if ($options_player['cvgplayer_stretching'] == "uniform") { _e('checked="checked"'); }?>/><?php _e('Uniform', 'cool-video-gallery');?></label>
				<label for="display_fill"><input type="radio" id="display_fill" name="options_player[cvgplayer_stretching]" value="fill" <?php if ($options_player['cvgplayer_stretching'] == "fill") { _e('checked="checked"'); }?>/><?php _e('Fill', 'cool-video-gallery');?></label>
		</div>	
		<br clear="all" />
		<br clear="all" />
		<?php wp_nonce_field('cvg_player_settings_nonce','cvg_player_settings_nonce_csrf'); ?>
		<div class="submit">
			<input class="button-primary" type="submit" name="update_CVGSettings" value="<?php _e('Save Player Settings', 'cool-video-gallery');?>" />
		</div>
	</form>
	
	<?php 
}
?>
<div class="wrap">
	<h2><?php _e('Video Player Settings', 'cool-video-gallery'); ?></h2>
	<div id="dashboard-widgets-container" class="cvg-overview">
		<div id="dashboard-widgets" class="metabox-holder">
			<div id="post-body">
				<div id="dashboard-widgets-main-content">
					<div class="postbox-container" id="main-container"
						style="width: 100%;">
                            <?php do_meta_boxes('cvg_player_settings', 'left', ''); ?>
                    </div>
				</div>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	jQuery(document).ready(function($) {
        postboxes.add_postbox_toggles('cvg_player_settings');
    });
	function showHelpDialog() {
		tb_show("", "#TB_inline?width=650&height=280&inlineId=skin_help&modal=false", false);
	}
</script>
<div style="display:none;">
	<div id="skin_help">
		<div>
		<?php _e('New JWPlayer skins available to use now. It\'s also easy to create and upload skin of your choice and that too in a few steps.', 'cool-video-gallery');?>
		<ul>
			<li>
				<?php _e('1. Find out more information on JWPlayer Skins at ', 'cool-video-gallery');?><a target="_blank" href="https://www.jwplayer.com/products/jwplayer/skins/">https://www.jwplayer.com/products/jwplayer/skins/</a>.
			</li>
			<li>
				<?php _e('2. To create/customize skins check documentation at ', 'cool-video-gallery');?><a target="_blank" href="https://developer.jwplayer.com/jw-player/docs/developer-guide/customization/css-skinning/skins_creating/">https://developer.jwplayer.com/jw-player/docs/developer-guide/customization/css-skinning/skins_creating/</a>.
			</li>
			<li>
				<?php _e('3. Locate your WorPress site installation folder in Webserver using an FTP client.', 'cool-video-gallery');?>
			</li>
			<li>
				<?php _e('4. Navigate to the following path <i><b>/wp-content/plugins/cool-video-gallery/third_party_lib/jwplayer_7.3.6/skins/</b></i>', 'cool-video-gallery');?>
			</li>
			<li>
				<?php _e('5. Upload the created skin file to this location in WebServer.', 'cool-video-gallery');?>
			</li>
			<li>
				<?php _e('6. In admin section of WordPress navigate to Video Player Settings Panel of Cool Video Gallery plugin.', 'cool-video-gallery')?> 
			</li>
			<li>
				<?php _e('7. The new JWPlayer skin will be listed in the drop down. Select the skin and save the Save Player Settings.', 'cool-video-gallery');?>
			</li>
			<li>
				<?php _e('8. Enjoy the skins from JW Player together with features of CVG.', 'cool-video-gallery');?>
			</li>
		</ul>
		</div>
	</div>
</div>