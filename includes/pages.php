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
        include WOO_TINY_DIR . 'templates/pages/payment-order.php';
    }
}