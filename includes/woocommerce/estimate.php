<?php
add_action('admin_menu', 'woo_tiny_estimate_register_page');
function woo_tiny_estimate_register_page()
{
    add_submenu_page(
        'woocommerce',
        'Orçamentos',
        'Orçamentos',
        'manage_options',
        'woo_tiny_estimates',
        'woo_tiny_estimate_display_page',
        2
    );
}


function woo_tiny_estimate_display_page()
{
    if(!class_exists('Woo_Tiny_Estimate_Page_Content')){
        require WOO_TINY_DIR . 'classes/class-woo-tiny-estimate-page-content.php';
    }
    $page_content = new Woo_Tiny_Estimate_Page_Content();
    $page_content->prepare_items();
    ?>
    <div class="wrap">
        <h2>Orçamentos</h2>
        <form id="events-filter" method="post">
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>" />
            <?php $page_content->display(); ?>
        </form>
    </div>
    <?php
}

function woo_tiny_estimate_generate_pdf($estimate_id){
    $estimate = wc_get_order($estimate_id);
    $items = $estimate->get_items();
    $estimate_doc = get_post_meta($estimate_id, 'estimate', true);
    ob_start();
    include WOO_TINY_DIR . 'templates/pdfs/estimative.php';
    $html = ob_get_clean();
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->WriteHTML($html);
    $filename = 'Orçamento #' . $estimate_id . '-' . get_bloginfo('name') . '.pdf';
    $mpdf->Output($filename, \Mpdf\Output\Destination::INLINE);
    exit;
}
