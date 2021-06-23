<?php
add_shortcode('pagar_pedido', 'woo_tiny_order_payment_page');
function woo_tiny_order_payment_page()
{
    if (isset($_GET['order-pay'])) {
        $order_id = absint($_GET['order-pay']);
        $order = wc_get_order($order_id);
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if (count($available_gateways)) {
            current($available_gateways)->set_current();
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