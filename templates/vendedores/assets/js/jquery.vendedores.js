'use strict';
if($ === undefined) {
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
        if(res.discount_type){
            let dataCoupon = $('#data-coupon');
            dataCoupon.attr('data-coupon-type', res.discount_type);
            dataCoupon.attr('data-coupon-amount', res.amount);
            calcula_subtotal();
        }
    });
});


$.fn.extend({
    limitBonus: function (limit = 5) {
        let inputQtd = $(this).find('input.qtd');
        let inputBonus = $(this).find('input.qtd_bonificacao');
        let amount = parseInt(inputQtd.val());
        let bonus = parseInt(inputBonus.val());
        inputQtd.val(amount);
        if(amount > 0) {
            if(((bonus/amount) * 100) <= limit){
                inputBonus.val(bonus);
                return true;
            }
            let difference = Math.floor((limit * amount)/100);
            inputBonus.val(bonus > 0 ? difference : 0);
            return false;
        }
        inputBonus.val(0);
        return false;
    }
});