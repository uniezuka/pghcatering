<h2>PGH Catering Settings</h2>
<form action="options.php" method="post">
    <?php 
    settings_fields( 'custom_pgh_catering_plugin_options' );
    do_settings_sections( 'custom_pgh_catering_plugin' ); 
    ?>
    <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
</form>