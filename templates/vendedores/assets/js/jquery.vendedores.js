'use strict';

jQuery(document).on("contextmenu keydown mousedown keypress", function (e) {
    if (e.keyCode === 123 || e.type === 'contextmenu') return false;
});
jQuery.validator.addMethod("cpfcnpj", brdocs.cpfcnpjValidator, "Informe um documento válido.");
jQuery.validator.addMethod("same", function (value, element, param) {
    if (jQuery(param).is(':checked') || jQuery(param).is(':selected')) {
        return value !== '';
    }
    return this.optional(element);
});
jQuery.validator.addMethod("reqMinInstallment", function (value, element, param) {
    if (jQuery(element).data('bw-order-installments') > 0) {
        console.log(jQuery('#input-order-installments>div').length)
        return jQuery('#input-order-installments>div').length > 0;
    }
    return this.optional(element);
});
jQuery(document).ready(function () {
    jQuery("#telefone").mask("(99) 99999-9999");
    jQuery("#celular").mask("(99) 99999-9999");
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
            bw_payment_option: {required: true},


            "shipping[postcode]": {same: '#has-shipping'},
            "shipping[address_1]": {same: '#has-shipping'},
            "shipping[number]": {same: '#has-shipping'},
            "shipping[neighborhood]": {same: '#has-shipping'},
            "shipping[city]": {same: '#has-shipping'},
            "shipping[state]": {same: '#has-shipping'},

            bw_order_installments: {same: '#bw_payment_option'},
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
            canal_venda: {required: "Informe um canal de venda"},
            bw_payment_option: {required: "Informe uma forma de pagamento", reqMinInstallment: 'Informe no mínimo uma parcela'},

            "shipping[postcode]": {same: 'Informe o CEP'},
            "shipping[address_1]": {same: 'Informe o endereço'},
            "shipping[number]": {same: 'Informe o numero'},
            "shipping[neighborhood]": {same: 'Informe o bairro'},
            "shipping[city]": {same: 'Informe a cidade'},
            "shipping[state]": {same: 'Informe o estado'}
        },
        submitHandler: function (form) {
            let formData = new FormData(form);
            let dowmloaded = false;
            jQuery.ajax({
                url: woo_tiny.ajax_url,
                type: 'POST',
                data: formData,
                mimeType: 'multipart/form-data',
                contentType: false,
                cache: false,
                processData: false,
                beforeSend: function () {
                    jQuery('#preload').modal('show');
                },
                success: function (res) {
                    if (typeof res === 'string') {
                        res = JSON.parse(res);
                    }
                    res = res.data;
                    if (res.download) {
                        jQuery.fileDownload(res.download, {
                            successCallback: function (url) {
                                if (res.redirect) {
                                    location.assign(res.redirect)
                                }else{
                                    location.reload();
                                }
                            },
                            abortCallback: function (url) {
                                if (res.redirect) {
                                    location.assign(res.redirect)
                                }else{
                                    location.reload();
                                }
                            },
                            failCallback: function (responseHtml, url, error) {
                                console.log(error);
                                if (res.redirect) {
                                    location.assign(res.redirect)
                                }else{
                                    location.reload();
                                }
                            },
                        });
                    }else {
                        if (res.redirect) {
                            location.assign(res.redirect)
                        }
                    }
                    dowmloaded = true;
                },
                complete: function (res) {
                    jQuery('#preload').modal('hide');
                }
            });
        }
    })
})

jQuery(document).on('click', '#triggerUpDocuments', function (e) {
    e.preventDefault();
    jQuery('input.upDocuments:first').trigger('click');
})
var countElements = 0;

jQuery(document).on('click', 'input.upDocuments', function () {
    jQuery(this).MultiFile({
        accept: 'pdf|png|jpg|zip',
        STRING: {
            remove: 'Remover',
            selected: 'Selecionado: $file',
            denied: 'Invalido arquivo de tipo $ext!',
            duplicate: 'Arquivo ja selecionado:\n$file!'
        },
        afterFileAppend: function (element, value, master_element) {
            let inputFile = jQuery('<input type="file" accept="pdf|png|jpg|zip" style="display: none" class="upDocuments MultiFile-applied"/>');
            inputFile.attr('name', 'documents[' + countElements + ']');
            inputFile.prop('files', new FileListItems(element.files));
            jQuery('#upDocuments').append(inputFile)
            countElements++;
        }
    })
});

