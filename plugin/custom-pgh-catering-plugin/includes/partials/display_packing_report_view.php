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

    $all_local_pickup     = array(); // to be sorted location name a > z
    $all_pickup_locations = array(); // a list of unique locations

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
    
        if (strtolower($shipping_method) === 'local pickup' ||
            strtolower($shipping_method) === 'pickup store' ||
            strtolower($shipping_method) === 'delivery location') {
    
            $location_names = array();
    
            if (strtolower($shipping_method) === 'local pickup' ) {
                foreach( $order->get_items( 'shipping' ) as $item_id => $shipping_item_obj ){
                    $shipping_item_data  = $shipping_item_obj->get_data();
                    $shipping_meta_data  = $shipping_item_data['meta_data'];
    
                    foreach ($shipping_meta_data as $key => $meta_data) {
                        $data = $meta_data->get_data();
    
                        if ( $data['key'] === '_pickup_location_name' ) {
                            $location_name = $data['value'];
                            $location_names[] = $location_name;
    
                            if ( ! in_array($location_name, $all_pickup_locations) ) {
                                array_push($all_pickup_locations, $location_name);
                            }
                        }
                    }
                }
            }
            else {
                $store_name = get_post_meta($order_id, '_shipping_pickup_stores', true);
                $store = get_page_by_title($store_name, OBJECT, 'store');
    
                if ( ! in_array($store_name, $all_pickup_locations) ) {
                    array_push($all_pickup_locations, $store_name);
                }
    
                $location_names[] = $store_name;
            }
    
            foreach ($location_names as $single => $location_name) {
                if ( count($location_names) > 1 ) {
                    $multiple = true;
                } else {
                    $multiple = false;
                }
                $all_local_pickup[] = array(
                    'type'      => 'Local Pickup',
                    'timestamp' => $timestamp,
                    'datestring'=> $datestring,
                    'location'  => $location_name,
                    'order_id'  => $order_id,
                    'alert'     => $multiple,
                    'order'     => $order
                );
            }
        }
    }

    $lp_keys = array_column( $all_local_pickup, 'location' );
    array_multisort( $lp_keys, SORT_ASC, $all_local_pickup );

    function get_order_notes( $order_id ) {
        $args = array(
            'post_id' => $order_id,
            'orderby' => 'comment_date',
            'order'   => 'DESC',
            'type'    => 'order_note',
            'number'  => ''
        );
      
        remove_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
      
        $notes = get_comments( $args );
      
        add_filter( 'comments_clauses', array( 'WC_Comments', 'exclude_order_comments' ), 10, 1 );
      
        return $notes;
    }

    function generate_customer_requests( $order_id = 0, $user_id = 0 ) {
        $order_id    = (int)$order_id;
        $user_id     = (int)$user_id;
        // DEFINE NOTES
        $admin_note                      = ''; // set on an order by admin
        $customer_requests               = ''; // set in user preferences
        $customer_internal_kitchen_note  = ''; // set by admin in user profile
        $customer_internal_delivery_note = ''; // set by admin in user profile
        $customer_delivery_note          = ''; // set on an order by customer 
        $customer_kitchen_note           = ''; // set on an order by customer

        $cust_requests_output = array();
        $order     = wc_get_order( $order_id );

        // ORDER-SPECIFIC ADMIN NOTE
        // if this is a legit order, look for order-specific "ADMIN:" note
        if ( is_object( $order ) ) {
            $admin_note_count = 0;
            $notes            = get_order_notes( $order_id );

            foreach ($notes as $key => $note) {
                $comment = $notes[$key]->comment_content;

                if ( strpos($comment, 'ADMIN:') !== false ) {
                    if ( $admin_note_count > 0 ) {
                        $admin_note .= ' | ';
                    }

                    $admin_note .= str_replace('ADMIN:','',$notes[$key]->comment_content);
                    $admin_note_count++;
                }
            }
        }

        if ( strlen($admin_note) > 0 ) {
            $admin_label = 'Order-specific notes set by admin on order #' . $order_id;
            $cust_requests_output['ADMIN ORDER NOTES'] = array($admin_label,$admin_note);
        }

        // INTERNAL KITCHEN NOTE
        if ( get_user_meta($user_id, 'customer_internal_kitchen_note', true) ) {
            $customer_internal_kitchen_note .= get_user_meta($user_id, 'customer_internal_kitchen_note', true);
        }
        if ( strlen($customer_internal_kitchen_note) > 0 ) {
          $customer_internal_kitchen_note_label = 'This note was set by admin in the user profile for user #'.$user_id;
          $cust_requests_output['INTERNAL KITCHEN NOTE'] = array($customer_internal_kitchen_note_label,$customer_internal_kitchen_note);
        }

        // INTERNAL DELIVERY NOTE
        if ( get_user_meta($user_id, 'customer_internal_delivery_note', true) ) {
            $customer_internal_delivery_note .= get_user_meta($user_id, 'customer_internal_delivery_note', true);
        }
        if ( strlen($customer_internal_delivery_note) > 0 ) {
            $customer_internal_delivery_note_label = 'This note was set by admin in the user profile for user #'.$user_id;
            $cust_requests_output['INTERNAL DELIVERY NOTE'] = array($customer_internal_delivery_note_label,$customer_internal_delivery_note);
        }

        // CUSTOMER DIETARY REQUESTS
        if ( get_user_meta($user_id, 'customer_requests', true) ) {
            $customer_requests .= get_user_meta($user_id, 'customer_requests', true);
        }
        if ( strlen($customer_requests) > 0 ) {
            $customer_requests_label = 'This note was set by customer #'.$user_id.' from My Account > Meal Preferences';
            $cust_requests_output['CUSTOMER REQUESTS'] = array($customer_requests_label,$customer_requests);
        }

        // # CUSTOMER DELIVERY NOTE
        if ( $order->get_customer_note() ) {
            $customer_delivery_note = $order->get_customer_note();
        }
        if ( strlen($customer_delivery_note) > 0 ) {
            $customer_delivery_note_label = 'This note was set by customer #'.$user_id.' while placing their order.';
            $cust_requests_output['CUSTOMER DELIVERY NOTE'] = array($customer_delivery_note_label,$customer_delivery_note);
        }

        // # CUSTOMER KITCHEN NOTE
        $order_meta = get_post_meta( $order_id );

        if ( array_key_exists('additional_customer_order_note', $order_meta) && strlen($order_meta['additional_customer_order_note'][0]) > 0 ) {
             $customer_kitchen_note = $order_meta['additional_customer_order_note'][0];
        } 

        if ( strlen($customer_kitchen_note) > 0 ) {
            $customer_kitchen_note_label = 'This note was set by customer #'.$user_id.' while placing their order.';
            $cust_requests_output['CUSTOMER KITCHEN NOTE'] = array($customer_kitchen_note_label,$customer_kitchen_note);
        }

        return $cust_requests_output;
    }
?>

<div class="report-intro-wrapper">
    <div>
        <strong>Report:</strong> <em>Packing Report</em>
    <div>
    <div>
        <strong>Total Pickup Orders:</strong> <?php echo count($all_local_pickup) ?>
    </div>
</div>

<div class="packing-reports-wrapper">
    <?php if ( count($all_local_pickup) > 0 ) : ?>
        <h1 class="packing-reports-heading page-break-before">Local Pickup</h1>

        <?php 
            foreach ($all_pickup_locations as $location_key => $one_pickup_location) : 
                $page_break_class = ( $location_key === 0 ) ? '' : 'page-break-before';
        ?>
        
        <h2 class="packing-reports-heading <?= $page_break_class ?>">Pickup — <?= $one_pickup_location ?></h2>
            <?php 
                foreach ($all_local_pickup as $local_pickup_key => $one_local_pickup) : 
                    if ( $one_local_pickup['location'] === $one_pickup_location ) :
                        include 'single_packing_report_view.php';
                    endif;
                endforeach 
            ?>
        <?php endforeach ?>

    <?php endif ?>
</div>