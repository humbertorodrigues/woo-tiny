<?php
add_action('init', 'woo_tiny_add_woocommerce_roles');

function woo_tiny_add_woocommerce_roles()
{
    $capabilities = [
        'edit_shop_order' => true,
        'edit_shop_orders' => true,
        'edit_published_shop_orders' => true,
        'edit_private_shop_orders' => true,
        'edit_others_shop_orders' => true,
        'read_shop_order' => true,
        'shop_order' => true,
        'woocommerce_order_itemmeta' => true,
        'woocommerce_order_items' => true,
        'woocommerce_view_order' => true,
        'edit_shop_order_terms' => true,
    ];
    add_role('bw_supervisor', 'Supervisor', [$capabilities]);
}
