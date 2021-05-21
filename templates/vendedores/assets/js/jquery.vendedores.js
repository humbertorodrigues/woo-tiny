'use strict';
if ($ === undefined) {
    let $ = window.jQuery || window.$;
}

$(document).on('change', '[data-product-id]', function (e) {
    e.preventDefault();
    let target = $(e.target);
    let limitBonus = $(this).limitBonus();
    calcula_subtotal();
});

$(document).on('click', '#apply-coupon', function (e) {
    e.preventDefault();
    let data = {
        action: 'woo_tiny_get_coupon',
        code: $(this).parent().parent().find('[name=coupon]').val()
    };
    $.get(woo_tiny.ajax_url, data, function (res) {
        if (res.discount_type) {
            let dataCoupon = $('#data-coupon');
            dataCoupon.attr('data-coupon-type', res.discount_type);
            dataCoupon.attr('data-coupon-amount', res.amount);
            calcula_subtotal();
        }
    });
});

$(document).on('keyup blur change', '[name=cpf_cnpj]', function (e) {
    e.preventDefault();
    e.stopPropagation();
    let inputVat = $(this);
    let data = {
        action: 'woo_tiny_get_customer',
        vat: inputVat.val()
    };
    if(data.vat.length < 11) return;
    $.post(woo_tiny.ajax_url, data, function (res) {
        if(res.success === true){
            for (var [key, value] of Object.entries(res.data)) {
                switch (key){
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
                if(key == 'shipping_postcode'){
                    $('#has-shipping').attr('checked', false);
                    $('#has-shipping').trigger('click');
                }
                $('[data-filled=' + key + ']').val(value);
            }
        }
    });
});

$(document).on('click', '#has-shipping', function (e){
    this.toggleAttribute('checked');
   if($(this).is(':checked')){
       $('#form-shipping').show();
   }else{
       $('#form-shipping').hide();
       $('#form-shipping').find('input').each(function (i, el) {
           $(el).val('');
           $(el).prop('readonly', false);
       });
   }
});

$(document).on('change', '#canal_venda', function (e) {
    e.preventDefault();
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


$.fn.extend({
    limitBonus: function (limit = 5) {
        let inputQtd = $(this).find('input.qtd');
        let inputBonus = $(this).find('input.qtd_bonificacao');
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