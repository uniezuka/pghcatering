<?php
    /**
     * The template for displaying PGH Fresh weekly menu.
     *
     * Template Name: PGH Fresh weekly menu
     */
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    get_header(); 

    function get_current_weekly_menu_items( $allowed_days, $allowed_status = array('publish') ) {
        $weekly_menu_args = array(
            'post_type'      => 'weekly_menus',
            'post_status'    => $allowed_status,
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'DESC'
        );

        $weekly_menu_id    = null;
        $weekly_menu       = array();
        $weekly_menu_query = new WP_Query( $weekly_menu_args );

        if( $weekly_menu_query->have_posts() ) {
            while( $weekly_menu_query->have_posts() ) {
                $weekly_menu_query->the_post();
                $weekly_menu_id = get_the_ID();

                foreach ( $allowed_days as $day ) {
                    $day_key = $day["id"];
                    $menu_for_day = get_field('menu_for_' .  $day_key );
                    $product_ids = array();

                    if ( $menu_for_day ) { 
                        foreach ($menu_for_day as $product_id) {
                            $product_ids[] = $product_id;
                        }
                    }

                    $weekly_menu[] = array( "id" => $day_key, "product_ids" => $product_ids );
                }
            }
        }

        wp_reset_postdata();

        return array(
            'weekly_menu'        => $weekly_menu,
            'weekly_menu_id'     => $weekly_menu_id
        );
    }

    function calculate_total_qty_in_cart_for_this( $pid, $current_cart, $menu_day = '') {
        $string = '';
        $count  = 0;

        if ($current_cart != null) {
            foreach( $current_cart as $cart_item_key => $values ) {
                $cart_product_type = $values['data']->get_type();
                $quantity          = $values['quantity'];
                
                if ( $cart_product_type === 'simple' ) {
                    $cart_product_id   = $values['data']->get_id();

                    if(array_key_exists('menu_day', $values)) {
                        if ($values['menu_day'] === $menu_day) {
                            if( $pid === $cart_product_id ) {
                                $count = intval($count) + intval($quantity);
                            }
                        }
                    }
                } 

                else if ( $cart_product_type === 'variation' ) {
                    $cart_product_id  = $values['data']->get_parent_id();

                    if(array_key_exists('menu_day', $values)) {
                        if ($values['menu_day'] === $menu_day) {
                            if( $pid === $cart_product_id ) {
                                $count = intval($count) + intval($quantity);
                            }
                        }
                    }
                }
            }
        }

        return $count;
    }

    function get_default_value_for_non_existing($value, $default_value) {
        return ($value) ? $value : $default_value;
    }

    function calc_perc_daily_value( $macro, $amount ) {
        if ( strpos($amount, 'g') || strpos($amount, 'G') ) {
            str_replace(array('g','G'), '', $amount);
        }
        $amount = floatval($amount);

        if ( $macro === 'fat' ) {
            $denominator = 65;
        } else if ( $macro === 'carb' ) {
            $denominator = 300;
        } else if ( $macro === 'protein' ) {
            $denominator = 50;
        }

        $perc = round( ($amount / $denominator) * 100, 1);

        return $perc;
    }

    $allowed_days = [
        array( "id" => "monday", "text" => "Monday" ),
        array( "id" => "tuesday", "text" => "Tuesday" ),
        array( "id" => "wednesday", "text" => "Wednesday" ),
        array( "id" => "thursday", "text" => "Thursday" ),
        array( "id" => "friday", "text" => "Friday" ),
    ];

    $weekly_menu = get_current_weekly_menu_items( $allowed_days );   
    $counter = 1;
    $columns = 3;
    $weekly_menu_id = $weekly_menu['weekly_menu_id'];

    $pagetitle = get_the_title();

    $menu_available_until = get_default_value_for_non_existing( get_field( 'weekly_menu_available_until', $weekly_menu_id ), '' );
    $delivery_date = get_default_value_for_non_existing( get_field( 'delivery_date', $weekly_menu_id ), '' );
    $delivery_date_message = '';

    if ($delivery_date !== '') {
        $delivery_time = strtotime($delivery_date);

        $delivery_date_message = 'Deliver ' . date('l', $delivery_time) .  ' "' . date('m/d/Y', $delivery_time) . '"';
    }

    $current_cart = ( WC() && WC()->cart) ? WC()->cart->get_cart() : null;
    
    include 'template-weekly-menu-parts/html-generator-functions.php';
?>

<!-- <?php echo '<pre>cart: ' . var_export(WC()->cart->get_cart(), true) . '</pre>'; ?> -->

<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
    <div class="entry-content">

        <?php include 'template-weekly-menu-parts/weekly-menu-info-dates.php'; ?>

        <div id="current-menu" class="full-width">
            <div id="menu-products" class="restrict-width">
            <?php foreach ( $allowed_days as $day ) : ?>
                <?php
                    $menu_for_the_day = array_filter(
                        $weekly_menu["weekly_menu"], 
                        function( $d ) use ( $day ) {
                            return $d["id"] == $day["id"];
                        });

                    $product_ids = array_values($menu_for_the_day)[0]['product_ids'];

                    if (count($product_ids) == 0) continue;

                    $menu_day = $day["id"];
                ?>

                <h3><?php echo $day["text"] ?></h3>

                <div class="menu-section-wrapper flex-row">
                <?php foreach ($product_ids as $product_id) : ?>
                    
                    <?php include 'template-weekly-menu-parts/weekly-menu-product.php'; ?>
                    
                <?php endforeach ?>
                </div>
            <?php endforeach; ?>
            </div>
        </div>
    </div>
</article>

<?php
    get_footer();