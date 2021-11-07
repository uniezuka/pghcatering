<?php
    // if ( $product->get_type() === 'simple' ) {
    //     $base_price = $product->get_regular_price();
    // } 
    // else if ( $product->get_type() === 'variable' ) {
    //     $available_variations = $product->get_available_variations();
    //     $base_price = $available_variations[0]['display_regular_price'];
    // } 
    // else if ( $product->get_type() === 'bundle' ) {
    //     $base_price = (float) 1.99;
    // }

    // $terms = get_the_terms($product->get_id(), 'product_cat');
    // $is_excluded = false;
    // $user_has_active_subscription = wcs_user_has_subscription( '', '', 'active');
    // $user_has_on_hold_subscription = wcs_user_has_subscription( '', '', 'on-hold');
?>
<div class="item-price <?= $location ?>">
    <?= $html ?>
</div>