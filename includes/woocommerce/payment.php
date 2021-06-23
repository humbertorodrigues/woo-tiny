<?php
add_filter('woocommerce_valid_order_statuses_for_payment', 'woo_tiny_order_valid_statuses_for_payment', 10);
add_filter('woocommerce_get_checkout_payment_url', 'woo_tiny_get_checkout_payment_url', 10, 2);
add_filter('generate_rewrite_rules', 'woo_tiny_add_rule_seller_pay');
add_filter('woocommerce_is_checkout', 'woo_tiny_order_is_checkout');
add_filter('query_vars', 'woo_tiny_add_query_var_seller_pay');
add_action('template_redirect', 'woo_tiny_include_seller_pay');

function woo_tiny_include_seller_pay()
{
    $bw_seller_pay = intval(get_query_var('bw_seller_pay'));
    if ($bw_seller_pay) {
        $order_id = absint(get_query_var('order-pay'));
        $order = wc_get_order($order_id);
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if (count($available_gateways)) {
            current($available_gateways)->set_current();
        }
        include WOO_TINY_DIR . 'templates/pages/payment-order.php';
        die;
    }
}

function woo_tiny_add_query_var_seller_pay($query_vars)
{
    $query_vars[] = 'bw_seller_pay';
    return $query_vars;
}

function woo_tiny_order_is_checkout($condition)
{
    $bw_seller_pay = intval(get_query_var('bw_seller_pay', 0));
    if ($bw_seller_pay) {
        $condition = true;
        wc_maybe_define_constant('WOOCOMMERCE_CHECKOUT', true);
        if (isset($_GET['order-pay'])) {
            set_query_var('order-pay', $_GET['order-pay']);
        }
    }
    return $condition;
}

function woo_tiny_add_rule_seller_pay($wp_rewrite)
{
    $wp_rewrite->rules = array_merge(
        ['pagar-pedido/?$' => 'index.php?bw_seller_pay=1'],
        $wp_rewrite->rules
    );
    return $wp_rewrite;
}

function woo_tiny_get_checkout_payment_url($pay_url, $order)
{
    if ($order->has_status(['revision', 'wallet'])) {
        $query = http_build_query([
            'bw_seller_pay' => true,
            'pay_for_order' => true,
            'order-pay' => $order->get_id(),
            'key' => $order->get_order_key(),
        ]);
        $pay_url = site_url('index.php?' . $query);
    }
    return $pay_url;
}

function woo_tiny_order_valid_statuses_for_payment($valid_order_statuses)
{
    $valid_order_statuses[] = 'revision';
    $valid_order_statuses[] = 'wallet';
    return $valid_order_statuses;
}
