<?php
/**
 * cvg_showcase_widget - Widget to show video gallery as a showcase
 *
 * @package Cool Video Gallery
 * @author Praveen Rajan
 * @copyright 2010 - 2016
 * @access public
 */
class cvg_showcase_widget extends WP_Widget {

	function cvg_showcase_widget() {
		
		$widget_ops = array('classname' => 'cvg_widget_showcase', 'description' => __( 'Show a Cool Video Gallery Showcase', 'cool-video-gallery') );
		parent::__construct('cvg_showcase', __('CVG Showcase', 'cool-video-gallery'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );
			
		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __('') : $instance['title'], $instance, $this->id_base);
		
		if ( ! $number = (int) $instance['number'] )
 			$number = 5;
 		else if ( $number < 1 )
 			$number = 1;
		
		$cvg_core = new CvgCore();
		
		$arg = array ();
		$arg ['galleryid'] = $instance['galleryid'];
		$arg ['mode'] = "showcase";
		$arg ['limit'] = $number;
		$arg ['preview-width'] = (int) $instance['preview_width'];
		$arg ['preview-height'] = (int) $instance['preview_height'];
		$arg ['width'] = (int) $instance['width'];
		$arg ['height'] = (int) $instance['height'];
		
		$out = $cvg_core->video_show_gallery($arg, "widget");
		if ( !empty( $out ) ) {
			echo $before_widget;
			if ( $title)
				echo $before_title . $title . $after_title;
		?>
		<span class="cvg_showcase widget">
			<?php echo $out; ?>
		</span>
		<?php
			echo $after_widget;
		}
  
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['galleryid'] = (int) $new_instance['galleryid'];
		$instance['number'] = (int) $new_instance['number'];
		$instance['width'] = (int) $new_instance['width'];
		$instance['height'] = (int) $new_instance['height'];
		$instance['preview_width'] = (int) $new_instance['preview_width'];
		$instance['preview_height'] = (int) $new_instance['preview_height'];
		
		return $instance;
	}

	function form( $instance ) {
		
		$options_player = get_option('cvg_player_settings');
		$options = get_option('cvg_settings');
		
		$player_width = $options_player['cvgplayer_width'];
		$player_height = $options_player['cvgplayer_height'];
		$thumb_width = $options ['cvg_preview_width'];
		$thumb_height = $options ['cvg_preview_height'];
		
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('CVG Showcase', 'cool-video-gallery'), 'galleryid' => '0') );
		
		$title  = esc_attr( $instance['title'] );
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
		$width = isset($instance['width']) ? absint($instance['width']) : $player_width;
		$height = isset($instance['height']) ? absint($instance['height']) : $player_height;
		$preview_width = isset($instance['preview_width']) ? absint($instance['preview_width']) : $thumb_width;
		$preview_height = isset($instance['preview_height']) ? absint($instance['preview_height']) : $thumb_height;
		
		$cvg_core = new CvgCore();
		$tables = $cvg_core->cvg_videodb->get_all_gallery_widgets();
		
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'cool-video-gallery'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('galleryid'); ?>"><?php _e('Select Gallery:', 'cool-video-gallery'); ?></label>
			<select size="1" name="<?php echo $this->get_field_name('galleryid'); ?>" id="<?php echo $this->get_field_id('galleryid'); ?>" class="widefat">
			<?php
			if($tables) {
				foreach($tables as $table) {
				echo '<option value="'.$table->gid.'" ';
				if ($table->gid == $instance['galleryid']) echo "selected='selected' ";
				echo '>'.$table->name.'</option>'."\n\t"; 
				}
			}
			?>
			</select>
		</p>
		<p>
			<label><?php _e('Player Resolution', 'cool-video-gallery'); ?></label>
			<input type="text" name="<?php echo $this->get_field_name('width'); ?>" id="<?php echo $this->get_field_id('width'); ?>" size="3" value="<?php echo $width; ?>"/> X 
		    <input type="text" name="<?php echo $this->get_field_name('height'); ?>" id="<?php echo $this->get_field_id('height'); ?>" size="3" value="<?php echo $height; ?>"/> px
		</p>
		<p>
			<label><?php _e('Preview Image Resolution', 'cool-video-gallery'); ?></label>
			<input type="text" name="<?php echo $this->get_field_name('preview_width'); ?>" id="<?php echo $this->get_field_id('preview_width'); ?>" size="3" value="<?php echo $preview_width; ?>"/> X 
		    <input type="text" name="<?php echo $this->get_field_name('preview_height'); ?>" id="<?php echo $this->get_field_id('preview_height'); ?>" size="3" value="<?php echo $preview_height; ?>"/> px
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Videos to show:', 'cool-video-gallery'); ?></label>
			<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>
<?php	
	}
}

// register it
add_action('widgets_init', create_function('', 'return register_widget("cvg_showcase_widget");'));


/**
 * cvgSlideShow - Widget to show video gallery as a slideshow
 *
 * @package Cool Video Gallery
 * @author Praveen Rajan
 * @copyright 2010 - 2016
 * @access public
 */

class cvg_slideshow_widget extends WP_Widget {

	function cvg_slideshow_widget() {
		$widget_ops = array('classname' => 'cvg_widget_slideshow', 'description' => __( 'Show a Cool Video Gallery Slideshow', 'cool-video-gallery') );
		parent::__construct('cvg_slideshow', __('CVG Slideshow', 'cool-video-gallery'), $widget_ops);
	}

