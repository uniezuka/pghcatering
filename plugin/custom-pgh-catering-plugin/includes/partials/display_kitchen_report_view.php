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
    $total_orders    = count($all_orders);

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
            $menu_day     = $item_product->get_meta('_pgh_menu_day');

            if ( $variation_id === 0 ) { // ts a simple product, make 
                $variation_id = $product_id;
            }

            // NOTE might be need. Stick around for a while
            // // create totals for all products (regardless of side)
            // $product_id_item_key = get_item_key($menu_day, $product_id, $product_id_counts);
            // if ( $product_id_item_key === false ) {
            //     $product_id_counts[] = array(
            //         'product_id' => $product_id,
            //         'menu_day' => $menu_day,
            //         'qty' => $qty
            //     );
            // }
            // else {
            //     $before = $product_id_counts[$product_id_item_key]['qty'];
            //     $after  = intval($before) + $qty;
            //     $product_id_counts[$product_id_item_key]['qty'] = $after;
            // }

            // // create totals for all variations (side-specific)
            // $variation_id_item_key = get_item_key($menu_day, $variation_id, $variation_id_counts);
            // if  ( $variation_id_item_key === false ) {
            //     $variation_id_counts[] = array(
            //         'variation_id' => $variation_id,
            //         'menu_day' => $menu_day,
            //         'qty' => $qty
            //     );
            // } 
            // else {
            //     $before = $variation_id_counts[$variation_id_item_key]['qty'];
            //     $after  = intval($before) + $qty;
            //     $variation_id_counts[$variation_id_item_key]['qty'] = $after;
            // }

            if ( has_term( 'single-entrees','product_cat', $product_id ) ) {
                // all parent entrees
                $item_key = get_item_key($menu_day, $product_id, $all_entrees);
                if ( $item_key === false ) {
                    $all_entrees[] = array(
                        'product_id' => $product_id,
                        'menu_day' => $menu_day,
                        'product' => $item_product,
                        'qty' => $qty
                    );
                }
                else {
                    $before = $all_entrees[$item_key]['qty'];
                    $after  = intval($before) + $qty;
                    $all_entrees[$item_key]['qty'] = $after;
                }

                // all entree variations
                $item_key = get_item_key($menu_day, $variation_id, $all_entrees_with_sides, 'variation_id');
                if  ( $item_key === false ) {
                    $all_entrees_with_sides[] = array(
                        'variation_id' => $variation_id,
                        'menu_day' => $menu_day,
                        'product' => $item_product,
                        'qty' => $qty
                    );
                } 
                else {
                    $before = $all_entrees_with_sides[$item_key]['qty'];
                    $after  = intval($before) + $qty;
                    $all_entrees_with_sides[$item_key]['qty'] = $after;
                }

                $total_entrees += $qty;
            }

            else if ( has_term( 'family-portions', 'product_cat', $product_id ) ) {
                // all parent family portions
                $item_key = get_item_key($menu_day, $product_id, $all_family);
                if ( $item_key === false ) {
                    $all_family[] = array(
                        'product_id' => $product_id,
                        'menu_day' => $menu_day,
                        'product' => $item_product,
                        'qty' => $qty
                    );
                }
                else {
                    $before = $all_family[$item_key]['qty'];
                    $after  = intval($before) + $qty;
                    $all_family[$item_key]['qty'] = $after;
                }

                // all family portion variations
                $item_key = get_item_key($menu_day, $variation_id, $all_family_with_sides, 'variation_id');
                if ( $item_key === false ) {
                    $all_family_with_sides[] = array(
                        'variation_id' => $product_id,
                        'menu_day' => $menu_day,
                        'product' => $item_product,
                        'qty' => $qty
                    );
                }
                else {
                    $before = $all_family_with_sides[$item_key]['qty'];
                    $after  = intval($before) + $qty;
                    $all_family_with_sides[$item_key]['qty'] = $after;
                }
            } 
        }
    }

    function get_item_key($menu_day, $id, $array_to_search, $field_lookup = 'product_id') {
        foreach( $array_to_search as $key => $item ) {        
            if ($item[$field_lookup] == $id &&
                $item['menu_day'] == $menu_day) {

                return $key;
            }
        }

        return false;
    }

    $day_table_map =  [
        "monday" => "Monday",
        "tuesday" => "Tuesday",
        "wednesday" => "Wednesday",
        "thursday" => "Thursday",
        "friday" => "Friday"
    ];

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
    
?>

<style>
    #table-scroll .table-wrap {
        overflow: auto;
        margin-bottom: 20px;
    }

    #table-scroll .day-header {
        text-align: center;
        border: 1px solid black; 
    }

    #table-scroll .table-wrap thead td:first-child{
        width: 30%;
    }

    #table-scroll .report-table thead th:first-child {
        position: sticky;
        left: 0;
    }

    #table-scroll .report tbody th {
        position: sticky;
        left: 0;
        z-index: 1;
    }
</style>

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

    <?php if ( $total_orders == 0 ) : ?> 
        <h2 style="text-align:center">No orders selected for this report.</h2>
    <?php else : ?>
        <div id="table-scroll" class="table-scroll">
            <div class="table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <td colspan="2" class="fixed-side" scope="col"></td>
                            <td class="day-header">Monday</td>
                            <td class="day-header">Tuesday</td>
                            <td class="day-header">Wednesday</td>
                            <td class="day-header">Thursday</td>
                            <td class="day-header">Friday</td>
                        <tr>
                    </thead>
                        
                    <tbody>
                    <?php 
                        foreach ($tables as $key => $items_table) : 
                            $items = $items_table[0];
                            $label = $items_table[1];

                            if ( count($items) === 0 ) {
                                continue;
                            }
                    ?>
                        <tr>
                            <th>Id</th>
                            <th><?php echo $label ?></th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                            <th>Qty</th>
                        </tr>
                        <?php 
                            foreach ($items as $item_id => $item_product) :
                                $item_product_record = $item_product['product'];
                                $product_id = $item_product_record->get_product_id();
                                $data    = $item_product_record->get_data();
                                $product = $item_product_record->get_product();
                                $variation_id = $data['variation_id'];

                                if ( $variation_id === 0 ) { // its a simple product, make v_id same as p_id
                                    $variation_id = $product_id;
                                }

                                if ( $label === 'Entree' || $label === 'Family Portion' ) { // this is Entrees/Family without Sides
                                    $name = $product->get_title();
                                    $total = $item_product['qty'];
                                } 
                                else {
                                    $name = $data['name'];
                                    $total = $item_product['qty'];
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
                            
                            <?php 
                                foreach ($day_table_map as $day_key => $day_value) : 
                                    $qty = ($day_key == $item_product['menu_day']) ? 
                                        $item_product['qty'] : '';
                                    
                            ?>
                            <td>
                                <?php echo $qty ?>
                            </td>
                            <?php 
                                endforeach
                            ?>
                        </tr>

                        <?php endforeach ?>
                    <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif ?>
</div>