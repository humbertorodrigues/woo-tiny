<?php
add_filter("manage_edit-shop_order_columns", "woo_tiny_shop_order_edit_columns");
add_action("manage_posts_custom_column", "woo_tiny_shop_order_custom_columns");
add_action('woocommerce_admin_order_data_after_order_details', 'woo_tiny_order_data_seller');
add_action('woocommerce_admin_order_data_after_shipping_address', 'woo_tiny_order_documents');
add_action('woocommerce_update_order', 'woo_tiny_order_save_meta', 10, 2);
add_action('woocommerce_update_order', 'woo_tiny_admin_channel_update', 10, 2);
add_filter('woocommerce_admin_order_actions', 'woo_tiny_admin_order_actions', 10, 2);
add_action('restrict_manage_posts', 'woo_tiny_shop_order_extra_tablenav', 10, 2);
add_action('disable_months_dropdown', 'woo_tiny_shop_order_disable_months_dropdown', 10, 2);
add_filter('request', 'woo_tiny_shop_order_extra_filter');
add_filter('bulk_actions-edit-shop_order', 'woo_tiny_shop_order_bulk_actions');

function woo_tiny_shop_order_bulk_actions($actions){
    $actions['export_pdf'] = 'Exportar PDF';
    
    return $actions;
}
function woo_tiny_shop_order_extra_filter($query_vars)
{
    global $pagenow;

    if ('edit.php' == $pagenow && 'shop_order' == $query_vars['post_type']) {
        if (empty($_GET['start_date'])) {
            $_GET['start_date'] = date('Y-m-d', strtotime('-1 month'));
        }

        if (empty($_GET['end_date'])) {
            $_GET['end_date'] = date('Y-m-d');
        }

        $query_vars['date_query'] = [
            'column' => 'post_date',
            'after' => $_GET['start_date'],
            'before' => $_GET['end_date'],
            'inclusive' => true,
        ];

        if (!isset($query_vars['meta_query'])) {
            $query_vars['meta_query'] = [];
        }

        if (!empty($_GET['regional'])) {
            $seller_ids = get_users([
                'role__in' => ['vendedores_bw'],
                'meta_key' => 'bw_regional',
                'meta_value' => $_GET['regional'],
                'meta_compare' => '=',
                'fields' => 'ID'
            ]);
            $_GET['seller_id'] = $seller_ids;
        }

        if (!empty($_GET['seller_id'])) {
            $meta_seller_compare = '=';
            if(is_array($_GET['seller_id'])){
                $_GET['seller_id'] = implode(',', $_GET['seller_id']);
                $meta_seller_compare = 'IN';
            }
            $query_vars['meta_query'][] = [
                'relation' => 'AND',
                [
                    'key' => 'bw_id_vendedor',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => 'bw_id_vendedor',
                    'compare' => $meta_seller_compare,
                    'value' => $_GET['seller_id']
                ]
            ];
        }

        if (!empty($_GET['channel_id'])) {
            $query_vars['meta_query'][] = [
                'relation' => 'AND',
                [
                    'key' => 'bw_canal_venda',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => 'bw_canal_venda',
                    'compare' => '=',
                    'value' => $_GET['channel_id']
                ]
            ];
        }
    }

    return $query_vars;
}

function woo_tiny_shop_order_disable_months_dropdown($disabled, $post_type)
{
    if ($post_type === 'shop_order') {
        $disabled = true;
    }
    return $disabled;
}

