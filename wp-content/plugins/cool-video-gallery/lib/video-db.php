<?php 
/**
 * Class for video gallery database.
 * 
 * @author Praveen Rajan
 */
class CvgVideoDB{
	
	var $cvg_instance; // Cool Video Gallery instance
	
	/**
	 * Constructor of class
	 */
	function CvgVideoDB() {
		
		$this->cvg_instance = CoolVideoGallery::get_instance();
		$this->default_gallery_path = $this->cvg_instance->default_gallery_path;
		$this->winabspath = $this->cvg_instance->winabspath;
	}
   
    /**
     * Get a gallery given its ID
     * 
     * @param int|string $id - Gallery id
     * @return An object (false if not found)
     */
    function find_gallery( $id ) {

        global $wpdb;
        
        if( is_numeric($id) ) {
            $gallery = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " .$wpdb->prefix . "cvg_gallery WHERE gid = %d", $id ) );
        } 
        
        // Build the object from the query result
        if ($gallery) {
            
        	$gallery->title = stripslashes($gallery->title);
            $gallery->galdesc  = stripslashes($gallery->galdesc);
            $gallery->abspath = $this->winabspath . $gallery->path;
            
            return $gallery;
        } else {
        	
            return false;
        }
    }
    
    /**
     * Get all the galleries
     * 
     * @param string $order_by
     * @param string $order_dir
     * @param bool $counter (optional) Select true  when you need to count the videos
     * @param int $limit number of paged galleries, 0 shows all galleries
     * @param int $start the start index for paged galleries
     * @return array $galleries
     */
    function find_all_galleries($order_by = 'gid', $order_dir = 'ASC', $counter = false, $limit = 0, $start = 0) {      
        global $wpdb; 
        
        // Check for the exclude setting
        $order_dir = ( $order_dir == 'DESC') ? 'DESC' : 'ASC';
        $limit_by  = ( $limit > 0 ) ? 'LIMIT ' . intval($start) . ',' . intval($limit) : '';
        $galleries = $wpdb->get_results( "SELECT SQL_CALC_FOUND_ROWS * FROM " . $wpdb->prefix . "cvg_gallery ORDER BY {$order_by} {$order_dir} {$limit_by}", OBJECT_K );
        
        if ( !$galleries )
            return array();
        
        // get the galleries information    
        foreach ($galleries as $key => $value) {
            $galleriesID[] = $key;
            // init the counter values
            $galleries[$key]->counter = 0;
            $galleries[$key]->title = stripslashes($galleries[$key]->title);
            $galleries[$key]->galdesc  = stripslashes($galleries[$key]->galdesc);
        }

        if ( !$counter )
            return $galleries;
        
        // get the counter values   
     	   $videoCounter = $wpdb->get_results('SELECT galleryid, COUNT(*) as counter FROM '. $wpdb->prefix .'cvg_videos WHERE galleryid IN (\''.implode('\',\'', $galleriesID).'\') GROUP BY galleryid', OBJECT_K);

        if ( !$videoCounter )
            return $galleries;
        
        // add the counter to the gallery objekt    
        foreach ($videoCounter as $key => $value) {
            $galleries[$value->galleryid]->counter = $value->counter;
        }
        
        return $galleries;
    }
    
