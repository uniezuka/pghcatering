<?php
/**
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;

$text_align  = is_rtl() ? 'right' : 'left';
$margin_side = is_rtl() ? 'left' : 'right';

$allowed_days = [
    array( "id" => "monday", "text" => "Monday" ),
    array( "id" => "tuesday", "text" => "Tuesday" ),
    array( "id" => "wednesday", "text" => "Wednesday" ),
    array( "id" => "thursday", "text" => "Thursday" ),
    array( "id" => "friday", "text" => "Friday" ),
];
foreach ( $allowed_days as $day ) {
?>
    <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item pgh_menu_day', $item, $order ) ); ?>">
        <td colspan="3" class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
            <?php echo $day["text"] ?>
        </td>
    </tr>

<?php
    foreach ( $items as $item_id => $item ) {
        $product       = $item->get_product();
        $sku           = '';
        $purchase_note = '';
        $image         = '';
        $item_menu_day = $item->get_meta('_pgh_menu_day');

        if ($item_menu_day !== $day["id"]) continue;

        if ( ! apply_filters( 'woocommerce_order_item_visible', true, $item ) ) {
            continue;
        }

        if ( is_object( $product ) ) {
            $sku           = $product->get_sku();
            $purchase_note = $product->get_purchase_note();
            $image         = $product->get_image( $image_size );
        }
?>

    <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
        <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
        <?php

        // Show title/image etc.
        if ( $show_image ) {
            echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) );
        }

        // Product name.
        echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $item->get_name(), $item, false ) );

        // SKU.
        if ( $show_sku && $sku ) {
            echo wp_kses_post( ' (#' . $sku . ')' );
        }

        // allow other plugins to add additional product information here.
        do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

        wc_display_item_meta(
            $item,
            array(
                'label_before' => '<strong class="wc-item-meta-label" style="float: ' . esc_attr( $text_align ) . '; margin-' . esc_attr( $margin_side ) . ': .25em; clear: both">',
            )
        );

        // allow other plugins to add additional product information here.
        do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );

        ?>
        </td>
        <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php
            $qty          = $item->get_quantity();
            $refunded_qty = $order->get_qty_refunded_for_item( $item_id );

            if ( $refunded_qty ) {
                $qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
            } else {
                $qty_display = esc_html( $qty );
            }
            echo wp_kses_post( apply_filters( 'woocommerce_email_order_item_quantity', $qty_display, $item ) );
            ?>
        </td>
        <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
        </td>
    </tr>

<?php
        if ( $show_purchase_note && $purchase_note ) {
?>

    <tr>
        <td colspan="3" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align:middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
            <?php
            echo wp_kses_post( wpautop( do_shortcode( $purchase_note ) ) );
            ?>
        </td>
    </tr>

<?php
        }
    } 
}
?>