function woo_tiny_shop_order_extra_tablenav($post_type, $which)
{
    global $pagenow;
    if ('edit.php' == $pagenow && $post_type === 'shop_order' && 'top' === $which) {
        $regionais = [
            'REGIONAL SUL' => [
                'PR' => 'PR',
                'RS' => 'RS',
                'SC' => 'SC',
            ],
            'REGIONAL SP' => [
                'SPC' => 'SPC',
                'SPI' => 'SPI',
            ],
            'REGIONAL SUDESTE' => [
                'ES' => 'ES',
                'MG' => 'MG',
                'RJ' => 'RJ',
            ],
            'REGIONAL NORDESTE' => [
                'AL' => 'AL',
                'BA' => 'BA',
                'CE' => 'CE',
                'MA' => 'MA',
                'PB' => 'PB',
                'PE' => 'PE',
                'PI' => 'PI',
                'RGN' => 'RGN',
                'SE' => 'SE',
            ],
            'REGIONAL CENTRO NORTE' => [
                'AC' => 'AC',
                'AM' => 'AM',
                'AP' => 'AP',
                'DF' => 'DF',
                'GO' => 'GO',
                'MS' => 'MS',
                'MT' => 'MT',
                'PA' => 'PA',
                'RO' => 'RO',
                'RR' => 'RR',
                'TO' => 'TO',
            ],
        ];
        $sellers = get_users([
                'role__in' => ['vendedores_bw'],
        ]);
        $channels = get_posts([
            'post_type' => 'canal_venda',
            'numberposts' => -1,
        ]);
        if (empty($_GET['start_date'])) {
            $_GET['start_date'] = date('Y-m-d', strtotime('-1 month'));
        }

        if (empty($_GET['end_date'])) {
            $_GET['end_date'] = date('Y-m-d');
        }
        ?>
        <select class="wc-enhanced-select" name="regional" data-placeholder="Selecione um regional..."
                data-allow_clear="true">
            <option value="">Selecione uma regional...</option>
            <?php foreach ($regionais as $regional => $estados): $estados = implode(', ', $estados); ?>
                <option value="<?= $regional ?>"
                        <?php if ($regional == $_GET['regional']): ?>selected="selected" <?php endif; ?>><?= $regional . ' (' . $estados . ')' ?></option>
            <?php endforeach; ?>
        </select>
        <select class="wc-enhanced-select" name="seller_id" data-placeholder="Selecione um vendedor..."
                data-allow_clear="true">
            <option value="">Selecione um vendedor...</option>
            <?php foreach ($sellers

            as $seller): ?>
            <option value="<?php echo esc_attr($seller->ID); ?>"
                    <?php if ($seller->ID == $_GET['seller_id']): ?>selected="selected" <?php endif; ?>><?php echo $seller->display_name; ?>
            <option>
                <?php endforeach; ?>
        </select>
        <select class="wc-enhanced-select" name="channel_id" data-placeholder="Selecione um canal..."
                data-allow_clear="true">
            <option value="">Selecione um canal...</option>
            <?php foreach ($channels

            as $channel): ?>
            <option value="<?php echo esc_attr($channel->ID); ?>"
                    <?php if ($channel->ID == $_GET['channel_id']): ?>selected="selected" <?php endif; ?>><?php echo $channel->post_title; ?>
            <option>
                <?php endforeach; ?>
        </select>
        <input type="text" size="11" placeholder="dd/mm/yyyy"
               value="<?php echo esc_attr(wp_unslash($_GET['start_date'])) ?>"
               name="start_date" class="range_datepicker from"
               autocomplete="off"/>
        <span>&ndash;</span>
        <input type="text" size="11" placeholder="dd/mm/yyyy"
               value="<?php echo esc_attr(wp_unslash($_GET['end_date'])); ?>"
               name="end_date" class="range_datepicker to"
               autocomplete="off"/>
        <script>
            jQuery(function ($) {
                $('.range_datepicker').datepicker({
                    dateFormat: 'yy-mm-dd',
                    maxDate: new Date()
                });
            });
        </script>
        <?php
    }
}

function woo_tiny_admin_order_actions($actions, $order)
{
    if ($order->has_status(['revision', 'wallet'])) {
        $actions['processing'] = [
            'url' => wp_nonce_url(admin_url('admin-ajax.php?action=woocommerce_mark_order_status&status=processing&order_id=' . $order->get_id()), 'woocommerce-mark-order-status'),
            'name' => __('Processing', 'woocommerce'),
            'action' => 'processing',
        ];
    }
    return $actions;
}

function woo_tiny_shop_order_edit_columns($columns)
{
    unset($columns['wc_actions']);
    $columns['woo_tiny_code'] = 'Código tiny';
    $columns['woo_tiny_seller'] = 'Vendedor';
    $columns['woo_tiny_payment_method'] = 'Forma de Pagamento';
    $columns['woo_tiny_channel'] = 'Canal';
    $columns['wc_actions'] = 'Ações';
    return $columns;
}

