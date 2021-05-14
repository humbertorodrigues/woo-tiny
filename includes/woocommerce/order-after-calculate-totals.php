<?php

add_action('woocommerce_order_after_calculate_totals', 'bw_order_after_calculate_totals', 10, 2);

function bw_order_after_calculate_totals($and_taxes, $order){
    $payment_option_id = (int) bw_get_meta_field('discount', (int) get_post_meta($order->get_id(),'bw_forma_pagamento_id', true));
    $discount = (float) $payment_option_id * $order->get_subtotal() / 100;
    $order->set_discount_total(round( $discount, wc_get_price_decimals() ));
    $order->set_total(round($order->get_subtotal() - $discount, wc_get_price_decimals()));
}
