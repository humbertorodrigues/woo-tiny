<?php
add_filter('woocommerce_coupon_data_tabs', 'woo_tiny_coupon_data_tab');
add_action('woocommerce_coupon_data_panels', 'woo_tiny_coupon_data_panel', 10, 2);
add_action('woocommerce_coupon_options_save', 'woo_tiny_coupon_data_save', 10, 2);
add_action('woocommerce_order_status_changed', 'woo_tiny_coupon_save_extra_data_in_order', 10, 4);

function woo_tiny_coupon_data_tab($tabs)
{
    $tabs['woo_tiny'] = [
        'label' => 'Opções Bueno Wines',
        'target' => 'woo_tiny_coupon_data',
        'class' => '',
    ];
    return $tabs;
}

function woo_tiny_coupon_data_panel($coupon_id, $coupon)
{
    ?>
    <div id="woo_tiny_coupon_data" class="panel woocommerce_options_panel">
        <?php
        $sellers = ['' => 'Selecione um vendedor...'];
        array_map(function ($user) use (&$sellers) {
            $sellers[$user->ID] = $user->display_name;
        }, get_users(['role__in' => ['vendedores_bw']]));

        woocommerce_wp_select(
            array(
                'id' => 'woo_tiny_seller_id',
                'label' => 'Selecionar vendedor',
                'options' => $sellers,
                'value' => get_post_meta($coupon_id, 'woo_tiny_seller_id', true),
            )
        );

        $channels = ['' => 'Selecione um canal...'];
        array_map(function ($channel) use (&$channels) {
            $channels[$channel->ID] = $channel->post_title;
        }, get_posts(['post_type' => 'canal_venda', 'numberposts' => -1]));
        woocommerce_wp_select(
            array(
                'id' => 'woo_tiny_channel_id',
                'label' => 'Selecionar canal',
                'options' => $channels,
                'value' => get_post_meta($coupon_id, 'woo_tiny_channel_id', true),
            )
        );
        ?>
    </div>
    <?php
}

function woo_tiny_coupon_data_save($post_id, $coupon)
{
    $seller_id = $_POST['woo_tiny_seller_id'] ?? '';
    $channel_id = $_POST['woo_tiny_channel_id'] ?? '';
    update_post_meta($post_id, 'woo_tiny_seller_id', $seller_id);
    update_post_meta($post_id, 'woo_tiny_channel_id', $channel_id);
}


function woo_tiny_coupon_save_extra_data_in_order($order_id, $status_from, $status_to, $order)
{
    $order = wc_get_order($order_id);
    $order_items = $order->get_items('coupon');
    foreach ($order_items as $item) {
        $coupon_post_obj = get_page_by_title($item->get_name(), OBJECT, 'shop_coupon');
        $coupon_id = $coupon_post_obj->ID;
        $seller_id = get_post_meta($order_id, 'bw_id_vendedor', true) ?? '';
        if ($seller_id == '') {
            $seller_id = get_post_meta($coupon_id, 'woo_tiny_seller_id', true) ?? $seller_id;
        }
        $channel_id = woo_tiny_coupon_get_channel_by_coupon_id($coupon_id, get_post_meta($order_id, 'bw_canal_venda', true));
        if ($channel_id != '') {
            if ($seller_id != '') {
                update_post_meta($order_id, "bw_id_vendedor", $seller_id);
            }
            update_post_meta($order_id, "bw_canal_venda", $channel_id);
            update_post_meta($order_id, "bw_canal_venda_descricao", get_the_title($channel_id));
        }
    }
    woo_tiny_coupon_set_channel_in_rule_user_email($order_id);
}

function woo_tiny_coupon_set_channel_in_rule_user_email($order_id)
{
    $emails = [
        '@agilize.com.br',
        '@appsistemas.com.br',
        '@cappta.com.br',
        '@conube.com.br',
        '@deliverymuch.com.br',
        '@equals.com.br',
        '@linkedgourmet.com.br',
        '@mlabs.com.br',
        '@mundipagg.com',
        '@menew.com.br',
        '@nodis.com.br',
        '@pagar.me',
        '@rhsoftware.com.br',
        '@stone.com.br',
        '@taginfraestrutura.com.br',
        '@trinks.com',
        '@vitta.me',
        '@vhsys.com.br',
        '@zurich.com',
        '@br.zurich.com',
    ];

    $channel_id = get_post_meta($order_id, "bw_canal_venda", true);
    $email = get_post_meta($order_id, '_billing_email', true);
    $email = substr($email, strpos($email, '@'));

    if ($channel_id == '' && in_array($email, $emails)) {
        $channel_id = 95290;
        update_post_meta($order_id, "bw_canal_venda", $channel_id);
        update_post_meta($order_id, "bw_canal_venda_descricao", get_the_title($channel_id));
        return true;
    }
    return false;
}

function woo_tiny_coupon_get_channel_by_coupon_id($coupon_id, $channel_id = '')
{
    if ($channel_id == '') {
        $channel_id = get_post_meta($coupon_id, 'woo_tiny_channel_id', true) ?? $channel_id;
        if ($channel_id == '') {
            global $wpdb;
            $query = "SELECT posts.ID as id FROM {$wpdb->posts} AS posts INNER JOIN {$wpdb->postmeta} AS postmeta ON (postmeta.post_id = posts.ID AND postmeta.meta_key = 'woo_tiny_channel_id') WHERE posts.post_type='shop_coupon' AND postmeta.meta_value = 95290";
            $coupon_ids = array_map(function ($item) {
                return absint($item->id);
            }, $wpdb->get_results($query));
            if(in_array($coupon_id, $coupon_ids)) {
                $channel_id = 95290;
            }
        }
    }
    return $channel_id;
}
