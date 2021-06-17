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
    $estimate = wc_get_order($estimate_id);

    $data['estimate_id'] = '#' . $estimate->get_id();
    $data['estimate_doc'] = get_post_meta($estimate->get_id(), 'estimate', true);
    $data['estimate_created'] = convert_date($estimate->get_date_created(), false, 'full');
    $data['estimate_customer'] = woo_tiny_get_customer_name($estimate->get_id());
    $data['estimate_contact'] = get_post_meta($estimate->get_id(), 'bw_nome_contato', true);
    $data['estimate_items'] = array_map(function ($item) {
        return [
            'name' => $item->get_name(),
            'price' => wc_price($item->get_total() / $item->get_quantity()),
            'qtd' => $item->get_quantity(),
            'total' => wc_price($item->get_total()),
        ];
    }, $estimate->get_items());
    $data['estimate_discount_total'] = wc_price($estimate->get_discount_total());
    $data['estimate_total'] = wc_price($estimate->get_total()); //estimate
    $estimate_extra = get_post_meta($estimate_id, 'estimate', true);
    $data['estimate_header'] = $estimate_extra['header'];
    $data['estimate_footer'] = $estimate_extra['footer'];
    $seller = woo_tiny_get_user_field((int)get_post_meta($estimate->get_id(), 'bw_id_vendedor', true));
    $data['estimate_seller_name'] = $seller->display_name ?? '';
    $data['estimate_seller_email'] = $seller->user_email ?? '';

    $author = get_bloginfo('name');
    $title = 'Orçamento #' . $estimate_id . ' ‹ ' . $author;
    $html = html_get_contents(WOO_TINY_DIR . 'templates/pdfs/estimative.php', $data);
    $stylesheet = file_get_contents(WOO_TINY_DIR . 'templates/pdfs/assets/css/estimate.css');
    $footer = '<img src="' . WOO_TINY_DIR . 'templates/pdfs/assets/images/estimate_footer.jpg">';
    $filename = 'orcamento' . $estimate_id . '.pdf';
    generate_pdf_by_html($filename, $html, 'D', '', $footer, $stylesheet, $title, $author);
}

function woo_tiny_get_customer_name(int $estimate_id): string
{
    if(get_post_meta($estimate_id, '_billing_persontype', true) == '1'){
        $customer_name = get_post_meta($estimate_id, '_billing_first_name', true) . ' ' . wc_get_order_item_meta($estimate_id, '_billing_last_name', true);
    }else{
        $customer_name = get_post_meta($estimate_id, '_billing_company', true);
    }
    return trim($customer_name);
}
