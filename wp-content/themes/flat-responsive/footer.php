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
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-3">
                        <img class="img-responsive" src="wp-content/themes/flat-responsive/images/logo-footer.png" alt="" width="105" height="106">
                    </div>
                    <div class="col-md-9">
                        Персональный сайт канала “Азартная Сашка”<br> Вся информация защищена. Только для лиц достигших совершеннолетия 18+<br> © <?php echo date('Y'); ?>
                    </div>
                </div>
            </div>
            <div class="col-md-6 hidden-xs hidden-sm">
                <?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false,'menu_class' => 'footer', 'fallback_cb' => false, 'depth' => 1) ); ?>
            </div>

<?php wp_footer(); ?>
</body>
</html>