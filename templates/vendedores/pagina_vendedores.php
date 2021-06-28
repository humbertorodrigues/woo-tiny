<?php
if (!is_user_logged_in()) {
    wp_redirect(wp_login_url(site_url('vendedores')));
    exit;
}
?>
<form action="<?= admin_url('admin-post.php') ?>" id="form_pedido_venda" method="post" enctype="multipart/form-data">
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
            <?php
            $user = wp_get_current_user();
            if (in_array('bw_supervisor', $user->roles) || in_array('administrator', $user->roles)):
                $sellers = get_users(['role__in' => ['vendedores_bw']]);
                ?>
                <div class="row mt-3">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="woo-tiny-seller">Vendedor</label>
                            <select name="bw_id_vendedor" id="woo-tiny-seller" class="form-control" required>
                                <option value="0">Selecione um vendedor...</option>
                                <?php foreach ($sellers as $seller): ?>
                                    <option value="<?= $seller->ID ?>"><?= $seller->display_name ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <input type="hidden" name="bw_id_vendedor" value="<?= $user->ID ?>">
            <?php endif; ?>
            <div class="row mt-3">
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="form-group">
                                <input type="text" class="form-control" name="cpf_cnpj" placeholder="CPF/CNPJ"
                                       data-filled="billing_vat">
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
                                    <input type="text" class="form-control no-focus" name="complemento"
                                           placeholder="Complemento"
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
                                <input type="email" class="form-control" name="email" placeholder="Email"
                                       data-filled="billing_email">
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
                                    <input type="text" class="form-control" name="shipping[postcode]" id="cep"
                                           placeholder="CEP"
                                           data-load-address="" data-filled="shipping_postcode">
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <input type="text" class="form-control" name="shipping[address_1]"
                                           placeholder="Endereço"
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
                                    <input type="text" class="form-control" name="shipping[neighborhood]"
                                           placeholder="Bairro"
                                           data-address="bairro" data-filled="shipping_neighborhood">
                                </div>
                            </div>
                            <div class="col-lg-8">
                                <div class="form-group">
                                    <input type="text" class="form-control no-focus" name="shipping[address_2]"
                                           placeholder="Complemento"
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
                                    <option value="" data-bw-order-payments="">Escolha um canal de venda</option>
                                    <?php foreach ($canais_vendas as $key => $canal_venda) {
                                        ?>
                                        <option value="<?php echo $canal_venda->ID ?>"
                                                data-bw-order-payments="<?= get_post_meta($canal_venda->ID, 'payment_methods', true) ?>"><?php echo $canal_venda->post_title ?></option>
                                        <?php
                                    } ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="bw_payment_option">Forma de pagamento</label>
                                <select class="form-control" name="bw_payment_option" id="bw_payment_option" required>
                                    <option value="" data-bw-order-installments="0" data-bw-order-discount="0" selected>
                                        Escolha uma forma de
                                        pagamento
                                    </option>
                                    <?php foreach ($payment_options as $payment_option): $payment_option_discount = bw_get_meta_field('discount', $payment_option->ID); ?>
                                        <option value="<?php echo $payment_option->ID ?>"
                                                data-bw-order-installments="<?= bw_get_meta_field('installments', $payment_option->ID) ?>"
                                                data-bw-order-discount="<?= $payment_option_discount ?>">
                                            <?= $payment_option->post_title . ' (Desconto de ' . $payment_option_discount . '%)' ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <div id="set-order-installments" style="display: none">
                                    <div class="btn-group-sm mt-3 text-right">
                                        <button type="button"
                                                class="btn btn-outline-danger btn-sm order-installment remove">-
                                        </button>
                                        <button type="button"
                                                class="btn btn-outline-primary btn-sm order-installment add">+
                                        </button>
                                    </div>
                                    <div class="row mt-3" id="input-order-installments">
                                    </div>
                                </div>
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
                                    <?php foreach ($produtos as $key => $produto): ?>
                                        <option data-descricao="<?php echo $produto->get_title() ?>"
                                                value="<?php echo $produto->get_id() ?>"><?php echo $produto->get_title() ?></option>
                                    <?php endforeach; ?>
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
                            foreach ($produtos as $key => $produto):
                                if (stripos($produto->get_title(), "kit") !== false || stripos($produto->get_title(), "let's") !== false) {
                                    continue;
                                }
                                $preco_bonificacao = get_post_meta($produto->get_id(), "bonificacao", "true");
                                $preco_bonificacao = str_replace(",", ".", $preco_bonificacao);
                                $in_stock = $produto->get_stock_quantity() > 0;
                                $pre_sale = get_post_meta($produto->get_id(), "bw_pre_venda", true);
                                $not_in_stock_and_pre_sale = !$in_stock && $pre_sale == 'yes';
                                ?>
                                <tr data-product-id="<?= $produto->get_id() ?>"
                                    <?php if ($not_in_stock_and_pre_sale): ?> class="text-primary"
                                    <?php elseif (!$in_stock && $pre_sale != 'yes'): ?> class="text-danger" <?php endif; ?>
                                >
                                    <td>
                                        <input type="hidden" name="id_produto[]"
                                               value="<?php echo $produto->get_id() ?>">
                                        <?php echo $produto->get_id() ?>
                                    </td>
                                    <td><?php echo $produto->get_title() ?></td>
                                    <td><input min="0" class="qtd" name="qtd[]"
                                               id="qtd_<?php echo $produto->get_id() ?>" type="number"
                                               value="0" <?php if (!$in_stock && $pre_sale != 'yes'): ?> readonly <?php endif; ?>>
                                    </td>
                                    <td><input min="0" class="qtd_bonificacao" name="qtd_bonificacao[]"
                                               id="qtd_bonificacao_<?php echo $produto->get_id() ?>" type="number"
                                               value="0" <?php if (!$in_stock && $pre_sale != 'yes'): ?> readonly <?php endif; ?>>
                                    </td>
                                    <td><input class="preco_unitario"
                                               id="preco_unitario_<?php echo $produto->get_id() ?>" min=""
                                               type="number"
                                               name="preco_unitario[]" <?php if (!$in_stock && $pre_sale != 'yes'): ?> readonly <?php endif; ?>>
                                    </td>
                                    <td><input value="<?php echo $preco_bonificacao ?>"
                                               class="preco_unitario_bonificacao"
                                               id="preco_unitario_bonificacao_<?php echo $produto->get_id() ?>" readonly
                                               type="number"
                                               name="preco_unitario_bonificacao[]" <?php if (!$in_stock && $pre_sale != 'yes'): ?> readonly <?php endif; ?>>
                                    </td>
                                    <td><input class="subtotal" data-idproduto="<?php echo $produto->get_id() ?>"
                                               id="subtotal_<?php echo $produto->get_id() ?>" readonly type="number"
                                               name="subtotal[]" <?php if (!$in_stock && $pre_sale != 'yes'): ?> readonly <?php endif; ?>>
                                    </td>

                                </tr>


                            <?php endforeach; ?>
                            <tbody>


                            </tbody>
                            <tfoot>
                            <tr>
                                <td colspan="7" class="w-100">
                                    <div class="input-group d-flex justify-content-end">
                                        <input type="text" placeholder="Cupom" name="coupon">
                                        <input type="hidden" id="data-coupon" data-coupon-type="percent"
                                               data-coupon-amount="0">
                                        <div class="input-group-append">
                                            <button class="btn btn-outline-secondary" id="apply-coupon" type="button">
                                                Aplicar
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="font-weight-bold">
                                <td colspan="5" class="text-right"><b>Desconto:</b> <span id="desconto">R$0,00</span>
                                </td>
                                <td colspan="1" class="text-right"><b>Cupom:</b> <span id="cupom">R$0,00</span></td>
                                <td colspan="1" class="text-right"><b>Total:</b> <span id="total">R$0,00</span></td>
                            </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-lg-4 ms-lg-auto">
                    <div class="form-group">
                        <label for="finish">Tipo de finalização</label>
                        <select name="finish" id="finish" class="form-control">
                            <option value="0">Nenhuma das opções</option>
                            <option value="1">Proceder com o pagamento</option>
                            <option value="2">Enviar orçamento</option>
                            <option value="3">Pedido em carteira</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="estimate-box" style="display: none">
                        <h3>Orçamento</h3>
                        <div class="form-group">
                            <label for="estimate-header" class="form-label">Escopo</label>
                            <?php wp_editor('', 'estimate-header', [
                                'media_buttons' => false,
                                'drag_drop_upload' => false,
                                'textarea_name' => 'estimate[header]',
                                'editor_class' => 'estimate',
                                'quicktags' => ['display' => true],
                            ]) ?>
                        </div>
                        <div class="form-group">
                            <label for="estimate-footer" class="form-label">Observações</label>
                            <?php wp_editor('', 'estimate-footer', [
                                'media_buttons' => false,
                                'drag_drop_upload' => false,
                                'textarea_name' => 'estimate[footer]',
                                'editor_class' => 'estimate',
                                'quicktags' => ['display' => true],
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right mb-5">
                    <input type="file" class="upDocuments" accept="pdf|png|jpg|zip" name="documents[]"
                           style="display: none">
                    <button class="btn btn-light me-sm-3" type="button" id="triggerUpDocuments">Anexar Documentos
                    </button>
                    <button class="btn btn-success" type="submit">Adicionar pedido</button>
                </div>
            </div>
        </div>
    </div>
    <div id="upDocuments"></div>
</form>
<div id="preload" class="modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body d-flex justify-content-center align-items-center" style="min-height: 250px">
                Aguarde...
            </div>
        </div>
    </div>
</div>
<script>
    const precos_por_canal = <?= json_encode($precos_por_canal) ?>;
</script>