    /**
     * Delete a gallery AND all the videos associated to this gallery!
     * 
     * @id The gallery ID
     * @return bool result of query
     */
    function delete_gallery( $id ) {        
       
    	global $wpdb;
                
       	$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "cvg_videos WHERE galleryid = %d", $id) );
       	$wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "cvg_gallery WHERE gid = %d", $id) );
       	
       	return true;
    }
    
    /**
     * Get an video given its ID
     * 
     * @param int $id The video ID
     * @return object An object representing the video (false if not found)
     */
    function find_video( $id ) {
    	
        global $wpdb;
        
        $result = $wpdb->get_results( $wpdb->prepare( "SELECT tt.*, t.* FROM " . $wpdb->prefix . "cvg_gallery AS t INNER JOIN " . $wpdb->prefix . "cvg_videos AS tt ON t.gid = tt.galleryid WHERE tt.pid = %d", $id, OBJECT_K ));
        
        // Build the object from the query result
        if ($result) {
            return $result;
        } 
                
        return false;
    }
    
    /**
     * Update or add meta data for a video
     * 
     * @param int $id The video ID
     * @param array $values An array with existing or new values
     * @return bool result of query
     */ 
    function update_video_meta( $id, $new_values ) {
        global $wpdb;
        
        $old_values = $wpdb->get_var( $wpdb->prepare( "SELECT meta_data FROM " . $wpdb->prefix . "cvg_videos WHERE pid = %d ", $id ) );
        $old_values = unserialize( $old_values );

        $meta = array_merge( (array)$old_values, (array)$new_values );
		
        $result = $wpdb->query( $wpdb->prepare("UPDATE " . $wpdb->prefix . "cvg_videos SET meta_data = %s WHERE pid = %d", serialize($meta), $id) );
        
        return $result;
    }
    
    function update_video_thumbnail_name( $id, $thumbnail_name ) {
    	
    	global $wpdb;
    	$result = $wpdb->query( $wpdb->prepare("UPDATE " . $wpdb->prefix . "cvg_videos SET thumb_filename = %s WHERE pid = %d", $thumbnail_name, $id) );
    	return $result;
    }
    
    /**
     * This function return all information about the gallery and the videos inside
     * 
     * @param int|string $id or $name
     * @param string $order_by 
     * @param string $order_dir (ASC |DESC)
     * @param bool $exclude
     * @param int $limit number of paged galleries, 0 shows all galleries
     * @param int $start the start index for paged galleries
     * @return An array containing the objects representing the videos in the gallery.
     */
    function get_gallery($id, $is_admin = true, $order_by = 'sortorder', $order_dir = 'ASC', $limit = 0, $start = 0) {

        global $wpdb;

        // init the gallery as empty array
        $gallery = array();
        
        // Say no to any other value
        $order_dir = ( $order_dir == 'DESC') ? 'DESC' : 'ASC';
        $order_by  = ( empty($order_by) ) ? 'sortorder' : $order_by;
        
        $limit_by  = ( $limit > 0 ) ? 'LIMIT ' . intval($start) . ',' . intval($limit) : '';
        
		$exclude_option = "";
		if(!$is_admin) {
			$exclude_option = "AND tt.exclude = '0'";
		}
        // Query database
        if( is_numeric($id) )
            $result = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS tt.*, t.* FROM " . $wpdb->prefix . "cvg_gallery AS t INNER JOIN " . $wpdb->prefix . "cvg_videos AS tt ON t.gid = tt.galleryid WHERE t.gid = %d  $exclude_option ORDER BY tt.{$order_by} {$order_dir} {$limit_by}", $id ), OBJECT_K );
        else
            $result = $wpdb->get_results( $wpdb->prepare( "SELECT SQL_CALC_FOUND_ROWS tt.*, t.* FROM " . $wpdb->prefix . "cvg_gallery AS t INNER JOIN " . $wpdb->prefix . "cvg_videos AS tt ON t.gid = tt.galleryid WHERE t.name = %s $exclude_option ORDER BY tt.{$order_by} {$order_dir} {$limit_by}", $id ), OBJECT_K );

        // Build the object
        if ($result) {
                
            // Now added all videos data
            foreach ($result as $key => $value)
                $gallery[$key] = $value;
        }
        return $gallery;        
    }

    /**
    * Delete an video entry from the database
    * 
    * @param integer $id is the video ID
    * @return bool result of query
    */
    function delete_video( $id ) {
    	
        global $wpdb;
        // Delete the video
        $result = $wpdb->query( $wpdb->prepare( "DELETE FROM " . $wpdb->prefix . "cvg_videos WHERE pid = %d", $id) );
        return $result;
    }
    
    /**
     * Function to update details of a gallery.
     * 
     * @return bool result of query
     */
    function update_gallery() {
    	
    	global $wpdb;
    	$gallery_desc = $wpdb->query( $wpdb->prepare ("UPDATE " . $wpdb->prefix . "cvg_gallery SET title= '%s', galdesc= '%s' WHERE gid = %d", esc_attr($_POST['title']), esc_attr( $_POST['gallerydesc'] ), $_POST['gid']) );
    	return true;
    }
    
    /**
     * Function to get all videos
     * 
     * @return all videos
     */
    function get_all_videos() {
    	
    	global $wpdb;
    	$result = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "cvg_videos ORDER BY pid", OBJECT_K );
    	return $result;
    }
	
	/**
     * Function to get all videos
     * 
	 * @param - Video Id
     * @return all videos
     */
    function get_gallery_id( $video_id ) {
    	
    	global $wpdb;
		$result = $wpdb->get_results( "SELECT galleryId FROM " . $wpdb->prefix . "cvg_videos  WHERE pid=". $video_id, OBJECT_K );
    	return $result;
    }
	
	/**
	 * Function to return details of all videos for sitemap
	 * 
	 * @return - all video details
	 */
	function get_all_videos_sitemap() {
		
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. $wpdb->prefix .'cvg_videos ORDER BY galleryid', ARRAY_A);
		return $results;
	}
	
	/**
	 * Function to get gallery
	 * 
	 * @param - Gallery Id
	 * @return - Gallery
	 */
	function get_gallery_sitemap($gallery_id) {
		
		global $wpdb;
		$results = $wpdb->get_results('SELECT * FROM '. $wpdb->prefix .'cvg_gallery WHERE gid='. $gallery_id, ARRAY_A);
		return $results;
	}
	
	/**
	 * Function to move video from gallery to another
	 * 
	 * @param - Video Id
	 * @param - Gallery Id
	 */
	function move_video( $id, $gid ) {
       
		global $wpdb;
        $result = $wpdb->query( $wpdb->prepare ("UPDATE " . $wpdb->prefix . "cvg_videos SET galleryid= %d WHERE pid = %d", $gid, $id) );
        return $result;
    }
    
    /**
     * Function to sort videos in a gallery
     *
     * @sort_order Sort Order of gallery id
     */
    function sort_gallery ($sort_order) {
    	
    	global $wpdb;
    	
    	$sortArray = explode(',', $sort_order);
    	if (is_array($sortArray)){
    		$sortindex = 1;
    		foreach($sortArray as $pid) {
    			$result = $wpdb->query( $wpdb->prepare ("UPDATE " . $wpdb->prefix . "cvg_videos SET sortorder= %d WHERE pid = %d", $sortindex, $pid) );
    			if($result === FALSE) {
    				return false;
    			}else {
    				$sortindex++;
    			}
    		}
    		return true;
    	}else {
    		return false;
    	}
    }
    
    function add_gallery($galleryname, $video_path, $gallerytitle, $gallery_desc) {
    	
    	global $wpdb, $user_ID;
    	
    	$result = $wpdb->get_var("SELECT name FROM " . $wpdb->prefix . "cvg_gallery WHERE name = '$galleryname' ");
    	
    	if ($result) {
    		return false;
    	} else {
    		$result = $wpdb->query( $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "cvg_gallery (name, path, title, author, galdesc) VALUES (%s, %s, %s, %s, %s)", $galleryname, $video_path, $gallerytitle , $user_ID, $gallery_desc) );
    		if ($result)
	    		return true;
    	}
    }
    
    function add_media_gallery($galleryID, $media_post_id) {
    	
    	global $wpdb;

    	// Get Post Details
    	$post_details = get_post($media_post_id);
    	$time_updated = current_time('mysql', 1);
    	$alttext = esc_sql($post_details->post_name);
    	$thumbs_file_name = "thumbs_" . $alttext . '.png';
    	
    	$video_title = isset($post_details->post_title) ? htmlspecialchars($post_details->post_title)  : htmlspecialchars($post_details->post_name);
    	
    	// save it to the database
    	$result = $wpdb->query( $wpdb->prepare("INSERT INTO " . $wpdb->prefix ."cvg_videos (galleryid, filename, thumb_filename, alttext, video_title, description, videodate, video_type) VALUES (%s, %s, %s, %s, %s, %s, %s, %s)", $galleryID, $post_details->guid, $thumbs_file_name, $alttext, $video_title, esc_sql($post_details->post_content), $time_updated, $this->cvg_instance->video_type_media) );
    	
    	if($result === FALSE)
    		return false;
    	else 
    		return (int) $wpdb->insert_id;
    }
    
    function add_youtube_video($galleryID, $video_details) {
    	
    	$cvg_core = new CvgCore();
    	
    	global $wpdb;
    	
    	$alttext = esc_sql($video_details->title);
    	$time_updated = current_time('mysql', 1);
    	$thumb_filename = $video_details->thumbnailURL;
    	
    	$videoDuration = $cvg_core->secondsToWords((int) $video_details->length);
    	
    	$meta =  array ( 'videoDuration' => $videoDuration );
    	
    	// save it to the database
    	$result = $wpdb->query( $wpdb->prepare("INSERT INTO " . $wpdb->prefix ."cvg_videos (galleryid, filename, thumb_filename, alttext, description, video_title, videodate, video_type, meta_data) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s)", $galleryID, $video_details->watchURL, $thumb_filename, $alttext, esc_sql($video_details->description), $video_details->title, $time_updated, $this->cvg_instance->video_type_youtube, serialize($meta)) );
    	
    	if($result === FALSE)
    		return false;
    	else
    		return (int) $wpdb->insert_id;
    }
    
    function check_video_gallery($galleryID, $video_newname) {
    	
    	global $wpdb;
    	$video_found = $wpdb->get_var("SELECT filename FROM " .  $wpdb->prefix . "cvg_videos  WHERE filename = '$video_newname' AND galleryid = '$galleryID'");
    	return $video_found;
    }
    
    function get_all_gallery_widgets() {
    	
    	global $wpdb;
    	$results = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix ."cvg_gallery ORDER BY 'name' ASC ");
    	return $results;
    }
}
?>