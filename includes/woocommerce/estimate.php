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
    $title = get_bloginfo('name');
    ob_start();
    include WOO_TINY_DIR . 'templates/pdfs/estimative.php';
    $html = ob_get_clean();
    $mpdf = new \Mpdf\Mpdf();
    $mpdf->SetTitle(\Mpdf\Utils\UtfString::strcode2utf('Orçamento #' . $estimate_id . ' ‹ ' . $title));
    $mpdf->SetAuthor(\Mpdf\Utils\UtfString::strcode2utf($title));
    $mpdf->SetCreator(\Mpdf\Utils\UtfString::strcode2utf($title));
    $mpdf->SetHTMLFooter('<img src="' . WOO_TINY_DIR . 'templates/pdfs/assets/images/estimate_footer.jpg">');
    $stylesheet = file_get_contents(WOO_TINY_DIR . 'templates/pdfs/assets/css/estimate.css');
    $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
    $mpdf->WriteHTML($html, \Mpdf\HTMLParserMode::HTML_BODY);
    $filename = 'orcamento-' . $estimate_id . '.pdf';
    $mpdf->Output($filename, \Mpdf\Output\Destination::DOWNLOAD);
    exit;
}
