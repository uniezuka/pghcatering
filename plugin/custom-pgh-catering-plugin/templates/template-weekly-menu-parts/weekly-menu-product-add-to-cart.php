<?php 
    $addtocart_options = array();

    $is_gift_card = false;

    foreach( wp_get_post_terms( $pid, 'product_cat' ) as $term ){
        if( $term &&  $term->slug == 'gift-card'){
            $is_gift_card = true;
            break;
        }
    }

    if( $product->is_type( 'variable' ) ) { 

        $product_type = 'variation';
        $variations = $product->get_available_variations();

        foreach ($variations as $key => $variation) {
            $variation_id    = $variation['variation_id'];
            
            // if ( ! in_array($variation_id, $allowed_variations) ) {
            //     continue;
            // }

            $current_quantity= 0;
            $variation_slug  = $variation['attributes']['attribute_pa_side-options'];
            $variation_term  = get_term_by( 'slug', $variation_slug, 'pa_side-options' );

            $variation_title = $is_gift_card ? 
                $variation['attributes']['attribute_gift-card-amount'] : 
                $variation_term->name;

            $variation_array = $variation['attributes'];

            foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
                $cart_product_type = $values['data']->get_type();
                $quantity          = $values['quantity'];

                if ( $cart_product_type === 'variation' ) {
                    $cart_product_id = $values['data']->get_id();

                    if(array_key_exists('menu_day', $values)) {
                        if ($values['menu_day'] === $menu_day) {
                            if( $variation_id === $cart_product_id ) {
                                $current_quantity = $quantity;
                            }
                        }
                    }
                }
            }

            $addtocart_options['current_quantity'] = $current_quantity;
            $addtocart_options['variation_id'] = $variation_id;
            $addtocart_options['variation_slug'] = $variation_slug;
            $addtocart_options['variation_title'] = $variation_title;
            $addtocart_options['variation_array'] = $variation_array;
            $addtocart_options['is_gift_card'] = $is_gift_card;
            $addtocart_options['menu_day'] = $menu_day;

            display_add_to_cart($product, $addtocart_options, $menu_day);
        }
    } 
    else if ( $product->is_type('simple') ) {
        $current_quantity = 0;
        $product_type    = 'simple';
        $variation_id    = $pid;
        $variation_slug  = __( 'more-info', CUSTOM_PGH_CATERING_DOMAIN_NAME ); // this is unused for simple products on menu page
        $variation_title = __( 'More info', CUSTOM_PGH_CATERING_DOMAIN_NAME );
        $variation_array = array();
        $can_show_cart = true;

        if ( has_term( 'a-la-carte', 'product_cat', $pid ) ) {
            $variation_slug  = __(' a-la-carte', CUSTOM_PGH_CATERING_DOMAIN_NAME ); // this is unused for simple products on menu page
            $variation_title = __( 'À la carte', CUSTOM_PGH_CATERING_DOMAIN_NAME );
        }

        foreach( WC()->cart->get_cart() as $cart_item_key => $values ) {
            $cart_product_type = $values['data']->get_type();
            $quantity          = $values['quantity'];

            if ( $cart_product_type === 'simple' ) {
                $cart_product_id = $values['data']->get_id();
                
                if(array_key_exists('menu_day', $values)) {
                    if ($values['menu_day'] === $menu_day) {
                        if( $pid === $cart_product_id ) {
                            $current_quantity = $quantity;
                        }
                    }
                }
            }
        }

        if ( $current_quantity > 0 ) {
            $variation_slug  = 'added-to-cart';
            $variation_title = 'Added To Cart ';
        }

        $addtocart_options['current_quantity'] = $current_quantity;
        $addtocart_options['variation_id'] = $variation_id;
        $addtocart_options['variation_slug'] = $variation_slug;
        $addtocart_options['variation_title'] = $variation_title;
        $addtocart_options['variation_array'] = $variation_array;
        $addtocart_options['is_gift_card'] = $is_gift_card;

        display_add_to_cart($product, $addtocart_options, $menu_day);
    } 
    else {
        echo 'neither';
    }
?>