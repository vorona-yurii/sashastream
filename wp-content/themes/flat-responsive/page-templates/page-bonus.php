<?php
/**
 *
Template Name: Bonuses page
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
                    <div class="entry-content">
                        <script>jQuery(document).ready(function($){$("#accordions-107.accordions").accordion({active: "",event: "click",collapsible: true,heightStyle: "content",animated: "swing",})})</script><style type="text/css">
                            #accordions-107{
                                text-align: left;}
                            #accordions-107{
                                background:#ffffff url(http://sashastream/wp-content/plugins/accordions/assets/global/images/bg/eight_horns.png) repeat scroll 0 0;
                                padding: 0;
                            }
                            #accordions-107 .accordions-head{
                                color:#ffffff;
                                font-size:14px;
                                background:#0a0404;
                            }

                            #accordions-107 .ui-accordion-header-active{
                                background: #f36f2e;

                            }
                            #accordions-107 .accordion-content{
                                background:#ffffff none repeat scroll 0 0;
                                color:#e8e8e8;
                                font-size:13px;
                            }

                            #accordions-107 .accordion-icons{
                                color:#565656;
                                font-size:16px;
                            }
                        </style><style type="text/css">#accordions-107{}
                            #accordions-107 .accordions-head, #accordions-107 .accordions-head:hover, #accordions-107 .accordions-head:active{border: none !important}
                            #accordions-107 .accordion-content{}</style>
                        <div id="accordions-107" class="accordions accordions-themes flat accordions-107 ui-accordion ui-widget ui-helper-reset" role="tablist">
                            <div style="" class="accordions-head ui-accordion-header ui-state-default ui-corner-all ui-accordion-icons" role="tab" id="ui-id-1" aria-controls="ui-id-2" aria-selected="false" aria-expanded="false" tabindex="0">
                                <span class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-e"></span>
                                <i class="accordion-icons left accordion-plus fa fa-chevron-up"></i>
                                <i class="accordion-icons left accordion-minus fa fa-arrow-down fa-chevron-down"></i>
                                <span class="accordions-head-title">Бонус на депозит</span>
                            </div>
                            <div class="accordion-content ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" id="ui-id-2" aria-labelledby="ui-id-1" role="tabpanel" aria-hidden="true" style="display: none;">
                                <?php
                                    if ( have_posts() ) : // если имеются записи в блоге.
                                    query_posts('cat=6');   // указываем ID рубрик, которые необходимо вывести.
                                    while (have_posts()) : the_post();  // запускаем цикл обхода материалов блога
                                    ?>
                                    <div class="col-md-3 col-xs-12 post-wrapp">
                                        <div class=" post-one-bonus">
                                            <a href="<?php the_permalink(); ?>">
                                                <?php the_title(); ?>
                                                <?php echo get_the_post_thumbnail();?>
                                            </a>
                                            <?php the_content(); ?>
                                        </div>
                                    </div>

                                <?php endwhile;  // завершаем цикл.
                                endif;
                                /* Сбрасываем настройки цикла. Если ниже по коду будет идти еще один цикл, чтобы не было сбоя. */
                                wp_reset_query();
                                ?>
                            </div>
                            <div style="" class="accordions-head ui-accordion-header ui-state-default ui-corner-all ui-accordion-icons" role="tab" id="ui-id-3" aria-controls="ui-id-4" aria-selected="false" aria-expanded="false" tabindex="-1">
                                <span class="ui-accordion-header-icon ui-icon ui-icon-triangle-1-e"></span>
                                <i class="accordion-icons left accordion-plus fa fa-chevron-up"></i>
                                <i class="accordion-icons left accordion-minus fa fa-arrow-down fa-chevron-down"></i>
                                <span class="accordions-head-title">Бездепозитный бонус</span>
                            </div>
                            <div class="accordion-content ui-accordion-content ui-helper-reset ui-widget-content ui-corner-bottom" id="ui-id-4" aria-labelledby="ui-id-3" role="tabpanel" aria-hidden="true" style="display: none;">
                                <?php
                                if ( have_posts() ) : // если имеются записи в блоге.
                                    query_posts('cat=7');   // указываем ID рубрик, которые необходимо вывести.
                                    while (have_posts()) : the_post();  // запускаем цикл обхода материалов блога
                                        ?>
                                        <div class="col-md-3 col-xs-12 post-wrapp">
                                            <div class=" post-one-bonus">
                                                <a href="<?php the_permalink(); ?>">
                                                    <?php the_title(); ?>
                                                    <?php echo get_the_post_thumbnail();?>
                                                </a>
                                                <?php the_content(); ?>
                                            </div>
                                        </div>

                                    <?php endwhile;  // завершаем цикл.
                                endif;
                                /* Сбрасываем настройки цикла. Если ниже по коду будет идти еще один цикл, чтобы не было сбоя. */
                                wp_reset_query();
                                ?>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
<?php get_footer(); ?>