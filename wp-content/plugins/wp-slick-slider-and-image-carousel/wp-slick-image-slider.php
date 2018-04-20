<?php
/**
 * Plugin Name: WP Slick Slider and Image Carousel
 * Plugin URI: https://www.wponlinesupport.com/plugins
 * Text Domain: wp-slick-slider-and-image-carousel
 * Domain Path: /languages/
 * Description: Easy to add and display wp slick image slider and carousel  
 * Author: WP Online Support
 * Version: 1.4
 * Author URI: https://www.wponlinesupport.com
 *
 * @package WordPress
 * @author WP Online Support
 */

if( !defined('WPSISAC_VERSION') ){
    define( 'WPSISAC_VERSION', '1.4' ); // Plugin version
}
if( !defined( 'WPSISAC_VERSION_DIR' ) ) {
    define( 'WPSISAC_VERSION_DIR', dirname( __FILE__ ) ); // Plugin dir
}
if( !defined( 'WPSISAC_URL' ) ) {
    define( 'WPSISAC_URL', plugin_dir_url( __FILE__ ) ); // Plugin url
}
if( !defined( 'WPSISAC_POST_TYPE' ) ) {
    define( 'WPSISAC_POST_TYPE', 'slick_slider' ); // Plugin post type
}

register_activation_hook( __FILE__, 'free_wpsisac_install_premium_version' );
function free_wpsisac_install_premium_version(){
if( is_plugin_active('wp-slick-slider-and-image-carousel-pro/wp-slick-image-slider.php') ){
     add_action('update_option_active_plugins', 'free_wpsisac_deactivate_premium_version');
    }
}
function free_wpsisac_deactivate_premium_version(){
   deactivate_plugins('wp-slick-slider-and-image-carousel-pro/wp-slick-image-slider.php',true);
}
add_action( 'admin_notices', 'free_wpsisac_rpfs_admin_notice');
function free_wpsisac_rpfs_admin_notice() {
    $dir = ABSPATH . 'wp-content/plugins/wp-slick-slider-and-image-carousel-pro/wp-slick-image-slider.php';
    if( is_plugin_active( 'wp-slick-slider-and-image-carousel/wp-slick-image-slider.php' ) && file_exists($dir)) {
        global $pagenow;
        if( $pagenow == 'plugins.php' ){
            deactivate_plugins ( 'wp-slick-slider-and-image-carousel-pro/wp-slick-image-slider.php',true);
            if ( current_user_can( 'install_plugins' ) ) {
                echo '<div id="message" class="updated notice is-dismissible"><p><strong>Thank you for activating  WP Slick Slider and Image Carousel</strong>.<br /> It looks like you had PRO version <strong>(<em> WP Slick Slider and Image Carousel  Pro</em>)</strong> of this plugin activated. To avoid conflicts the extra version has been deactivated and we recommend you delete it. </p></div>';
            }
        }
    }
}     

add_action('plugins_loaded', 'wpsisac_load_textdomain');
function wpsisac_load_textdomain() {
	load_plugin_textdomain( 'wp-slick-slider-and-image-carousel', false, dirname( plugin_basename(__FILE__) ) . '/languages/' );
} 

// Function file
require_once( WPSISAC_VERSION_DIR . '/includes/wpsisac-function.php' );

// Script
require_once( WPSISAC_VERSION_DIR . '/includes/class-wpsisac-script.php' );

// Post type file
require_once( WPSISAC_VERSION_DIR . '/includes/wpsisac-slider-custom-post.php' );

// Shortcode File
require_once( WPSISAC_VERSION_DIR . '/includes/shortcodes/wpsisac-slider.php' );
require_once( WPSISAC_VERSION_DIR . '/includes/shortcodes/wpsisac-carousel.php' );

// Admin File
require_once( WPSISAC_VERSION_DIR . '/includes/admin/class-wpsisac-admin.php' );

// How it work file, Load admin files
if ( is_admin() || ( defined( 'WP_CLI' ) && WP_CLI ) ) {
    require_once( WPSISAC_VERSION_DIR . '/includes/admin/wpsisac-how-it-work.php' );	
}