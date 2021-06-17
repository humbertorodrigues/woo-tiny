<?php
add_filter('woocommerce_admin_reports', 'woo_tiny_admin_reports');
add_filter('woocommerce_reports_charts', 'woo_tiny_admin_reports');
add_filter('wc_admin_reports_path', 'woo_tiny_reports_path', 10, 3);
add_filter('woocommerce_admin_report_customer_list_user_query_args', 'woo_tiny_admin_report_customer_list');

function woo_tiny_admin_reports($reports)
{
    $reports['woo_tiny_sellers'] = [
        'title' => 'Vendedores',
        'reports' => [
            'woo_tiny_seller_list' => [
                'title' => 'Lista de Vendedores',
                'description' => '',
                'hide_title' => true,
                'callback' => ['WC_Admin_Reports', 'get_report'],
            ]
        ],
    ];
    $reports['woo_tiny_channels'] = [
        'title' => 'Canais',
        'reports' => [
            'woo_tiny_channel_list' => [
                'title' => 'Lista de Canais',
                'description' => '',
                'hide_title' => true,
                'callback' => ['WC_Admin_Reports', 'get_report'],
            ]
        ],
    ];
    $woo_tiny_reports_orders = [
        'woo_tiny_sales_by_seller' => [
            'title' => 'Vendas por vendedor',
            'description' => '',
            'hide_title' => true,
            'callback' => ['WC_Admin_Reports', 'get_report'],
        ],
        'woo_tiny_sales_by_channel' => [
            'title' => 'Vendas por canal',
            'description' => '',
            'hide_title' => true,
            'callback' => ['WC_Admin_Reports', 'get_report'],
        ],
        'woo_tiny_sales_by_payment_option' => [
            'title' => 'Vendas por forma de pagamento',
            'description' => '',
            'hide_title' => true,
            'callback' => ['WC_Admin_Reports', 'get_report'],
        ],
    ];
    if(isset($reports['orders'])) {
        $reports_orders = array_chunk($reports['orders']['reports'], 3, true);
        $reports['orders']['reports'] = array_merge(array_shift($reports_orders), $woo_tiny_reports_orders, ...$reports_orders);
    }

    return $reports;
}

function woo_tiny_reports_path($path, $name)
{
    $prefix = 'woo-tiny-';
    if (str_contains($prefix, $name)) {
        $name = str_replace($prefix, '', $name);
        if(in_array($name, ['seller-list', 'sales-by-seller', 'sales-by-channel', 'sales-by-payment-option', 'channel-list'])){
            $path = WOO_TINY_DIR . 'classes/class-woo-tiny-report-' . $name . '.php';
        }
    }
    return $path;
}

function woo_tiny_admin_report_customer_list($query)
{
    $seller_users = new WP_User_Query(
        [
            'role' => 'vendedores_bw',
            'fields' => 'ID',
        ]
    );
    $supervisor_users = new WP_User_Query(
        [
            'role' => 'bw_supervisor',
            'fields' => 'ID',
        ]
    );
    $query['exclude'] = array_merge($query['exclude'], $seller_users->get_results(), $supervisor_users->get_results());
    return $query;
}