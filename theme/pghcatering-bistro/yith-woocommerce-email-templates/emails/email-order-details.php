<?php
/**
 * Order details table shown in emails.
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @version     3.7.0
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
}
global $current_email;
$template           = yith_wcet_get_email_template( $current_email );
$meta               = yith_wcet_get_template_meta( $template );
$show_thumbs        = ( isset( $meta[ 'show_prod_thumb' ] ) ) ? $meta[ 'show_prod_thumb' ] : false;
$premium_mail_style = ( !empty( $meta[ 'premium_mail_style' ] ) ) ? $meta[ 'premium_mail_style' ] : 0;

$titles = array(
    'product'  => esc_html__( 'Product', 'woocommerce' ),
    'quantity' => esc_html__( 'Quantity', 'woocommerce' ),
    'price'    => esc_html__( 'Price', 'woocommerce' )
);

foreach ( $titles as $key => $value ) {
    $titles[ $key ] = apply_filters( 'yith_wcet_order_details_table_title_' . $key, $value, $order, $sent_to_admin, $plain_text, $email );
}

do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php if ( apply_filters( 'yith_wcet_order_details_show_order_title', true, $order, $sent_to_admin, $plain_text, $email ) ): ?>
    <h2>
        <?php
        if ( $sent_to_admin ) {
            $before = '<a class="link" href="' . esc_url( $order->get_edit_order_url() ) . '">';
            $after  = '</a>';
        } else {
            $before = '';
            $after  = '';
        }
        /* translators: %s: Order ID. */
        echo wp_kses_post( $before . sprintf( __( 'Order #%s', 'woocommerce' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
        ?>
    </h2>
<?php endif; ?>

<?php

$current_weekly_menu = pghf_get_current_menu_items();
$weekly_menu_id      = $current_weekly_menu['weekly_menu_id'];

$blurbs = pghf_generate_email_text_blurbs();
$shipping_method = $order->get_shipping_method();
$email_home_delivery_label = $blurbs['email_home_delivery_label'];
$email_home_delivery_blurb = $blurbs['email_home_delivery_blurb'];
$email_local_pickup_label  = $blurbs['email_local_pickup_label'];
$email_local_pickup_blurb  = $blurbs['email_local_pickup_blurb'];

$delivery_date = '';

// if this is Local Pickup, fill the array
if ( strtolower($shipping_method) === 'local pickup'  || strtolower($shipping_method) === 'pickup store' ) {
    // pickup monday
    if ( get_field( 'weekly_menu_pickup_monday', $weekly_menu_id ) )
        $pickup_monday   = get_field( 'weekly_menu_pickup_monday', $weekly_menu_id );
    else
        $pickup_monday   = '';

    // pickup output
    $delivery_date = $pickup_monday;
} 
else {
    // delivery sunday
    if ( get_field( 'weekly_menu_delivery_sunday', $weekly_menu_id ) )
        $delivery_sunday   = get_field( 'weekly_menu_delivery_sunday', $weekly_menu_id );
    else
        $delivery_sunday   = '';
    
    // delivery monday
    if ( get_field( 'weekly_menu_delivery_monday', $weekly_menu_id ) )
        $delivery_monday   = get_field( 'weekly_menu_delivery_monday', $weekly_menu_id );
    else
        $delivery_monday   = '';

    $delivery_date = $delivery_sunday;
}

$email_home_delivery_blurb  = str_replace('#delivery_date', $delivery_date, $email_home_delivery_blurb);
$email_local_pickup_blurb  = str_replace('#delivery_date', $delivery_date, $email_local_pickup_blurb);


if ( in_array( strtolower($shipping_method), array( 'free delivery', 'flat rate', 'free shipping', 'home delivery' ) ) )
    printf('<h2 style="color:#ff8c00">%s</h2><p>%s</p>', __($email_home_delivery_label,'pgh-fresh'), __($email_home_delivery_blurb,'pgh-fresh'));
else if ( in_array( strtolower($shipping_method), array( 'local pickup', 'pickup store' ) ) )
    printf('<h2 style="color:#ff8c00">%s</h2><p>%s</p>', __($email_local_pickup_label,'pgh-fresh'), __($email_local_pickup_blurb,'pgh-fresh'));

?>

<table id="yith-wcet-order-items-table" cellspacing="0" cellpadding="6" style="width: 100%;">
    <thead>
    <tr>
        <th id="yith-wcet-th-title-product" class="yith-wcet-order-items-table-element" scope="col" style="padding:6px"><?php echo $titles[ 'product' ] ?></th>
        <th id="yith-wcet-th-title-quantity" class="yith-wcet-order-items-table-element" scope="col" style="padding:6px"><?php echo $titles[ 'quantity' ] ?></th>
        <th id="yith-wcet-th-title-price" class="yith-wcet-order-items-table-element" scope="col" style="padding:6px"><?php echo $titles[ 'price' ] ?></th>
    </tr>
    </thead>
    <tbody>
    <?php
    echo wc_get_email_order_items( $order, array(
        'show_sku'      => $sent_to_admin,
        'show_image'    => !!$show_thumbs,
        'image_size'    => apply_filters( 'yith_wcet_product_thumbnail_size', array( 32, 32 ), $template, $current_email ),
        'plain_text'    => $plain_text,
        'sent_to_admin' => $sent_to_admin
    ) );
    ?>
    </tbody>

    <?php if ( $premium_mail_style < 2 ): ?>
        <tfoot>
        <?php
        if ( $item_totals = $order->get_order_item_totals() ) {
            $i       = 0;
            $t_count = count( $item_totals );
            foreach ( $item_totals as $total ) {
                $i++;
                $last_class = $i == $t_count ? 'last' : 'not_last';
                ?>
                <tr>
                <th class="yith-wcet-order-items-table-element<?php if ( $i == 1 )
                    echo '-bigtop'; ?> <?php echo $last_class; ?>" scope="row" colspan="2"><?php echo wp_kses_post( $total[ 'label' ] ); ?></th>
                <td class="yith-wcet-order-items-table-element<?php if ( $i == 1 )
                    echo '-bigtop'; ?> <?php echo $last_class; ?>"><?php echo wp_kses_post( $total[ 'value' ] ); ?></td>
                </tr><?php
            }
        }
        ?>
        </tfoot>
    <?php endif ?>
</table>

<?php if ( $premium_mail_style > 1 ):
    $totals_table_width_percentage = absint( apply_filters( 'yith_wcet_order_details_table_totals_table_width_percentage', '50', $current_email, $premium_mail_style ) );
    $first_column_width_percentage = 100 - $totals_table_width_percentage;
    ?>
    <table width="100%">
        <tr>
            <td width="100%"></td>
        </tr>
    </table>

    <table class="yith-wcet-two-columns" width="100%" cellspacing="0" cellpadding="0">
        <tr>
            <td width="<?php echo $first_column_width_percentage ?>%" style="padding:0px">

            </td>
            <td width="<?php echo $totals_table_width_percentage ?>%" style="padding:0px">
                <table id="yith-wcet-foot-price-list">
                    <?php
                    if ( $item_totals = $order->get_order_item_totals() ) {
                        $i       = 0;
                        $t_count = count( $item_totals );
                        foreach ( $item_totals as $total ) {
                            $i++;
                            $last_class  = $i == $t_count ? 'last' : 'not_last';
                            $total_label = str_replace( ':', '', $total[ 'label' ] );
                            $total_label = apply_filters( 'yith_wcet_total_label', $total_label, $current_email );
                            ?>
                            <tr>
                            <th <?php if ( $i == $t_count ) {
                                echo 'id="yith-wcet-total-title"';
                            } ?> class="<?php echo $last_class; ?>" scope="row" colspan="2"><?php echo wp_kses_post( $total_label ); ?></th>
                            <td <?php if ( $i == $t_count ) {
                                echo 'id="yith-wcet-total-price"';
                            } ?> class="<?php echo $last_class; ?>"><?php echo wp_kses_post( $total[ 'value' ] ); ?></td>
                            </tr><?php
                        }
                    }
                    ?>
                </table>
            </td>
        </tr>
    </table>
<?php endif ?>


<?php if ( $order->get_customer_note() ) : ?>
    <table width="100%">
        <tr>
            <td width="100%"></td>
        </tr>
    </table>
    <table id="yith-wcet-order-customer-note-table" cellspacing="0" cellpadding="6" style="width: 100%;">
        <tr>
            <td>
                <h2><?php _e( 'Note:', 'woocommerce' ) ?></h2>
                <p><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ) ?></p>
            </td>
        </tr>
    </table>
<?php endif ?>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
