<?php 
/**
 * Class specifying main functions of video gallery.
 * 
 * @author Praveen Rajan
 *
 */
class CvgCore{
	
	var $default_gallery_path;
	var $winabspath;
	var $cvg_videodb;
	var $cvg_instance;
	
	/**
	 * Initializes values
	 */
	function CvgCore() {
		
		$this->cvg_instance = CoolVideoGallery::get_instance();
		$this->default_gallery_path = $this->cvg_instance->default_gallery_path;
		$this->winabspath = $this->cvg_instance->winabspath;
		
		$this->cvg_videodb = new CvgVideoDB();
	}
	
	/**
	 * Function to upload and add gallery.
	 */
	function processor(){
		
    	if (isset($_POST['addgallery']) && $_POST['addgallery']){

			// wp_nonce_field('cvg_add_gallery_nonce','cvg_add_gallery_nonce_csrf');
			// if this fails, check_admin_referer() will automatically print a "failed" page and die.
			if ( check_admin_referer( 'cvg_add_gallery_nonce', 'cvg_add_gallery_nonce_csrf' ) ) {
			
	    		$newgallery = esc_attr( $_POST['galleryname'] );
	    		if(isset($_POST['gallerydesc'])) 
	    			$gallery_desc = esc_attr ( $_POST['gallerydesc'] );
				else 
					$gallery_desc = '';
	    		if ( !empty($newgallery) )
	    			$this->create_gallery($newgallery, $gallery_desc);
	    		else
	    			$this->show_video_error( __('No valid gallery name!', 'cool-video-gallery') );
			}
    	}
		
		if (isset($_POST['uploadvideo']) && $_POST['uploadvideo']){
			
			// wp_nonce_field('cvg_upload_video_nonce','cvg_upload_video_nonce_csrf');
			if ( check_admin_referer( 'cvg_upload_video_nonce', 'cvg_upload_video_nonce_csrf' ) ) {
	    		if ( $_FILES['videofiles']['error'][0] == 0 ){
	    			$messagetext = $this->upload_videos();
	    		}else{
	    			$mess = sprintf(__('Upload failed! %s', 'cool-video-gallery'), $this->decode_upload_error( $_FILES['videofiles']['error'][0]));
	    			$this->show_video_error($mess);
	    		}
			}	
    	}
		
		if(isset($_POST['addvideo']) && $_POST['addvideo']) {
			
			// wp_nonce_field('cvg_attach_youtube_nonce','cvg_attach_youtube_nonce_csrf');
			if ( check_admin_referer( 'cvg_attach_youtube_nonce', 'cvg_attach_youtube_nonce_csrf' ) ) {
				if(empty($_POST['videourl']))
					$this->show_video_error( __('Enter a valid Youtube video URL.', 'cool-video-gallery') );
				else {
					$this->add_youtube_videos();
				}
			}
		}
		
		if(isset($_POST['addmedia']) && $_POST['addmedia']) {
			
			// wp_nonce_field('cvg_add_media_nonce','cvg_add_media_nonce_csrf');
			if ( check_admin_referer( 'cvg_add_media_nonce', 'cvg_add_media_nonce_csrf' ) ) {
				
				$this->add_media_videos();
			}
		}
	}
	
	/**
	 * Function to create a new gallery & folder
	 * 
	 * @param string $gallerytitle
	 * @param string $defaultpath
	 * @param bool $output if the function should show an error messsage or not
	 */
	function create_gallery($gallerytitle, $gallery_desc) {

		$defaultpath = $this->default_gallery_path;	
		
		$galleryname = sanitize_file_name( $gallerytitle );
		$video_path = $defaultpath . $galleryname;
		$videoRoot = $this->winabspath . $defaultpath;
		$txt = '';

		if ( empty($galleryname) ) {	
			$this->show_video_error( __('No valid gallery name!', 'cool-video-gallery') );
			return false;
		}
		
		if ( !is_dir($videoRoot) ) {
			if ( !wp_mkdir_p( $videoRoot ) ) {
				$txt  = __('Directory', 'cool-video-gallery').' <strong>' . $defaultpath . '</strong> '.__('didn\'t exist. Please create first the main gallery folder', 'cool-video-gallery').'!<br />';
				$txt .= __('Check this link, if you didn\'t know how to set the permission :', 'cool-video-gallery') . ' <a href="http://codex.wordpress.org/Changing_File_Permissions" target="_blank">http://codex.wordpress.org/Changing_File_Permissions</a>';
				$this->show_video_error($txt);
				return false;
			}
		}

		if ( !is_writeable( $videoRoot ) ) {
			$txt  = __('Directory', 'cool-video-gallery').' <strong>' . $defaultpath . '</strong> '.__('is not writeable !', 'cool-video-gallery').'<br />';
			$txt .= __('Check this link, if you didn\'t know how to set the permission :', 'cool-video-gallery').' <a href="http://codex.wordpress.org/Changing_File_Permissions">http://codex.wordpress.org/Changing_File_Permissions</a> ';
			$this->show_video_error($txt);
			return false;
		}

		if ( !is_dir($this->winabspath . $video_path) ) {
			if ( !wp_mkdir_p ($this->winabspath . $video_path) ) 
				$txt  = __('Unable to create directory ', 'cool-video-gallery').$video_path.'!<br />';
		}
		
		if ( !is_writeable($this->winabspath . $video_path ) ) {
			$txt .= __('Directory', 'cool-video-gallery').' <strong>'.$video_path.'</strong> '.__('is not writeable !', 'cool-video-gallery').'<br />';
		}
		
		if ( !is_dir($this->winabspath . $video_path . '/thumbs') ) {				
			if ( !wp_mkdir_p ( $this->winabspath . $video_path . '/thumbs') ) 
				$txt .= __('Unable to create directory ', 'cool-video-gallery').' <strong>' . $video_path . '/thumbs !</strong>';
		}
		
		if ( !empty($txt) ) {
			rmdir($this->winabspath . $video_path . '/thumbs');
			rmdir($this->winabspath . $video_path);
		}
		
		$result = $this->cvg_videodb->add_gallery($galleryname, $video_path, $gallerytitle, $gallery_desc);
		
		if($result) {
			
			$message = sprintf(__("Gallery '%s' successfully created.", 'cool-video-gallery'), $galleryname);
			$this->show_video_message($message);
			return true;
		}else {
			$this->show_video_error( _n( 'Gallery', 'Galleries', 1, 'cool-video-gallery' ) .' <strong>\'' . $galleryname . '\'</strong> '.__('already exists', 'cool-video-gallery'));
			return false;
		}
	}
	
	/**
	 * Function for uploading of videos via the upload form
	 * 
	 * @return void
	 */
	function upload_videos() {
	
		// Videos must be an array
		$videoslist = array();
	
		// get selected gallery
		$galleryID = (int) $_POST['galleryselect'];
	
		if ($galleryID == 0) {
			$this->show_video_error(__('No gallery selected !', 'cool-video-gallery'));
			return;	
		}
		
		// get the path to the gallery	
		$gallery = $this->cvg_videodb->find_gallery($galleryID);
		
		if ( empty($gallery->path) ){
			$this->show_video_error(__('Failure in database, no gallery path set !', 'cool-video-gallery'));
			return;
		} 
	
		// read list of videos
		$dirlist = $this->scandir_video_name($gallery->abspath);
		
		$videofiles = $_FILES['videofiles'];
		
		if (is_array($videofiles)) {
			foreach ($videofiles['name'] as $key => $value) {
	
				// look only for uploded files
				if ($videofiles['error'][$key] == 0) {
					
					$temp_file = $videofiles['tmp_name'][$key];
					
					$temp_file_size = filesize($temp_file);
					$temp_file_size = intval($this->wp_convert_bytes_to_kb($temp_file_size));
					
					$max_upload_size = $this->get_max_size();

					if($temp_file_size > $max_upload_size){
						
						$this->show_video_error( __('File upload size limit exceeded.', 'cool-video-gallery'));
						continue;
					}
					//clean filename and extract extension
					$filepart = $this->fileinfo( $videofiles['name'][$key] );
					$filename = $filepart['basename'];
					$file_name = $filepart['filename'];
						
					// check for allowed extension
					
					$cool_video_gallery = $this->cvg_instance;
					$ext = $cool_video_gallery->allowed_extension; 
					
					if ( !in_array($filepart['extension'], $ext) || !@filesize($temp_file) ){ 
						$this->show_video_error('<strong>' . $videofiles['name'][$key] . ' </strong>' . __('is no valid video file !', 'cool-video-gallery'));
						continue;
					}
	
					// check if this filename already exist in the folder
					$i = 0;
					
					while ( in_array( $file_name, $dirlist ) ) {
						$i++;
						$filename = $filepart['filename'] . '_' . $i . '.' .$filepart['extension'];
						$file_name = $filepart['filename'] . '_' . $i;
					}
					
					$dest_file = $gallery->abspath . '/' . $filename;
					
					//check for folder permission
					if ( !is_writeable($gallery->abspath) ) {
						$message = sprintf(__('Unable to write to directory %s. Is this directory writable by the server?', 'cool-video-gallery'), $gallery->abspath);
						$this->show_video_error($message);
						return;				
					}
					
					// save temp file to gallery
					if ( !@move_uploaded_file($temp_file, $dest_file) ){
						$this->show_video_error(__('Error, the file could not moved to : ', 'cool-video-gallery') . $dest_file);
						continue;
					} 
					if ( !$this->chmod($dest_file) ) {
						$this->show_video_error(__('Error, the file permissions could not set.', 'cool-video-gallery'));
						continue;
					}
					
					// add to videolist & dirlist
					$videolist[] = $filename;
					$dirlist[] = $file_name;
				}else {
					
					$this->show_video_error($this->decode_upload_error($videofiles['error'][0]));
					return;
				}
			}
		}
	
		if (count($videolist) > 0) {
			
			// add videos to database		
			$videos_ids = $this->add_Videos($galleryID, $videolist);
	
			if ($this->ffmpegcommandExists()) 	{
				foreach($videos_ids as $video_id )
					$this->create_thumbnail_video($video_id);
			}	
			
			$this->show_video_message( count($videos_ids) . __(' Video(s) successfully uploaded.', 'cool-video-gallery'));
		}
		return;
	}
	
	/**
	 * Function to add videos from Media Library
	 *
	 * @return void
	 */
	function add_media_videos() {
	
		global $wpdb;
	
		$cool_video_gallery = $this->cvg_instance;
	
		// get selected gallery
		$galleryID = (int) $_POST['galleryselect_media'];
	
		if ($galleryID == 0) {
			$this->show_video_error(__('No gallery selected !', 'cool-video-gallery'));
			return;
		}
	
		// get the path to the gallery
		$gallery = $this->cvg_videodb->find_gallery($galleryID);
	
		if ( empty($gallery->path) ){
			$this->show_video_error(__('Failure in database, no gallery path set !', 'cool-video-gallery'));
			return;
		}
	
		$add_media = $this->cvg_videodb->add_media_gallery($galleryID, (int) $_POST['mediaselect_add']);
		
		if($add_media != FALSE) {
			if ($this->ffmpegcommandExists()) {
				$vid_id = $add_media;
				$this->create_thumbnail_video($vid_id);
			}
			$this->show_video_message(__('Video successfully added.', 'cool-video-gallery'));
		}
	}
	
