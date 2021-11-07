<?php if ( $terms && ! is_wp_error( $terms ) ) : ?>
    <div class="mp-icons">
    <?php 
        foreach ( $terms as $term ) {
            $term_id  = 'meal_preferences_' . $term->term_id;
            $display  = get_field('display_on_frontend',$term_id);

            if ( $display ) {
                $mp_label = get_default_value_for_non_existing(get_field('preference_label',$term_id), $term->name);
                $meal_preference_image_id = get_term_meta( $term->term_id, 'meal-preference-image-id', true );

                if ($meal_preference_image_id) {
                    $icon = wp_get_attachment_image( $meal_preference_image_id, 'mp_thumbnail', false, array('class' => 'img-responsive') );
                    $icon_url  = $menu_url . '?fwp_meal_preferences=' . $term->slug . '#current-menu';
                }
                else {
                    $icon = '<div style="height:30px;width:1px;display:inline-block"></div>';
                    $icon_url  = $menu_url . '#';
                }
    ?>
        <div class="mp-icon mp-icon-<?= $term->slug ?>">
            <div class="mp-label"><?= $mp_label ?></div>
            <?= sprintf('<a href="%s">%s</a>', esc_url($icon_url), $icon) ?>
        </div>
    <?php 
            } 
        }
    ?>
    </div>
<?php endif ?>