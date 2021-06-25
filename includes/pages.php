<?php
add_shortcode('pagar_pedido', 'woo_tiny_order_payment_page');
function woo_tiny_order_payment_page()
{
    if (isset($_GET['order-pay'])) {
        $order_id = absint(get_query_var('order-pay'));
        $order = wc_get_order($order_id);
        $payment_id = absint(get_post_meta($order_id, 'bw_forma_pagamento_id', true));
        $gateway_id = get_post_meta($payment_id, 'payment_gateway', true);
        if(!empty($gateway_id)) {
            $available_gateways = array_filter(WC()->payment_gateways->payment_gateways(), function ($gateway) use ($gateway_id) {
                return $gateway->id == $gateway_id;
            });
            WC()->payment_gateways->set_current_gateway($available_gateways);
        }else{
            $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
            if (count($available_gateways)) {
                current($available_gateways)->set_current();
            }
        }
        wc_get_template(
            'checkout/form-pay.php',
            array(
                'order' => $order,
                'available_gateways' => $available_gateways,
                'order_button_text' => apply_filters('woocommerce_pay_order_button_text', __('Pay for order', 'woocommerce')),
            )
        );
    }
}