<?php
/**
 * Function to manage galleries.
 * @author Praveen Rajan
 */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) 
	die(__('You are not allowed to call this page directly.', 'cool-video-gallery')); 

//Loads WP default thickbox scripts
wp_enqueue_script('thickbox');
wp_enqueue_style('thickbox'); 

$cvg_core = new CvgCore();
$cvg_videodb = new CvgVideoDB();

//Section to delete list of galleries
if(isset($_POST['TB_gallerylist']) && !empty($_POST['TB_gallerylist'])) {
	
	// wp_nonce_field('cvg_manage_delete_gallery_nonce','cvg_manage_delete_gallery_nonce_csrf');
	if ( check_admin_referer( 'cvg_manage_delete_gallery_nonce', 'cvg_manage_delete_gallery_nonce_csrf' ) ) {
		$gids = explode(',', $_POST['TB_gallerylist']);
		foreach($gids as $gid) {
			$cvg_core->delete_video_gallery($gid);
			$cvg_videodb->delete_gallery($gid);
		}
		$cvg_core->show_video_message( __('Galleries deleted successfully.', 'cool-video-gallery'));
	}
}

//Section to delete a single gallery
if(isset($_POST['TB_gallerysingle']) && !empty($_POST['TB_gallerysingle'])) {
	
	// wp_nonce_field('cvg_manage_delete_single_gallery_nonce','cvg_manage_delete_single_gallery_nonce_csrf');
	if ( check_admin_referer( 'cvg_manage_delete_single_gallery_nonce', 'cvg_manage_delete_single_gallery_nonce_csrf' ) ) { 
		$gid = $_POST['TB_gallerysingle'];
		$cvg_core->delete_video_gallery($gid);
		$cvg_videodb->delete_gallery($gid);
		$gallery_delete_message = sprintf( 'Gallery \'%1$s\' deleted successfully.', $gid);
		$cvg_core->show_video_message(__($gallery_delete_message, 'cool-video-gallery'));
	}
}

//Section to publish a single video as Post
if(isset($_POST['gallerysingle_publish'])) {

	// wp_nonce_field('cvg_details_publish_post_nonce','cvg_details_publish_post_nonce_csrf');
	if ( check_admin_referer( 'cvg_gallery_publish_post_nonce', 'cvg_gallery_publish_post_nonce_csrf' ) ) {
		$cvg_core->publish_gallery_post();
	}
}

//Build the pagination for more than 25 galleries
if ( ! isset( $_GET['paged'] ) || $_GET['paged'] < 1 )
	$_GET['paged'] = 1;

$options = get_option('cvg_settings');
$per_page = $options['max_cvg_gallery'];	
$pagenum = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 0;
if ( empty($pagenum) )
	$pagenum = 1;

/*Start and end page settings for pagination.*/
$start_page = ($pagenum - 1) * $per_page;
$end_page = $start_page + $per_page;
	
$total_num_pages = count($cvg_videodb->find_all_galleries());

$total_value = ceil($total_num_pages / $per_page);
$defaults = array(
				'base' => add_query_arg( 'paged', '%#%' ), // http://example.com/all_posts.php%_% : %_% is replaced by format (below)
				'format' => '?paged=%#%', // ?page=%#% : %#% is replaced by the page number
				'total' => $total_value,
				'current' => $pagenum,
				'show_all' => false,
				'prev_next' => true,
				'prev_text' => __('&laquo;'),
				'next_text' => __('&raquo;'),
				'end_size' => 1,
				'mid_size' => 2,
				'type' => 'plain',
				'add_fragment' => ''
			);
$page_links = paginate_links( $defaults );			
				
