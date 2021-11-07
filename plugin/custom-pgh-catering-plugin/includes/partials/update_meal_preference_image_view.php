<tr class="form-field term-group-wrap">
    <th scope="row">
        <label for="meal-preference-image-id"><?php _e( 'Image', CUSTOM_PGH_CATERING_DOMAIN_NAME ); ?></label>
    </th>
    <td>
        <?php $image_id = get_term_meta ( $term -> term_id, 'meal-preference-image-id', true ); ?>
        <input type="hidden" id="meal-preference-image-id" name="meal-preference-image-id" value="<?php echo $image_id; ?>">
        <div id="category-image-wrapper">
        <?php if ( $image_id ) { ?>
            <?php echo wp_get_attachment_image ( $image_id, 'thumbnail' ); ?>
        <?php } ?>
        </div>
        <p>
            <input type="button" class="button button-secondary ct_tax_media_button" id="ct_tax_media_button" name="ct_tax_media_button" value="<?php _e( 'Add Image', 'hero-theme' ); ?>" />
            <input type="button" class="button button-secondary ct_tax_media_remove" id="ct_tax_media_remove" name="ct_tax_media_remove" value="<?php _e( 'Remove Image', 'hero-theme' ); ?>" />
        </p>
    </td>
</tr>