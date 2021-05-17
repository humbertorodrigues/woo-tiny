<?php

add_action('woocommerce_order_after_calculate_totals', 'woo_tiny_order_after_calculate_totals', 10, 2);
add_action( 'init', 'woo_tiny_register_new_order_statuses' );
add_filter( 'wc_order_statuses', 'woo_tiny_new_wc_order_statuses' );
add_filter( 'wc_order_is_editable', 'woo_tiny_wc_order_is_editable', 10, 2 );
add_action('wp_ajax_woo_tiny_get_coupon', 'woo_tiny_ajax_get_coupon_by_code');

function woo_tiny_order_after_calculate_totals($and_taxes, $order){
    $payment_option_id = (int) bw_get_meta_field('discount', (int) get_post_meta($order->get_id(),'bw_forma_pagamento_id', true));
    $discount = (float) $payment_option_id * $order->get_subtotal() / 100;
    $discount += $order->get_discount_total();
    $order->set_discount_total(round( $discount, wc_get_price_decimals() ));
    $order->set_total(round($order->get_subtotal() - $discount, wc_get_price_decimals()));
}

function woo_tiny_register_new_order_statuses() {
    register_post_status( 'wc-revision', array(
        'label'                     => _x( 'Em revisão', 'Order status', 'woocommerce' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Em revisão <span class="count">(%s)</span>', 'Em revisão <span class="count">(%s)</span>', 'woocommerce' )
    ) );
}


function woo_tiny_new_wc_order_statuses( $order_statuses ) {
    $order_statuses['wc-revision'] = _x( 'Em revisão', 'Order status', 'woocommerce' );

    return $order_statuses;
}

function woo_tiny_wc_order_is_editable($editable, $order){
    if($order->get_status() == 'revision'){
        $editable = true;
    }
    return $editable;
}

function woo_tiny_ajax_get_coupon_by_code()
{
    $code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);
    global $woocommerce;
    $coupon = new WC_Coupon($code);
    wp_send_json($coupon->get_data(), 200);
}