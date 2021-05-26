<?php
add_filter("manage_edit-shop_order_columns", "woo_tiny_shop_order_edit_columns");
add_action("manage_posts_custom_column", "woo_tiny_shop_order_custom_columns");
add_action( 'woocommerce_admin_order_data_after_shipping_address', 'woo_tiny_order_data_seller');

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
    $payment_method = bw_get_meta_field('bw_forma_pagamento_id');
    $payment_method = ($payment_method != '') ? get_the_title($payment_method) : $payment_method;
    $channel = bw_get_meta_field('bw_canal_venda');
    $channel = ($channel != '') ? get_the_title($channel) : $channel;

    include WOO_TINY_DIR . 'templates/post-types/shop-order/meta-seller-data.php';
}