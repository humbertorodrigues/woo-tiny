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
    if(data.vat.length < 11) return;
    jQuery.post(woo_tiny.ajax_url, data, function (res) {
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
                    jQuery('#has-shipping').attr('checked', false);
                    jQuery('#has-shipping').trigger('click');
                }
                if(key == 'bw_custom_product_prices'){
                    jQuery('#canal_venda').attr('data-user-prices', JSON.stringify(value))
                }
                if(value!=""){
                    jQuery('[data-filled=' + key + ']').val(value);
                }
            }
        }
    });
});

jQuery(document).on('click', '#has-shipping', function (e){
    this.toggleAttribute('checked');
   if(jQuery(this).is(':checked')){
       jQuery('#form-shipping').show();
   }else{
       jQuery('#form-shipping').hide();
       jQuery('#form-shipping').find('input').each(function (i, el) {
           jQuery(el).val('');
           jQuery(el).prop('readonly', false);
       });
   }
});
jQuery(document).on('click', '#send-estimate', function (e) {
    if(jQuery(this).is(':checked')){
        jQuery('#payment-order').attr('disabled', true).prop('checked', false);
        jQuery('.estimate-box').show();
        jQuery('.estimate').attr('disabled', false).prop('required', true);
        jQuery('.switch-tmce').trigger('click');
        jQuery('.wp-editor-tools').hide();
    }else{
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