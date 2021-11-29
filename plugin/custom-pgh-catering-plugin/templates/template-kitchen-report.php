<?php
    /**
     * The template for displaying kitchen reports.
     *
     * Template Name: Kitchen Reports
     */
    if ( ! defined( 'ABSPATH' ) ) {
        exit;
    }

    get_header(); 
?>
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php echo apply_filters( 'the_content',' [report_buttons] ') ?>
        <?php echo apply_filters( 'the_content',' [kitchen_report_view] ') ?>
    </main>
</div>
<?php
    get_footer();