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
    $all_local_pickup    = array();
    $all_pickup_locations = array();

    foreach ( $all_orders as $key => $order ) {
        if ( ! is_object( $order ) ) {
            continue;
        }

        $order_id   = $order->ID;
        $order      = wc_get_order( $order_id );
        $order_meta = get_post_meta($order_id);

        // double check for refunds/subscriptions
        if ( $order->get_type() === 'shop_order_refund' || $order->get_type() === 'shop_subscription' ) {
            continue;
        }

        $date       = $order->get_date_created();
        $timestamp  = $date->getTimestamp();
        $datestring = $date->date('Y-m-d');

        $shipping_method = $order->get_shipping_method();

        if ( strtolower($shipping_method) === 'pickup store' ||
            strtolower($shipping_method) === 'delivery location' ) {

            $store_name = get_post_meta($order_id, '_shipping_pickup_stores', true);
            $store = get_page_by_title($store_name, OBJECT, 'store');

            if ( ! in_array($store_name, $all_pickup_locations) ) {
                array_push($all_pickup_locations, $store_name);
            }

            $all_local_pickup[] = array(
                'type'      => 'Local Pickup',
                'timestamp' => $timestamp,
                'datestring'=> $datestring,
                'location'  => $store_name,
                'order_id'  => $order_id,
                'alert'     => false,
                'order'     => $order
            );
        }
    }

    function create_item_array($item_product) {
        $product_id   = $item_product->get_product_id();
        $data         = $item_product->get_data();
        $variation_id = $data['variation_id'];
        $term_id   = 1;
        $term_name = '-';

        if ( $variation_id === 0 ) { // ts a simple product, make same as product id
            $variation_id = $product_id;
        } 
        else { // its a variable product
            $product = wc_get_product( $variation_id );
            $attributes = $product->get_attributes();
        
            if ( is_array($attributes) && ! empty($attributes)) {
                foreach ($attributes as $taxonomy => $attrslug) {
                    if ( gettype($attrslug) !== 'string' ) {
                        continue;
                    }

                    $term = get_term_by('slug', $attrslug, $taxonomy);

                    if ( gettype($term) === 'object' ) {
                        $term_name = $term->name;
                        $term_id   = $term->term_id;
                    }
                }
            }
        } 

        $product_name = get_post_field('post_title',$product_id);

        $item_array = array(
            'product_id'   => $product_id,
            'variation_id' => $variation_id,
            'product_name' => $product_name,
            'term_id'    => $term_id,
            'term_name' => $term_name
        );

        return $item_array;
    }

    function create_product_table_header_map($items) {
        $product_table_map =  [
            "monday" => [],
            "tuesday" => [],
            "wednesday" => [],
            "thursday" => [],
            "friday" => []
        ];

        foreach ($items as $item_key => $single_order) :
            $order      = $single_order['order'];

            foreach( $order->get_items() as $item_id => $item_product ) {
                $product_id   = $item_product->get_product_id();
    
                if (has_term('gift-card', 'product_cat', $product_id)) 
                    continue;

                $item_array = create_item_array($item_product);

                $item_menu_day = $item_product->get_meta('_pgh_menu_day');

                $find = array_filter($product_table_map[$item_menu_day], function($product_map) use($item_array) {
                    if (($product_map['product_id'] == $item_array['product_id']) &&
                        ($product_map['variation_id'] == $item_array['variation_id']) &&
                        ($product_map['term_id'] == $item_array['term_id'])) {
                        return true;
                    }
                    else 
                        return false;
                });

                if (count($find) == 0) {
                    $product_table_map[$item_menu_day][] = $item_array;
                }
            }

        endforeach;

        return $product_table_map;
    }
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
        <strong>Report:</strong> <em>Packing Report</em>
    <div>
    <div>
        <strong>Total Pickup Orders:</strong> <?php echo count($all_local_pickup) ?>
    </div>
    
    <?php 
        if ( count($all_local_pickup) > 0 ) : 
            $product_table_map = create_product_table_header_map($all_local_pickup);

            //echo '<pre>' . var_export($product_table_map, true) . '</pre>';

            $mon_colspan = count($product_table_map['monday']);
            $tue_colspan = count($product_table_map['tuesday']);
            $wed_colspan = count($product_table_map['wednesday']);
            $thu_colspan = count($product_table_map['thursday']);
            $fri_colspan = count($product_table_map['friday']);
    ?>
        

    <div id="table-scroll" class="table-scroll">
        <div class="table-wrap">
            <table class="report-table">
                <thead>
                    <tr>
                        <td colspan="3" class="fixed-side" scope="col"></td>
                        <td class="day-header" colspan="<?= ($mon_colspan == 0) ? 1 : $mon_colspan ?>">Monday</td>
                        <td class="day-header" colspan="<?= ($tue_colspan == 0) ? 1 : $tue_colspan ?>">Tuesday</td>
                        <td class="day-header" colspan="<?= ($wed_colspan == 0) ? 1 : $wed_colspan ?>">Wednesday</td>
                        <td class="day-header" colspan="<?= ($thu_colspan == 0) ? 1 : $thu_colspan ?>">Thursday</td>
                        <td class="day-header" colspan="<?= ($fri_colspan == 0) ? 1 : $fri_colspan ?>">Friday</td>
                    <tr>
                </thead>

                <tbody>
                    <tr>
                        <th>Order Number</th>
                        <th>Customer Name</th>
                        <th>Delivery Location</th>

                        <?php foreach ($product_table_map as $map_key => $map_values) : ?>
                            <?php if (count($map_values) == 0) : ?>
                                <th></th>
                            <?php else: ?>
                                <?php foreach ($map_values as $item_key => $item_value) : ?>
                                    <th class="product-header"><?= $item_value['product_name'] ?> w/ <?= $item_value['term_name'] ?></th>
                                <?php endforeach ?>
                            <?php endif ?>
                        <?php endforeach ?>
                    </tr>

                <?php 
                    foreach ($all_local_pickup as $local_pickup_key => $single_order) : 
                        $order_id   = $single_order['order_id'];
                        $edit_order_link = sprintf( '%s/wp-admin/post.php?post=%s&action=edit', home_url(), $order_id );
                        $order_id_linked = sprintf( '<a href="%s" target="_blank" class="blue">%d</a>', $edit_order_link, $order_id );
                        $order      = $single_order['order'];
                        $location   = $single_order['location'];

                        $user_id              = $order->get_user_id();

                        $order_meta = get_post_meta($order_id);
        
                        if ( $order_meta['_shipping_first_name'][0] ) {
                            $customer = $order_meta['_shipping_first_name'][0] . ' ' . $order_meta['_shipping_last_name'][0];
                        } 
                        else if ( $order_meta['_billing_first_name'][0] ) {
                            $customer = $order_meta['_billing_first_name'][0] . ' ' . $order_meta['_billing_last_name'][0] . ' (billing)';
                        } 
                        else {
                            $customer = 'not provided';
                        }

                        if ( $user_id === 0 ) {
                            $edit_customer_link = sprintf( '/wp-admin/post.php?post=%s&action=edit', $order_id ); // guest; send to order instead
                            $edit_customer_link = home_url( $edit_customer_link );
                            $customer_linked    = sprintf( '<a href="%s" target="_blank" class="blue">%s (as guest)</a>', $edit_customer_link, $customer );
                        } 
                        else {
                            $edit_customer_link = '/wp-admin/user-edit.php?user_id=' . $user_id;
                            $edit_customer_link = home_url( $edit_customer_link );
                            $customer_linked    = sprintf( '<a href="%s" target="_blank" class="blue">%s</a>', $edit_customer_link, $customer );
                        }
                ?>
                    <tr>
                        <td><?= $order_id_linked; ?></td>
                        <td><?= $customer_linked; ?></td>
                        <td><?= $location; ?></td>

                        <?php 
                            $order_items = $order->get_items();

                            foreach ($product_table_map as $map_key => $map_values){
                                if (count($map_values) == 0) {
                                   echo '<td></td>';
                                }
                                else {
                                    foreach ($map_values as $item_key => $item_value) {
                                        $product_qty = 0;

                                        foreach( $order_items as $item_id => $item_product ) {
                                            $item_array = create_item_array($item_product); 
                                            $item_menu_day = $item_product->get_meta('_pgh_menu_day');

                                            if ($item_value['product_id'] == $item_array['product_id'] &&
                                                $item_value['variation_id'] == $item_array['variation_id'] &&
                                                $item_value['term_id'] == $item_array['term_id'] && 
                                                $item_menu_day == $map_key) {
                                                
                                                $product_qty = $item_product->get_quantity();

                                                break;
                                            }
                                        }

                                        echo '<td>' . (string)(($product_qty == 0) ? "" :$product_qty) . '</td>';
                                    }
                                }

                            }
                        ?>
                    </tr>

                <?php endforeach ?>

                </tbody>
            </table>
        </div>
    </div>

    <?php endif ?>
</div>