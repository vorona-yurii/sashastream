<?php
/**
 * Admin Class
 *
 * Handles the Admin side functionality of plugin
 *
 * @package WP Slick Slider and Image Carousel
 * @since 1.0.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

class Wpsisac_Admin {

	function __construct() {		
		
		// Action to add admin menu
		add_action( 'admin_menu', array($this, 'wpsisac_register_menu'), 12 );

		
	}

	
	/**
	 * Function to add menu
	 * 
	 * @package WP Slick Slider and Image Carousel
	 * @since 1.0.0
	 */
	function wpsisac_register_menu() {

		// Register plugin premium page
		add_submenu_page( 'edit.php?post_type='.WPSISAC_POST_TYPE, __('Upgrade to PRO - WP Slick Slider and Image Carousel', 'wp-slick-slider-and-image-carousel'), '<span style="color:#2ECC71">'.__('Upgrade to PRO', 'wp-slick-slider-and-image-carousel').'</span>', 'manage_options', 'wpsisac-premium', array($this, 'wpsisac_premium_page') );
	}

	/**
	 * Getting Started Page Html
	 * 
	 * @package WP Slick Slider and Image Carousel
	 * @since 1.0.0
	 */
	function wpsisac_premium_page() {
		include_once( WPSISAC_VERSION_DIR . '/includes/admin/settings/premium.php' );
	}
	
	
}

$wpsisac_admin = new Wpsisac_Admin();