jQuery(document).on('change', '#canal_venda', function (e) {
    e.preventDefault();
    let id_canal_venda = jQuery(this).val();

    if (id_canal_venda === "") {
        jQuery(".preco_unitario").val("");
        jQuery(".subtotal").val("");
    } else {
        for (let id_produto of Object.keys(precos_por_canal)) {
            let userPrice = getProductPriceByUser(id_produto, id_canal_venda);
            let finalPrice = userPrice > 0 ? userPrice : precos_por_canal[id_produto][id_canal_venda];
            jQuery("#preco_unitario_" + id_produto).val(finalPrice);
            jQuery("#preco_unitario_" + id_produto).attr('min', finalPrice);
        }
    }
    let paymentIds = jQuery(this).find('option:selected').data('bw-order-payments');
    let paymentMethods = jQuery('#bw_payment_option').find('option');
    paymentMethods.prop('disabled', false).prop('selected', false);
    if (paymentIds) {
        if(typeof paymentIds == 'string') {
            paymentIds = paymentIds.split(',');
        }
        if(typeof paymentIds == 'number'){
            paymentIds = [paymentIds];
        }
        paymentMethods.each(function (i, el) {
            if (!paymentIds.includes($(el).val())) {
                $(el).prop('disabled', true);
            }
        })
    }
    jQuery('#bw_payment_option').trigger('change');
    calcula_subtotal();
})

jQuery(document).on('click', '#adicionar_produto', function () {
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
    let id_canal_venda = jQuery("#canal_venda").val();
    let descricao_produto = jQuery("#produto option:selected").data().descricao;
    let qtd_produto = document.createElement('input');
    qtd_produto.name = "qtd[]";
    qtd_produto.type = "number";
    qtd_produto.value = "1";
    let userPrice = getProductPriceByUser(id_produto, id_canal_venda);
    let preco_unitario = userPrice > 0 ? userPrice : precos_por_canal[id_produto][id_canal_venda];
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

});
jQuery(document).on('change', '[data-product-id]', function (e) {
    e.preventDefault();
    let target = jQuery(e.target);
    if (jQuery('#woo-tiny-seller').length === 0) {
        let limitBonus = jQuery(this).limitBonus();
    }
    calcula_subtotal();
});

jQuery(document).on('click', '#apply-coupon', function (e) {
    e.preventDefault();
    let data = {
        action: 'woo_tiny_get_coupon',
        code: jQuery(this).parent().parent().find('[name=coupon]').val()
    };
    jQuery.get(woo_tiny.ajax_url, data, function (res) {
        if (res.discount_type) {
            let dataCoupon = jQuery('#data-coupon');
            dataCoupon.attr('data-coupon-type', res.discount_type);
            dataCoupon.attr('data-coupon-amount', res.amount);
            calcula_subtotal();
        }
    });
});

jQuery(document).on('keyup blur change', '[name=cpf_cnpj]', function (e) {
    e.preventDefault();
    e.stopPropagation();
    let inputVat = jQuery(this);
    let data = {
        action: 'woo_tiny_get_customer',
        vat: inputVat.val()
    };
    if (data.vat.length < 11) return;
    jQuery.post(woo_tiny.ajax_url, data, function (res) {
        if (res.success === true) {
            for (var [key, value] of Object.entries(res.data)) {
                switch (key) {
                    case 'billing_cnpj':
                    case 'billing_cpf':
                        key = 'billing_vat';
                        break;
                    case 'billing_ie':
                    case 'billing_rg':
                        key = 'billing_doc';
                        break;
                    default:
                        break;
                }
                if (key == 'shipping_postcode') {
                    jQuery('#has-shipping').attr('checked', false);
                    jQuery('#has-shipping').trigger('click');
                }
                if (key == 'bw_custom_product_prices') {
                    jQuery('#canal_venda').attr('data-user-prices', JSON.stringify(value))
                }
                if (value != "") {
                    jQuery('[data-filled=' + key + ']').val(value);
                }
            }
        }
    });
});

jQuery(document).on('click', '#has-shipping', function (e) {
    this.toggleAttribute('checked');
    if (jQuery(this).is(':checked')) {
        jQuery('#form-shipping').show();
    } else {
        jQuery('#form-shipping').hide();
        jQuery('#form-shipping').find('input').each(function (i, el) {
            jQuery(el).val('');
            jQuery(el).prop('readonly', false);
        });
    }
});

jQuery(document).on('change', '#finish', function (e) {
    if (jQuery(this).val() === '2') {
        jQuery('.estimate-box').show();
        jQuery('.estimate').attr('disabled', false).prop('required', true);
        jQuery('.switch-tmce').trigger('click');
        jQuery('.wp-editor-tools').hide();
    } else {
        jQuery('.estimate-box').hide();
        jQuery('.estimate').attr('disabled', true).prop('required', false);
    }
});

