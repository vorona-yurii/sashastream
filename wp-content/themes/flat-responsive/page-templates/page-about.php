<?php
/**
 *
Template Name: Page About
 *
 * Description: A page template without the left or right columns
 * @package flat-responsive
 * @since 1.0.0
 */

get_header(); ?>


<?php get_sidebar( 'top' ); ?>
<?php get_sidebar( 'inset-top' ); ?>
    <section id="fr-content-area" class="fr-contents" role="main">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <?php while( have_posts() ) : the_post();?>
                        <h2><?php the_title() ?></h2>
                        <div class="col-md-6 col-sm-12">
                            <?php echo get_the_post_thumbnail() ?>
                        </div>
                        <div class="col-md-6 col-sm-12">
                            <?php echo the_content() ?>
                        </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </section>
<?php get_footer(); ?>