	function widget( $args, $instance ) {
		extract( $args );
			
		$title = apply_filters('widget_title', empty( $instance['title'] ) ? __('') : $instance['title'], $instance, $this->id_base);
		
		if ( ! $number = (int) $instance['number'] )
 			$number = 5;
 		else if ( $number < 1 )
 			$number = 1;
 			
		$cvg_core = new CvgCore();
		
		$arg = array ();
		$arg ['galleryid'] = $instance['galleryid'];
		$arg ['mode'] = "slideshow";
		$arg ['limit'] = $number;
		$arg ['preview-width'] = (int) $instance['preview_width'];
		$arg ['preview-height'] = (int) $instance['preview_height'];
		$arg ['width'] = (int) $instance['width'];
		$arg ['height'] = (int) $instance['height'];
		
		$out = $cvg_core->video_show_gallery($arg, "widget");
		if ( !empty( $out ) ) {
			echo $before_widget;
			if ( $title)
				echo $before_title . $title . $after_title;
		?>
		<span class="cvg_slideshow widget">
			<?php echo $out; ?>
		</span>
		<?php
			echo $after_widget;
		}
  
	}
	
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$instance['galleryid'] = (int) $new_instance['galleryid'];
		$instance['number'] = (int) $new_instance['number'];
		$instance['width'] = (int) $new_instance['width'];
		$instance['height'] = (int) $new_instance['height'];
		$instance['preview_width'] = (int) $new_instance['preview_width'];
		$instance['preview_height'] = (int) $new_instance['preview_height'];
		
		return $instance;
	}

	function form( $instance ) {
		
		$options_player = get_option('cvg_player_settings');
		$options = get_option('cvg_settings');
		
		$player_width = $options_player['cvgplayer_width'];
		$player_height = $options_player['cvgplayer_height'];
		$thumb_width = $options ['cvg_preview_width'];
		$thumb_height = $options ['cvg_preview_height'];
		
		//Defaults
		$instance = wp_parse_args( (array) $instance, array( 'title' => __('CVG Slideshow', 'cool-video-gallery'), 'galleryid' => '0') );
		$title  = esc_attr( $instance['title'] );
		$number = isset($instance['number']) ? absint($instance['number']) : 5;
		$width = isset($instance['width']) ? absint($instance['width']) : $player_width;
		$height = isset($instance['height']) ? absint($instance['height']) : $player_height;
		$preview_width = isset($instance['preview_width']) ? absint($instance['preview_width']) : $thumb_width;
		$preview_height = isset($instance['preview_height']) ? absint($instance['preview_height']) : $thumb_height;
		
		$cvg_core = new CvgCore();
		$tables = $cvg_core->cvg_videodb->get_all_gallery_widgets();
		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'cool-video-gallery'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
		<p>
			<label for="<?php echo $this->get_field_id('galleryid'); ?>"><?php _e('Select Gallery:', 'cool-video-gallery'); ?></label>
			<select size="1" name="<?php echo $this->get_field_name('galleryid'); ?>" id="<?php echo $this->get_field_id('galleryid'); ?>" class="widefat">
			<?php
			if($tables) {
				foreach($tables as $table) {
				echo '<option value="'.$table->gid.'" ';
				if ($table->gid == $instance['galleryid']) echo "selected='selected' ";
				echo '>'.$table->name.'</option>'."\n\t"; 
				}
			}
			?>
			</select>
		</p>
		<p>
			<label><?php _e('Player Resolution', 'cool-video-gallery'); ?></label>
			<input type="text" name="<?php echo $this->get_field_name('width'); ?>" id="<?php echo $this->get_field_id('width'); ?>" size="3" value="<?php echo $width; ?>"/> X 
		    <input type="text" name="<?php echo $this->get_field_name('height'); ?>" id="<?php echo $this->get_field_id('height'); ?>" size="3" value="<?php echo $height; ?>"/> px
		</p>
		<p>
			<label><?php _e('Preview Image Resolution', 'cool-video-gallery'); ?></label>
			<input type="text" name="<?php echo $this->get_field_name('preview_width'); ?>" id="<?php echo $this->get_field_id('preview_width'); ?>" size="3" value="<?php echo $preview_width; ?>"/> X 
		    <input type="text" name="<?php echo $this->get_field_name('preview_height'); ?>" id="<?php echo $this->get_field_id('preview_height'); ?>" size="3" value="<?php echo $preview_height; ?>"/> px
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of Videos to show:', 'cool-video-gallery'); ?></label>
			<input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" />
		</p>
<?php	
	}
}

// register it
add_action('widgets_init', create_function('', 'return register_widget("cvg_slideshow_widget");'));


/**
 * cvgShowCaseWidget($galleryID)
 * Function for templates without widget support
 * 
 * @param integer $galleryID 
 * @return echo the widget content
 * @author Praveen Rajan
 */
function cvgShowCaseWidget($galleryID, $limit) {
	$cvg_core = new CvgCore();
	
	$arg = array ();
	$arg ['galleryid'] = $galleryID;
	$arg ['mode'] = "showcase";
	$arg ['limit'] = $limit;
	
	echo $cvg_core->video_show_gallery($arg);
}

/**
 * cvgSlideShowWidget($galleryID)
 * Function for templates without widget support
 * 
 * @param integer $galleryID 
 * @return echo the widget content
 * @author Praveen Rajan
 */
function cvgSlideShowWidget($galleryID, $limit) {
	$cvg_core = new CvgCore();
	
	$arg = array ();
	$arg ['galleryid'] = $galleryID;
	$arg ['mode'] = "slideshow";
	$arg ['limit'] = $limit;
	
	echo $cvg_core->video_show_gallery($arg);
}
?>