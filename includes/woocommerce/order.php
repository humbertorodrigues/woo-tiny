<?php

add_action('woocommerce_order_after_calculate_totals', 'woo_tiny_order_after_calculate_totals', 10, 2);
add_action('init', 'woo_tiny_register_new_order_statuses');
add_filter('wc_order_statuses', 'woo_tiny_new_wc_order_statuses');
add_filter('wc_order_is_editable', 'woo_tiny_wc_order_is_editable', 10, 2);
add_action('wp_ajax_woo_tiny_get_coupon', 'woo_tiny_ajax_get_coupon_by_code');
add_action('admin_post_woo_tiny_save_order', 'woo_tiny_save_order');
add_action('wp_ajax_woo_tiny_save_order', 'woo_tiny_save_order');
add_action('woocommerce_new_order_item', 'woo_tiny_order_item', 10, 3);
add_action('woocommerce_update_order_item', 'woo_tiny_order_item', 10, 3);

function woo_tiny_order_item($item_id, $item, $order_id)
{
    $product = $item->get_product();
    $total = $item->get_total();
    $subtotal = $item->get_subtotal();
    $qtd = $item->get_quantity();
    $price = $product->get_price();
    $channel_id = get_post_meta($order_id, 'bw_canal_venda', true);
    $customer_id = get_post_meta($order_id, '_customer_user', true);
    //$payment_option_id = get_post_meta($order_id, 'bw_forma_pagamento_id', true);

    $customer_discount = get_custom_product_price_by_user_id($customer_id, $product->get_id(), $channel_id);
    //$channel_discount = get_post_meta($channel_id, '', true);
    //$payment_option_discount = (int)get_post_meta($payment_option_id, 'discount', true);
    $bonus = get_post_meta($order_id, 'bw_bonificacao_pedido_pai', true);
    if ($customer_discount && $bonus == '') {
        $price = $customer_discount;
        $subtotal = $customer_discount * $qtd;
        $total = $customer_discount * $qtd;
    }

    $product->set_price($price);
    $item->set_product($product);
    $item->set_subtotal($subtotal);
    $item->set_total($total);
    $item->save_meta_data();
    $item->apply_changes();
}

function woo_tiny_order_after_calculate_totals($and_taxes, $order)
{
    // Calculate discount by payment option
    $payment_option_id = (int)bw_get_meta_field('discount', (int)get_post_meta($order->get_id(), 'bw_forma_pagamento_id', true));
    $discount = (float)$payment_option_id * $order->get_subtotal() / 100;
    $discount += $order->get_discount_total();

    // Calculate discount by channel
    // Calculate discount by customer

    $order->set_discount_total(round($discount, wc_get_price_decimals()));
    $order->set_total(round($order->get_subtotal() - $discount, wc_get_price_decimals()));
    //$order->save_meta_data();
    //$order->apply_changes();
}

function woo_tiny_register_new_order_statuses()
{
    register_post_status('wc-revision', array(
        'label' => 'Em revisão',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Em revisão <span class="count">(%s)</span>', 'Em revisão <span class="count">(%s)</span>', 'woocommerce')
    ));

    register_post_status('wc-wallet', array(
        'label' => 'Em carteira',
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Em carteira <span class="count">(%s)</span>', 'Em carteira <span class="count">(%s)</span>', 'woocommerce')
    ));
}


function woo_tiny_new_wc_order_statuses($order_statuses)
{
    $order_statuses['wc-revision'] = 'Em revisão';
    $order_statuses['wc-wallet'] = 'Em carteira';

    return $order_statuses;
}

function woo_tiny_wc_order_is_editable($editable, $order)
{
    $editable_statuses = [
        'wallet',
        'revision'
    ];
    if (in_array($order->get_status(), $editable_statuses, true)) {
        $editable = true;
    }
    return $editable;
}

function woo_tiny_ajax_get_coupon_by_code()
{
    $code = filter_input(INPUT_GET, 'code', FILTER_SANITIZE_STRING);
    $coupon = new WC_Coupon($code);
    wp_send_json($coupon->get_data(), 200);
}

