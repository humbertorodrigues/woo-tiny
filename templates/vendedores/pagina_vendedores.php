<?php
if (!is_user_logged_in()) {

    wp_redirect(wp_login_url(site_url("vendedores")));
    exit;

}
if (isset($_POST['nome'])) {
    global $woocommerce;
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
        'postcode' => $cep,
        'country' => "BR"
    );
    if (strlen($cpf_cnpj) == 11) {
        $address['cpf'] = $cpf_cnpj;
        $address['rg'] = $rg_inscricao;
    } else {
        $address['cnpj'] = $cpf_cnpj;
        $address['inscricao_estadual'] = $rg_inscricao;
    }

    // Now we create the order
    $order = wc_create_order(['status' => 'wc-revision']);
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
    $order->set_address($address, 'billing');
    $order->set_address($address, 'shipping');
    // //

    if(isset($_POST['coupon'])){
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
        $order_bonificacao->set_address($address, 'billing');
        $order_bonificacao->set_address($address, 'shipping');
        // //
        $order_bonificacao->calculate_totals();

        /*if(isset($_POST['revisao'])){
            $order_bonificacao->update_status("wc-pending", 'Pedido por vendedor', TRUE);  
        }else{

            $order_bonificacao->update_status("wc-processing", 'Pedido por vendedor', TRUE);  
        }*/

    }


}
$canais_vendas = get_posts(array(
    'post_type' => 'canal_venda',
    'numberposts' => -1
));
$precos_por_canal = array();

foreach ($canais_vendas as $canal_venda) {
    $id_canal_venda = $canal_venda->ID;
    foreach ($produtos as $key => $produto) {
        $id_produto = $produto->get_ID();
        $precos_canal_venda = get_post_meta($id_produto, 'canais_venda', true);
        if (is_array($precos_canal_venda)) {
            if (isset($precos_canal_venda[$id_canal_venda]) && $precos_canal_venda[$id_canal_venda] > 0) {
                $precos_por_canal[$id_produto][$id_canal_venda] = str_replace(",", ".", $precos_canal_venda[$id_canal_venda]);
            } else {
                $precos_por_canal[$id_produto][$id_canal_venda] = $produto->get_price();
            }
        } else {
            $precos_por_canal[$id_produto][$id_canal_venda] = $produto->get_price();
        }
    }
}

$payment_options = get_posts(array(
    'post_type' => 'bw-payment-options',
    'numberposts' => -1
));
?>
<style>
    #pedido_venda input {
        max-width: 100px;
        width: 100px;
    }

    .text-right {
        text-align: right;
    }
