/**
 * Theme Customizer enhancements for a better user experience.
 *
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */

( function( $ ) {
	// Site title and description.
	wp.customize( 'blogname', function( value ) {
		value.bind( function( to ) {
			$( '.site-title a' ).text( to );
		} );
	} );
	wp.customize( 'blogdescription', function( value ) {
		value.bind( function( to ) {
			$( '.site-description' ).text( to );
		} );
	} );
} )( jQuery );

jQuery(document).ready(function() {

	jQuery( '.wp-full-overlay-sidebar-content' ).prepend( '<a style="width: 80%; margin: 10px auto 5px auto; display: block; text-align: center;" href="https://www.styledthemes.com/themes/flat-responsive-pro/?utm_source=FreeThemes&utm_medium=CustomizerLink&utm_campaign=Flat_Responsive" class="button" target="_blank">'+ flatresponsive_button.pro +'</a>' );
	jQuery( '.wp-full-overlay-sidebar-content' ).prepend( '<a style="width: 80%; margin: 10px auto 5px auto; display: block; text-align: center;" href="https://wordpress.org/support/view/theme-reviews/flat-responsive?filter=5" class="button" target="_blank">'+ flatresponsive_button.review +'</a>' );
	jQuery( '.wp-full-overlay-sidebar-content' ).prepend( '<a style="width: 80%; margin: 10px auto 5px auto; display: block; text-align: center;" href="https://www.styledthemes.com/documentation/free-themes/flat-responsive/?utm_source=FreeThemes&utm_medium=CustomizerLink&utm_campaign=Flat_Responsive" class="button" target="_blank">'+ flatresponsive_button.documentation +'</a>' );	
	jQuery('input[data-customize-setting-link="nav_position_scrolltop_val"] ').attr('readonly', 'readonly');
});