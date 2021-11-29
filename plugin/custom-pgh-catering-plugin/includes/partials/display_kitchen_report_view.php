<?php
    $selected = 1;

    if ( isset( $_GET['order_ids'] ) && ! empty( $_GET['order_ids'] ) ) {
        $selected = explode('-', $_GET['order_ids']);
    }

    $query_args = array(
        'post_type'      => wc_get_order_types(),
        'post_status'    => array_keys( wc_get_order_statuses() ),
        'posts_per_page' => 9999,
        'include'        => $selected
    );
    
    $all_orders          = get_posts( $query_args );

    $all_entrees         = array();
    $all_family          = array();
    $all_alacarte        = array();
    $all_others          = array();
    $all_nonmeals        = array();

    $entree_sides        = array();
    $family_sides        = array();

    $all_entrees_with_sides = array();
    $all_family_with_sides  = array();

    $total_entrees         = 0;
    $total_family          = 0;
    $total_meals           = 0; // entrees + family portions

    $product_id_counts   = array();
    $variation_id_counts = array();

    $total_orders    = count($all_orders);

    foreach ( $all_orders as $order ) {
        $order_id = $order->ID;
        $order    = wc_get_order( $order->ID );

        if ( ! is_object( $order ) ) {
            continue;
        }
    
        foreach( $order->get_items() as $item_id => $item_product ) {
            $product_id   = $item_product->get_product_id();
            $data         = $item_product->get_data();
            $qty          = $item_product->get_quantity();
            $variation_id = $data['variation_id'];

            if ( $variation_id === 0 ) { // ts a simple product, make 
                $variation_id = $product_id;
            }
        
            // create totals for all products (regardless of side)
            if ( ! array_key_exists($product_id, $product_id_counts) ) {
                $product_id_counts[$product_id] = $qty;
            } 
            else {
                $before = $product_id_counts[$product_id];
                $after  = intval($before) + $qty;
                $product_id_counts[$product_id] = $after;
            }

            // create totals for all variations (side-specific)
            if ( ! array_key_exists($variation_id, $variation_id_counts) ) {
                $variation_id_counts[$variation_id] = $qty;
            } 
            else {
                $before = $variation_id_counts[$variation_id];
                $after  = intval($before) + $qty;
                $variation_id_counts[$variation_id] = $after;
            }
        
            if ( has_term( 'single-entrees','product_cat', $product_id ) ) {
                // all parent entrees
                if ( ! array_key_exists($product_id, $all_entrees) ) {
                    $all_entrees[$product_id] = $item_product;
                }

                // all entree variations
                if ( ! array_key_exists($variation_id, $all_entrees_with_sides) ) {
                    $all_entrees_with_sides[$variation_id] = $item_product;
                }

                // all entree sides
                $product = wc_get_product( $variation_id );
                $attributes = $product->get_attributes();

                if ( is_array($attributes) && ! empty($attributes) ) {
                    foreach ($attributes as $taxonomy => $attrslug) {
                        if ( ! $attrslug || gettype($attrslug) !== 'string' ) {
                            continue;
                        }
                        // this should always be a loop of 1 iteration (1 side)
                        // NOTE: if there is more than one attribute, this is only selecting the last in the array
                        $term = get_term_by('slug', $attrslug, $taxonomy);
                        $term_name = $term->name;
                        $term_id   = $term->term_id;
                    }
                }

                if ( ! array_key_exists($term_name, $entree_sides) ) {
                    $entree_sides[$term_name]['total'] = $qty;
                    $entree_sides[$term_name]['id'] = $term_id;
                } 
                else {
                    $before = $entree_sides[$term_name]['total'];
                    $after = $before + $qty;
                    $entree_sides[$term_name]['total'] = $after;
                }
    
            } 
            else if ( has_term( 'family-portions', 'product_cat', $product_id ) ) {
                // all parent family portions
                if ( ! array_key_exists($product_id, $all_family) ) {
                    $all_family[$product_id] = $item_product;
                }

                // all family portion variations
                if ( ! array_key_exists($variation_id, $all_family_with_sides) ) {
                    $all_family_with_sides[$variation_id] = $item_product;
                }

                // all family sides
                $product = wc_get_product( $variation_id );
                $attributes = $product->get_attributes();
                if ( is_array($attributes) && ! empty($attributes) ) {
                    foreach ($attributes as $taxonomy => $attrslug) { 
                        if ( ! $attrslug || gettype($attrslug) !== 'string' ) {
                            continue;
                        }
                        // this should always be a loop of 1 iteration (1 side)
                        // NOTE: if there is more than one attribute, this is only selecting the last in the array
                        $term = get_term_by('slug', $attrslug, $taxonomy);
                        $term_name = $term->name;
                        $term_id   = $term->term_id;
                    }          
                }

                if ( ! array_key_exists($term_name, $family_sides) ) {
                    $family_sides[$term_name]['total'] = $qty;
                    $family_sides[$term_name]['id'] = $term_id;
                } 
                else {
                    $before = $family_sides[$term_name]['total'];
                    $after = $before + $qty;
                    $family_sides[$term_name]['total'] = $after;
                }
            } 
            else if ( has_term( 'a-la-carte', 'product_cat', $product_id ) ) {
                if ( ! array_key_exists($variation_id, $all_alacarte) ) {
                    $all_alacarte[$variation_id] = $item_product;
                }
            } 
            else if ( has_term( array( 'drinks', 'dessert', 'snacks', 'dressing'), 'product_cat', $product_id ) ) {
                if ( ! array_key_exists($variation_id, $all_others) ) {
                    $all_others[$variation_id] = $item_product;
                }
            } 
            else {
                if ( ! array_key_exists($variation_id, $all_nonmeals) ) {
                    $all_nonmeals[$variation_id] = $item_product;
                }
          }
        }
    }

    $tables = array(
        array( $all_entrees, 'Entree' ), 
        array( $entree_sides, 'Entree Sides' ), 
        array( $all_entrees_with_sides, 'Entree + Side Combinations' ),
        array( $all_family, 'Family Portion' ),
        array( $family_sides, 'Family Portion Sides' ), 
        array( $all_family_with_sides, 'Family Portion + Side Combinations' ),
        array( $all_alacarte, 'A la carte' ),
        array( $all_others, 'Extra' )
    );

    sort_items_in_table( $tables );
    calculate_total_meals_of_entrees( $tables, $product_id_counts, $total_entrees, $total_family );
    
    function calculate_total_meals_of_entrees($tables, $product_id_counts, &$total_entrees, &$total_family) {
        foreach ($tables as $key => $items_table) {
            if ( $items_table[1] === 'Entree Sides' || $items_table[1] === 'Family Portion Sides' ) {
                continue;
            }

            $items = $items_table[0];
            $label = $items_table[1];

            if ( $label === 'Entree' || $label === 'Family Portion' ) { // this is Entrees/Family without Sides
                foreach ($items as $item_id => $item_product) {
                    $product_id = $item_product->get_product_id();
                    $total_meals = $total_meals + $product_id_counts[$product_id];

                    if ( $label === 'Entree' ) {
                        $total_entrees = $total_entrees + $product_id_counts[$product_id];
                    } 
                    else if ( $label === 'Family Portion' ) {
                        $total_family = $total_family + $product_id_counts[$product_id];
                    }
                }
            }
        }
    }

    function sort_items_in_table($tables) {
        foreach ($tables as $key => $items_table) {
            if ( $items_table[1] === 'Entree Sides' || $items_table[1] === 'Family Portion Sides' ) {
                continue;
            }
    
            $items        = $items_table[0];
            $items_by_sku = array();
            $items_by_name= array();
            $items_redux  = array();
    
            foreach ($items as $item_id => $item_product) {
                $items_by_sku[$item_id] = $item_product;
            }
    
            ksort($items_by_sku);
    
            // then lets sort it by product name to keep entrees grouped together
            foreach ($items_by_sku as $item_id => $item_product) {
                $item_data    = $item_product->get_data();
                $item_title   = $item_data['name'];
    
                if ( strpos($item_title, '- No') !== false ) {
                    $item_title = explode('- No', $item_title);
                    $item_title = $item_title[0] . '- Ao' . $item_title[1];
                }
                // note we need title AND id here because titles are not unique (is this true in kitchen report? code taken from preference report)
                $items_by_name[$item_title.$item_id] = $item_product;
            }
        
            ksort($items_by_name);
    
            foreach ($items_by_name as $sku => $item_product) {
                $items_redux[] = $item_product;
            }
    
            $tables[$key][0] = $items_redux;
        }
    }

    function get_portionsize_data( $product_id, $product_qty ) {
        if ( get_post_meta( $product_id, 'pghf_portionsize', true ) ) {
            $portion_size = get_post_meta( $product_id, 'pghf_portionsize', true );
            $portion_size = trim($portion_size);
        } 
        else {
            $portion_size = '1';
        }
      
        if ( strpos($portion_size, '/') === false ) {
            $portion_qty  = str_replace(array('+','-'), '', $portion_size);
            $portion_qty  = (int) filter_var($portion_qty, FILTER_SANITIZE_NUMBER_INT);
        } 
        else { // houston, we have a rational number...
          // remove anything thats not 0-9 or /
          $portion_qtys = explode('/', $portion_size);
          $numerator    = (int) filter_var( $portion_qtys[0], FILTER_SANITIZE_NUMBER_INT );
          $denominator  = (int) filter_var( $portion_qtys[1], FILTER_SANITIZE_NUMBER_INT );
          $portion_qty  = $numerator / $denominator;
          $portion_qty  = round( $portion_qty, 4 );
        }

        $total_amount = intval($product_qty) * floatval($portion_qty);
        $portion_words  = preg_replace("/[^A-Za-z]/", '', $portion_size);

        return array(
            'quantity' => $total_amount,
            'measure'  => $portion_words
        );
    }
