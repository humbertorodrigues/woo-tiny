<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

wc_get_template(
    'checkout/form-pay.php',
    array(
        'order' => $order,
        'available_gateways' => $available_gateways,
        'order_button_text' => apply_filters('woocommerce_pay_order_button_text', __('Pay for order', 'woocommerce')),
    )
);
