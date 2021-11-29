<?php
    $all_entrees         = array();
    $all_family          = array();
    $all_alacarte        = array();
    $all_extras          = array();
    $all_nonmeals        = array();
  
    $total_entrees         = 0;
    $total_family          = 0;
    $total_meals           = 0; // entrees + family portions

    $total_qty = 0;
?>

<?php if ( ! is_object( $order ) ) : ?>
    <div class="order-line-item error">This order returned an error. Contact admin.</div>
<?php 
    else : 
        foreach( $order->get_items() as $item_id => $item_product ) {
            $product_id   = $item_product->get_product_id();
        
            if (has_term('gift-card', 'product_cat', $product_id)) 
                continue;
        
            $data         = $item_product->get_data();
            $variation_id = $data['variation_id'];

            if ( $variation_id === 0 ) { // ts a simple product, make same as product id
                $variation_id = $product_id;
                $term_name = '-';
                $term_id   = 1;
            } 
            else { // its a variable product
                $product = wc_get_product( $variation_id );
                $attributes = $product->get_attributes();
            
                if ( is_array($attributes) && ! empty($attributes)) {
                    foreach ($attributes as $taxonomy => $attrslug) {
                        if ( gettype($attrslug) !== 'string' ) {
                            continue;
                        }

                        // this should always be a loop of 1 iteration (1 side)
                        // NOTE: if there is more than one attribute, this is only selecting the last in the array
                        $term = get_term_by('slug', $attrslug, $taxonomy);

                        if ( gettype($term) !== 'object' ) {
                            $term_name = '-';
                            $term_id   = 1;
                        } 
                        else {
                            $term_name = $term->name;
                            $term_id   = $term->term_id;
                        }
                    }
                } 
                else {
                    $term_name = '-';
                    $term_id   = 1;
                }
            }

            $parent_title = get_post_field('post_title',$product_id);
            $name         = sprintf( '<div class="oli-name">%s</div>', $parent_title );
            $side         = sprintf( '<div class="oli-side">%s</div>', $term_name );
            $qty          = sprintf( '<div class="oli-qty">%s</div>', $item_product->get_quantity() );
            $total_qty += $item_product->get_quantity();
        
            $item_array = array(
                'product_id'   => $product_id,
                'variation_id' => $variation_id,
                'side_name'    => $term_name,
                'output'       => sprintf('<div class="order-line-item">%s %s %s</div>',$name,$side,$qty)
            );
        
            if (has_term('single-entrees', 'product_cat', $product_id))
                $all_entrees[]  = $item_array;
        
            else if (has_term('family-portions', 'product_cat', $product_id))
                $all_family[]   = $item_array;
        
            else if (has_term('a-la-carte', 'product_cat', $product_id))
                $all_alacarte[] = $item_array;
            
            else if (has_term(array('drinks', 'dessert', 'snacks', 'dressing'), 'product_cat', $product_id))
                $all_extras[]   = $item_array;
        
            else if (!has_term('gift-card', 'product_cat', $product_id))
                    $all_nonmeals[] = $item_array;
        }

        $sections = array(
            array($all_entrees,'Entrees'), 
            array($all_family,'Family Portions'),
            array($all_alacarte,'A la carte'),
            array($all_extras,'Extras'),
            array($all_nonmeals,'Other Items')
        );

        foreach ($sections as $section_key => $section) {
            $items = $section[0];
            $label = $section[1];

            if ( count($items) === 0 ) {
                continue;
            }

            $side_label = ( $label === 'Entrees' || $label === 'Family Portions' )
                ? 'SIDE': '-';

            $add_sep = false;
            $current_item_id = 0;
?>
<div class="order-line-item oli-header">
    <div class="oli-name">
        <strong><?= $label ?></strong>
    </div>
    <div class="oli-side">
        <strong><?= $side_label ?></strong>
    </div>
    <div class="oli-qty">
        <strong>QTY</strong>
    </div>
</div>
<?php    
            foreach ($items as $item_key => $item_array) {
                if ( $item_key > 0 ) {
                    $prev_key = $item_key - 1;

                    if ( $items[$prev_key]['product_id'] === $item_array['product_id'] ) {
                        $current_item_id = $item_array['product_id']; // we know theres at least 2 of this entree parent
                    }
                }
                
                echo $item_array['output'];
            }
        }
?>

<div class="order-line-item oli-footer">
    <div class="oli-name">
        <strong>TOTAL:</strong>
    </div>
    <div class="oli-side">
        <strong>-</strong>
    </div>
    <div class="oli-qty">
        <strong><?= $total_qty ?></strong>
    </div>
</div>
<?php endif ?>