<?php
add_action('wp_ajax_woo_tiny_get_customer', 'woo_tiny_ajax_get_customer_by_vat');
global $woocommerce;


/**
 * @todo adicionar opção de preencher endereço de entrega
*/


function woo_tiny_ajax_get_customer_by_vat()
{
    if('POST' != $_SERVER['REQUEST_METHOD']) wp_send_json_error('Requisição inválida');
    $vat = filter_input(INPUT_POST, 'vat', FILTER_SANITIZE_STRING);
    if(strlen(only_numbers($vat)) < 11) wp_send_json_error('CPF OU CNPJ inválido');
    try {
        $customer = woo_tiny_get_user_id_by_cpf_cnpj($vat);
        if($customer) {
            $private_keys = [
                'wp_user_level',
                'wp_capabilities',
                'wc_last_active',
                'use_ssl',
                'syntax_highlighting',
                'show_admin_bar_front',
                'rich_editing',
                'nickname',
                'mailchimp_woocommerce_is_subscribed',
                'locale',
                'last_update',
                'googleplus',
                'facebook',
                'dismissed_wp_pointers',
                'description',
                'comment_shortcuts',
                'bw_id_vendedor_tiny_vinicola',
                'bw_id_vendedor_tiny_bw',
                'admin_color',
                '_yoast_wpseo_profile_updated',
                '_order_count',
                'twitter',
            ];
            $customer_data = get_user_meta($customer);
            foreach ($private_keys as $key){
                unset($customer_data[$key]);
            }
            wp_send_json_success($customer_data);
        }
        wp_send_json_error('Usuário não cadastrado');
    } catch (Exception $e) {
        wp_send_json_error($e->getMessage());
    }
}

function woo_tiny_get_user_id_by_cpf_cnpj($cpf_cnpj){
    global $wpdb;
    $meta_value = only_numbers($cpf_cnpj);
    $meta_key = strlen($meta_value) > 11 ? 'billing_cnpj' : 'billing_cpf';
    $wp_sql_prepare = $wpdb->prepare( "SELECT user_id FROM {$wpdb->prefix}usermeta WHERE meta_key='%s' AND (meta_value='%s' OR meta_value='%s')", $meta_key, $meta_value, format_cpf_or_cnpj($cpf_cnpj));
    $user_row = $wpdb->get_results($wp_sql_prepare);
    if (empty($user_row)) return false;
    return $user_row[0]->user_id;
}

function woo_tiny_save_customer_meta_data($customer_data){
    $customer_id = get_user_by_email($customer_data['email']);
    if(!$customer_id){
        $customer_id = woo_tiny_get_user_id_by_cpf_cnpj($customer_data['vat']) ?? wc_create_new_customer($customer_data['email']);
    }
    $customer_id = is_a($customer_id, 'WP_User') ? $customer_id->ID : $customer_id;
    try {
        $customer = new WC_Customer($customer_id);
        $customer_store_data = $customer->get_data();
        foreach ($customer_store_data as $prop => $val){
            $set = 'set_';
            if(array_key_exists($prop, $customer_data)) {
                $set .= $prop;
                if(is_array($customer_store_data[$prop])){
                    $props = array_keys($customer_store_data[$prop]);
                    foreach ($props as $p){
                        $s = $set . '_' . $p;
                        $customer->$s($customer_data[$prop][$p] ?? '');
                        unset($s);
                    }
                }else{
                    $customer->$set($customer_data[$prop] ?? '');
                }
            }
        }
        $customer->save();
        woo_tiny_save_customer_extra_meta($customer, $customer_data);
        return $customer;
    } catch (Exception $e) {
        return false;
    }
}

function woo_tiny_save_customer_extra_meta($customer, $customer_data)
{
    if(is_a($customer, 'WC_Customer')) $customer = $customer->get_id();
    $vat = only_numbers($customer_data['vat']);
    $vat_key = 'billing_cpf';
    $doc_key = 'billing_rg';
    $doc_value = $customer_data['rg'] ?? '';
    $person_type = 1;
    if(strlen($vat) > 11){
        $vat_key = 'billing_cnpj';
        $doc_key = 'billing_ie';
        $doc_value = $customer_data['ie'] ?? '';
        $person_type = 2;
    }

    if(is_int($customer) && $customer > 0){
        update_user_meta($customer, $vat_key, format_cpf_or_cnpj($customer_data['vat']));
        update_user_meta($customer, $doc_key, $doc_value);
        update_user_meta($customer, 'billing_number', $customer_data['number'] ?? '');
        update_user_meta($customer, 'shipping_number', $customer_data['number'] ?? '');
        update_user_meta($customer, 'billing_neighborhood', $customer_data['neighborhood'] ?? '');
        update_user_meta($customer, 'shipping_neighborhood', $customer_data['neighborhood'] ?? '');
        update_user_meta($customer, 'billing_persontype', $person_type);
    }
}

function woo_tiny_get_customer_data($customer_data){
    if(!array_key_exists('billing', $customer_data)) {
        $customer_data['billing'] = $customer_data;
    }
    if(!array_key_exists('shipping', $customer_data)) {
        $customer_data['shipping'] = $customer_data;
    }
    if(!array_key_exists('vat', $customer_data)){
        $customer_data['vat'] = ($customer_data['cpf'] ?? $customer_data['cnpj']) ?? '';
    }
    return $customer_data;
}


