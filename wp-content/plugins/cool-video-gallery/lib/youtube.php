<?php
/**
 * Helper Class for Youtube API
 * 
 * @author - Praveen Rajan
 */
class CVGYoutubeAPI {

	/**
	 * Initializes values
	 */
	function CVGYoutubeAPI() {

	}

	/**
	 * Returns Youtube video details
	 */
	function youtube_video_details($vids) {
		
		$options = get_option('cvg_settings');
		
		if (isset ( $options ['cvg_youtubeapikey'] ) && $options ['cvg_youtubeapikey'] != "") {
			
			$api_url = 'https://www.googleapis.com/youtube/v3/videos?id=' . $vids . '&key='.$options ['cvg_youtubeapikey'] .'&part=snippet,contentDetails';
			$result = json_decode ( @file_get_contents ( $api_url ), true );
			
			if(isset ($result)) {
				
				$video_list = array ();
					
				foreach ( $result ['items'] as $item ) {
				
					// Loop through video details
					$obj = new stdClass;
					
					$obj -> title = $item ['snippet'] ['title'];
					$obj -> description = $item ['snippet'] ['description'];
					
					$thumbURL = "";
					if(isset($item ['snippet'] ['thumbnails'] ['maxres'])) {
						$thumbURL =  $item ['snippet'] ['thumbnails'] ['maxres'] ['url'];
					}else if(isset($item ['snippet'] ['thumbnails'] ['standard'])) {
						$thumbURL =  $item ['snippet'] ['thumbnails'] ['standard'] ['url'];
					}else if(isset($item ['snippet'] ['thumbnails'] ['high'])) {
						$thumbURL =  $item ['snippet'] ['thumbnails'] ['high'] ['url'];
					}else if(isset($item ['snippet'] ['thumbnails'] ['medium'])) {
						$thumbURL =  $item ['snippet'] ['thumbnails'] ['medium'] ['url'];
					}else if(isset($item ['snippet'] ['thumbnails'] ['default'])) {
						$thumbURL =  $item ['snippet'] ['thumbnails'] ['default'] ['url'];
					}
					$obj -> thumbnailURL = $thumbURL;
					$obj -> watchURL = "https://www.youtube.com/watch?v=" . $item ['id'];
					$obj -> length = $this->covtime ( $item ['contentDetails'] ['duration'] );
				
					array_push($video_list, $obj);
					
				}
				
				return $video_list;
				
			}else {
				
				return "false";
			}
			
		} else {
			
			return "false";
		}
	}
	
	/**
	 * Convert Youtube Duration to seconds
	 * @author Praveen Rajan
	 */
	function covtime($youtube_time){
		$start = new DateTime('@0'); // Unix epoch
		$start->add(new DateInterval($youtube_time));
		return $start->format('U');
	}
}
?>