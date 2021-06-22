<?php
add_filter("manage_edit-shop_order_columns", "woo_tiny_shop_order_edit_columns");
add_action("manage_posts_custom_column", "woo_tiny_shop_order_custom_columns");
add_action( 'woocommerce_admin_order_data_after_order_details', 'woo_tiny_order_data_seller');
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'woo_tiny_order_documents');
add_action('woocommerce_update_order', 'woo_tiny_order_save_meta', 10, 2);
add_action('woocommerce_update_order', 'woo_tiny_admin_channel_update', 10, 2);

function woo_tiny_shop_order_edit_columns($columns)
{
    unset($columns['wc_actions']);
    $columns['woo_tiny_code'] = 'Código tiny';
    $columns['woo_tiny_seller'] = 'Vendedor';
    $columns['woo_tiny_payment_method'] = 'Forma de Pagamento';
    $columns['woo_tiny_channel'] = 'Canal';
    $columns['wc_actions'] = 'Ações';
    return $columns;
}

function woo_tiny_shop_order_custom_columns($column)
{
    switch ($column) {
        case 'woo_tiny_code':
            $code = bw_get_meta_field('codigo_tiny');
                echo ($code != '') ? "<a target=\"_blank\" href=\"https://erp.tiny.com.br/vendas#edit/".$code."\">Ver pedido ($code) </a>" : $code;
            break;
        case 'woo_tiny_seller':
            $seller_id = bw_get_meta_field('bw_id_vendedor');
            $seller = get_userdata($seller_id);
            echo $seller ? $seller->display_name : '';
            break;
        case 'woo_tiny_payment_method':
            $payment_method = bw_get_meta_field('bw_forma_pagamento_id');
            echo ($payment_method != '') ? get_the_title($payment_method) : $payment_method;
            break;
        case 'woo_tiny_channel':
            $channel = bw_get_meta_field('bw_canal_venda');
            echo ($channel != '') ? get_the_title($channel) : $channel;
            break;
        default:
            break;
    }
}

function woo_tiny_order_data_seller($order){
    $seller_id = bw_get_meta_field('bw_id_vendedor');
    $seller = get_userdata($seller_id);
    $seller = $seller ? $seller->display_name : '';
    $payment_method_id = bw_get_meta_field('bw_forma_pagamento_id');
    $payment_method = ($payment_method_id != '') ? get_the_title($payment_method_id) : '';
    $channel_id = bw_get_meta_field('bw_canal_venda');
    $channel = ($channel_id != '') ? get_the_title($channel_id) : '';
    $user = wp_get_current_user();
    $channels = get_posts([
        'post_type' => 'canal_venda',
        'numberposts' => -1
    ]);
    if(in_array('bw_supervisor', $user->roles)){
        $sellers = get_users(['role__in' => ['vendedores_bw']]);
        $channels = get_posts(array(
            'post_type' => 'canal_venda',
            'numberposts' => -1
        ));
        $payment_methods = get_posts(array(
            'post_type' => 'bw-payment-options',
            'numberposts' => -1
        ));
        include WOO_TINY_DIR . 'templates/post-types/shop-order/meta-seller-form.php';
    }else {
        include WOO_TINY_DIR . 'templates/post-types/shop-order/meta-seller-data.php';
    }
    $installments = get_post_meta($order->get_id(), 'bw_order_installments', true);
    $installments = !empty($installments) ? $installments : [];
    include WOO_TINY_DIR . 'templates/post-types/shop-order/meta-seller-installments.php';
}

function woo_tiny_order_save_meta($order_id, $order){
    $user = wp_get_current_user();

    if(in_array('bw_supervisor', $user->roles)) {
        $default_fields = [
            'bw_id_vendedor' => '',
            'bw_canal_venda' => '',
            'bw_forma_pagamento_id' => '',
        ];

        if(array_key_exists('canal_venda', $_POST)){
            $_POST['bw_canal_venda'] = $_POST['canal_venda'];
        }

        if(array_key_exists('bw_payment_option', $_POST)){
            $_POST['bw_forma_pagamento_id'] = $_POST['bw_payment_option'];
        }

        $data = wp_parse_args($_POST, $default_fields);
        foreach ($default_fields as $key => $val) {
            update_post_meta($order_id, $key, $data[$key]);
        }
    }
}
function woo_tiny_admin_channel_update($order_id, $order){
    if(array_key_exists('bw_canal_venda', $_POST)){
        $bw_canal_venda = $_POST['bw_canal_venda'];
        $descricao_canal = get_the_title($bw_canal_venda);
        update_post_meta($order_id, "bw_canal_venda", $bw_canal_venda);
        update_post_meta($order_id, "bw_canal_venda_descricao", $descricao_canal);
    }
}
function woo_tiny_order_documents($order){
    $attachments = get_posts([
        'post_type'   => 'attachment',
        'numberposts' => -1,
        'post_status' => 'private',
        'post_parent' => $order->get_id()
    ]);

    include WOO_TINY_DIR . 'templates/post-types/shop-order/documents-data.php';
}