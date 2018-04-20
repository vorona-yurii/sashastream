<?php
/**
 * Section to display gallery overview
 * @author Praveen Rajan
 */

if (preg_match ( '#' . basename ( __FILE__ ) . '#', $_SERVER ['PHP_SELF'] ))
	die ( __('You are not allowed to call this page directly.', 'cool-video-gallery') );

class CvgGalleryOverview {
	
	var $cvg_core;
	var $title;
	
	function CvgGalleryOverview() {
		
		$this->cvg_core = new CvgCore ();
		$this->title =  'Cool Video Gallery Overview';
		
		wp_enqueue_script ( 'postbox' );
		
		add_meta_box ( 'cvg_overview_meta_box', __ ( 'Welcome to Cool Video Gallery !', 'cool-video-gallery' ), array (
				&$this,
				'overview_metabox' 
		), 'cvg_overview', 'left', 'core' );
		add_meta_box ( 'cvg_rate_meta_box', __ ( 'Do you like this Plugin?', 'cool-video-gallery' ), array (
				&$this,
				'sharing_metabox' 
		), 'cvg_overview', 'right', 'core' );
		add_meta_box ( 'cvg_upcoming_features_meta_box', __ ( 'Upcoming Features!!!', 'cool-video-gallery' ), array (
				&$this,
				'upcomingfeature_metabox'
		), 'cvg_overview', 'right', 'core' );
		add_meta_box ( 'cvg_about_author_meta_box', __ ( 'About Author - Praveen Rajan', 'cool-video-gallery'), array (
				&$this,
				'about_author_metabox' 
		), 'cvg_overview', 'right', 'core' );
		add_meta_box ( 'cvg_server_meta_box', __ ( 'Server / Plugin Info', 'cool-video-gallery'), array (
				&$this,
				'serverinfo_metabox' 
		), 'cvg_overview', 'left', 'core' );
		add_meta_box ( 'cvg_shortcode_meta_box', __ ( 'Shortcode Configuration Options', 'cool-video-gallery'), array (
				&$this,
				'shortcodeinfo_metabox'
		), 'cvg_overview', 'left', 'core' );
		
		
		
	}
	
	// Show Overview of Gallery /Videos
	function overview_metabox() {
		$this->cvg_core->gallery_overview ();
	}
	
	// Shows rating section
	function sharing_metabox() {
		$this->cvg_core->cvg_plugin_ratings ();
	}
	
	// Shows server info section
	function serverinfo_metabox() {
		$this->cvg_core->cvg_serverinfo ();
	}
	
	// Shows author details section
	function about_author_metabox() {
		$this->cvg_core->cvg_authorinfo ();
	}
	
	function shortcodeinfo_metabox() {
		$this->cvg_core->cvg_shortcodeinfo ();
	}
	
	function upcomingfeature_metabox() {
		$this->cvg_core->cvg_upcoming_features ();
	}
	
	function display_overview() {
		?>
		<div class="wrap">
			<h2><?php echo _e( $this->title, 'cool-video-gallery' ); ?></h2>
			<div id="dashboard-widgets-container" class="cvg-overview">
				<div id="dashboard-widgets" class="metabox-holder">
					<div id="post-body">
						<div id="dashboard-widgets-main-content">
							<div class="postbox-container" id="main-container"
								style="width: 65%;">
		                            <?php do_meta_boxes('cvg_overview', 'left', ''); ?>
		                    </div>
							<div class="postbox-container" id="side-container"
								style="width: 35%;">
		                            <?php do_meta_boxes('cvg_overview', 'right', ''); ?>
		                    </div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<script type="text/javascript">
			jQuery(document).ready(function($) {
		        postboxes.add_postbox_toggles('cvg-overview');
		    });
		</script>
		<?php
	}
}
$CVG_Overview = new CvgGalleryOverview ();
$CVG_Overview->display_overview ();
?>