$gallerylist = $cvg_videodb->find_all_galleries('gid', 'asc', TRUE ,$per_page, $start_page);
?>
<script type="text/javascript"> 
	function checkAll(form)	{
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox") {
				if(form.elements[i].name == "doaction[]") {
					if(form.elements[i].checked == true)
						form.elements[i].checked = false;
					else
						form.elements[i].checked = true;
				}
			}
		}
	}
	function getNumChecked(form){
		var num = 0;
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox") {
				if(form.elements[i].name == "doaction[]")
					if(form.elements[i].checked == true)
						num++;
			}
		}
		return num;
	}
	function checkSelected() {
	
		var numchecked = getNumChecked(document.getElementById('editgalleries'));
		if(numchecked < 1) { 
			alert('<?php echo esc_js(__('No video gallery selected.', 'cool-video-gallery')); ?>');
			return false; 
		} 
		actionId = jQuery('#bulkaction').val();
		switch (actionId) {
			case "no_action":
				return true;	
				break;
			case "delete_gallery":
				showDialog('delete_gallery', 100);
				return false;
				break;
		}
	}
	function showDialog( windowId, height ) {
		var form = document.getElementById('editgalleries');
		var elementlist = "";
		for (i = 0, n = form.elements.length; i < n; i++) {
			if(form.elements[i].type == "checkbox") {
				if(form.elements[i].name == "doaction[]")
					if(form.elements[i].checked == true)
						if (elementlist == "")
							elementlist = form.elements[i].value
						else
							elementlist += "," + form.elements[i].value ;
			}
		}
		jQuery("#" + windowId + "_bulkaction").val(jQuery("#bulkaction").val());
		jQuery("#" + windowId + "_deletelist").val(elementlist);
		tb_show("", "#TB_inline?width=200&height=" + height + "&inlineId=" + windowId + "&modal=true", false);
	}
	function showDialogDelete(id) {
		jQuery("#delete_gallery_single_value").val(id);
		tb_show("", "#TB_inline?width=200&height=100&inlineId=delete_gallery_single&modal=true", false);
	}
	function showDialogShortCode(id, gallery_name) {
		jQuery("#shortcode_gallery_single_value").val(id);
		jQuery('#generated_shortcode_gallery').val('<?php echo esc_js(__("Click on 'Generate' for Shortcode", 'cool-video-gallery')); ?>');
		tb_show("<?php _e('Generate Shortcode for Gallery', 'cool-video-gallery');?> - " + gallery_name, "#TB_inline?width=420&height=180&inlineId=shortcode_gallery_single&modal=false", false);
	}

	//	Function to show popup for publishing video
	function showDialogPublish(id, video_title) {
		jQuery("#publish_gallery_single_value").val(id);
		jQuery('#post_title_name').val(video_title);
		tb_show("<?php _e('Publish this gallery', 'cool-video-gallery'); ?>", "#TB_inline?width=400&height=190&inlineId=publish_gallery_single&modal=false", false);
	}
	