</style>
<form action="" id="form_pedido_venda" method="post" class="form-address">
    <div class="container-fluid">
        <div class="container">
            <div class="row">
                <div class="col-12 mt-3">
                    <h1>Nova venda</h1>
                    <?php if (isset($order_id)): ?>
                        <div class="alert alert-success" role="alert">
                            <strong>Pedido <?php echo $order_id ?> gerado com sucesso</strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-8">
                            <div class="form-group">
                                <input type="text" class="form-control" name="nome"
                                       placeholder="Nome do cliente ou razão social">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="codigo" placeholder="Código">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="nome_fantasia"
                                       placeholder="Nome fantasia">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="cpf_cnpj" placeholder="CPF/CNPJ">
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="rg_inscricao"
                                       placeholder="RG/Inscrição Estadual">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <input type="text" class="form-control" name="cep" id="cep" placeholder="CEP"
                                       data-load-address="">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-group">
                                <input type="text" class="form-control" name="endereco" placeholder="Endereço"
                                       data-address="logradouro">
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="form-group">
                                <input type="text" class="form-control" name="numero" placeholder="Número"
                                       data-address="addressnumber">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="bairro" placeholder="Bairro"
                                       data-address="bairro">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-group">
                                <input type="text" class="form-control" name="complemento" placeholder="Complemento" data-address="complemento" data-readonly="false">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-3">
                            <div class="form-group">
                                <input type="text" class="form-control" name="cidade" placeholder="Cidade"
                                       data-address="localidade">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <input type="text" class="form-control" name="estado" placeholder="Estado"
                                       data-address="uf">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <input type="text" class="form-control" id="telefone" name="telefone"
                                       placeholder="Telefone">
                            </div>
                        </div>
                        <div class="col-lg-3">
                            <div class="form-group">
                                <input type="text" class="form-control" id="celular" name="celular"
                                       placeholder="Celular">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <input type="text" class="form-control" name="nome_contato"
                                       placeholder="Contato (Nome)">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <input type="email" class="form-control" name="email" placeholder="Email">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">Canal de venda</label>
                                <select class="form-control" name="canal_venda" id="canal_venda">
                                    <option value="">Escolha um canal de venda</option>
                                    <?php foreach ($canais_vendas as $key => $canal_venda) {
                                        ?>
                                        <option value="<?php echo $canal_venda->ID ?>"><?php echo $canal_venda->post_title ?></option>
                                        <?php
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="">Forma de pagamento</label>
                                <select class="form-control" name="bw_payment_option" id="bw_payment_option" required>
                                    <option value="" data-bw-order-discount="0" selected>Escolha uma forma de
                                        pagamento
                                    </option>
                                    <?php foreach ($payment_options as $payment_option) {
                                        ?>
                                        <option value="<?php echo $payment_option->ID ?>"
                                                data-bw-order-discount="<?= bw_get_meta_field('discount', $payment_option->ID) ?>"><?= $payment_option->post_title . ' (Desconto de ' . bw_get_meta_field('discount', $payment_option->ID) . '%)' ?></option>
                                        <?php
                                    } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-12">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" name="revisao" id="revisao"
                                           value="revisao">
                                    Solicitar revisão dos valores deste pedido
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-12">
                            <div class="form-group">
                                <textarea class="form-control" name="obs" rows="3"
                                          placeholder="Observações (Detalhes sobre entrega, restrições, horários, endereços diferenciados, referências, etc)"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3 d-none">
                        <h3>Produtos</h3>
                        <div class="col-lg-10">
                            <div class="form-group">
                                <select class="form-control" id="produto">
                                    <?php
                                    foreach ($produtos as $key => $produto) {
                                        ?>
                                        <option data-descricao="<?php echo $produto->get_title() ?>"
                                                value="<?php echo $produto->get_id() ?>"><?php echo $produto->get_title() ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-2">
                            <div class="row">
                                <div class="col-12">
                                    <button id="adicionar_produto" class="btn btn-sm btn-secondary" type="button">
                                        Adicionar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12">
                    <div class="table-responsive">
                        <table id="pedido_venda" class="table table-striped">
                            <thead>
                            <tr>
                                <th scope="col">Cod. Produto</th>
                                <th scope="col">Descrição</th>
                                <th scope="col">Quantidade</th>
                                <th scope="col">Bonificação</th>
                                <th scope="col">Preço unitário</th>
                                <th scope="col">Preço unitário bonificação</th>
                                <th scope="col">Subtotal</th>

                            </tr>
                            </thead>
                            <?php
                            foreach ($produtos as $key => $produto) {
                                if (stripos($produto->get_title(), "kit") !== false || stripos($produto->get_title(), "let's") !== false) {
                                    continue;
                                }
                                $preco_bonificacao = get_post_meta($produto->get_id(), "bonificacao", "true");
                                $preco_bonificacao = str_replace(",", ".", $preco_bonificacao);
                                ?>
                                <tr data-product-id="<?= $produto->get_id() ?>">
                                    <td>
                                        <input type="hidden" name="id_produto[]"
                                               value="<?php echo $produto->get_id() ?>">
                                        <?php echo $produto->get_id() ?>
                                    </td>
                                    <td><?php echo $produto->get_title() ?></td>
                                    <td><input min="0" class="qtd" name="qtd[]"
                                               id="qtd_<?php echo $produto->get_id() ?>" type="number" value="0"></td>
                                    <td><input min="0" class="qtd_bonificacao" name="qtd_bonificacao[]"
                                               id="qtd_bonificacao_<?php echo $produto->get_id() ?>" type="number"
                                               value="0"></td>
                                    <td><input class="preco_unitario"
                                               id="preco_unitario_<?php echo $produto->get_id() ?>" readonly
                                               type="number" name="preco_unitario[]"></td>
                                    <td><input value="<?php echo $preco_bonificacao ?>"
                                               class="preco_unitario_bonificacao"
                                               id="preco_unitario_bonificacao_<?php echo $produto->get_id() ?>" readonly
                                               type="number" name="preco_unitario_bonificacao[]"></td>
                                    <td><input class="subtotal" data-idproduto="<?php echo $produto->get_id() ?>"
                                               id="subtotal_<?php echo $produto->get_id() ?>" readonly type="number"
                                               name="subtotal[]"></td>

                                </tr>


                                <?php
                            }
                            ?>
                            <tbody>


                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="7" class="w-100">
                                        <div class="input-group d-flex justify-content-end">
                                            <input type="text" placeholder="Cupom" name="coupon">
                                            <input type="hidden" id="data-coupon" data-coupon-type="percent" data-coupon-amount="0">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" id="apply-coupon" type="button">Aplicar
                                                </button>
                                            </div>
                                        </div>
                                </td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td colspan="5" class="text-right"><b>Desconto:</b> <span id="desconto">R$0,00</span></td>
                                <td colspan="1" class="text-right"><b>Cupom:</b> <span id="cupom">R$0,00</span></td>
                                <td colspan="1" class="text-right"><b>Total:</b> <span id="total">R$0,00</span></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right mb-5">
                    <button class="btn btn-success" type="submit">Adicionar pedido</button>
                </div>
            </div>
        </div>
    </div>
</form>
<script>
    var precos_por_canal = <?php echo json_encode($precos_por_canal); ?>;
    jQuery.validator.addMethod("cpfcnpj", brdocs.cpfcnpjValidator, "Informe um documento válido.");

    jQuery(document).ready(function () {
        jQuery("#telefone").mask("(99)99999-9999");
        jQuery("#celular").mask("(99)99999-9999");
        jQuery("#cep").mask("99999-999");
        jQuery("#form_pedido_venda").validate({
            rules: {
                nome: {required: true},

                nome_fantasia: {required: true},
                cpf_cnpj: {required: true, "cpfcnpj": true},
                rg_inscricao: {required: true},
                endereco: {required: true},
                cep: {required: true},
                bairro: {required: true},
                cidade: {required: true},
                estado: {required: true},
                telefone: {required: true},
                celular: {required: true},

                email: {required: true, email: true},
                canal_venda: {required: true},
                bw_payment_option: {required: true}
            },
            messages: {
                nome: {required: "Informe o nome do cliente"},

                nome_fantasia: {required: "Informe o nome fantasia"},
                cpf_cnpj: {required: "Informe o CPF/CNPJ"},
                rg_inscricao: {required: "Informe o RG ou inscrição estadual"},
                endereco: {required: "Informe o endereço"},
                cep: {required: "Informe o CEP"},
                bairro: {required: "Informe o bairro"},
                cidade: {required: "Informe a cidade"},
                estado: {required: "Informe o estado"},
                telefone: {required: "Informe o telefone"},
                celular: {required: "Informe o celular"},
                nome_contato: {required: "Informe"},
                email: {required: "Informe o email", email: "Informe um email válido"},
                canal_venda: {required: "Informe um canal de venda"}
            }
        })
        jQuery("#canal_venda").change(function () {
            id_canal_venda = jQuery("#canal_venda").val();

            if (id_canal_venda == "") {
                jQuery(".preco_unitario").val("");
                jQuery(".subtotal").val("");
            } else {
                for (produto in precos_por_canal) {
                    jQuery("#preco_unitario_" + produto).val(precos_por_canal[produto][id_canal_venda]);

                }
            }
            calcula_subtotal();
        })

        jQuery("#bw_payment_option").change(function () {
            calcula_subtotal();
        })

        jQuery("#adicionar_produto").click(function () {
            let linha = document.createElement('tr');
            let col_id_produto = document.createElement('td');
            let col_desc_produto = document.createElement('td');
            let col_qtd_produto = document.createElement('td');
            let col_preco_unitario = document.createElement('td');
            let col_sub_total = document.createElement('td');
            let col_remover = document.createElement('td');

            col_preco_unitario.classList.add("preco_unitario");
            col_sub_total.classList.add("subtotal");

            let id_produto = jQuery("#produto").val();
            let canal_venda = jQuery("#canal_venda").val();
            let descricao_produto = jQuery("#produto option:selected").data().descricao;
            let qtd_produto = document.createElement('input');
            qtd_produto.name = "qtd[]";
            qtd_produto.type = "number";
            qtd_produto.value = "1";
            let preco_unitario = precos_por_canal[id_produto][canal_venda];
            let subtotal = preco_unitario * qtd_produto.value;

            col_id_produto.append(id_produto);
            col_desc_produto.append(descricao_produto)
            col_qtd_produto.append(qtd_produto)
            col_preco_unitario.append(preco_unitario)
            col_sub_total.append(subtotal)
            col_remover.append("X")

            linha.append(col_id_produto);
            linha.append(col_desc_produto);
            linha.append(col_qtd_produto);
            linha.append(col_preco_unitario);
            linha.append(col_sub_total);
            linha.append(col_remover);
            jQuery("#pedido_venda tbody").append(linha);

        })
    })

    function calcula_subtotal() {
        total = 0;
        jQuery(".subtotal").each(function (index) {

            id_produto = jQuery(this).data('idproduto');
            preco_unitario = jQuery("#preco_unitario_" + id_produto).val();
            preco_unitario_bonificacao = jQuery("#preco_unitario_bonificacao_" + id_produto).val();
            qtd = jQuery("#qtd_" + id_produto).val();
            qtd_bonificacao = jQuery("#qtd_bonificacao_" + id_produto).val();


            subtotal = (preco_unitario * qtd) + (preco_unitario_bonificacao * qtd_bonificacao);

            jQuery("#subtotal_" + id_produto).val(subtotal.toFixed(2));
            total = total + subtotal;
        })

        let discount = calculateDiscount(total);
        let coupon = calculateCoupon(total);
        total -= discount;
        total -= coupon;
        if (total < 0) total = 0;
        jQuery("#total").html("R$ " + total.toFixed(2));
    }

    function calculateCoupon(total = 0){
        let coupon = $('#data-coupon');
        let type = coupon.attr('data-coupon-type');
        let amount = Number(coupon.attr('data-coupon-amount'));
        let discount = 0;
        switch (type){
            case 'fixed_cart':
                discount += amount;
                break;
            case 'percent':
                discount += (amount * total) / 100;
                break;
            default:
                break;
        }
        jQuery('#cupom').html("R$ " + discount.toFixed(2));
        return discount;
    }

    function  calculateDiscount(total = 0){
        let discount = Number(jQuery('#bw_payment_option>option:selected').data('bw-order-discount'));
        discount = (discount * total) / 100;
        jQuery('#desconto').html("R$ " + discount.toFixed(2));
        return discount;
    }
</script>