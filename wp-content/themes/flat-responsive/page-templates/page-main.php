<?php
/**
 *
Template Name: Main template
 *
 *
 */

get_header(); ?>


<?php get_sidebar( 'top' ); ?>
<?php get_sidebar( 'inset-top' ); ?>
    <section id="fr-content-area" class="fr-contents" role="main">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="wrap1 flex2">
                        <div class="sect-side img-back col-md-6 col-sm-12">
                            <div class="illust-side video-for-mac1">
                                <div class="video-block">
                                    <div id="ytplayer"></div>
                                    <iframe id="autoplay-video" width="100%" height="100%" src="https://www.youtube.com/embed/LMmKLzkKTe8" frameborder="0" allowfullscreen></iframe>
                                </div>
                            </div>
                            <img src="wp-content/themes/flat-responsive/images/mac1.png" alt="mac1">
                        </div>
                        <div class="sect-side col-md-6 col-sm-12">
                            <h2>НЕМНОГО ОБО МНЕ</h2>
                            <p class="text-about">
                                <?php
                                while ( have_posts() ) : the_post(); ?>
                                    <?php get_template_part( 'content', 'page' ); ?>
                                <?php endwhile; // end of the loop. ?>
                            </p>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="features">
        <div class="container">
            <div class="row">
                <div class="bonus-title">
                    <img src="wp-content/themes/flat-responsive/images/title-block.png" alt="">
                </div>
                <article class="action-post col-md-4 col-sm-6 col-xs-12">
                    <div class="padding" data-mh="offers">
                        <img src="wp-content/themes/flat-responsive/images/casinox-300x140.png">
                        <div class="desc">
                            <p>Бонус
                                <strong> 200%</strong> до
                                <strong> 100 000 рублей</strong>
                                <br> + <strong>200</strong> бесплатных спинов в игре
                                <strong>STARBURST</strong><br>
                                <em style="color: red;">Бонусы дают при депозите от 1000р.</em>
                            </p>
                        </div>
                        <a href="https://goo.gl/f6Rbi4" target="_blank" class="playbtn">Играть</a>
                    </div>
                </article>
                <article class="action-post col-md-4 col-sm-6 col-xs-12">
                    <div class="padding" data-mh="offers">
                        <img src="wp-content/themes/flat-responsive/images/buran.png"">
                        <div class="desc">
                            <p><strong>30 БЕСПЛАТНЫХ</strong> спинов в&nbsp;игре Butterfly Staxx за регистрацию и <strong>100% на депозит</strong></p>
                        </div>
                        <a href="https://goo.gl/y53ZDp" target="_blank" class="playbtn">Играть</a>
                    </div>
                </article>
                <article class="action-post col-md-4 col-sm-6 col-xs-12">
                    <div class="padding" data-mh="offers">
                        <img src="wp-content/themes/flat-responsive/images/joycasino-300x140.png">
                        <div class="desc"><p>Бонус <strong>200%</strong> до <strong>100 000 рублей</strong>
                                <br> + <strong>200</strong> бесплатных спинов в игре <strong>Vikings Go Berzerk</strong><br>
                                <em style="color: red;">Бонусы дают при депозите от 1000р.</em></p></div>
                        <a href="https://goo.gl/b2mWMp" target="_blank" class="playbtn">Играть</a>
                    </div>
                </article>
                <article class="action-post col-md-4 col-sm-6 col-xs-12">
                    <div class="padding" data-mh="offers">
                        <img src="wp-content/themes/flat-responsive/images/cadoola-logo-150.png">
                        <div class="desc">
                            <p><strong>100% на депозит</strong> и ежедневный <strong>cashback</strong></p>
                        </div>
                        <a href="https://goo.gl/q8H1KM" target="_blank" class="playbtn">Играть</a>
                    </div>
                </article>
                <article class="action-post col-md-4 col-sm-6 col-xs-12">
                    <div class="padding" data-mh="offers">
                        <img src="wp-content/themes/flat-responsive/images/redpingwin.jpg">
                        <div class="desc"><p>Бонус <strong>200 euro на депозит</strong> + <strong>100 спинов</strong></p>
                            <p>Бонус-код активируется только при регистрации по ссылке</p>
                        </div>
                        <a href="https://goo.gl/oKpEFz" target="_blank" class="playbtn">Играть</a>
                    </div>
                </article>
                <article class="action-post col-md-4 col-sm-6 col-xs-12">
                    <div class="padding" data-mh="offers">
                        <img src="wp-content/themes/flat-responsive/images/yoyo.png">
                        <div class="desc"><p><strong>Бонус до 30000 рублей</strong> на депозит + <strong>175 фри спинов</strong></p>
                            <p>Бонус-код активируется только при регистрации по ссылке</p>
                        </div>
                        <a href="https://goo.gl/zAJvsa" target="_blank" class="playbtn">Играть</a>
                    </div>
                </article>
            </div>
        </div>
    </section>
    <section id="last-video">
        <div class="container">
            <h2>Последние заносы</h2>
            <?php echo do_shortcode('[slick-carousel-slider design="design-6" sliderheight="200"]'); ?>
        </div>
    </section>
<?php get_footer(); ?>