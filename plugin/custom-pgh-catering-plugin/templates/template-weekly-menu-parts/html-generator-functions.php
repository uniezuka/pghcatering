<?php
    function display_add_to_cart($product, $addtocart_options, $menu_day = '') {
        
        $product_type    =  $product->is_type( 'variable' ) ? 'variation' : 'simple';
        $product_id   = $product->get_id();
        $current_quantity = $addtocart_options['current_quantity'];
        $variation_id = $addtocart_options['variation_id'];
        $variation_slug = $addtocart_options['variation_slug'];
        $variation_title = $addtocart_options['variation_title'];
        $variation_array = $addtocart_options['variation_array'];
        $is_gift_card = $addtocart_options['is_gift_card'];
        
        if ( $product_type === 'simple' || $is_gift_card ) {
            $label_before = '';
        } else if ( $product_type === 'variation' ) {
            $label_before = 'w/ ';
        }
    
        if ( $current_quantity === NULL ) {
            $current_quantity = '0';
        }

        $nf_modal_id = $variation_id . '-' . $menu_day;
?>
    <div class="atc-lockup">
        <div class="atc-label <?= $product_type ?>-label">

        <?php if ($is_gift_card) : ?>
            <span><?= $variation_title ?></span><strong><span>"></span>         <span></span></strong>
        <?php else : ?>
            <a href="#" data-modal_id="<?= $nf_modal_id ?>" class="nf-popup"><span><?= $label_before . $variation_title ?></span> <i class="far fa-info-circleOFF" style="font-size:12px">Nutrition</i> <strong><span></span>         <span></span></strong></a>
        <?php endif ?>

        </div>

        <div class="atc-buttons <?= $product_type ?>-buttons">
            <div class="atc-button-bar">
                <a href="javascript:void(0);" class="custom-add-to-cart" data-type="<?= $product_type ?>" data-action="remove" data-variationid="<?= $variation_id ?>" data-variation="<?= json_encode($variation_array) ?>" data-product_id="<?= $product_id ?>" data-menu_day="<?= $menu_day ?>">
                    <i class="far fa-minus"></i>
                </a>

                <span><span class="atc-current-quantity"><?= $current_quantity ?></span></span>

                <a href="javascript:void(0);" class="custom-add-to-cart" data-type="<?= $product_type ?>" data-action="add" data-variationid="<?= $variation_id ?>" data-variation="<?= json_encode($variation_array) ?>" data-product_id="<?= $product_id ?>" data-menu_day="<?= $menu_day ?>">
                    <i class="far fa-plus"></i>
                </a>
            </div>
        </div>
    </div>
<?php 
        generate_nf_modal($product_type, $product_id, $variation_id, $variation_title, $menu_day);
    } 
?>

<?php
    function generate_nf_modal($product_type, $parent_id, $variation_id, $variation_title, $menu_day = '') {
        $product_id = $variation_id;

        if ( $product_type === 'simple' ) {
            $title = get_the_title($parent_id);
            $subtitle = get_post_meta( $parent_id, 'product_subtitle', true );

            if ( !empty($subtitle) ) { 
                $subtitle = '('.$subtitle.')'; 
            } else { 
                $subtitle = ''; 
            }

            $sidetitle = '';
            $description = get_post_field('post_content', $parent_id);
        } 
        else if ( $product_type === 'variation' ) {
            $title = get_the_title($parent_id);
            $subtitle = get_post_meta( $parent_id, 'product_subtitle', true );
        
            if ( ! empty($subtitle) ) { 
                $subtitle = '('.$subtitle.')'; 
            } else { 
                $subtitle = ''; 
            }

            $sidetitle = $variation_title;
            $description = get_post_field('post_content', $parent_id);
        }

        $prefix = 'pghf_';
        $calories = get_post_meta( $product_id, $prefix . 'calories', true );
        if ( empty($calories) ) { $calories = 1; }

        $protein = get_post_meta( $product_id, $prefix . 'protein', true );
        if ( empty($protein) ) { $protein = 1; }
        
        $fat = get_post_meta( $product_id, $prefix . 'fat', true );
        if ( empty($fat) ) { $fat = 1; }
        
        $carb = get_post_meta( $product_id, $prefix . 'carb', true );
        if ( empty($carb) ) { $carb = 1; }

        $nf_modal_id = $variation_id . '-' . $menu_day;
?>
    <div id="nf-modal-<?= $nf_modal_id ?>" class="modal">
        <div class="modal-content">
            <div class="modal-menu-item-details">
                <h4 class="modal-menu-title"><?= $title . ' ' . $subtitle ?></h4>
                <?php if ( !empty($sidetitle) ) : ?>
                <div class="modal-menu-sidetitle">with <?= $sidetitle ?></div>
                <?php endif ?>
                <div class="modal-menu-desc"><?= $description ?></div>
            </div>
            <span class="close-button">&times;</span>
            <div class="modal-nutrition-facts">
                <h2 class="nf-header">Nutrition Facts</h2>
                <div class="flex-row">1 serving per container</div>
                <div class="flex-row nowrap border-bottom-8">
                    <div class="flex-col-2 text-left"><strong>Serving size</strong></div>
                    <div class="flex-col-1 text-right">1</div>
                </div>
                <div class="flex-row nowrap"><strong>Amount Per Serving</strong></div>
                <div class="flex-row nowrap border-bottom-5">
                    <div class="flex-col-2 text-left font-2"><strong>Calories</strong></div>
                    <div class="flex-col-1 text-right font-2"><?= $calories ?></div>
                </div>
                <div class="flex-row nowrap text-right">
                    <div class="flex-col-1 text-right"><strong>% Daily Value*</strong></div>
                </div>
                <div class="flex-row nowrap border-bottom-1">
                    <div class="flex-col-2 text-left"><strong>Total Fat</strong> <?= $fat ?>g</div>
                    <div class="flex-col-1 text-right"><?= calc_perc_daily_value('fat', $fat) ?>%</div>
                </div>
                <div class="flex-row nowrap border-bottom-1">
                    <div class="flex-col-2 text-left"><strong>Total Carbohydrate</strong> <?= $carb ?>g</div>
                    <div class="flex-col-1 text-right"><?= calc_perc_daily_value('carb', $carb) ?>%</div>
                </div>
                <div class="flex-row nowrap border-bottom-8">
                    <div class="flex-col-2 text-left"><strong>Protein</strong> <?= $protein ?>g</div>
                    <div class="flex-col-1 text-right"><?= calc_perc_daily_value('protein', $protein) ?>%</div>
                </div>
                <div class="flex-row nowrap border-bottom-1">
                    <div class="flex-col-1 text-left">Not a significant source of cholesterol, vitamin D, calcium, iron and potassium </div>
                </div>
                <div class="flex-row nowrap">
                    <div class="flex-col-1 text-left">* The % Daily Value (DV) tells you how much a nutrient in a serving of food contributes to a daily diet. 2,000 calories a day is used for general nutrition advice.</div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>