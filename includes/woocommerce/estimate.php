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
    if (!class_exists('Woo_Tiny_Estimate_Page_Content')) {
        require WOO_TINY_DIR . 'classes/class-woo-tiny-estimate-page-content.php';
    }
    $page_content = new Woo_Tiny_Estimate_Page_Content();
    $page_content->prepare_items();
    ?>
    <div class="wrap">
        <h2>Orçamentos</h2>
        <form id="events-filter" method="post">
            <input type="hidden" name="page" value="<?php echo esc_attr($_REQUEST['page']) ?>"/>
            <?php $page_content->display(); ?>
        </form>
    </div>
    <?php
}

function woo_tiny_estimate_generate_pdf($estimate_id)
{
    $data['estimate'] = wc_get_order($estimate_id);
    $data['items'] = $data['estimate']->get_items();
    $data['estimate_doc'] = get_post_meta($estimate_id, 'estimate', true);
    $author = get_bloginfo('name');
    $title = 'Orçamento #' . $estimate_id . ' ‹ ' . $author;
    $html = html_get_contents(WOO_TINY_DIR . 'templates/pdfs/estimative.php', $data);
    $stylesheet = file_get_contents(WOO_TINY_DIR . 'templates/pdfs/assets/css/estimate.css');
    $footer = '<img src="' . WOO_TINY_DIR . 'templates/pdfs/assets/images/estimate_footer.jpg">';
    $filename = 'orcamento' . $estimate_id . '.pdf';
    generate_pdf_by_html($filename, $html, 'D','', $footer, $stylesheet, $title, $author);
}
