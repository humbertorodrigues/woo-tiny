'use strict';
jQuery(document).on('change', '[data-product-id]', function (e) {
    e.preventDefault();
    let target = jQuery(e.target);
    let limitBonus = jQuery(this).limitBonus();
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
jQuery(document).on('click', '#send-estimate', function (e) {
    if (jQuery(this).is(':checked')) {
        jQuery('#payment-order').attr('disabled', true).prop('checked', false);
        jQuery('.estimate-box').show();
        jQuery('.estimate').attr('disabled', false).prop('required', true);
        jQuery('.switch-tmce').trigger('click');
        jQuery('.wp-editor-tools').hide();
    } else {
        jQuery('#payment-order').attr('disabled', false);
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
    if (installmentValue === 0) {
        jQuery('#set-order-installments').hide();
        jQuery('#input-order-installments').html('');
    } else {
        jQuery('#set-order-installments').show();
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
            if(dueDate.getMonth() === 0){
                dueDate.setFullYear(inputDueDate.getFullYear() + 1)
            }else{
                dueDate.setFullYear(inputDueDate.getFullYear())
            }
        }
        dueDate.setMonth((dueDate.getMonth() + 1));
        let month = ("0" + dueDate.getMonth()).slice(-2);
        if(month === '00'){
            month = '12';
            dueDate.setFullYear(dueDate.getFullYear() - 1);
        }
        dueDate = dueDate.getFullYear() + '-' + month + '-' + dueDate.getDate();
        jQuery('#input-order-installments').append('<div class="text-right">' +
            '<label>Parcela ' + qtdInputsInstallments + ': </label>' +
            '<input type="date" name="bw_order_installments[' + qtdInputsInstallments + '][duedate]" value="' + dueDate + '" required>' +
            '</div>');
    } else if (jQuery(this).hasClass('remove')) {
        jQuery('#input-order-installments>div:last-child').remove();
    } else {
    }
});