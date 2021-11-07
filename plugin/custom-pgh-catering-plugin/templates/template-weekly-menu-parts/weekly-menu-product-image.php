<?php
    if ( get_post_thumbnail_id( $product->get_id() ) ) {
        $image_src  = wp_get_attachment_image_src( get_post_thumbnail_id( $pid ), 'menu_thumbnail' );
        $overlay    = '';
    } 
    else if ( get_field('menu_item_placeholder_image','option') ) {
        $attachment_id = get_field('menu_item_placeholder_image','option');
        $image_src     = wp_get_attachment_image_src( $attachment_id, 'menu_thumbnail' );
        $overlay       = '<div class="placeholder-image-overlay">image<br>coming<br>soon</div>';
    } 
    else {
        $image_src  = array(wc_placeholder_img_src());
        $overlay    = '<div class="placeholder-image-overlay">image<br>coming<br>soon</div>';
    }
?>

<a href="#" class="title-popup-link" data-modal_id="<?= $pid . '-' . $menu_day; ?>">
    <div class="product-card-image-wrapper">
        <div class="product-card-image">
            <img src="<?= $image_src[0] ?>" alt="<?= $title ?>" />
            <?= $overlay ?>
        </div>

        <div id="overlay-<?= $pid . '-' . $menu_day; ?>" class="product-card-image-overlay <?= $in_cart_class ?>">
            <div class="qty-in-cart"><?= $total_qty_in_cart ?></div>
        </div>
    </div>
</a>