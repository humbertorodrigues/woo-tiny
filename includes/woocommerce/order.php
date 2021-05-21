<?php

add_action('woocommerce_order_after_calculate_totals', 'woo_tiny_order_after_calculate_totals', 10, 2);
add_action('init', 'woo_tiny_register_new_order_statuses');
add_filter('wc_order_statuses', 'woo_tiny_new_wc_order_statuses');
add_filter('wc_order_is_editable', 'woo_tiny_wc_order_is_editable', 10, 2);
add_action('wp_ajax_woo_tiny_get_coupon', 'woo_tiny_ajax_get_coupon_by_code');
add_action('admin_post_woo_tiny_save_order', 'woo_tiny_save_order');

global $woocommerce;

function woo_tiny_order_after_calculate_totals($and_taxes, $order)
{
    $payment_option_id = (int)bw_get_meta_field('discount', (int)get_post_meta($order->get_id(), 'bw_forma_pagamento_id', true));
    $discount = (float)$payment_option_id * $order->get_subtotal() / 100;
    $discount += $order->get_discount_total();
    $order->set_discount_total(round($discount, wc_get_price_decimals()));
    $order->set_total(round($order->get_subtotal() - $discount, wc_get_price_decimals()));
}

function woo_tiny_register_new_order_statuses()
{
    register_post_status('wc-revision', array(
        'label' => _x('Em revisão', 'Order status', 'woocommerce'),
        'public' => true,
        'exclude_from_search' => false,
        'show_in_admin_all_list' => true,
        'show_in_admin_status_list' => true,
        'label_count' => _n_noop('Em revisão <span class="count">(%s)</span>', 'Em revisão <span class="count">(%s)</span>', 'woocommerce')
    ));
}


function woo_tiny_new_wc_order_statuses($order_statuses)
{
    $order_statuses['wc-revision'] = _x('Em revisão', 'Order status', 'woocommerce');

    return $order_statuses;
}

function woo_tiny_wc_order_is_editable($editable, $order)
{
    if ($order->get_status() == 'revision') {
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
    $referer = 'vendedores';
    if (!is_user_logged_in()) {
        wp_redirect(wp_login_url(site_url($referer)));
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

        $user_id = get_current_user_id();

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
        if (strlen(str_replace(array('.','-'),"",$cpf_cnpj)) == 11) {
            $address['cpf'] = $cpf_cnpj;
            $address['rg'] = $rg_inscricao;
            $address['persontype'] = 1;
        } else {
            $address['cnpj'] = $cpf_cnpj;
            $address['ie'] = $rg_inscricao;
            $address['persontype'] = 2;
        }
        $address['billing'] = wc_serialize_br_address($address, 'billing');
        $address['shipping'] = wc_serialize_br_address((!empty($_POST['shipping']['postcode']) ? $_POST['shipping'] : $address['billing']), 'shipping');
        $address['shipping']['first_name'] = $address['billing']['first_name'];
        $address['shipping']['last_name'] = $address['billing']['last_name'];
        $address['shipping']['company'] = $address['billing']['company'];
        $customer = woo_tiny_save_customer_meta_data(woo_tiny_get_customer_data($address));
        if (!$customer) {
            $referer .= set_alert('danger', 'Falha ao processar');
            wp_redirect($referer);
            exit;
        }

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


        //Temos bonificacao, vamos montar um pedido à parte
        if (array_sum($qtd_bonificacao) > 0) {
            $order_bonificacao = wc_create_order(['status' => 'wc-revision']);
            $order_bonificacao_id = $order_bonificacao->get_id();

            update_post_meta($order_bonificacao_id, "bw_codigo", $codigo);
            update_post_meta($order_bonificacao_id, "bw_id_vendedor", $user_id);
            update_post_meta($order_bonificacao_id, "bw_rg_inscricao", $rg_inscricao);
            update_post_meta($order_bonificacao_id, "bw_nome_contato", $nome_contato);
            update_post_meta($order_bonificacao_id, "bw_canal_venda", $canal_venda);
            update_post_meta($order_bonificacao_id, "bw_canal_venda_descricao", get_the_title($canal_venda));
            update_post_meta($order_bonificacao_id, "bw_bonificacao_pedido_pai", $order_id);

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

        }


    }
    $referer .= $order_id > 0 ? set_alert('success', "Pedido #{$order_id} salvo com sucesso") : set_alert('danger', 'Falha ao processar');
    wp_redirect($referer);
}



