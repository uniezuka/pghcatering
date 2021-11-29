<?php
    $single_order = $one_local_pickup;
    
    $type       = $single_order['type'];
    $timestamp  = $single_order['timestamp'];
    $datestring = $single_order['datestring'];
    $location   = $single_order['location'];
    $order_id   = $single_order['order_id'];
    $alert      = $single_order['alert'];
    $order      = $single_order['order'];
  
    $edit_order_link = sprintf( '%s/wp-admin/post.php?post=%s&action=edit', home_url(), $order_id );
    $order_id_linked = sprintf( '<a href="%s" target="_blank" class="blue">%d</a>', $edit_order_link, $order_id );
  
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
  
    $user_id              = $order->get_user_id();

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
  
    $customer_delivery_note = '';
    $check_for_notes = generate_customer_requests( $order_id, $user_id );
  
    $cellphone = '-';
    $homephone = '-';
    $company = '-';
    $address1 = '(no shipping address)';
    $address2 = '';
    $city = '(no city)';
    $state = '(no state)';
    $postcode = '(no zip)';
  
    if ($order_meta['_billing_phone'][0])
        $cellphone = $order_meta['_billing_phone'][0];

    if ($order_meta['_billing_home_phone'][0]) 
        $homephone = $order_meta['_billing_home_phone'][0];
    
    if (array_key_exists('_shipping_company', $order_meta) && $order_meta['_shipping_company'][0]) 
        $company = $order_meta['_shipping_company'][0];

    if ($order_meta['_shipping_address_1'][0]) 
        $address1 = $order_meta['_shipping_address_1'][0];

    if (array_key_exists('_shipping_address_2', $order_meta) && $order_meta['_shipping_address_2'][0]) 
        $address2 = $order_meta['_shipping_address_2'][0] . '<br>';

    if ($order_meta['_shipping_city'][0]) 
        $city = $order_meta['_shipping_city'][0];

    if ($order_meta['_shipping_state'][0]) 
        $state = $order_meta['_shipping_state'][0];

    if ($order_meta['_shipping_postcode'][0]) 
        $postcode = $order_meta['_shipping_postcode'][0];
  
    $address_lockup = $address1 . '<br>' . $address2 . $city . ', ' . $state . ' ' . $postcode;
  
    $user_id = get_post_meta( $order_id, '_customer_user', true );
    $user_id_string = 'user_' . $user_id;
   
    $wrapper_class = strtolower(str_replace(' ', '-', $type));
    $metac    = 'single-order-meta';
    $labelc   = 'meta-label';
?>
<div class="single-order-wrapper <?= $wrapper_class ?> page-break">
    <div class="single-order-inner flex-row">
        <div class="single-order-topleft">
            <?php if ( $alert && $type === 'Local Pickup' ) : ?>
                <div class="<?= $metac ?> orange">
                    <div class="<?= $labelc ?>">ALERT:</div>
                    <div class="<?= $labelc ?>">Multiple delivery locations! Please contact customer.</div>
                </div>
            <?php endif ?>

            <div class="<?= $metac ?>">
                <div class="<?= $labelc ?>">
                    <strong>Order ID:</strong>
                </div>
                <div class="<?= $labelc ?>"><?= $order_id_linked ?></div>
            </div>

            <div class="<?= $metac ?>">
                <div class="<?= $labelc ?>">
                    <strong>Order Date:</strong>
                </div>
                <div class="<?= $labelc ?>"><?= $datestring ?></div>
            </div>

            <div class="<?= $metac ?>">
                <div class="<?= $labelc ?>">
                    <strong>Delivery Method:</strong>
                </div>
                <div class="<?= $labelc ?>"><?= $type ?></div>
            </div>

            <div class="<?= $metac ?>">
                <div class="<?= $labelc ?>">
                    <strong>Customer Name:</strong>
                </div>
                <div class="<?= $labelc ?>"><?= $customer_linked ?></div>
            </div>

            <div class="<?= $metac ?>">
                <div class="<?= $labelc ?>">
                    <strong>Cell Number:</strong>
                </div>
                <div class="<?= $labelc ?>"><?= $cellphone ?></div>
            </div>
        </div>

        <div class="single-order-topright">
            <div class="<?= $metac ?> margin-bottom">
                <div class="<?= $labelc ?>">
                    <strong>Delivery Address:</strong>
                </div>
                <div class="<?= $labelc ?>"><?= $address_lockup ?></div>
            </div>
        </div>

        <?php
            if ( is_array($check_for_notes) && ! empty($check_for_notes) ) {
                foreach ($check_for_notes as $which => $request) {
                    if ( $request && strlen($request[1]) > 0 ) {
        ?>
        <div class="single-order-message" title="<?= $check_for_notes[$which][0] ?>">
            <strong><?= ucwords(strtolower($which)) ?>:</strong> <span class="red"><?= $check_for_notes[$which][1] ?></span>
        </div>
        <?php
                    }
                }
            }
        ?>

        <div class="single-order-bottom">
            <div class="single-order-bottom-liner">
                <?php include 'single_packing_list_report_view.php'; ?>
            </div>
        </div>
    </div>
</div>

<div class="page-break"></div>