	/**
	 * Function for add videos from Youtube
	 * 
	 * @return void
	 */
	function add_youtube_videos() {
	
		// Videos must be an array
		$videoslist = array();
	
		// get selected gallery
		$galleryID = (int) $_POST['galleryselect_add'];
	
		if ($galleryID == 0) {
			$this->show_video_error(__('No gallery selected !', 'cool-video-gallery'));
			return;	
		}
		
		if(ini_get('allow_url_fopen')) {
			// get the path to the gallery	
			$gallery = $this->cvg_videodb->find_gallery($galleryID);
			
			if ( empty($gallery->path) ){
				$this->show_video_error(__('Failure in database, no gallery path set !', 'cool-video-gallery'));
				return;
			} 
			
			$videos_ids = $this->process_youtube_videos ( $galleryID, $_POST ['videourl'] );
			
			if (count ( $videos_ids ) != 0)
				$this->show_video_message ( count ( $videos_ids ) . __ ( ' Video(s) successfully added.', 'cool-video-gallery' ) );
			
		}else {
			$this->show_video_error(__('Please enable the PHP setting `allow_url_fopen`', 'cool-video-gallery'));
		}
		return;
	}

	/**
	 * Function to process youtube videos
	 * 
	 * @return void
	 */
	function process_youtube_videos($galleryID, $videolist) {
		
		global $wpdb;
	
		$cool_video_gallery = $this->cvg_instance;
		$video_ids = array();
		
		$youtube_api = new CVGYoutubeAPI();
		$videos = $youtube_api->youtube_video_details($videolist);
		
		if($videos == "false") {
			$this->show_video_error(__('Youtube API Error', 'cool-video-gallery'));
			return;
		}
		
		if ( is_array($videos) ) {
			
			foreach($videos as $video_details) {
				$youtube_video = $this->cvg_videodb->add_youtube_video($galleryID, $video_details);
				if($youtube_video != FALSE) {
					$vid_id = (int) $wpdb->insert_id;
					$video_ids[] = $vid_id;
				}
			} 
		} // is_array
	        
		return $video_ids;
	}

	/**
	 * Function for uploading of videos via the upload form
	 *
	 * @return void
	 */
	function upload_preview() {
	
		// Videos must be an array
		$imageslist = array();
	
		// get selected gallery
		$videoID = (int) $_POST['TB_previewimage_single'];
	
		if ($videoID == 0) {
			$this->show_video_error(__('Error uploading preview image!', 'cool-video-gallery'));
			return;
		}
	
		$video = $this->cvg_videodb->find_video($videoID);
		$video_thumb_name = $video[0]->thumb_filename;
	
		$gallery_path = $this->winabspath . $video[0]->path;
		if ( empty($video[0]->path) ){
			$this->show_video_error(__('Failure in database, no gallery path set !', 'cool-video-gallery'));
			return;
		}
	
		$videofiles = $_FILES['preview_image'];
	
		if (is_array($videofiles)) {
			foreach ($videofiles['name'] as $key => $value) {
	
				// look only for uploded files
				if ($videofiles['error'][$key] == 0) {
	
					$temp_file = $videofiles['tmp_name'][0];
					
					$filetype = wp_check_filetype($videofiles['name'][0]); // Get file extension
					
					$info = pathinfo( $video_thumb_name );
						
					$video_thumb_name = str_replace($info['extension'], $filetype['ext'], $video_thumb_name); // Change filename with required file type

					$temp_file_size = filesize($temp_file);
					$temp_file_size = intval($this->wp_convert_bytes_to_kb($temp_file_size));
					$max_upload_size = $this->get_max_size();
						
					if($temp_file_size > $max_upload_size){
						$this->show_video_error( __('File upload size limit exceeded.', 'cool-video-gallery'));
						return;
					}
						
					$dest_file = $gallery_path . '/thumbs/' . $video_thumb_name;
					
					if ( !@move_uploaded_file($temp_file, $dest_file) ){
						$this->show_video_error(__('Error, the file could not moved to : ', 'cool-video-gallery') . $dest_file);
						return;
					}else {
	
						// Delete old thumbnail
						if($info['extension'] != $filetype['ext']) {
							$thumb_path = $this->winabspath . $video[0]->path . '/thumbs/' . $video[0]->thumb_filename;
							@unlink($thumb_path);
						}
						
						$new_size = @getimagesize ( $dest_file );
						$size ['width'] = $new_size [0];
						$size ['height'] = $new_size [1];
							
						// add them to the database
						$this->cvg_videodb->update_video_meta ( $video[0]->pid, array ('video_thumbnail' => $size) );
						$this->cvg_videodb->update_video_thumbnail_name( $video[0]->pid, $video_thumb_name);
						
						if ( !$this->chmod($dest_file) ) {
							$this->show_video_error(__('Error, the file permissions could not set', 'cool-video-gallery'));
							return;
						}
					}
				}else {
					$this->show_video_error($this->decode_upload_error($videofiles['error'][0]));
					return;
				}
			}
		}
		$this->show_video_message( __('Video preview image successfully added.', 'cool-video-gallery'));
		return;
	}
	
	/**
	 * Function to scan gallery folder for new videos
	 * @param $galleryID - gallery id
	 */
	function scan_upload_videos($galleryID, $enable = true){
		
		global $wpdb;
		
		$gallery = $this->cvg_videodb->find_gallery($galleryID);
		
		if(!$gallery)
			return;
		
		$dirlist = $this->scandir_video($gallery->abspath);
		$videolist = array();
		
		foreach($dirlist as $video) {
			$video_newname = sanitize_file_name($video);
			$video_found = $this->cvg_videodb->check_video_gallery($galleryID, $video_newname);
			if(!$video_found) {
				@rename($gallery->abspath . '/' . $video, $gallery->abspath . '/' . $video_newname );
				$videolist[] = $video_newname;
			}	
		}
		
		// add videos to database		
		$videos_ids = $this->add_Videos($galleryID, $videolist);

		if ($this->ffmpegcommandExists()) 	{
			foreach($videos_ids as $video_id )
				$this->create_thumbnail_video($video_id);
		}	
		if(count($videos_ids)> 0) {
			
			if($enable)
				$this->show_video_message( count($videos_ids) . __(' Video(s) successfully added.', 'cool-video-gallery'));
		}else {
			
			if($enable)
				$this->show_video_error( __(' No new video(s) found.', 'cool-video-gallery'));
		} 
				
	}
	
	/**
	 * Add videos to database
	 * 
	 * @param int $galleryID
	 * @param array $videolist
	 * @return array $video_ids Id's which are sucessful added
	 */
	function add_Videos($galleryID, $videolist) {
		
		global $wpdb;
	
		$video_ids = array();
		
		$cool_video_gallery = $this->cvg_instance;
		
		if ( is_array($videolist) ) {
			foreach($videolist as $video) {
				
				// strip off the extension of the filename
				$path_parts = pathinfo( $video );
				$alttext = ( !isset($path_parts['filename']) ) ? substr($path_parts['basename'], 0,strpos($path_parts['basename'], '.')) : $path_parts['filename'];
				$time_updated = current_time('mysql', 1);
				
				$thumb_filename = 'thumbs_' . $alttext . '.png';
				
				// save it to the database 
				$result = $wpdb->query( $wpdb->prepare("INSERT INTO " . $wpdb->prefix ."cvg_videos (galleryid, filename, thumb_filename, alttext, video_title, description, videodate, video_type) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", $galleryID, $video, $thumb_filename, $alttext, $alttext, $alttext, $time_updated, $cool_video_gallery->video_type_upload) );
				$vid_id = (int) $wpdb->insert_id;
				
				if ($result) 
					$video_ids[] = $vid_id;
	
			} 
		} // is_array
	        
		return $video_ids;
	}
	
	/**
	 * Function to create a preview thumbnail for video
	 * 
	 * @param object | int $video contain all information about the video or the id
	 * @return string result code
	 */
	function create_thumbnail_video($videoID) {
	
		$options = get_option('cvg_settings');
		$thumb_width = $options['cvg_ffmpeg_preview_width'];
		$thumb_height = $options['cvg_ffmpeg_preview_height'];
			
		if (is_numeric ( $videoID ))
			$videoDetails = $this->cvg_videodb->find_video ( $videoID );
			
		$video = $videoDetails[0];
		
		if ( !is_object($video) ) 
			return __('Object didn\'t contain correct data', 'cool-video-gallery');
			
		$filepart = $this->fileinfo( $video->filename );
			
		// check for allowed extension 
		$cool_video_gallery = $this->cvg_instance;
		$ext = $cool_video_gallery->allowed_extension;
		  
		if ( !in_array($filepart['extension'], $ext) ){ 
			return;
		}
		
		$gallery = $this->cvg_videodb->find_gallery($video->galleryid);
		$video_input = $gallery->abspath . '/' . $video->filename;
		$new_target_filename = $video->alttext . '.png';
		$new_target_file = $gallery->abspath . '/thumbs/thumbs_' . $new_target_filename;
		
		$input_file = "";
		
		$seek_location = $options ['cvg_ffmpeg_seek_hour'] . ":" . $options ['cvg_ffmpeg_seek_minute'] . ":" . $options ['cvg_ffmpeg_seek_second'];
		
		if($video->video_type == $cool_video_gallery->video_type_media){
			$input_file = $video->filename;
		}else {
			$input_file = $video_input;
		}
		$command  = escapeshellarg($options['cvg_ffmpegpath']) . " -i '". escapeshellarg($input_file) . "' -ss " . escapeshellarg($seek_location) . " -s " .escapeshellarg($thumb_width) ."x".escapeshellarg($thumb_height)." -vframes 1 '". escapeshellarg($new_target_file). "' 2>&1";
		
		exec ( $command, $output );
		
		//get video duration
		$video_duration = $this->video_duration($input_file);
		
		if (file_exists ( $new_target_file )) {
			
			$this->chmod ($new_target_file); 
			
			/** ver 1.5
			 *	Size calculation 
			 */
			$new_size = @getimagesize ( $new_target_file );
			$size ['width'] = $new_size [0];
			$size ['height'] = $new_size [1];
			
			// add them to the database
			$this->cvg_videodb->update_video_meta ( $video->pid, array ('video_thumbnail' => $size , 'videoDuration' => $video_duration ) );
		}else {
			
			$this->show_video_warning( __("FFMPEG Thumbnail Generation Error : ", 'cool-video-gallery') . $output[count($output) - 1]);
			
			// add them to the database
			$this->cvg_videodb->update_video_meta ( $video->pid, array ('videoDuration' => $video_duration ) );
		}
	}
	
	/**
	 * Function to delete video file from a gallery
	 * 
	 * @param $pid - video id
	 */	
	function delete_video_files($pid = '') {
			
		$cool_video_gallery = $this->cvg_instance;
		
		$video_detail = $this->cvg_videodb->find_video($pid);
		
		if($video_detail[0]->video_type == $cool_video_gallery->video_type_upload) {
		    $video_path = $this->winabspath . $video_detail[0]->path . '/' . $video_detail[0]->filename;
		    $thumb_path = $this->winabspath . $video_detail[0]->path . '/thumbs/' . $video_detail[0]->thumb_filename;
			
			@unlink($video_path);
			@unlink($thumb_path);
		}else if($video_detail[0]->video_type == $cool_video_gallery->video_type_media) {

			$thumb_path = $this->winabspath . $video_detail[0]->path . '/thumbs/' . $video_detail[0]->thumb_filename;
			@unlink($thumb_path);
		}
		
	}
	
