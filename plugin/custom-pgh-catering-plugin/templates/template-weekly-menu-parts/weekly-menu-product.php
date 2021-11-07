<?php 
    $product = wc_get_product( $product_id ); 
    $counter_class = ($counter % 2 == 0) ? 'even' : 'odd';
    $pid = $product->get_id();
    $slug = $product->get_slug();
    $title = $product->get_title();
    $subtitle = get_post_meta( $pid, 'product_subtitle', true );
    $html = $product->get_price_html();
    $description = get_post_field('post_content', $pid);

    $portionsize = get_default_value_for_non_existing( get_post_meta($pid, 'pghf_portionsize', true ), '' );

    if ( $portionsize != '' ) {
        $portionsize = ' <h6 class="right"><small class="portionsize">(' .$portionsize . ')</small></h6>';
    } 

    $total_qty_in_cart = calculate_total_qty_in_cart_for_this($pid, $current_cart, $menu_day);
    $in_cart_class = ( $total_qty_in_cart > 0 ) ? 'found-in-cart' : 'not-in-cart';

    $options_label = ( $product->get_type() === 'variable' ) ?
        __('Side Options', CUSTOM_PGH_CATERING_DOMAIN_NAME ) : __('Options', CUSTOM_PGH_CATERING_DOMAIN_NAME );
?>

<div class="product-card is-<?= $counter_class ?> flex-columns-<?= $columns ?>">
    <div class="product-card-wrapper">
        <div class="product-card-inner">

            <?php include 'weekly-menu-product-image.php'; ?>

            <div class="product-card-details-wrapper">
                <div class="product-card-details">
                    <div class="product-card-details-mp-price flex-row">
                        <?php
                            $terms = get_the_terms( $product->get_id(), 'meal_preferences' );

                            include 'weekly-menu-product-meal-preference.php';
                            include 'weekly-menu-product-price.php';
                        ?>
                    </div>

                    <a href="#" class="title-popup-link" data-modal_id="<?= $pid . '-' . $menu_day; ?>">
                        <h4><?= $title ?> <small class="small-info"><i class="far fa-info-circleOFF" style="font-size:12px">INFO</i></small></h4>
                        <div class="subtitle">
                            <h6 class="left"><?= $subtitle ?> </h6><?= $portionsize ?>
                        </div>
                    </a>

                    <div class="adjust-separator"><span><?= $options_label ?></span><?= __( 'Quantity', CUSTOM_PGH_CATERING_DOMAIN_NAME ) ?></div>
                    
                    <div id="product-addtocart-<?= $pid ?>" class="product-card-addtocart">
                        <?php include 'weekly-menu-product-add-to-cart.php'; ?>
                        <?php include 'weekly-menu-product-title-modal.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>