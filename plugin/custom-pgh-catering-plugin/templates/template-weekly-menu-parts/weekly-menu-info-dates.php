<?php
    $color_value1 = '#ff8c00';
    $color_value2 = '#ffffff';

    $feat_query = new WC_Product_Query( array(
        'limit'     => 1,
        'status'    => 'publish',
        'featured'  => true,
        'nogo'      => true,
        'include'   => $allowed_ids,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => 'single-entrees'
            ),
        ),
    ) );

    $image_src = '';
    $title     = '';

    $featured_products = $feat_query->get_products();

    foreach ($featured_products as $product) {
        $title      = $product->get_title();
        $image_src  = wp_get_attachment_url( get_post_thumbnail_id( $product->get_id() ) );
    }

    if ( $image_src === '' ) {
        $upload_dir  = wp_upload_dir();
        $uploads     = untrailingslashit( $upload_dir['baseurl'] );
        $image_src   = $uploads . '/2018/08/PGH-Fresh-Icon.png';
        $background_size = 'contain';
        $title       = __('Pittsburgh Fresh', 'pgh-fresh');

    } else {
        $background_size = 'cover';
    }

    wp_reset_postdata();
?>

<?php if ($image_src): ?>
<style>
    #menu-info-left {
        background-color: <?= $color_value1 ?>;
        background-image: url(<?= $image_src ?>);
        background-position: center center;
        background-size: <?= $background_size ?>;
        background-repeat: no-repeat;
        background-attachment: scroll;
    }
</style>
<?php endif ?>

<div class="text-center full-width menu-info-dates-container">
    <div id="menu-info-dates" class="">
        <div class="menu-info-wrapper">
            <div id="menu-info-left" class="menu-info-left" title="<?= $title ?>" style="background-color:<?= $color_value2 ?>"></div>
            <div id="menu-info-right" class="menu-info-right" style="background-color:<?= $color_value1 ?>">
                <div id="menu-info-right-content" class="menu-info-right-content">
                    <h1 id="current-menu-title"><?= $pagetitle ?></h1>
                    <div id="delivery">
                        <h4><?= __( 'DELIVERY:', CUSTOM_PGH_CATERING_DOMAIN_NAME ) ?></h4>
                        <div class="menu-info-datebox">
                            <?= $delivery_date_message ?>
                        </div>
                    </div>
                    <div id="available">
                        <h4><?= __( 'MENU AVAILABLE UNTIL:', CUSTOM_PGH_CATERING_DOMAIN_NAME ) ?></h4>
                        <div class="menu-info-datebox"><?= $menu_available_until ?></div>
                    </div>
                    <div id="disclaimer">
                        <h6><?= __( 'Some exceptions apply. See order details.', CUSTOM_PGH_CATERING_DOMAIN_NAME ) ?></h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="text-center full-width below-menu-banner">
    <?php if ( is_active_sidebar( 'below-menu-banner' ) ) : ?>
        <?php dynamic_sidebar( 'below-menu-banner' ); ?>
    <?php endif; ?>
</div>