	/**
	 * Function to delete folder for gallery.
	 * 
	 * @param $gid - gallery id
	 */
	function delete_video_gallery($gid = '') {
		
		$videos = $this->cvg_videodb->get_gallery($gid);
		$video_gallery_path = $this->cvg_videodb->find_gallery($gid);
		
		$this->deleteDir( $video_gallery_path->abspath. '/thumbs' );
		$this->deleteDir( $video_gallery_path->abspath );
	
		return true;	
	}
	
	
	/**
	 * Function to remove directory and its files recursively
	 * @param $directory - directory path
	 * @param $empty - recursive true/false
	 * @return true or false
	 */
	function deleteDir($directory, $empty = false) {
	    if(substr($directory,-1) == "/") {
	        $directory = substr($directory,0,-1);
	    }
	    if(!file_exists($directory) || !is_dir($directory)) {
	        return false;
	    } elseif(!is_readable($directory)) {
	        return false;
	    } else {
	        $directoryHandle = opendir($directory);
	        while ($contents = readdir($directoryHandle)) {
	            if($contents != '.' && $contents != '..') {
	                $path = $directory . "/" . $contents;
	                if(is_dir($path)) {
	                    $this->deleteDir($path);
	                } else {
	                    @unlink($path);
	                }
	            }
	        }
	        closedir($directoryHandle);
	        if($empty == false) {
	            if(!@rmdir($directory)) {
	                return false;
	            }
	        }
	        return true;
	    }
	} 
	
	/**
	 * Function to generate xml sitemap for videos
	 * 
	 */
	function xml_sitemap() {
		
		$cool_video_gallery = $this->cvg_instance;
		
		$xml = '<?xml-stylesheet type="text/xsl" href="'. $cool_video_gallery->plugin_url . 'css/cvg-video-sitemap.xsl"?>';
		$xml .= "\n" .'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">';
		$xml .= "\n" .'<!-- Generated by (http://wordpress.org/extend/plugins/cool-video-gallery/), Authored by Praveen Rajan -->' . "\n";
		
		foreach ($_POST['cvg_sitemapaction'] as $key => $post_id) { 

			$xml .= "\t". '<url>'. "\n";
			$xml .= "\t\t". '<loc>'. get_permalink($post_id) . '</loc>'. "\n";
			
			$post_details = get_post($post_id);
			$pattern = get_shortcode_regex();
			
			
			if (   preg_match_all( '/'. $pattern .'/s', $post_details->post_content, $matches )
					&& array_key_exists( 2, $matches )
					&& (in_array( 'cvg-video', $matches[2] ) || in_array( 'cvg-gallery', $matches[2] )) )
			{
				$cvg_shortcodes = $matches[0];
				
				foreach ($cvg_shortcodes as $cvg_shortcode) {
					
					$cvg_shortcode_params = shortcode_parse_atts($cvg_shortcode);
					
					// Gallery Present
					if(isset($cvg_shortcode_params['galleryid'])) {
						
						$limit_by = 0;
						if(isset($cvg_shortcode_params['limit']))
							$limit_by  = ( $cvg_shortcode_params['limit'] > 0 ) ? $cvg_shortcode_params['limit'] : 0;
								
							
						$videolist = $this->cvg_videodb->get_gallery($cvg_shortcode_params['galleryid'], false, 'sortorder', 'ASC', $limit_by);
						
						foreach ($videolist as $video) {
							
							$xml .= $this->videoSitemapTag($video);
						}
					}
					
					// Video Present
					if(isset($cvg_shortcode_params['videoid'])) {
					
						$video_details = $this->cvg_videodb->find_video($cvg_shortcode_params['videoid']);
						$xml .= $this->videoSitemapTag($video_details[0]);
					}
				}
			}
			$xml .= "\t". '</url>'. "\n";
		}
		
		$xml .= '</urlset>'. "\n";
		
		if(isset($_POST['cvg_sitemapname']) && trim($_POST['cvg_sitemapname']) != "") {
			$sitemap_filename = esc_attr( $_POST['cvg_sitemapname']  );
			$sitemap_file = sanitize_file_name( $sitemap_filename ) . "-cvg.xml";
		}else {
			$sitemap_file = "sitemap-video-cvg.xml";
		}
		$video_sitemap_url = ABSPATH . $sitemap_file;
		
		if($this->createFile($video_sitemap_url)) {
			if (file_put_contents ($video_sitemap_url, $xml)) {
		
				$message =  __('Google XML Video Sitemap successfully created at location ', 'cool-video-gallery') ."<b>$video_sitemap_url</b><br/>";
				$message .= "<a href='".site_url() . '/' . $sitemap_file."' target='_blank'>Click Here</a>" . __(' to open Video Sitemap.', 'cool-video-gallery') ;
				
				$this->show_video_message($message);
				return true;
			}
		}
	}
	
	/**
	 * Generate XML string for video sitemap
	 * @param $video video details
	 * @return string xml format of video sitemap
	 */
	function videoSitemapTag($video) {
		
		$cool_video_gallery = $this->cvg_instance;
		
		$xml = '';
		
		if($video->meta_data != ''){
			$video_meta_data = unserialize($video->meta_data);
			if (isset ( $video_meta_data ['videoDuration'] )) {
				$seconds = date ( 's', strtotime ( $video_meta_data ['videoDuration'] ) );
				$minutes = date ( 'i', strtotime ( $video_meta_data ['videoDuration'] ) );
				$hours = date ( 'H', strtotime ( $video_meta_data ['videoDuration'] ) );
				$total_seconds = round ( ($hours * 60 * 60) + ($minutes * 60) + $seconds );
			}else {
				$total_seconds = 100;
			}
		}else{
			$total_seconds = 100;
		}
			
		$gallery_details = $this->cvg_videodb->get_gallery_sitemap($video->gid);
		
		if($video->video_type == $cool_video_gallery->video_type_upload) {
				
			$video_url = site_url()  . '/' . $gallery_details[0]['path'] . '/' . $video->filename;
			$thumb_url = site_url() . '/' . $gallery_details[0]['path'] . '/thumbs/' . $video->thumb_filename;
				
		}else if($video->video_type == $cool_video_gallery->video_type_media) {
				
			$video_url = $video->filename;
			$thumb_url = site_url() . '/' . $gallery_details[0]['path'] . '/thumbs/' . $video->thumb_filename;
				
		}else if($video->video_type == $cool_video_gallery->video_type_youtube) {
				
			$video_url = $video->filename;
			$thumb_url = $video->thumb_filename;
		}
			
		$video_desc = $video->description;
		if($video->description == "") {
			$video_desc = $video->video_title;
		}
			
		$xml .= "\t\t". '<video:video>'. "\n";
		$xml .= "\t\t\t". '<video:thumbnail_loc>' . htmlspecialchars($thumb_url) . '</video:thumbnail_loc>'. "\n";
		$xml .= "\t\t\t". '<video:title>' . htmlspecialchars($video->video_title) . '</video:title>'. "\n";
		$xml .= "\t\t\t". '<video:description>' . htmlspecialchars(stripcslashes($video_desc)) . '</video:description>'. "\n";
		$xml .= "\t\t\t". '<video:content_loc>' . htmlspecialchars($video_url) . '</video:content_loc>'. "\n";
		$xml .= "\t\t\t". '<video:duration>' . $total_seconds . '</video:duration>'. "\n";
		$xml .= "\t\t". '</video:video> '. "\n";
		
		return $xml;
	}
	
	/**
	 * Function to create a file with permissions.
	 * 
	 * @param $filename - file path
	 */
	function createFile($filename) {
		if(!is_writable($filename)) {
			if(!@chmod($filename, 0666)) {
				$pathtofilename = dirname($filename);
				if(!is_writable($pathtofilename)) {
					if(!@chmod($pathtoffilename, 0666)) {
						return false;
					}
				}
			}
		}
		return true;
	}
	
	/**
	 * Function to return proper error messages while uploading files.
	 * 
	 * @param $code
	 */
	function decode_upload_error( $code ) {
		
        switch ($code) {
            case UPLOAD_ERR_INI_SIZE:
                $message = __ ( 'The uploaded file exceeds the upload_max_filesize directive in php.ini', 'cool-video-gallery' );
                break;
            case UPLOAD_ERR_FORM_SIZE:
                $message = __ ( 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'cool-video-gallery' );
                break;
            case UPLOAD_ERR_PARTIAL:
                $message = __ ( 'The uploaded file was only partially uploaded', 'cool-video-gallery' );
                break;
            case UPLOAD_ERR_NO_FILE:
                $message = __ ( 'No file was uploaded', 'cool-video-gallery' );
                break;
            case UPLOAD_ERR_NO_TMP_DIR:
                $message = __ ( 'Missing a temporary folder', 'cool-video-gallery' );
                break;
            case UPLOAD_ERR_CANT_WRITE:
                $message = __ ( 'Failed to write file to disk', 'cool-video-gallery' );
                break;
            case UPLOAD_ERR_EXTENSION:
                $message = __ ( 'File upload stopped by extension', 'cool-video-gallery' );
                break;
            default:
                $message = __ ( 'Unknown upload error', 'cool-video-gallery' );
                break;
        }
        return $message; 
	}
	