?>
<div class="report-intro-wrapper">
    <div>
        <strong>Report:</strong> <em>Kitchen Report</em>
    <div>
    <div>
        <strong>Total Orders:</strong> <?php echo $total_orders ?>
    </div>
    <div>
        <strong>Total Entrees:</strong> <?php echo $total_entrees ?>
    </div>  

    <?php if ( $total_family > 0 ) : ?>
        <div>
            <strong>Total Family Portions:</strong>  <?php echo $total_family ?>
        </div>
    <?php endif ?>
</div>

<?php if ( $total_orders == 0 ) : ?> 
    <h2 style="text-align:center">No orders selected for this report.</h2>
<?php else : ?>
    <?php
    foreach ($tables as $key => $items_table) :
        $items = $items_table[0];
        $label = $items_table[1];

        if ( count($items) === 0 ) {
            continue;
        }

        $grand_total = 0;
    ?>
    <table class="report-table">
        <thead>
            <tr>
                <td class="col-sku">ID</td>
                <td class="col-item"><?php echo $label ?></td>
                <td class="col-total">Qty</td>
                <td class="col-total">Est.</td>
                <td class="col-total">Portion</td>
                <td class="col-total">Total</td>
            </tr>
        </thead>
        <?php if ( $label === 'Entree Sides' || $label === 'Family Portion Sides' ) : ?>
            <?php foreach ($items as $side_name => $side_data) : ?>
            <tr>
                <td><?php echo $side_data['id'] ?></td>
                <td><strong><?php echo $side_name ?></strong></td>
                <td class="col-total"><?php echo $side_data['total'] ?></td>
                <td class="col-total">-</td>
                <td class="col-total">-</td>
                <td class="col-total"><?php echo $side_data['total'] ?></td>
            </tr>
            <?php 
                $grand_total = $grand_total + $side_data['total'];
                endforeach 
            ?>
        <?php else : ?>
            <?php 
                foreach ($items as $item_id => $item_product) :
                    $product_id = $item_product->get_product_id();
                    $product = $item_product->get_product();
                    $sku     = $product->get_sku();
                    $data    = $item_product->get_data();
                    $variation_id = $data['variation_id'];
                    $projected = '';

                    if ( $variation_id === 0 ) { // its a simple product, make v_id same as p_id
                        $variation_id = $product_id;
                    }

                    if ( get_post_meta( $variation_id, 'pghf_portionsize', true ) ) {
                        $portion = get_post_meta( $variation_id, 'pghf_portionsize', true );
                    } else {
                        $portion = '';
                    }

                    if ( $label === 'Entree' || $label === 'Family Portion' ) { // this is Entrees/Family without Sides
                        $name = $product->get_title();
                        $total = $product_id_counts[$product_id];
                        $portion_size_array = get_portionsize_data( $product_id, $product_id_counts[$product_id] );
                        $grand_total = $grand_total + $product_id_counts[$product_id];
                    } 
                    else {
                        $name = $data['name'];
                        $total = $variation_id_counts[$variation_id];
                        $portion_size_array = get_portionsize_data( $variation_id, $variation_id_counts[$variation_id] );
                        $grand_total = $grand_total + $variation_id_counts[$variation_id];
                    }

                    if ( strpos($label, 'Combinations') !== false ) {
                        $best_id_url = sprintf( '<a href="%s%s%d%s" target="_blank">%d</a>', home_url(), '/wp-admin/post.php?post=', $product_id, '&action=edit', $variation_id );
                    } else {
                        $best_id_url = sprintf( '<a href="%s%s%d%s" target="_blank">%d</a>', home_url(), '/wp-admin/post.php?post=', $product_id, '&action=edit', $product_id );
                    } 
            ?>
            <tr>
                <td><?php echo $best_id_url ?></td>
                <td><strong><?php echo $name ?></strong></td>
                <td class="col-total"><?php echo $total ?></td>
                <td class="col-total"><?php echo $projected ?></td>
                <td class="col-total"><?php echo $portion ?></td>
                <td class="col-total"><?php echo $portion_size_array['quantity'] . ' ' . $portion_size_array['measure'] ?></td>
            </tr>

            <?php endforeach ?>
        <?php endif ?>
        <tbody>
                
        </tbody>

        <tfoot>
            <tr>
                <td> — </td>
                <td> — </td>
                <td class="col-total"> <?php echo $grand_total ?> </td>
                <td class="col-total"> — </td>
                <td class="col-total"> — </td>
                <td class="col-total"> — </td>
            </tr>
        </tfoot>
    </table>
    <?php endforeach ?>
<?php endif ?>