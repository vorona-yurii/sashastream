<?php
/**
 * Section to uninstall plugin
 * @author Praveen Rajan
 */

if (preg_match ( '#' . basename ( __FILE__ ) . '#', $_SERVER ['PHP_SELF'] ))
	die ( __('You are not allowed to call this page directly.', 'cool-video-gallery') );

$cvg_main = CoolVideoGallery::get_instance();

wp_enqueue_style('cvg-styles', $cvg_main->plugin_url . 'css/cvg-styles.css', '');

if (isset ( $_POST ['uninstallplugin'] )) {
	
	// wp_nonce_field('cvg_plugin_uninstall_nonce','cvg_plugin_uninstall_nonce_csrf');
	if (check_admin_referer ( 'cvg_plugin_uninstall_nonce', 'cvg_plugin_uninstall_nonce_csrf' )) {
		$cvg_main->cvg_uninstall ();
		require_once (ABSPATH . 'wp-admin/includes/plugin.php');
		deactivate_plugins ( dirname ( dirname ( __FILE__ ) ) . '/cool-video-gallery.php' );
		?>
		<script type="text/javascript">location.href= "<?php  echo admin_url('plugins.php');?>";</script>
		<?php
	}
}
?>
<div class="wrap">
	<h2><?php _e('CVG Uninstall', 'cool-video-gallery');?></h2>
	<div id="dashboard-widgets-wrap">
		<form method="post"
			action="<?php  echo admin_url('admin.php?page=cvg-plugin-uninstall');?>">
				<div style="width: 100%;" class="postbox-container">
					<div class="postbox">
						<p style="padding-left: 15px;color:red;">
							<?php _e( '<b>Note:</b> For future use, please backup all your video gallery files and plugin database tables before you uninstall this plugin.', 'cool-video-gallery');?>
						</p>
							<?php wp_nonce_field('cvg_plugin_uninstall_nonce','cvg_plugin_uninstall_nonce_csrf'); ?>
						<div style="padding-left: 15px;">
							<input type="submit" class="button-primary cvg-uninstall-button" 
								name="uninstallplugin" value="<?php _e("Uninstall CVG", 'cool-video-gallery');?>" />
						</div>
						<br clear="all" />
					</div>
				</div>
		</form>
	</div>
</div>