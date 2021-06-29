<?php
add_shortcode('pagar_pedido', 'woo_tiny_order_payment_page');
add_shortcode('relatorio_canais', 'woo_tiny_channel_report_page');
function woo_tiny_order_payment_page()
{
    if (isset($_GET['order-pay'])) {
        $order_id = absint(get_query_var('order-pay'));
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

function woo_tiny_channel_report_page(){
    if(!class_exists('WC_Report_Woo_Tiny_Channel_List')){
        include WOO_TINY_DIR . 'classes/class-woo-tiny-report-channel-list.php';
    }
    if(!class_exists('WP_Screen')){
        include ABSPATH . 'wp-admin/includes/class-wp-screen.php';
    }
    if (!function_exists('get_column_headers')) {
        include ABSPATH . 'wp-admin/includes/screen.php';
    }
    if(!function_exists('convert_to_screen')){
        include ABSPATH . 'wp-admin/includes/template.php';
        include ABSPATH . 'wp-load.php';
    }
    (new WC_Report_Woo_Tiny_Channel_List())->output_report();
}