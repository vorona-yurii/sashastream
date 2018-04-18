<?php
/**
 * The template for displaying the footer.
 *
 * Contains footer content and the closing of the #main and #page div elements.
 *
 * @package flat-responsive
 * @since 1.0.3
 */
?>
<?php get_sidebar( 'bottom' ); ?>


<div class="flat_responsive_footer">
	<div class="container">
		<div class="row">
            <div class="col-md-12">
            <?php
                if (get_theme_mod('footer_social_display') == 1) {
                    get_template_part('partials/social','bar');
                }
                ?>
            </div>
            <div class="col-md-12">
                <?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false,'menu_class' => 'footer', 'fallback_cb' => false, 'depth' => 1) ); ?>
            </div>
            <div class="col-md-12">
                <div class="copyright">
                <p> <?php $site_name = esc_attr(get_bloginfo('name')); ?>
                    <?php esc_attr_e('Copyright &copy;', 'flat-responsive'); 
                    $fr_date = date('Y'); ?> 
                    <?php printf(__('%s', 'flat-responsive'), $fr_date ); ?> <strong><?php echo esc_attr(get_theme_mod('copyright', $site_name));?></strong>
                    <?php 
                    $head_img = get_header_image();
                    $head_img_array = explode("/", $head_img);
                    if(get_theme_mod('footer_author_link') != '1' ){
                        if(in_array("banner-1.jpg", $head_img_array)){  
                         ?>
                                <span>
                                    <a href="<?php echo esc_url('https://www.styledthemes.com/themes/flat-responsive-pro/');?>"><?php esc_html_e('Flat Responsive','flat-responsive'); ?></a>
                                        <?php echo esc_html__( 'Wordpress Theme by','flat-responsive');?> 
                                    <a href="<?php echo esc_url('https://www.styledthemes.com/');?>">
                                        <?php echo esc_html__( 'Styled Themes','flat-responsive');?>
                                    </a>
                                </span>
                            <?php
                            }
                         }else{
                            esc_attr_e('All rights reserved.', 'flat-responsive'); 
                      }
                    ?>

                </div>
            </div>

<?php wp_footer(); ?>
</body>
</html>