function woo_tiny_shop_order_custom_columns($column)
{
    switch ($column) {
        case 'woo_tiny_code':
            $code = bw_get_meta_field('codigo_tiny');
            echo ($code != '') ? "<a target=\"_blank\" href=\"https://erp.tiny.com.br/vendas#edit/" . $code . "\">Ver pedido ($code) </a>" : $code;
            break;
        case 'woo_tiny_seller':
            $seller_id = bw_get_meta_field('bw_id_vendedor');
            $seller = get_userdata($seller_id);
            echo $seller ? $seller->display_name : '';
            break;
        case 'woo_tiny_payment_method':
            $payment_method = bw_get_meta_field('bw_forma_pagamento_id');
            echo ($payment_method != '') ? get_the_title($payment_method) : $payment_method;
            break;
        case 'woo_tiny_channel':
            $channel = bw_get_meta_field('bw_canal_venda');
            echo ($channel != '') ? get_the_title($channel) : $channel;
            break;
        default:
            break;
    }
}

function woo_tiny_order_data_seller($order)
{
    $seller_id = bw_get_meta_field('bw_id_vendedor');
    $seller = get_userdata($seller_id);
    $seller = $seller ? $seller->display_name : '';
    $payment_method_id = bw_get_meta_field('bw_forma_pagamento_id');
    $payment_method = ($payment_method_id != '') ? get_the_title($payment_method_id) : '';
    $channel_id = bw_get_meta_field('bw_canal_venda');
    $channel = ($channel_id != '') ? get_the_title($channel_id) : '';
    $user = wp_get_current_user();
    $channels = get_posts([
        'post_type' => 'canal_venda',
        'numberposts' => -1
    ]);
    if ($order->has_status(['pending', 'on-hold', 'revision', 'wallet'])) {
        include WOO_TINY_DIR . 'templates/post-types/shop-order/button-order-processing.php';
    }
    if (in_array('bw_supervisor', $user->roles)) {
        $sellers = get_users(['role__in' => ['vendedores_bw']]);
        $channels = get_posts(array(
            'post_type' => 'canal_venda',
            'numberposts' => -1
        ));
        $payment_methods = get_posts(array(
            'post_type' => 'bw-payment-options',
            'numberposts' => -1
        ));
        include WOO_TINY_DIR . 'templates/post-types/shop-order/meta-seller-form.php';
    } else {
        include WOO_TINY_DIR . 'templates/post-types/shop-order/meta-seller-data.php';
    }
    $installments = get_post_meta($order->get_id(), 'bw_order_installments', true);
    $installments = !empty($installments) ? $installments : [];
    include WOO_TINY_DIR . 'templates/post-types/shop-order/meta-seller-installments.php';
}

function woo_tiny_order_save_meta($order_id, $order)
{
    $user = wp_get_current_user();

    if (in_array('bw_supervisor', $user->roles)) {
        $default_fields = [
            'bw_id_vendedor' => '',
            'bw_canal_venda' => '',
            'bw_forma_pagamento_id' => '',
        ];

        if (array_key_exists('canal_venda', $_POST)) {
            $_POST['bw_canal_venda'] = $_POST['canal_venda'];
        }

        if (array_key_exists('bw_payment_option', $_POST)) {
            $_POST['bw_forma_pagamento_id'] = $_POST['bw_payment_option'];
        }

        $data = wp_parse_args($_POST, $default_fields);
        foreach ($default_fields as $key => $val) {
            update_post_meta($order_id, $key, $data[$key]);
        }
    }
}

function woo_tiny_admin_channel_update($order_id, $order)
{
    if (array_key_exists('bw_canal_venda', $_POST)) {
        $bw_canal_venda = $_POST['bw_canal_venda'];
        $descricao_canal = get_the_title($bw_canal_venda);
        update_post_meta($order_id, "bw_canal_venda", $bw_canal_venda);
        update_post_meta($order_id, "bw_canal_venda_descricao", $descricao_canal);
    }
}

function woo_tiny_order_documents($order)
{
    $attachments = get_posts([
        'post_type' => 'attachment',
        'numberposts' => -1,
        'post_status' => 'private',
        'post_parent' => $order->get_id()
    ]);

    include WOO_TINY_DIR . 'templates/post-types/shop-order/documents-data.php';
}