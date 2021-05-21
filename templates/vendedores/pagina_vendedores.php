<form action="<?= admin_url('admin-post.php') ?>" id="form_pedido_venda" method="post">
    <input type="hidden" name="action" value="woo_tiny_save_order"/>
    <?php wp_nonce_field('woo_tiny_shop_order'); ?>
    <div class="container-fluid">
        <div class="container">
            <div class="row">
                <div class="col-12 mt-3">
                    <h1>Nova venda</h1>
                    <?php if (isset($_GET['class'])): ?>
                        <div class="alert alert-<?= $_GET['class'] ?>" role="alert">
                            <strong><?= $_GET['message'] ?></strong>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="cpf_cnpj" placeholder="CPF/CNPJ" data-filled="billing_vat">
                            </div>
                        </div>
                        <div class="col-lg-8">
                            <div class="form-group">
                                <input type="text" class="form-control" name="nome"
                                       placeholder="Nome do cliente ou razão social" data-filled="first_name">
                            </div>
                        </div>
                        
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-2">
                            <div class="form-group">
                                <input type="text" class="form-control" name="codigo" placeholder="Código">
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <input type="text" class="form-control" name="nome_fantasia"
                                       placeholder="Nome fantasia" data-filled="billing_company">
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="rg_inscricao"
                                       placeholder="RG/Inscrição Estadual" data-filled="billing_doc">
                            </div>
                        </div>
                    </div>
                    <div class="form-address">
                        <div class="row mt-3">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="cep" id="cep" placeholder="CEP"
                                           data-load-address="" data-filled="billing_postcode">
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="endereco" placeholder="Endereço"
                                           data-address="logradouro" data-filled="billing_address_1">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="numero" placeholder="Número"
                                           data-address="addressnumber" data-filled="billing_number">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="bairro" placeholder="Bairro"
                                           data-address="bairro" data-filled="billing_neighborhood">
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <input type="text" class="form-control no-focus" name="complemento" placeholder="Complemento"
                                           data-address="complemento" data-filled="billing_address_2">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="cidade" placeholder="Cidade"
                                           data-address="localidade" data-filled="billing_city">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="estado" placeholder="Estado"
                                           data-address="uf" data-filled="billing_state">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="telefone" name="telefone"
                                           placeholder="Telefone" data-filled="billing_phone">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="celular" name="celular"
                                           placeholder="Celular" data-filled="billing_cellphone">
                                </div>
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
                                <input type="email" class="form-control" name="email" placeholder="Email" data-filled="billing_email">
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-lg-12">
                            <div class="form-check">
                                <label class="form-check-label">
                                    <input type="checkbox" class="form-check-input" id="has-shipping">
                                    Entregar em um endereço diferente
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="form-address" id="form-shipping" style="display: none;">
                        <div class="row mt-3">
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="shipping[postcode]" id="cep" placeholder="CEP"
                                           data-load-address="" data-filled="shipping_postcode">
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="shipping[address_1]" placeholder="Endereço"
                                           data-address="logradouro" data-filled="shipping_address_1">
                                </div>
                            </div>
                            <div class="col-lg-2">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="shipping[number]" placeholder="Número"
                                           data-address="addressnumber" data-filled="shipping_number">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="shipping[neighborhood]" placeholder="Bairro"
                                           data-address="bairro" data-filled="shipping_neighborhood">
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <input type="text" class="form-control no-focus" name="shipping[address_2]" placeholder="Complemento"
                                           data-address="complemento" data-filled="shipping_address_2">
                                </div>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="shipping[city]" placeholder="Cidade"
                                           data-address="localidade" data-filled="shipping_city">
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="shipping[state]" placeholder="Estado"
                                           data-address="uf" data-filled="shipping_state">
                                </div>
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
                    let userPrice = get_product_price_by_user(produto, id_canal_venda);
                    let finalPrice = precos_por_canal[produto][id_canal_venda];
                    if(userPrice > 0){
                        finalPrice = userPrice;
                    }
                    jQuery("#preco_unitario_" + produto).val(finalPrice);

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
            let userPrice = get_product_price_by_user(produto, id_canal_venda);
            let preco_unitario = precos_por_canal[produto][id_canal_venda];
            if(userPrice > 0){
                preco_unitario = userPrice;
            }
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

    function get_product_price_by_user(product_id, channel_id){
        let data = {
            action: 'woo_tiny_get_product_price_by_user',
            vat: $('[name=cpf_cnpj]').val() ?? '',
            product_id: product_id,
            channel_id: channel_id
        };
        let user_price = 0
        jQuery.get(woo_tiny.ajax_url, data, function (res) {
            if(res.success){
                user_price = res.data;
            }
        });
        return user_price;
    }
</script>