function woo_tiny_save_order()
{
    if ('POST' != $_SERVER['REQUEST_METHOD'] || !wp_verify_nonce($_POST['_wpnonce'], 'woo_tiny_shop_order')) die('Requisição Inválida');
    $referer = site_url('vendedores');
    if (!is_user_logged_in()) {
        if (wp_doing_ajax()) {
            wp_send_json_error([
                'redirect' => wp_login_url($referer),
            ]);
        }
        wp_redirect(wp_login_url($referer));
        exit;
    }

    if (isset($_POST['nome'])) {
        $nome = $_POST["nome"];
        $codigo = $_POST["codigo"];
        $nome_fantasia = $_POST["nome_fantasia"];
        $cpf_cnpj = $_POST["cpf_cnpj"];
        $rg_inscricao = $_POST["rg_inscricao"];
        $cep = $_POST["cep"];
        $endereco = $_POST["endereco"];
        $complemento = $_POST["complemento"] ?? '';
        $numero = $_POST["numero"];
        $bairro = $_POST["bairro"];
        $cidade = $_POST["cidade"];
        $estado = $_POST["estado"];
        $telefone = $_POST["telefone"];
        $celular = $_POST["celular"];
        $nome_contato = $_POST["nome_contato"];
        $email = $_POST["email"];
        $canal_venda = $_POST["canal_venda"];
        $obs = $_POST["obs"];

        $id_produto = $_POST["id_produto"];
        $qtd = $_POST["qtd"];
        $preco_unitario = $_POST["preco_unitario"];
        $subtotal = $_POST["subtotal"];

        $qtd_bonificacao = $_POST["qtd_bonificacao"];
        $preco_unitario_bonificacao = $_POST["preco_unitario_bonificacao"];

        $payment_option_id = $_POST['bw_payment_option'];

        $user_id = $_POST['bw_id_vendedor'];

        $order_finish = (int)$_POST['finish'];

        $address = array(
            'first_name' => $nome,
            'company' => $nome_fantasia,
            'email' => $email,
            'phone' => $telefone,
            'cellphone' => $celular,
            'address_1' => $endereco,
            'address_2' => $complemento,
            'neighborhood' => $bairro,
            'city' => $cidade,
            'number' => $numero,
            'state' => $estado,
            'postcode' => $cep
        );
        if (strlen(only_numbers($cpf_cnpj)) == 11) {
            $address['cpf'] = $cpf_cnpj;
            $address['rg'] = $rg_inscricao;
            $address['persontype'] = 1;
        } else {
            $address['cnpj'] = $cpf_cnpj;
            $address['ie'] = $rg_inscricao;
            $address['persontype'] = 2;
        }
        $address['billing'] = wc_serialize_br_address($address, 'billing');
        if (isset($_POST['has_shipping_address'])) {
            $address['shipping'] = wc_serialize_br_address($_POST['shipping'], 'shipping');
        } else {
            $address['shipping'] = wc_serialize_br_address($address['billing'], 'shipping');
        }
        $address['shipping']['first_name'] = $address['billing']['first_name'];
        $address['shipping']['last_name'] = $address['billing']['last_name'];
        $address['shipping']['company'] = $address['billing']['company'];
        $customer = woo_tiny_save_customer_meta_data(woo_tiny_get_customer_data($address));
        if (!$customer) {
            $referer .= set_alert('danger', 'Falha ao processar');
            if (wp_doing_ajax()) {
                wp_send_json_error([
                    'redirect' => $referer,
                ]);
            }
            wp_redirect($referer);
            exit;
        }

        if (array_sum($qtd) > 0) {

            // Now we create the order
            $order = wc_create_order([
                'status' => 'wc-revision',
                'customer_id' => $customer->get_id()
            ]);

            $order_id = $order->get_id();

            update_post_meta($order_id, "bw_codigo", $codigo);
            update_post_meta($order_id, "bw_id_vendedor", $user_id);
            update_post_meta($order_id, "bw_rg_inscricao", $rg_inscricao);
            update_post_meta($order_id, "bw_nome_contato", $nome_contato);
            update_post_meta($order_id, "bw_canal_venda", $canal_venda);
            update_post_meta($order_id, "bw_canal_venda_descricao", get_the_title($canal_venda));
            update_post_meta($order_id, "bw_obs", $obs);
            update_post_meta($order_id, "bw_forma_pagamento_id", $payment_option_id);
            update_post_meta($order_id, "bw_forma_pagamento_descricao", get_the_title($payment_option_id));
            if (array_key_exists('bw_order_installments', $_POST) && isset($_POST['bw_order_installments'])) {
                update_post_meta($order_id, "bw_order_installments", $_POST['bw_order_installments']);
            }

            $order->add_order_note($obs);

            // // The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php

            foreach ($id_produto as $key_produto => $produto) {
                if ($qtd[$key_produto] == 0) {
                    continue;
                }

                $produto_add = wc_get_product($produto);
                $produto_add->set_price($preco_unitario[$key_produto]);

                $order->add_product($produto_add, $qtd[$key_produto]);
            }
            $order->set_address($address['billing'], 'billing');
            $order->set_address($address['shipping'], 'shipping');
            // //

            if (isset($_POST['coupon'])) {
                $order->apply_coupon($_POST['coupon']);
            }

            $order->calculate_totals();

            /*if(isset($_POST['revisao'])){
                    $order->update_status("wc-pending", 'Pedido por vendedor', TRUE);
                }else{

                    $order->update_status("wc-processing", 'Pedido por vendedor', TRUE);
                }*/
        }

        if (empty($order_id)) $order_id = 0;
        //Temos bonificacao, vamos montar um pedido à parte
        if (array_sum($qtd_bonificacao) > 0) {
            $order_bonificacao = wc_create_order([
                'status' => 'wc-revision',
                'customer_id' => $customer->get_id()
            ]);
            $order_bonificacao_id = $order_bonificacao->get_id();

            update_post_meta($order_bonificacao_id, "bw_codigo", $codigo);
            update_post_meta($order_bonificacao_id, "bw_id_vendedor", $user_id);
            update_post_meta($order_bonificacao_id, "bw_rg_inscricao", $rg_inscricao);
            update_post_meta($order_bonificacao_id, "bw_nome_contato", $nome_contato);
            update_post_meta($order_bonificacao_id, "bw_canal_venda", $canal_venda);
            update_post_meta($order_bonificacao_id, "bw_canal_venda_descricao", get_the_title($canal_venda));
            update_post_meta($order_bonificacao_id, "bw_bonificacao_pedido_pai", $order_id);
            update_post_meta($order_bonificacao_id, "bw_forma_pagamento_id", $payment_option_id);
            update_post_meta($order_bonificacao_id, "bw_forma_pagamento_descricao", get_the_title($payment_option_id));

            $order_bonificacao->add_order_note($obs);

            foreach ($id_produto as $key_produto => $produto) {
                if ($qtd_bonificacao[$key_produto] == 0) {
                    continue;
                }

                $produto_add = wc_get_product($produto);
                $produto_add->set_price($preco_unitario_bonificacao[$key_produto]);

                $order_bonificacao->add_product($produto_add, $qtd_bonificacao[$key_produto]);
            }
            $order_bonificacao->set_address($address['billing'], 'billing');
            $order_bonificacao->set_address($address['shipping'], 'shipping');
            // //
            $order_bonificacao->calculate_totals();

            /*if(isset($_POST['revisao'])){
                $order_bonificacao->update_status("wc-pending", 'Pedido por vendedor', TRUE);
            }else{

                $order_bonificacao->update_status("wc-processing", 'Pedido por vendedor', TRUE);
            }*/

            if (!in_array($order_finish, [2, 3], true)) {
                woo_tiny_trigger_order_revision_email($order_bonificacao);
            }
        }
        $download = false;
        if ($order_id > 0) {
            woo_tiny_order_upload_files($order_id, $_FILES['documents']);
            $referer .= set_alert('success', "Pedido #{$order_id} salvo com sucesso");
            switch ($order_finish) {
                case 1:
                    $referer = $order->get_checkout_payment_url();
                    break;
                case 2:
                    $estimate = $_POST['estimate'];
                    update_post_meta($order_id, 'estimate', $estimate);
                    global $wpdb;
                    $wpdb->update($wpdb->posts, ['post_status' => 'wc-estimate'], ['ID' => $order_id], ['%s'], ['%d']);
                    $download = admin_url(sprintf('admin.php?page=%s&action=%s&item=%d&_wpnonce=%s', 'woo_tiny_estimates', 'woo_tiny_estimate_show', $order_id, wp_create_nonce('woo_tiny_estimate_nonce')));
                    break;
                case 3:
                    global $wpdb;
                    $wpdb->update($wpdb->posts, ['post_status' => 'wc-wallet'], ['ID' => $order_id], ['%s'], ['%d']);
                    break;
                case 0:
                default:
                    break;
            }
        } else {
            $referer .= $order_bonificacao_id > 0 ? set_alert('success', "Bonificação #{$order_bonificacao_id} salvo com sucesso") : set_alert('danger', 'Falha ao processar');
        }
        if (!in_array((int)$_POST['finish'], [2, 3], true)) {
            woo_tiny_trigger_order_revision_email($order);
        }
        if (wp_doing_ajax()) {
            wp_send_json_success([
                'download' => $download,
                'redirect' => $referer
            ]);
        }
        wp_redirect($referer);
    }
}

function woo_tiny_order_upload_files($order_id, $files)
{

    $_FILES = rearrange_files($files);
    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');
    foreach ($_FILES as $file_handler => $file) {
        $attachment_id = media_handle_upload($file_handler, $order_id, ['post_status' => 'private', 'comment_status' => 'closed']);
        if (is_integer($attachment_id)) {
            update_post_meta($attachment_id, 'document_order', 'yes');
        }
    }
}