</script>
<div class="wrap">
	<h2><?php echo _e('Manage Video Gallery', 'cool-video-gallery'); ?></h2>
	<div class="cvg-clear"></div>
	<form id="editgalleries" method="POST" action="<?php echo admin_url('admin.php?page=cvg-gallery-manage&paged=' . $_GET['paged']); ?>" accept-charset="utf-8">
		<div class="tablenav">
			<div class="cvg-align-left">
				<select name="bulkaction" id="bulkaction">
					<option value="no_action" ><?php _e("No action"); ?></option>
					<option value="delete_gallery" ><?php _e("Delete"); ?></option>
				</select>
				<input class="button-secondary" type="submit" value="<?php _e('Apply', 'cool-video-gallery'); ?>" onclick="if ( !checkSelected() ) return false;" />
			</div>	
			<?php if ( $page_links ) { ?>
				<div class="tablenav-pages">
					<?php
						$page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s', 'cool-video-gallery') . '</span>%s',
											number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 ),
											number_format_i18n( min( $pagenum * $per_page, $total_num_pages ) ),
											number_format_i18n( $total_num_pages ),
											$page_links
											);
						echo $page_links_text;
					?>
				</div>
			<?php }?>
		</div>
		<table class="widefat">
			<thead>
			<tr>
				<th scope="col" class="column-cb" >
					<input type="checkbox" onclick="checkAll(document.getElementById('editgalleries'));" name="checkall"/>
				</th>
				<th scope="col" ><?php _e('ID', 'cool-video-gallery'); ?></th>
				<th scope="col" ><?php _e('Title', 'cool-video-gallery'); ?></th>
				<th scope="col" style="width:50%;"><?php _e('Description', 'cool-video-gallery'); ?></th>
				<th scope="col" ><?php _e('Author', 'cool-video-gallery'); ?></th>
				<th scope="col" ><?php _e('Quantity', 'cool-video-gallery'); ?></th>
				<th scope="col" ><?php _e('Action', 'cool-video-gallery'); ?></th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th scope="col" class="column-cb" >
					<input type="checkbox" onclick="checkAll(document.getElementById('editgalleries'));" name="checkall"/>
				</th>
				<th scope="col" ><?php _e('ID', 'cool-video-gallery'); ?></th>
				<th scope="col" ><?php _e('Title', 'cool-video-gallery'); ?></th>
				<th scope="col" ><?php _e('Description', 'cool-video-gallery'); ?></th>
				<th scope="col" ><?php _e('Author', 'cool-video-gallery'); ?></th>
				<th scope="col" ><?php _e('Quantity', 'cool-video-gallery'); ?></th>
				<th scope="col" ><?php _e('Action', 'cool-video-gallery'); ?></th>
			</tr>
			</tfoot>            
			<tbody>
				<?php
				if($gallerylist) {
					$index = number_format_i18n( ( $pagenum - 1 ) * $per_page + 1 );
					foreach($gallerylist as $gallery) {
						$class = ( !isset($class) || $class == 'class="alternate"' ) ? '' : 'class="alternate"';
						$gid = $gallery->gid;
						$name = (empty($gallery->title) ) ? $gallery->name : $gallery->title;
						$author_user = get_userdata( (int) $gallery->author );
						?>
						<tr id="gallery-<?php echo $gid ?>" <?php echo $class; ?> >
							<th scope="row" class="cb column-cb">
								<input name="doaction[]" type="checkbox" value="<?php echo $gid ?>" />
							</th>
							<td scope="row"><?php echo $gid; ?></td>
							<td>
								<a href="<?php echo admin_url( 'admin.php?page=cvg-gallery-manage&gid=' . $gid)?>" class='edit' title="<?php _e('Edit', 'cool-video-gallery'); ?>" >
									<?php echo $name; ?>
								</a>
							</td>
							<td>
							<?php 
								$trimmed = wp_trim_words( $gallery->galdesc, $num_words = 20, $more = null );
								echo "<span title='$gallery->galdesc'>" . $trimmed . "</span>"; 
							?>
							</td>
							<td><?php echo $author_user->display_name; ?></td>
							<td><?php echo $gallery->counter; ?></td>
							<td>
								<?php
									$actions = array();
									$actions['publish'] = '<a onclick="showDialogPublish(' . $gid . ', '.  "'$name'" . ');" href="#" >' . __('Publish', 'cool-video-gallery') . '</a>';
									$actions['shortcode'] = '<a onclick="showDialogShortCode(' . $gid . ', '.  "'$name'" . ');" href="#" >' . __('Shortcode', 'cool-video-gallery') . '</a>';
									$actions['delete'] = '<a class="submitdelete" onclick="showDialogDelete(' . $gid . ');" href="#" >' . __('Delete', 'cool-video-gallery') . '</a>'; 
									$action_count = count($actions);
									
									$i = 0;
									echo '<div class="row-actions" style="left:0px !important;">';
									foreach ( $actions as $action => $link ) {
										++$i;
										( $i == $action_count ) ? $sep = '' : $sep = ' | ';
										echo "<span class='$action'>$link$sep</span>";
									}
									echo '</div>';
								?>
							</td>
						</tr>
						<?php
					}
				} else {
					echo '<tr><td colspan="7" align="center"><strong>' . __('No galleries found', 'cool-video-gallery') . '</strong></td></tr>';
				}
				?>			
			</tbody>
		</table>
		</form>
		
		<!-- #gallery_delete -->
		<div id="delete_gallery" style="display: none;" >
			<form id="form-delete-gallery" method="POST" accept-charset="utf-8" action="<?php echo admin_url('admin.php?page=cvg-gallery-manage'); ?>">
				<input type="hidden" id="delete_gallery_deletelist" name="TB_gallerylist" value="" />
				<input type="hidden" id="delete_gallery_bulkaction" name="TB_bulkaction" value="" />
				<input type="hidden" name="page" value="manage-galleries" />
				<table width="100%" border="0" cellspacing="3" cellpadding="3" >
					<tr valign="top">
						<td><strong><?php _e('Delete Gallery?', 'cool-video-gallery'); ?></strong></td>
					</tr>
				  	<tr align="center">
				  		<?php wp_nonce_field('cvg_manage_delete_gallery_nonce','cvg_manage_delete_gallery_nonce_csrf'); ?>
				    	<td colspan="2" class="submit">
				    		<input class="button-primary" type="submit" name="TB_DeleteGallery" value="<?php _e('OK', 'cool-video-gallery'); ?>" />
				    		&nbsp;
				    		<input class="button-secondary" type="reset" value="&nbsp;<?php _e('Cancel', 'cool-video-gallery'); ?>&nbsp;" onclick="tb_remove()"/>
				    	</td>
					</tr>
				</table>
			</form>
		</div>
		<!-- #gallery_delete -->
		
		<!-- #gallery_delete_single -->
		<div id="delete_gallery_single" style="display: none;" >
			<form id="form-delete-gallery_single" method="POST" accept-charset="utf-8" action="<?php echo admin_url('admin.php?page=cvg-gallery-manage') ; ?>">
				<input type="hidden" id="delete_gallery_single_value" name="TB_gallerysingle" value="" />
				<table width="100%" border="0" cellspacing="3" cellpadding="3" >
					<tr valign="top">
						<td><strong><?php _e('Delete Gallery?', 'cool-video-gallery'); ?></strong></td>
					</tr>
				  	<tr align="center">
				  		<?php wp_nonce_field('cvg_manage_delete_single_gallery_nonce','cvg_manage_delete_single_gallery_nonce_csrf'); ?>
				    	<td colspan="2" class="submit">
				    		<input class="button-primary" type="submit" name="TB_DeleteSingle" value="<?php _e('OK', 'cool-video-gallery'); ?>" />
				    		&nbsp;
				    		<input class="button-secondary" type="reset" value="&nbsp;<?php _e('Cancel', 'cool-video-gallery'); ?>&nbsp;" onclick="tb_remove()"/>
				    	</td>
					</tr>
				</table>
			</form>
		</div>
		<!-- #gallery_delete_single -->
		
		<!-- #Shortcode generator -->
		<div id="shortcode_gallery_single" style="display: none;" >
				<script type="text/javascript">
					function generateShortCode() {
						var galleryId = jQuery('#shortcode_gallery_single_value').val();
						var galleryLimit = jQuery('#shortcode_generate_gallery_limit').val();
						var selection = jQuery('input[name=showtypegallery_generate]:checked', "").val();
						var mode_temp = "";
						if(selection == "embed"){
							mode_temp = "mode=playlist";
						}
						var mode_temp = '';
						if(selection != 'playlist')
							mode_temp = "limit=" + galleryLimit;
						var shortcode_text;
						if (galleryId != 0 )
							shortcode_text = "[cvg-gallery galleryid=" + galleryId + " mode=" + selection + " " + mode_temp  +  "]";
						jQuery('#generated_shortcode_gallery').val(shortcode_text);
					}
					
					function copyShortCodeClipBoard() {
						// Select all text
						var shortcode_field = document.getElementById("generated_shortcode_gallery");
						shortcode_field.focus();
						shortcode_field.setSelectionRange(0, shortcode_field.value.length) ;
					    var copysuccess = copySelectionText();
					    if (copysuccess){
						    alert('<?php _e( 'Shortcode copied to Clipboard!', 'cool-video-gallery');?>');
					    }else {
					    	alert('<?php _e( 'Web Browser compatibility issue. Please copy manually.', 'cool-video-gallery');?>');
					    }
					}

					function copySelectionText() {
						var copysuccess;
					    try{
							copysuccess = document.execCommand("copy");
					    } catch(e){
					        copysuccess = false;
					    }
					    return copysuccess;
					}
				</script>
				
				<input type="hidden" id="shortcode_gallery_single_value" value="" />
				<table width="100%" border="0" cellspacing="2" cellpadding="2" >
					<tr valign="top">
					<th align="left"><?php _e('Show as', 'cool-video-gallery') ?></th>
					<td>
						<label><input name="showtypegallery_generate" type="radio" value="showcase" checked="checked" /> <?php _e('Showcase', 'cool-video-gallery') ;?></label>&nbsp;&nbsp;&nbsp;&nbsp;
	            		<label><input name="showtypegallery_generate" type="radio" value="slideshow" /> <?php _e('Slideshow', 'cool-video-gallery') ;?></label>&nbsp;&nbsp;&nbsp;&nbsp;
	            		<label><input name="showtypegallery_generate" type="radio" value="playlist" /> <?php _e('Playlist', 'cool-video-gallery') ;?></label>&nbsp;&nbsp;
     				</td>
    			    </tr>
    			    <tr valign="top">
						<td><strong><?php _e('Limit: ', 'cool-video-gallery'); ?></strong></td>
						<td>
							<input type="text" size="5" maxlength="5" name="width" id="shortcode_generate_gallery_limit" value="5"/>
						</td>
					</tr>
				  	<tr align="right">
				  		<td colspan="2">
				  			<input type="text" style="width:100%;" id="generated_shortcode_gallery" readonly/>
				  		</td>
				  	</tr>
				  	<tr>	
				    	<td colspan="2" class="submit">
				    		<input class="button-primary" onclick="generateShortCode();" type="button" id="generate_shortcode_gallery" value="<?php _e('Generate', 'cool-video-gallery'); ?>" />
				    		<input class="button-primary" onclick="copyShortCodeClipBoard();" type="button" id="copy_shortcode_gallery" value="<?php _e('Copy to Clipboard', 'cool-video-gallery'); ?>" />
				    	</td>
					</tr>
				</table>
		</div>
		<!-- #Shortcode generator -->
		
		
		<!-- #publish as Post -->
		<div id="publish_gallery_single" style="display: none;" >
			<form id="form-publish-gallery_single" method="POST" accept-charset="utf-8" action="<?php echo admin_url('admin.php?page=cvg-gallery-manage'); ?>">
				<?php wp_nonce_field('cvg_gallery_publish_post_nonce','cvg_gallery_publish_post_nonce_csrf'); ?>
				<input type="hidden" id="publish_gallery_single_value" name="gallerysingle_publish" value="" />
				<table width="100%" border="0" cellspacing="2" cellpadding="2" >
					<tr valign="top">
						<td><strong><?php _e('Post Title: ', 'cool-video-gallery'); ?></strong></td>
						<td><span id='spanButtonPlaceholder'><input type="text" id="post_title_name" name="post_title" style="width:100%;"/></span>
						</td>
					</tr>
					<tr valign="top">
					<th align="left"><?php _e('Show as', 'cool-video-gallery') ?></th>
					<td>
						<label><input name="showtypegallery" type="radio" value="showcase" id="showcase" checked="checked" /> <?php _e('Showcase', 'cool-video-gallery') ;?></label><br />
	            		<label><input name="showtypegallery" type="radio" value="slideshow" id="slideshow" /> <?php _e('Slideshow', 'cool-video-gallery') ;?></label><br />
	            		<label><input name="showtypegallery" type="radio" value="playlist" id="playlist" /> <?php _e('Playlist', 'cool-video-gallery') ;?></label><br />
         			</td>
        			</tr>
        			<tr>
	            		<td nowrap="nowrap" valign="top"><label for="showtype"><?php _e("Limit", 'cool-video-gallery'); ?></label></td>
	            		<td><input type="text" value="5" name="gallery_limit" id="gallery_limit" size="3"/></td>
	          		</tr>
					  	<tr align="right">
					    	<td colspan="2" class="submit">
					    		<input class="button-primary" type="submit" name="publish" id="publish_gallery_post" value="<?php _e('Publish', 'cool-video-gallery'); ?>" />
				    		&nbsp;
				    		<input class="button-secondary"  type="submit" name="draft" value="<?php _e('Draft', 'cool-video-gallery'); ?>"/>
				    	</td>
					</tr>
				</table>
			</form>
		</div>
		<!-- #publish as Post -->
</div><!-- wrap -->