jQuery.fn.extend({
    limitBonus: function (limit = 5) {
        let inputQtd = jQuery(this).find('input.qtd');
        let inputBonus = jQuery(this).find('input.qtd_bonificacao');
        let amount = parseInt(inputQtd.val());
        let bonus = parseInt(inputBonus.val());
        inputQtd.val(amount);
        if (amount > 0) {
            if (((bonus / amount) * 100) <= limit) {
                inputBonus.val(bonus);
                return true;
            }
            let difference = Math.floor((limit * amount) / 100);
            inputBonus.val(bonus > 0 ? difference : 0);
            return false;
        }
        inputBonus.val(0);
        return false;
    }
});

jQuery(document).on('change', '#bw_payment_option', function (e) {
    let installmentValue = jQuery('#bw_payment_option>option:selected').data('bw-order-installments');
    jQuery('#input-order-installments').html('');
    if (installmentValue === 0) {
        jQuery('#set-order-installments').hide();
    } else {
        jQuery('#set-order-installments').show();
        jQuery('.order-installment.add').trigger('click');
    }
    calcula_subtotal();
});

jQuery(document).on('click', '.order-installment', function (e) {
    e.preventDefault();
    if (jQuery(this).hasClass('add')) {
        let qtdInputsInstallments = jQuery('#input-order-installments>div').length + 1;
        let inputDueDate = jQuery('#input-order-installments>div:last-child input').val();
        let dueDate = new Date();
        if (inputDueDate !== undefined) {
            inputDueDate = new Date(Date.parse(inputDueDate));
            dueDate.setMonth(inputDueDate.getMonth() + 1);
            if (dueDate.getMonth() === 0) {
                dueDate.setFullYear(inputDueDate.getFullYear() + 1)
            } else {
                dueDate.setFullYear(inputDueDate.getFullYear())
            }
        }
        dueDate.setMonth((dueDate.getMonth() + 1));
        let month = ("0" + dueDate.getMonth()).slice(-2);
        if (month === '00') {
            month = '12';
            dueDate.setFullYear(dueDate.getFullYear() - 1);
        }
        dueDate = dueDate.getFullYear() + '-' + month + '-' + ("0" + dueDate.getDate()).slice(-2);

        let installmentQuantity = jQuery('#bw_payment_option>option:selected').data('bw-order-installments');
        if(qtdInputsInstallments <= installmentQuantity) {
            jQuery('#input-order-installments').append('<div class="text-right">' +
                '<label>Parcela ' + qtdInputsInstallments + ': </label>' +
                '<input type="date" name="bw_order_installments[' + qtdInputsInstallments + '][duedate]" value="' + dueDate + '" required>' +
                '</div>');
        }
    } else if (jQuery(this).hasClass('remove')) {
        jQuery('#input-order-installments>div:last-child').remove();
    } else {
    }
});

function calcula_subtotal() {
    let total = 0;
    jQuery(".subtotal").each(function (index) {

        let id_produto = jQuery(this).data('idproduto');
        let preco_unitario = jQuery("#preco_unitario_" + id_produto).val();
        let preco_unitario_bonificacao = jQuery("#preco_unitario_bonificacao_" + id_produto).val();
        let qtd = jQuery("#qtd_" + id_produto).val();
        let qtd_bonificacao = jQuery("#qtd_bonificacao_" + id_produto).val();


        let subtotal = (preco_unitario * qtd) + (preco_unitario_bonificacao * qtd_bonificacao);

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

function calculateCoupon(total = 0) {
    let coupon = jQuery('#data-coupon');
    let type = coupon.attr('data-coupon-type');
    let amount = Number(coupon.attr('data-coupon-amount'));
    let discount = 0;
    switch (type) {
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

function calculateDiscount(total = 0) {
    let discount = Number(jQuery('#bw_payment_option>option:selected').data('bw-order-discount'));
    discount = (discount * total) / 100;
    jQuery('#desconto').html("R$ " + discount.toFixed(2));
    return discount;
}

function getProductPriceByUser(product_id, channel_id) {
    let prices = jQuery('#canal_venda').data('user-prices');
    let userPrice = 0;
    if (prices !== undefined) {
        // esse for está causando erro ao tentar carregar os preços
        /*for (let index = 0; index < prices.length; index++) {
            const element = array[index];

        }*/
        for (var new_price in prices) {
            if (prices[new_price].product_id === product_id && prices[new_price].channel_id === channel_id) {
                userPrice = Number(prices[new_price].new_price);
            }

        }
    }
    return userPrice;
}

/**
 * @params {File[]} files Array of files to add to the FileList
 * @return {FileList}
 */
function FileListItems(files) {
    var b = new ClipboardEvent("").clipboardData || new DataTransfer()
    for (var i = 0, len = files.length; i < len; i++) b.items.add(files[i])
    return b.files
}