	/**
	 * Function to display overview of video gallery
	 *
	 * @return html code to display overview
	 * 
	 */
	function gallery_overview() {
			
		global $wpdb;
		
		$videos    = intval( $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "cvg_videos") );
		$galleries = intval( $wpdb->get_var("SELECT COUNT(*) FROM " . $wpdb->prefix . "cvg_gallery") );
		?>
		<table>
			<tbody>
				<tr class="first">
					<td class="first b"><a href="<?php echo admin_url('admin.php?page=cvg-gallery-manage');?>"><?php echo $videos; ?></a></td>
					<td class="b"></td>
					<td class="t"><a href="<?php echo admin_url('admin.php?page=cvg-gallery-manage');?>"><?php echo _n( 'Videos', 'Videos', $videos, 'cool-video-gallery' ); ?></a></td>
				</tr>
				<tr>
					<td class="first b"><a href="<?php echo admin_url('admin.php?page=cvg-gallery-manage');?>"><?php echo $galleries; ?></a></td>
					<td class="b"></td>
					<td class="t"><a href="<?php echo admin_url('admin.php?page=cvg-gallery-manage');?>"><?php echo _n( 'Gallery', 'Galleries', $galleries, 'cool-video-gallery' ); ?></a></td>
				</tr>
			</tbody>
		</table>
	    <p>
		<a class="button rbutton" href="<?php echo admin_url('admin.php?page=cvg-gallery-add#uploadvideo');?>"><?php _e('Upload videos', 'cool-video-gallery') ?></a>
		<p><?php _e('Here you can control your videos and galleries.', 'cool-video-gallery'); ?></p>
		<?php
	}
	
	/**
	 * Function to get tab order.
	 * 
	 */
	function tabs_order() {
	    $tabs = array();
	    $tabs['addgallery'] = __( 'Add New Gallery', 'cool-video-gallery' );
	    $tabs['uploadvideo'] = __( 'Upload Videos', 'cool-video-gallery' );
		$tabs['addmedia'] = __( 'Attach Media', 'cool-video-gallery' );
		$tabs['linkvideo'] = __( 'Youtube Videos', 'cool-video-gallery' );
	   	return $tabs;
	}
	
	/**
	 * Function for gallery tab.
	 * 
	 */
 	function tab_addgallery() {
    ?>
		<!-- create gallery -->
		<form name="addgallery" id="addgallery_form" method="POST" action="<?php echo admin_url('admin.php?page=cvg-gallery-add') . '#add'; ?>" accept-charset="utf-8" >
			<table class="form-table"> 
			<tr valign="top"> 
				<th scope="row"><?php _e('New Gallery', 'cool-video-gallery') ;?>:</th> 
				<td><input type="text" size="35" name="galleryname" value="" style="width:94%;"/><br />
				<i>( <?php _e('Allowed characters for file and folder names are', 'cool-video-gallery') ;?>: a-z, A-Z, 0-9, -, _ )</i></td>
			</tr>
			<tr>
				<th><?php _e('Description', 'cool-video-gallery') ?>:</th> 
				<td><textarea name="gallerydesc" cols="30" rows="3" style="width: 94%" ></textarea></td>
			</tr>
			</table>
			<?php wp_nonce_field('cvg_add_gallery_nonce','cvg_add_gallery_nonce_csrf'); ?>
			<div class="submit"><input class="button-primary" type="submit" name= "addgallery" value="<?php _e('Add Gallery', 'cool-video-gallery') ;?>"/></div>
		</form>
    <?php
    }

    /**
	 * Function for upload video tab.
	 * 
	 */
	 function tab_uploadvideo() {
	?>
    	<!-- upload videos -->
    	<?php 
			$max_upload_size = size_format(wp_max_upload_size());
    	?>
    	<form name="uploadvideo" id="uploadvideo_form" method="POST" enctype="multipart/form-data" action="<?php echo admin_url('admin.php?page=cvg-gallery-add').'#uploadvideo'; ?>" accept-charset="utf-8" >
			<table class="form-table"> 
			<tr valign="top"> 
				<th scope="row"><?php _e('Choose Videos', 'cool-video-gallery') ;?></th>
				<td><span id='spanButtonPlaceholder'></span><input type="file" name="videofiles[]" id="videofiles" size="35" class="videofiles"/>
				<br/>
				<i><?php _e('Allowed File Formats: H.264 (.mp4, .mov, .m4v), FLV (.flv) and MP3 (.mp3)', 'cool-video-gallery') ;?>
					<br />
					<?php 
					printf(__( 'Maximum file upload size: %1$s', 'cool-video-gallery' ),
							$max_upload_size
							);
					?> 
				</i></td>
			</tr> 
			<tr valign="top"> 
				<th scope="row"><?php _e('into', 'cool-video-gallery') ;?></th> 
				<td><select name="galleryselect" id="galleryselect">
				<option value="0" ><?php _e('Choose gallery', 'cool-video-gallery') ?></option>
				<?php
					$gallerylist = $this->cvg_videodb->find_all_galleries('gid', 'ASC');
					foreach($gallerylist as $gallery) {
						$name = ( empty($gallery->title) ) ? $gallery->name : $gallery->title;
						echo '<option value="' . $gallery->gid . '" >' . $gallery->gid . ' - ' . $name . '</option>' . "\n";
					}					
					?>
				</select>
			</tr> 
			</table>
			<?php wp_nonce_field('cvg_upload_video_nonce','cvg_upload_video_nonce_csrf'); ?>
			<div class="submit">
				<input type="hidden" value="Upload Videos" name="uploadvideo" />
				<input class="button-primary" type="button" name="uploadvideo_btn" id="uploadvideo_btn" value="<?php _e('Upload Video(s)', 'cool-video-gallery') ;?>" />
			</div>
		</form>
		
    <?php
    } 

	/**
	 * Function for link videos
	 * 
	 */
	 function tab_linkvideo() {
	 	
		?>
    	<!-- Add youtube videos -->
    	<form name="addvideo" id="addvideo_form" method="POST"  action="<?php echo admin_url('admin.php?page=cvg-gallery-add').'#linkvideo'; ?>" accept-charset="utf-8" >
			<table class="form-table"> 
			<tr valign="top"> 
				<th scope="row"><?php _e('Enter Youtube Video IDs separated by comma', 'cool-video-gallery') ;?></th> 
				<td><input type="text" size="35" name="videourl" value="" style="width:94%;" placeholder="Youtube Video ID"/><br />
					<i><?php _e('Example: Video ID for URL', 'cool-video-gallery');?> http://www.youtube.com/watch?v=YE7VzlLtp-4 is `YE7VzlLtp-4`</i>
				</td>	
			</tr> 
			<tr valign="top"> 
				<th scope="row"><?php _e('into', 'cool-video-gallery') ;?></th> 
				<td><select name="galleryselect_add" id="galleryselect_add">
				<option value="0" ><?php _e('Choose gallery', 'cool-video-gallery') ?></option>
				<?php
					$gallerylist = $this->cvg_videodb->find_all_galleries('gid', 'ASC');
					foreach($gallerylist as $gallery) {
						$name = ( empty($gallery->title) ) ? $gallery->name : $gallery->title;
						echo '<option value="' . $gallery->gid . '" >' . $gallery->gid . ' - ' . $name . '</option>' . "\n";
					}					
				?>
				</select>
			</tr> 
			</table>
			<br/>
			<i style="color:red;"><?php _e('Note: Youtube API Key is required to retrieve data from Youtube. Enter your Youtube API Key at ', 'cool-video-gallery');?><a href="<?php echo admin_url('admin.php?page=cvg-gallery-settings');?>"><?php _e('Gallery Settings', 'cool-video-gallery')?></a>.</i>
			<br/>	
			<?php wp_nonce_field('cvg_attach_youtube_nonce','cvg_attach_youtube_nonce_csrf'); ?>
			<div class="submit">
				<input type="hidden" value="Add Videos" name="addvideo" />
				<input class="button-primary" type="button" name="addvideo_btn" id="addvideo_btn" value="<?php _e('Add Video(s)', 'cool-video-gallery') ;?>" />
			</div>
		</form>
		
    <?php
    }        
    
	/**
	 * Function for show tab for media videos
	 * 
	 */
	 function tab_addmedia() {
	 	
		?>
    	<!-- Add media videos -->
    	<form name="addmedia" id="addmedia_form" method="POST"  action="<?php echo admin_url('admin.php?page=cvg-gallery-add').'#addmedia'; ?>" accept-charset="utf-8" >
			<table class="form-table"> 
			<tr valign="top"> 
				<th scope="row"><?php _e('Choose Media from Library', 'cool-video-gallery') ;?></th> 
				<td>
					<select name="mediaselect_add" id="mediaselect_add">
					<option value="0" ><?php _e('Choose media', 'cool-video-gallery') ?></option>
					<?php
						
						$cool_video_gallery = $this->cvg_instance;
						$ext = $cool_video_gallery->allowed_extension;
					
						$args = array('post_type' => 'attachment', 'post_mime_type' => 'video','numberposts' => -1 );
						$mediafiles = get_posts($args);
						foreach ($mediafiles as $file) {
							
							$filepart = $this->fileinfo($file->guid);
								
							if (in_array($filepart['extension'], $ext)){
								$name = ( empty($file->post_name) ) ? $file->post_title : $file->post_name;
								echo '<option value="' . $file->ID . '" >' . $name . '</option>' . "\n";
							}
						}
						
						$args_audio = array('post_type' => 'attachment', 'post_mime_type' => 'audio','numberposts' => -1 );
						$mediafiles_audio = get_posts($args_audio);
						foreach ($mediafiles_audio as $file) {
							
							$filepart = $this->fileinfo($file->guid);
								
							if (in_array($filepart['extension'], $ext)){
								$name = ( empty($file->post_name) ) ? $file->post_title : $file->post_name;
								echo '<option value="' . $file->ID . '" >' . $name . '</option>' . "\n";
							}
						}					
					?>
					</select>
					
				</td>
			</tr> 
			<tr valign="top"> 
				<th scope="row"><?php _e('into', 'cool-video-gallery') ;?></th> 
				<td>
				<select name="galleryselect_media" id="galleryselect_media">
				<option value="0" ><?php _e('Choose gallery', 'cool-video-gallery') ?></option>
				<?php
					$gallerylist = $this->cvg_videodb->find_all_galleries('gid', 'ASC');
					foreach($gallerylist as $gallery) {
						$name = ( empty($gallery->title) ) ? $gallery->name : $gallery->title;
						echo '<option value="' . $gallery->gid . '" >' . $gallery->gid . ' - ' . $name . '</option>' . "\n";
					}					
				?>
				</select>
			</tr> 
			</table>
			<?php wp_nonce_field('cvg_add_media_nonce','cvg_add_media_nonce_csrf'); ?>
			<div class="submit">
				<input type="hidden" value="Add Media" name="addmedia" />
				<input class="button-primary" type="button" name="addmedia_btn" id="addmedia_btn" value="<?php _e('Add Media', 'cool-video-gallery') ;?>" />
			</div>
		</form>
		
    <?php
    } 

    /**
     * Function to get maximum upload size of a file.
     * @return file size
     */
    function get_max_size() {
    	
		return intval($this->wp_convert_bytes_to_kb(wp_max_upload_size()));
    }
    
    function wp_convert_bytes_to_kb ($bytes) {
    	
    	return intval($bytes/1024);
    }
	/**
	 * Function to update video details.
	 * 
	 */
	function update_videos() {
		global $wpdb;
	
		$description = 	isset ( $_POST['description'] ) ? $_POST['description'] : false;
		$video_title = isset ( $_POST['video_title'] ) ? $_POST['video_title'] : false;
		
		$galleryId = $_POST['galleryId'];
		$wpdb->query( "UPDATE " . $wpdb->prefix . "cvg_videos SET exclude = '0' WHERE galleryid = $galleryId");
		
		if(isset($_POST['exclude'])) {
		
			foreach ($_POST['exclude'] as $key => $value) {
				$wpdb->query( "UPDATE " . $wpdb->prefix . "cvg_videos SET exclude = '1' WHERE pid = $value");
			}
		}
		if ( is_array($description) ) {
			foreach( $description as $key => $value ) {
				$desc = esc_sql($value);
				$wpdb->query( "UPDATE " . $wpdb->prefix . "cvg_videos SET description = '$desc' WHERE pid = $key");
			}
		}
		
		if ( is_array($video_title) ) {
			foreach( $video_title as $key => $value ) {
				$desc = esc_sql($value);
				$wpdb->query( "UPDATE " . $wpdb->prefix . "cvg_videos SET video_title = '$desc' WHERE pid = $key");
			}
		}
		return true;
	}
	
	/**
	 * Function to return duration of an uploaded video.
	 * 
	 * @param $videofile
	 * @return duration of VideoSource
	 */
	function video_duration($videofile) {
		ob_start ();
		
		$options = get_option('cvg_settings');
		passthru ( $options['cvg_ffmpegpath'] . " -i \"" . $videofile . "\" 2>&1" );
		$duration = ob_get_contents ();
		ob_end_clean ();
		preg_match ( '/Duration: (.*?),/', $duration, $matches );
		
		if(!empty($matches)) {
			$duration = $matches [1];
			return ($duration);	
		}else {
			return 0;
		}
	}
		
	/**
	 * Fucntion to convert time duration in seconds to hours, minutes and seconds
	 * @param - Seconds
	 */
	function secondsToWords($seconds) {
		
	    /*** return value ***/
	    $ret = "";
	
	    /*** get the hours ***/
	    $hours = intval(intval($seconds) / 3600);
	    if($hours > 0)
	    {
	    	if(strlen($hours) == 1)
	        	$ret .= "0$hours:";
			else 
				$ret .= "$hours:";
	    }else {
	    	$ret .= "00:";
	    }
		
	    /*** get the minutes ***/
	    $minutes = bcmod((intval($seconds) / 60),60);
	    if($hours > 0 || $minutes > 0)
	    {
	    	if(strlen($minutes) == 1)
	        	$ret .= "0$minutes:";
			else 
				$ret .= "$minutes:";
			
	    }else {
	    	$ret .= "00:";
		}
	  
	    /*** get the seconds ***/
	    $seconds = bcmod(intval($seconds),60);
		
		if(strlen($seconds) == 1)
        	$ret .= "0$seconds.0";
		else 
			$ret .= "$seconds.0";
			
	    return $ret;
	}
	
	/**
	 * Function to get fileinfo 
	 * 
	 * @param string $name The name being checked. 
	 * @return array containing information about file
	 */
	function fileinfo( $name ) {
		
		//Sanitizes a filename replacing whitespace with dashes
		$name = sanitize_file_name($name);
		
		//get the parts of the name
		$filepart = pathinfo ( strtolower($name) );
		
		if ( empty($filepart) )
			return false;
		
		if ( empty($filepart['filename']) ) 
			$filepart['filename'] = substr($filepart['basename'],0 ,strlen($filepart['basename']) - (strlen($filepart['extension']) + 1) );
		
		$filepart['filename'] = sanitize_title_with_dashes( $filepart['filename'] );
		
		$filepart['extension'] = $filepart['extension'];	
		//combine the new file name
		$filepart['basename'] = $filepart['filename'] . '.' . $filepart['extension'];
		
		return $filepart;
	}
	
	/**
	 * Scan folder for new videos
	 * 
	 * @param string $dirname
	 * @return array $files list of video filenames
	 */
	function scandir_video( $dirname = '.' ) {
		
		$cool_video_gallery = $this->cvg_instance;
		$ext = $cool_video_gallery->allowed_extension;  

		$files = array(); 
		if( $handle = opendir( $dirname ) ) { 
			while( false !== ( $file = readdir( $handle ) ) ) {
				$info = pathinfo( $file );
				// just look for video with the correct extension
                if ( isset($info['extension']) )
				    if ( in_array( strtolower($info['extension']), $ext) )
					   $files[] = utf8_encode( $file );
			}		
			closedir( $handle ); 
		} 
		sort( $files );
		return ( $files ); 
	} 
	
	/**
	 * Function to scan video file names
	 * @param $dirname - directory name
	 */
	function scandir_video_name( $dirname = '.' ) {
			 
		$cool_video_gallery = $this->cvg_instance;
		$ext = $cool_video_gallery->allowed_extension; 

		$files = array(); 
		if( $handle = opendir( $dirname ) ) { 
			while( false !== ( $file = readdir( $handle ) ) ) {
				$info = pathinfo( $file );
				// just look for video with the correct extension
                if ( isset($info['extension']) )
				    if ( in_array( strtolower($info['extension']), $ext) )
					   $files[] = utf8_encode( $info['filename'] );
			}		
			closedir( $handle ); 
		} 
		sort( $files );
		return ( $files ); 
	}
	
	/**
	 * Function to check if ffmpeg is installed.
	 * 
	 */
	function ffmpegcommandExists() {
		
		$options = get_option('cvg_settings');
		
	    $command = escapeshellarg($options['cvg_ffmpegpath']);
	    exec($command, $output, $return);

		if ($return <= 1) {
			return true;
		}
		
		return false;
	}
	
	/**
	 * Function to get webserver information.
	 */
	function cvg_serverinfo() {
	
		global $wpdb, $wp_version;

		// Get PHP Max Upload Size
		$upload_max = size_format(wp_max_upload_size());
		
		if ($this->ffmpegcommandExists()) 
		   $ffmpeg = 'Installed';
		else 
		   $ffmpeg = 'Not Installed';
		
		$options_player = get_option('cvg_player_settings');
		
		$jwplayer_license = 'NO';
		
		if(isset($options_player['cvgplayer_jwplayer_key']) && ($options_player['cvgplayer_jwplayer_key'] != ""))
			$jwplayer_license  = "YES";
		
		?>
		<ul class="cvg_settings">
		<li><?php _e('Operating System', 'cool-video-gallery'); ?> : <span><?php echo PHP_OS; ?>&nbsp;(<?php echo (PHP_INT_SIZE * 8) ?>&nbsp;Bit)</span></li>
		<li><?php _e('Server', 'cool-video-gallery'); ?> : <span><?php echo $_SERVER["SERVER_SOFTWARE"]; ?></span></li>
		<li><?php _e('MySQL Version', 'cool-video-gallery'); ?> : <span><?php echo $wpdb->db_version(); ?></span></li>
		<li><?php _e('PHP Version', 'cool-video-gallery'); ?> : <span><?php echo PHP_VERSION; ?></span></li>
		<li><?php _e('PHP Max Upload Size', 'cool-video-gallery'); ?> : <span><?php echo $upload_max; ?></span></li>
		
		<li><?php _e('FFMPEG'); ?> : 
			<span><?php _e($ffmpeg, 'cool-video-gallery'); ?></span>
			<?php if($ffmpeg == 'Not Installed') {?> 
			<span style="color:red;font-weight:lighter;"><?php _e('[Note: Preview images for uploaded videos will not be generated. Manually upload preview images for videos.]', 'cool-video-gallery');?></span>
			<?php } ?>
		</li>
		<li style="width:100%;height:2px;background-color:#EEEEEE;"></li>
		<li><?php _e('WordPress Version', 'cool-video-gallery')?> : <span><?php echo $wp_version; ?></span></li>
		<li><?php _e('CVG Plugin Version', 'cool-video-gallery')?> : <span><?php echo $this->cvg_instance->cvg_version; ?></span></li>
		<li><?php _e('JWPlayer Version', 'cool-video-gallery')?> : <span id="cvg_jwplayer_version"></span></li>
		<li><?php _e('JWPlayer License Key Installed', 'cool-video-gallery')?> : 
			<span><?php _e($jwplayer_license, 'cool-video-gallery'); ?></span>
			<?php if($jwplayer_license == 'NO') {?> 
			<br /><span style="color:red;font-weight:lighter;"><?php _e('[Note: Please register with JWPlayer and receive a license key to use with this plugin. Save JWPlayer license at ', 'cool-video-gallery');?><a style="color:red;font-weight:bold;" href="<?php echo admin_url('admin.php?page=cvg-player-settings');?>">Video Player Settings</a>]</span>
			<?php } ?>
		</ul>
		<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery('#cvg_jwplayer_version').html( jwplayer.version.split('+').length > 1 ? jwplayer.version.split('+')[0] : jwplayer.version);
		});
		</script>
		
		<?php
	}
	
	/**
	 * Function to show author information
	 */
	function cvg_authorinfo() {
	
		?>
		<div>
			<?php _e('Alas I found my space!!!', 'cool-video-gallery');?> <img width="20" height="20" src="<?php echo trailingslashit( WP_PLUGIN_URL . '/' .	dirname(dirname( plugin_basename(__FILE__)))) ?>/images/1.png" />
			<br />
			<br />
			<label style="color:green;"><?php _e('No features in here!!!', 'cool-video-gallery');?></label> <label style="color:red;"><?php _e('No bugs in here!!!', 'cool-video-gallery');?></label>
			<br/>
			<br />
			<?php _e("Just a few bits of my info!!! So it isn't really going to take your website space", 'cool-video-gallery');?> <img width="20" height="20" src="<?php echo trailingslashit( WP_PLUGIN_URL . '/' .	dirname(dirname( plugin_basename(__FILE__)))) ?>/images/2.png" />
			<br/>
			<br />
			<?php _e("Let's get started.", 'cool-video-gallery');?>
			<br/>
			<br />
			<div style="text-align: justify">
			<?php _e('I started my career into Software Development Industry in 2009 with PHP as my technology. It was then I started playing with WordPress plugins for a project at my firm. Soon I thought of making my own plugins for WordPress and I finally ended up here.', 'cool-video-gallery');?>
			</div>
			<br />
			<div style="text-align: justify">
			<?php _e("Now I'm into Mobile Appliation Development(iOS, Android and BB Tablet) at my work. I do these stuffs as a hobby. Kindly give your supports by adding your ratings and let the world know that this is the right one they are looking for.", 'cool-video-gallery');?>
			</div>
			<br />
			<?php _e('Apologies for the bugs in this plugin', 'cool-video-gallery');?> <img width="20" height="20" src="<?php echo trailingslashit( WP_PLUGIN_URL . '/' .	dirname(dirname( plugin_basename(__FILE__)))) ?>/images/2.png" /> <?php _e('Do let me know your suggestions and issues through', 'cool-video-gallery');?> <a href="http://wordpress.org/support/plugin/cool-video-gallery" target="_blank"><?php _e('WordPress Plugin Support Forum', 'cool-video-gallery');?></a>.
			<br/>
			<br />
			<b style="font-size:15px;"><?php _e('CODE IT', 'cool-video-gallery');?> <img width="20" height="20" src="<?php echo trailingslashit( WP_PLUGIN_URL . '/' .	dirname(dirname( plugin_basename(__FILE__)))) ?>/images/4.png" /> <?php _e('LEARN IT', 'cool-video-gallery');?> <img width="20" height="20" src="<?php echo trailingslashit( WP_PLUGIN_URL . '/' .	dirname(dirname( plugin_basename(__FILE__)))) ?>/images/4.png" /></b>
		</div>
		<div class="cvg-clear"></div>
		<div style="float:right;">
			<a href="https://www.facebook.com/praveenr1987" target="_blank"><img src="<?php echo trailingslashit( WP_PLUGIN_URL . '/' .	dirname(dirname( plugin_basename(__FILE__)))) ?>/images/facebook.png" width="32" height="32" /></a>
			<a href="http://in.linkedin.com/in/praveen87" target="_blank"><img src="<?php echo trailingslashit( WP_PLUGIN_URL . '/' .	dirname(dirname( plugin_basename(__FILE__)))) ?>/images/linkedin.png" width="32" height="32" /></a>
			<a href="https://twitter.com/#!/praveen_rajan" target="_blank"><img src="<?php echo trailingslashit( WP_PLUGIN_URL . '/' .	dirname(dirname( plugin_basename(__FILE__)))) ?>/images/twitter.png" width="32" height="32" /></a>
		</div>
		<div class="cvg-clear"></div>
		
		<?php 
	}
	
	/**
	 * Function to show plugin ratings
	 */
	function cvg_plugin_ratings() {
	
		_e('Please mark your valuable ratings at ', 'cool-video-gallery');
		
		echo '<a href="http://wordpress.org/extend/plugins/cool-video-gallery/" target="_blank">';
		for($i = 0; $i < 5; $i ++) {
			echo '<img src="' . trailingslashit ( WP_PLUGIN_URL . '/' . dirname ( dirname ( plugin_basename ( __FILE__ ) ) ) ) . '/images/star.png" />';
		}
		echo '</a>';
	}
	
	/**
	 * Function to display shortcode configuration settings
	 */
	function cvg_shortcodeinfo() {
		
		?>
		<ul class="cvg_settings">
		<li><span>galleryid</span>: Id of Gallery to present as Showcase / Slideshow / Playlist</li>
		<li><span>videoid</span>: Id of Video to present as Embed / Popup</li>
		<li><span>limit</span>: Number of Videos to be shown in a Gallery</li>
		<li><span>width</span>: Width of Video Player</li>
		<li><span>height</span>: Height of Video Player</li>
		<li><span>preview-width</span>: Width of Preview Thumbnail</li>
		<li><span>preview-height</span>: Height of Preview Thumbnail</li>
		<li><span>mode</span>: Mode to present Gallery or Video(s). Different options available for mode:
			<br />
			<ul style="list-style-type: circle;margin-left:50px;margin-top:10px;">
				<li>`showcase`: List all Videos</li>
				<li>`slideshow`: Show Videos as Slideshow</li>
				<li>`playlist`: Show Videos as Playlist / Embedded</li>
				<li>`embed`: Show Video as Embedded</li>
			</ul>
		</li>
		</ul>
		
		<?php 
	}
	
	/**
	 * Function to display upcoming version release details 
	 */
	function cvg_upcoming_features() {
		
		$contents = @file_get_contents("https://raw.githubusercontent.com/praveen-rajan/cool-video-gallery/master/Config/upcoming-release.json");
		$contents = utf8_encode($contents);
		$results = json_decode($contents);
		
		if (isset ( $results )) {
			
			_e ( "Version : ", "cool-video-gallery" );
			echo $results->version;
			echo "<br/><br/>";
			_e ( "Features : ", "cool-video-gallery" );
			echo '<ol class="cvg_settings">';
			
			foreach ( $results->changelog as $result ) {
				echo '<li><span>' . $result . '</span></li>';
			}
			echo '</ol>';
		}else {
			
			_e("Configuration file not available to display latest news.", "cool-video-gallery");
		}
	}
	
	/**
	 * Set correct file permissions (taken from wp core)
	 * 
	 * @param string $filename
	 * @return bool $result
	 */
	function chmod($filename = '') {

		$stat = @ stat(dirname($filename));
		$perms = $stat['mode'] & 0007777;
		$perms = $perms & 0000666;
		if ( @chmod($filename, $perms) )
			return true;
			
		return false;
	}
	
	/**
	* Show a error messages
	*/
	function show_video_error($message) {
		echo '<div class="notice notice-error is-dismissible"><p>' . $message . '</p></div>';
	}
	
	/**
	* Show a success messages
	*/
	function show_video_message($message) {
		echo '<div class="notice notice-success is-dismissible"><p>' . $message . '</p></div>';
	}
	
	/**
	 * Show a warning messages
	 */
	function show_video_warning($message) {
		echo '<div class="notice notice-warning is-dismissible"><p>' . $message . '</p></div>';
	}
	
	/**
	 * video_show_gallery() - return a gallery
	 *
	 * @param arguments - Arguments passed in shortcode
	 * @param place_holder - Main template or Widget
	 * @return the content
	 */
	function video_show_gallery( $arguments , $place_holder = "main" ) {
		 
		$gallery_id = $arguments ['galleryid'];
			
		if (isset ( $arguments ['limit'] ))
			$limit = $arguments ['limit'];
		else
			$limit = 0;
		
		$mode = $arguments ['mode'];
				
		$galleryID = (int) $gallery_id;
		
		// Check videos in gallery folder and automatically add them.
		$this->scan_upload_videos ( $galleryID, false );
		
		$limit_by = ($limit > 0) ? $limit : 0;
		
		// get gallery videos
		$videolist = $this->cvg_videodb->get_gallery ( $galleryID, false, 'sortorder', 'ASC', $limit_by );
		
		if (! $videolist)
			return __( '[No videos found]', 'cool-video-gallery');
		
		// Different modes available
		if ($mode == 'playlist') {
			
			return $this->cvg_render_playlist ($arguments, $place_holder);
			
		}else if ($mode == 'showcase') {
		
			return $this->cvg_render_showcase ($arguments, $place_holder);
			
		}else if ($mode == 'slideshow') {
		
			return $this->cvg_render_slideshow ($arguments, $place_holder);
		}
	}
	
	/**
	 * Function to generate showcase of videos
	 * @param $arguments - arguments from shortcode
	 * @return showcase
	 */
	function cvg_render_showcase($arguments, $place_holder) {
		
		$cool_video_gallery = $this->cvg_instance;
		
		$options = get_option ( 'cvg_settings' );
		$options_player = get_option ( 'cvg_player_settings' );
		
		if ($options_player ['cvgplayer_autoplay'] == 1)
			$autoplay = "true";
		else
			$autoplay = "false";
		
		if ($options_player ['cvgplayer_mute'] == 1)
			$mute = "true";
		else
			$mute = "false";
		
		// Thumbnail width and height
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
					
						
		$gallery_id = $arguments ['galleryid'];
		
		if (isset ( $arguments ['limit'] ))
			$limit = $arguments ['limit'];
		else
			$limit = 0;
		
		$mode = $arguments ['mode'];
		
		$galleryID = ( int ) $gallery_id;
		$limit_by = ($limit > 0) ? $limit : 0;
		
		$outer = '';
		$out = '';
		
		// get gallery values
		$videolist = $this->cvg_videodb->get_gallery ( $galleryID, false, 'sortorder', 'ASC', $limit_by );
		
		if (isset ( $options ['cvg_random_video'] ) && $options ['cvg_random_video'] == 1)
			shuffle ( $videolist );
		
		if (is_array ( $videolist )) {
			 
			foreach ($videolist as $video) {
				$first_video = $video;
				break;
			}
			
			if ($options ['cvg_gallery_description'] == 1)
				$out .= '<span>' . $first_video->galdesc . '</span><br clear="all"/><br clear="all"/>';
			
			$out .= '<span class="cvg-gallery-showcase">';
			
			foreach ( $videolist as $video ) {
				
				$out .= '<span class="cvg-gallery-showcase-videos" style="height:' . $thumb_height . 'px;width:' . $thumb_width . 'px;">';
				
				$current_video = array();
					
				if($video->video_type == $cool_video_gallery->video_type_upload) {
						
					// Upload file type
					$current_video['filename'] = site_url()  . '/' . $video->path . '/' . $video->filename;
					$current_video['thumb_filename'] =  $video->path . '/thumbs/' . $video->thumb_filename;
						
					if (! file_exists ( ABSPATH . $current_video ['thumb_filename'] ))
						$current_video ['thumb_filename'] = WP_CONTENT_URL . '/plugins/' . dirname(dirname( plugin_basename(__FILE__))) . '/images/default_video.png';
					else
						$current_video ['thumb_filename'] = site_url () . '/' . $current_video ['thumb_filename'];
								
				}else if($video->video_type == $cool_video_gallery->video_type_youtube){
				
					// Youtube file type
					$current_video['filename'] =  $video->filename;
					$current_video['thumb_filename'] =  $video->thumb_filename;
				
				}else if($video->video_type == $cool_video_gallery->video_type_media) {
				
					// Media file type
					$current_video ['filename'] = $video->filename;
					$current_video ['thumb_filename'] = $video->path . '/thumbs/' . $video->thumb_filename;
					
					if (! file_exists ( ABSPATH  . $current_video ['thumb_filename'] ))
						$current_video ['thumb_filename'] = WP_CONTENT_URL . '/plugins/' . dirname(dirname( plugin_basename(__FILE__))) . '/images/default_video.png';
					else
						$current_video ['thumb_filename'] = site_url () . '/' . $current_video ['thumb_filename'];
								
				}
				
				$out .=  '<a href="' . $current_video['filename'] . '" title="' . stripslashes($video->description) . '"  rel="fancy_cvg_gallery_'.$galleryID.'_'. $place_holder.'">' ;
				$out .=  '<img src="' .$current_video['thumb_filename'] . '" style="width:' . $thumb_width . 'px;height:' . $thumb_height .'px;" ' ;
				$out .=  'alt="' . __('Click to Watch Video', 'cool-video-gallery') . '" /></a>';
				
				
				$current_video_title = isset ( $video->video_title ) ? $video->video_title : "";
				if ($options ['cvg_description'] == 1)
					$out .= '<label class="cvg-single-video-wrapper-title" style="width:' . $thumb_width . 'px;">' . stripcslashes ( $current_video_title ) . '</label>';
				
				$out .= '</span>';
			}
			
			echo "<!-- Cool Video Gallery Script starts here -->";
			?>
		 	<script type="text/javascript">
				jQuery(document).ready(function() {
						jQuery("a[rel=fancy_cvg_gallery_<?php echo $galleryID.'_'.$place_holder;?>]").fancybox({

							'content' : '<div id="video_fancy_cvg_items_gallery_<?php echo $galleryID.'_'.$place_holder;?>" style="overflow:hidden;"></div>',
							'width' : "<?php echo $player_width ; ?>",
							'height' :  "<?php echo $player_height ; ?>",
							'titlePosition' : 'inside',
							'autoScale' : false,
							'autoDimensions' : false,
							'margin' : 0,
							'padding' : 15,
							'transitionIn' : 'none',
							'transitionOut' : 'none',
							'centerOnScroll' : false,
							'showNavArrows' : false,
							'titleFormat' : function(title, currentArray, currentIndex, currentOpts) {
								
								var label_count = 'Video ' + (currentIndex + 1) + ' / ' + currentArray.length + '<br/>';
								var prev_disable_class = "";
								var next_disable_class = "";
								
								if(currentIndex + 1 == currentArray.length) 
									next_disable_class = "btnDisabled";	

								if(currentIndex == 0) 
									prev_disable_class = "btnDisabled";
								
								var prev_button = '<div class="cvg-fancybox-buttons fancybox-buttons"><a class="btnPrev ' +prev_disable_class+ '" title="Previous" href="javascript:;" onclick="jQuery.fancybox.prev();"></a></div>'   ;
								var next_button = '<div class="cvg-fancybox-buttons fancybox-buttons"><a class="btnNext ' +next_disable_class+ '" title="Next" href="javascript:;" onclick="jQuery.fancybox.next();"></a></div><br/>';
								var title = "";
								
								<?php if($options['cvg_description'] == 1) { ?>
									title =  this.title.length ?  jQuery.stripslashes(this.title) : '';
								<?php }?>
									
								return label_count + prev_button + next_button + title;
							},
							'onComplete' : function() {

								jwplayer('video_fancy_cvg_items_gallery_<?php echo $galleryID.'_'.$place_holder;?>').setup({

									'file' : this.href,
									"autostart" : "<?php echo $autoplay;?>",
									"volume" : "<?php echo $options_player['cvgplayer_volume']; ?>",
									"width" : "<?php echo $player_width; ?>",
									"height" : "<?php echo $player_height; ?>",
									"mute" : "<?php echo $mute; ?>",
									"image" :  this.orig[0].src ,
									<?php if($options_player['cvgplayer_share_option'] == 1) { // Enable Share ?>			
									"sharing" : {
										"sites": ['twitter','email']
									},
									<?php } ?>
										
									"stretching" : "<?php echo $options_player['cvgplayer_stretching']; ?>",
									"skin" : "<?php echo $options_player['cvgplayer_skin']; ?>",
									"width" : "100%",
									"height" : "100%"
								});
								
								jwplayer('video_fancy_cvg_items_gallery_<?php echo $galleryID.'_'.$place_holder;?>').onComplete(function() {
									
									if((jQuery.fancybox.getPos() + 1) < jQuery.fancybox.getTotal()) {
										<?php 
											if ($options_player['cvgplayer_autoplay']) {
											?>
												jQuery.fancybox.next();
											<?php } 
										?>
									}else {
										<?php 
											if ($options_player['cvgplayer_auto_close_single']) {
											?>
												jQuery.fancybox.close();
											<?php 
											}
										?>
									}
								});
							}
						});
					});
				</script>
		 	<?php
		 	
		 	echo "<!-- Cool Video Gallery Script ends here -->";
		 	
		 	$outer .= '<span class="video-gallery-thumbnail-box-outer">';
		 	$outer .= $out;
		 	$outer .= '</span>';
		}
		return $outer;
	}
	
	/**
	 * Function to generate slideshow of videos
	 * @param $arguments - arguments from shortcode
	 * @return slideshow
	 */
	function cvg_render_slideshow($arguments, $place_holder) {
	
		$cool_video_gallery = $this->cvg_instance;
		
		$options = get_option ( 'cvg_settings' );
		$options_player = get_option ( 'cvg_player_settings' );
		
		if ($options_player ['cvgplayer_autoplay'] == 1)
			$autoplay = "true";
		else
			$autoplay = "false";
		
		if ($options_player ['cvgplayer_mute'] == 1)
			$mute = "true";
		else
			$mute = "false";
			
		// Thumbnail width and height
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
					
		$gallery_id = $arguments ['galleryid'];
		
		if (isset ( $arguments ['limit'] ))
			$limit = $arguments ['limit'];
		else
			$limit = 0;
		
		$mode = $arguments ['mode'];
		
		$galleryID = ( int ) $gallery_id;
		
		$limit_by = ($limit > 0) ? $limit : 0;
		
		// get gallery values
		$videolist = $this->cvg_videodb->get_gallery ( $galleryID, false, 'sortorder', 'ASC', $limit_by );
		$outer = '';
		$out = '';
		
		if (isset ( $options ['cvg_random_video'] ) && $options ['cvg_random_video'] == 1)
			shuffle ( $videolist );
		
		if (is_array ( $videolist )) {
			
			$video_gallery = $this->cvg_videodb->find_gallery ( $galleryID );
			
			foreach ($videolist as $video) {
				$first_video = $video;
				break;
			}
			
			if($options['cvg_gallery_description'] == 1)
				$out .= '<span>'. $first_video->galdesc.'</span><br clear="all"/><br clear="all"/>';
					
			$out .= '<span class="cvg-gallery-slideshow" id="cvg-gallery-slideshow-'.$galleryID.'-'.$place_holder.'" style="height:'.$thumb_height .'px;width:'.$thumb_width .'px;">';
			
			$i = 0;
			foreach ( $videolist as $video ) {
				
				$class = "";
				if($i == 0) {
					$class = 'class="active"';
				}
				$i++;
				
				$out .= '<span '.$class .'>';
				
				
				$current_video = array ();
				
				if ($video->video_type == $cool_video_gallery->video_type_upload) {
					
					// Upload file type
					$current_video ['filename'] = site_url () . '/' . $video->path . '/' . $video->filename;
					$current_video ['thumb_filename'] = $video->path . '/thumbs/' . $video->thumb_filename;
					
					if (! file_exists ( ABSPATH . $current_video ['thumb_filename'] ))
						$current_video ['thumb_filename'] = WP_CONTENT_URL . '/plugins/' . dirname ( dirname ( plugin_basename ( __FILE__ ) ) ) . '/images/default_video.png';
					else
						$current_video ['thumb_filename'] = site_url () . '/' . $current_video ['thumb_filename'];
				} else if ($video->video_type == $cool_video_gallery->video_type_youtube) {
					
					// Youtube file type
					$current_video ['filename'] = $video->filename;
					$current_video ['thumb_filename'] = $video->thumb_filename;
				} else if ($video->video_type == $cool_video_gallery->video_type_media) {
					
					// Media file type
					$current_video ['filename'] = $video->filename;
					$current_video ['thumb_filename'] = $video->path . '/thumbs/' . $video->thumb_filename;
					
					if (! file_exists ( ABSPATH . $current_video ['thumb_filename'] ))
						$current_video ['thumb_filename'] = WP_CONTENT_URL . '/plugins/' . dirname ( dirname ( plugin_basename ( __FILE__ ) ) ) . '/images/default_video.png';
					else
						$current_video ['thumb_filename'] = site_url () . '/' . $current_video ['thumb_filename'];
				}
				
				
				$out .=  '<a href="' . $current_video['filename'] . '" title="' . stripslashes($video->description) . '"  rel="fancy_cvg_gallery_slide_'.$galleryID.'_'. $place_holder .'">' ;
				$out .=  '<img  src="' .$current_video['thumb_filename'] . '" style="width:' . $thumb_width . 'px;height:' . $thumb_height .'px;" ' ;
				$out .=  'alt="' . __('Click to Watch Video', 'cool-video-gallery') . '" /></a>';
				
				
				$current_video_title = isset($video->video_title) ? $video->video_title : "";
				if($options['cvg_description'] == 1)
					$out .= '<label class="cvg-single-video-wrapper-title" style="width:' . $thumb_width . 'px;">'. stripcslashes($current_video_title).'</label>';
				
				$out .= '</span>';	
			}
			
			$out .= '</span>';
			
			echo "<!-- Cool Video Gallery Script starts here -->";
			?>
	 		<script type="text/javascript">
				jQuery(document).ready(function() {
					// Slider
					jQuery(function() {
						if(jQuery('#cvg-gallery-slideshow-<?php echo $galleryID;?>-<?php echo $place_holder;?> span').length > 1) {
						    setInterval( function() { cvg_slide_switch('cvg-gallery-slideshow-<?php echo $galleryID;?>-<?php echo $place_holder;?>'); } , parseInt(<?php echo $options['cvg_slideshow']; ?>) );
						}
					});
					
					jQuery("a[rel=fancy_cvg_gallery_slide_<?php echo $galleryID.'_'.$place_holder;?>]").fancybox({
						'content' : '<div id="video_fancy_cvg_slide_gallery_<?php echo $galleryID."_".$place_holder;?>" style="overflow:hidden;"></div>',
						'width' : "<?php echo $player_width ; ?>",
						'height' :  "<?php echo $player_height ; ?>",
						'titlePosition' : 'inside',
						'autoScale' : false,
						'autoDimensions' : false,
						'margin' : 0,
						'padding' : 15,
						'transitionIn' : 'none',
						'transitionOut' : 'none',
						'centerOnScroll' : false,
						'showNavArrows' : false,
						'titleFormat' : function(title, currentArray, currentIndex, currentOpts) {
							
							var label_count = 'Video ' + (currentIndex + 1) + ' / ' + currentArray.length + '<br/>';
							var prev_disable_class = "";
							var next_disable_class = "";
							
							if(currentIndex + 1 == currentArray.length) 
								next_disable_class = "btnDisabled";	

							if(currentIndex == 0) 
								prev_disable_class = "btnDisabled";
							
							var prev_button = '<div class="cvg-fancybox-buttons fancybox-buttons"><a class="btnPrev ' +prev_disable_class+ '" title="Previous" href="javascript:;" onclick="jQuery.fancybox.prev();"></a></div>'   ;
							var next_button = '<div class="cvg-fancybox-buttons fancybox-buttons"><a class="btnNext ' +next_disable_class+ '" title="Next" href="javascript:;" onclick="jQuery.fancybox.next();"></a></div><br/>';
							var title = "";
							
							<?php if($options['cvg_description'] == 1) { ?>
								title =  this.title.length ?  jQuery.stripslashes(this.title) : '';
							<?php }?>
								
							return label_count + prev_button + next_button + title;
						},
						'onComplete' : function() {
							
							jwplayer('video_fancy_cvg_slide_gallery_<?php echo $galleryID.'_'.$place_holder;?>').setup({
								'file' : this.href,
								"autostart" : "<?php echo $autoplay;?>",
								"volume" : "<?php echo $options_player['cvgplayer_volume']; ?>",
								"width" : "<?php echo $player_width; ?>",
								"height" : "<?php echo $player_height; ?>",
								"mute" : "<?php echo $mute; ?>",
								"image" :  this.orig[0].src ,
								
								<?php if($options_player['cvgplayer_share_option'] == 1) { // Enable Share ?>			
								"sharing" : {
									"sites": ['twitter','email']
								},
								<?php } ?>
									
								"stretching" : "<?php echo $options_player['cvgplayer_stretching']; ?>",
								"skin" : "<?php echo $options_player['cvgplayer_skin']; ?>",
								"width" : "100%",
								"height" : "100%"
							});
							
							jwplayer('video_fancy_cvg_slide_gallery_<?php echo $galleryID.'_'.$place_holder;?>').onComplete(function() {

								if((jQuery.fancybox.getPos() + 1) < jQuery.fancybox.getTotal()) {
									<?php 
										if ($options_player['cvgplayer_autoplay']) {
										?>
											jQuery.fancybox.next();
										<?php } 
									?>
								}else {
									<?php 
										if ($options_player['cvgplayer_auto_close_single']) {
										?>
											jQuery.fancybox.close();
										<?php 
										}
									?>
								}
							});
						}
					});
					
				});

			</script>
			 <?php
			 echo "<!-- Cool Video Gallery Script ends here -->";
			
			$outer .= '<span class="video-gallery-thumbnail-box-outer">';
			$outer .= $out;
			$outer .= '</span>';
		}
		return $outer;
	}
		
	/**
	 * Function to generate playlist of videos
	 * @param $arguments - arguments from shortcode
	 * @return embeded playlist
	 */
	function cvg_render_playlist($arguments) {
		
		$cool_video_gallery = $this->cvg_instance;
		
		$options = get_option ( 'cvg_settings' );
		$options_player = get_option ( 'cvg_player_settings' );
		
		if ($options_player ['cvgplayer_autoplay'] == 1)
			$autoplay = "true";
		else
			$autoplay = "false";
		
		if ($options_player ['cvgplayer_mute'] == 1)
			$mute = "true";
		else
			$mute = "false";
			
		if (isset ( $arguments ['width'] ))
			$player_width = $arguments ['width'];
		else
			$player_width = $options_player ['cvgplayer_width'];
		
		if (isset ( $arguments ['height'] ))
			$player_height = $arguments ['height'];
		else
			$player_height = $options_player ['cvgplayer_height'];
		
		$gallery_id = $arguments ['galleryid'];
		
		if (isset ( $arguments ['limit'] ))
			$limit = $arguments ['limit'];
		else
			$limit = 0;
		
		$mode = $arguments ['mode'];
		
		
		$limit_by = ($limit > 0) ? $limit : 0;
		
		// get gallery values
		$videolist = $this->cvg_videodb->get_gallery ( $gallery_id, false, 'sortorder', 'ASC', $limit_by );
		$outer = '';
		$out = '';
		
		if (isset ( $options ['cvg_random_video'] ) && $options ['cvg_random_video'] == 1)
			shuffle ( $videolist );
			
		$playlist = array();
		
		foreach ( $videolist as $video ) {
			
			if($video->video_type == $cool_video_gallery->video_type_upload) {
			
				$video_url = site_url()  . '/' . $video->path . '/' . $video->filename;
				$thumb_url = site_url() . '/' . $video->path . '/thumbs/' . $video->thumb_filename;
			
				if(!file_exists(ABSPATH . '/' . $video->path . '/thumbs/' . $video->thumb_filename ))
					$thumb_url  = WP_CONTENT_URL .  '/plugins/' . dirname(dirname( plugin_basename(__FILE__))) . '/images/default_video.png';
			
			}else if($video->video_type == $cool_video_gallery->video_type_youtube){
			
				$video_url = $video->filename;
				$thumb_url = $video->thumb_filename;
			}else if($video->video_type == $cool_video_gallery->video_type_media){
			
				$video_url = $video->filename;
			
				$thumb_url = site_url() . '/' . $video->path . '/thumbs/' . $video->thumb_filename;
				if(!file_exists(ABSPATH . '/' . $video->path . '/thumbs/' . $video->thumb_filename ))
					$thumb_url  = WP_CONTENT_URL .  '/plugins/' . dirname(dirname( plugin_basename(__FILE__))) . '/images/default_video.png';
			
			}else {
			
				$video_url = site_url()  . '/' . $video->path . '/' . $video->filename;
				$thumb_url = site_url() . '/' . $video->path . '/thumbs/' . $video->thumb_filename;
			
				if(!file_exists(ABSPATH . '/' . $video->path . '/thumbs/' . $video->thumb_filename ))
					$thumb_url  = WP_CONTENT_URL .  '/plugins/' . dirname(dirname( plugin_basename(__FILE__))) . '/images/default_video.png';
			
			}
			
			$desc = stripcslashes($video->description);
			$pub_date = $video->videodate;
			$title = isset($video->video_title) ? $video->video_title : "" ;
			
			$playlist_item = array();
			
			$playlist_item['file'] = htmlspecialchars($video_url);
			$playlist_item['image'] = htmlspecialchars($thumb_url);
			$playlist_item['pubDate'] = htmlspecialchars($pub_date);
			
			if ($options ['cvg_description'] == 1) {
				$playlist_item['title'] = htmlspecialchars($title);
				$playlist_item['description'] = htmlspecialchars($desc);
			}
			
			array_push($playlist, $playlist_item);
		}
			
		$gallery_display = '<div style="max-width:'.$player_width.'px;height:'.$player_height.'px;width: 100%;display: inline-block;">';
		
		foreach ($videolist as $video) {
			$first_video = $video;
			break;
		}
		
		if($options['cvg_gallery_description'] == 1)
			$gallery_display .= '<span>'. $first_video->galdesc.'</span><br clear="all" /><br clear="all" />';
		
		$gallery_display .= '<span id="mediaplayer_gallery_' . $gallery_id . '"></span>';
		
		$gallery_display .= '</div>'
		?>
		<script type="text/javascript">
		
		jQuery(document).ready(function(){
			jwplayer("<?php echo 'mediaplayer_gallery_'.$gallery_id; ?>").setup({
    			'playlist': <?php echo json_encode($playlist); ?>,
    			'height' : parseInt("<?php echo $player_height; ?>"),
				'width' : parseInt("<?php echo $player_width; ?>"),
				'autostart' : "<?php echo $autoplay; ?>",
				'volume' : "<?php echo $options_player['cvgplayer_volume']; ?>",
				'mute' : "<?php echo $mute; ?>",
				
				<?php if($options_player['cvgplayer_share_option'] == 1) { // Enable Share ?>			
				"sharing" : {
					"sites": ['twitter','email']
				},
				<?php } ?>
				
				'stretching' : "<?php echo $options_player['cvgplayer_stretching']; ?>",
				"skin" : "<?php echo $options_player['cvgplayer_skin']; ?>",
				"width" : "100%"
			});
			jwplayer("<?php echo 'mediaplayer_gallery_'.$gallery_id; ?>").onReady(function() {
				jQuery("#<?php echo 'mediaplayer_gallery_'.$gallery_id; ?>").addClass('cvg-single-video-wrapper');
			});
		});
		
		</script>
		<?php
		return $gallery_display;
	}
	
			
	/**
	 * Function to publish videos as post.
	 */
	function publish_video_post() {
		
		global $user_ID;
		
		if(isset($_POST['post_title']) && $_POST['post_title'] == "") {
			
			$this->show_video_error(__('Please provide a title for Post', 'cool-video-gallery'));
			return;	
		}
		
		if($_POST['width'] == "" || $_POST['height'] == "") {
			
			$this->show_video_error(__('Width/Height not set properly.', 'cool-video-gallery'));
			return;
		}
		
		$width  = (int) $_POST['width'];
		$height = (int) $_POST['height'];

		$mode = "";
		
		if(isset($_POST['showtypevideo']) && $_POST['showtypevideo'] == "embed") {
			$mode = "mode=playlist"; 
		}

		$post['post_type']    = 'post';
		$post['post_content'] = "[cvg-video videoid=". $_POST['videosingle_publish'] ." width=$width height=$height $mode]";
		$post['post_author']  = $user_ID;
		$post['post_status']  = isset ( $_POST['publish'] ) ? 'publish' : 'draft';
		$post['post_title']   = $_POST['post_title'];

		$post_id = wp_insert_post ($post);
		
		$permalink = get_permalink($post_id);
		
		if ($post_id != 0) {
			
			$view_post_link_html = sprintf( 'Published a new %1$s. <a href="%2$s" target="_blank">%3$s</a>',
					isset  ($_POST['publish']) ? __('post') : __('draft'),
					esc_url( $permalink ),
					isset  ($_POST['publish']) ? __( 'View post' ) : __( 'View draft' )
					);
			
			$this->show_video_message( __($view_post_link_html, 'cool-video-gallery') );
            
		}
	}
	
	/**
	 * Function to publish gallery as post.
	 */
	function publish_gallery_post() {
	
		global $user_ID;
	
		if(isset($_POST['post_title']) && $_POST['post_title'] == "") {
				
			$this->show_video_error(__('Please provide a title for Post', 'cool-video-gallery'));
			return;
		}
		
		$mode = "";
	
		if(isset($_POST['showtypegallery']) && $_POST['showtypegallery'] == "showcase") {
			$mode = "mode=showcase";
		}else if(isset($_POST['showtypegallery']) && $_POST['showtypegallery'] == "slideshow") {
			$mode = "mode=slideshow";
		}else if(isset($_POST['showtypegallery']) && $_POST['showtypegallery'] == "playlist") {
			$mode = "mode=playlist";
		}
	
		$limit = "";
		
		if(isset($_POST['gallery_limit'])) {
			
			$limit  = ( $_POST['gallery_limit'] > 0 ) ? $_POST['gallery_limit'] : "";
		}
		
		$post['post_type']    = 'post';
		$post['post_content'] = "[cvg-gallery galleryid=". $_POST['gallerysingle_publish'] . " " . $mode . " limit=" . $limit . "]";
		$post['post_author']  = $user_ID;
		$post['post_status']  = isset ( $_POST['publish'] ) ? 'publish' : 'draft';
		$post['post_title']   = $_POST['post_title'];

		$post_id = wp_insert_post ($post);
	
		$permalink = get_permalink($post_id);
	
		if ($post_id != 0) {
				
			$view_post_link_html = sprintf( 'Published a new %1$s. <a href="%2$s" target="_blank">%3$s</a>',
					isset  ($_POST['publish']) ? __('post') : __('draft'),
					esc_url( $permalink ),
					isset  ($_POST['publish']) ? __( 'View post' ) : __( 'View draft' )
					);
				
			$this->show_video_message( __($view_post_link_html, 'cool-video-gallery') );
	
		}
	}

	/**
	 * Function to move video file and thumbnail from one gallery folder to another.
	 * 
	 * @param $vid - Video ID
	 * @param $gid - Gallery ID
	 */
	function move_video($vid, $gid) {
		
		$details = $this->cvg_videodb->find_video($vid);
		$video_details = $details[0];
		
		if($video_details->video_type == 'upload') {
			
			$source_video_file = $this->winabspath . $video_details->path . '/' . $video_details->filename;
			$source_thumb_file = $this->winabspath . $video_details->path .  '/thumbs/' . $video_details->thumb_filename;
			
			$gallery_details = $this->cvg_videodb->find_gallery($gid);
			
			$dest_video_file = $gallery_details->abspath . '/' . $video_details->filename;
			$dest_thumb_file = $gallery_details->abspath . '/thumbs/' . $video_details->thumb_filename;
			
			if (file_exists($source_video_file)) {
				if (copy($source_video_file, $dest_video_file)) {
					
			       unlink($source_video_file);
			    }
			}
			
			if (file_exists($source_thumb_file)) {
				if (copy($source_thumb_file, $dest_thumb_file)) {
					
			       unlink($source_thumb_file);
			    }
			}
			
		}else {
			return;
		}
	}
	
	/**
	 * Function to get list of all player skins from folder
	 * @param $path - folder path of skins
	 * @param $match - extention of file
	 * @param $prematch
	 * @param $revsort - order of sort
	 * @return array of skins
	 */
	function get_dir_skin($path, $match = "", $prematch = "", $revsort = true){
	
		$handle = opendir($path);
		$list = array();
	
		while (false !== ($file = readdir($handle))){
			if ($match != ""){
				if (substr($file, strlen($file) - strlen($match)) == $match){
					if ($prematch != ""){
						if (substr($file, 0, strlen($prematch)) == $prematch){
							$list[count($list)] = substr($file, strlen($prematch), strlen($file) - (strlen($match) + strlen($prematch)));
						}
					}else{
						$list[count($list)] = substr($file, 0, strlen($file) - strlen($match));
					}
				}
			}else{
				if ($prematch != ""){
					if (substr($file, 0, strlen($prematch)) == $prematch){
						$list[count($list)] = substr($file, strlen($prematch), strlen($file) - strlen($prematch));
					}
				}else{
					$list[count($list)] = $file;
				}
			}
		}
		if ($revsort){
			rsort($list);
		}else{
			sort($list);
		}
		return $list;
	}
}

/**
 * Function to override bcmod
 */
if( !function_exists('bcmod') ) {
	
	/**
	 * by Andrius Baranauskas and Laurynas Butkus
	 **/
	function bcmod( $x, $y )
	{
	    $take = 5;
	    $mod = ''; 
	
	    do
	    {
	        $a = (int)$mod.substr( $x, 0, $take );
	        $x = substr( $x, $take );
	        $mod = $a % $y;
	    }
	    while ( strlen($x) ); 
	
	    return (int)$mod;
	}
}
?>