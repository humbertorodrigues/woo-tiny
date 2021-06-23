'use strict';
jQuery.fn.extend({
    bwLoadContentAdmin: function () {
        let data = this.data();
        let content = this;
        jQuery.get(woo_tiny.admin_ajax_url, data, function (res) {
            if (res.success) {
                content.html(res.data);
            }
        }).fail(function (error) {
            console.log('no content')
        });
    }
});

jQuery(document).on('click', '.bw-installment-value', function (e) {
    e.preventDefault();
    jQuery('.bw-input-yes-no').val(jQuery(this).data('value'));
});


jQuery(document).on('click', '#add-bw-custom-product-price', function (e) {
    e.preventDefault();
    let form = jQuery('#form-bw-custom-product-price');
    let data = {
        action: 'woo_tiny_update_price_product_by_user',
        nonce: woo_tiny.admin_nonce,
        user_id: jQuery('#user_id').val(),
        product_id: jQuery('#product_id').val(),
        channel_id: jQuery('#channel_id').val(),
        new_price: jQuery('#new_price').val()
    };

    jQuery.post(woo_tiny.admin_ajax_url, data, function (res) {
        if (res.success) {
            form.prepend('<div class="notice notice-success" id="bw-alert"><p>Novo Preço adicionado</p></div>');
            jQuery('#content-bw-custom-product-price').bwLoadContentAdmin();
        } else {
            form.prepend('<div class="notice notice-error" id="bw-alert"><p>Ops! Algo deu errado</p></div>');
        }
        setTimeout(function () {
            jQuery('#bw-alert').remove();
        }, 3600);
    });
});

jQuery(document).on('click', '#delete-bw-custom-product-price', function (e) {
    e.preventDefault();
    let data = jQuery(this).data();
    data.action = 'woo_tiny_delete_price_product_by_user';
    data.nonce = woo_tiny.admin_nonce;
    jQuery.post(woo_tiny.admin_ajax_url, data, function (res) {
        if (res.success) {
            jQuery('#content-bw-custom-product-price').bwLoadContentAdmin();
        }
    });
});
if (jQuery('#content-bw-custom-product-price').length > 0) {
    jQuery('#content-bw-custom-product-price').bwLoadContentAdmin();
}

jQuery(document).on('keyup blur', '.bw-custom-product-price', function (e) {
    jQuery(this).maskMoney({thousands: '.', decimal: ',', affixesStay: false});
});

// jQuery(document).ready(function () {
//     jQuery('.datepicker-year').datepicker({
//         changeMonth: true,
//         changeYear: true,
//         showButtonPanel: true,
//         dateFormat: 'MM yy',
//         onChangeMonthYear: function (year, month, inst){
//             let input = jQuery('#woo_tiny_goal');
//             input.attr('name', 'goal_' + year + '_' + month)
//             let data = {
//                 metakey: input.attr('name'),
//                 postid: jQuery('#post_ID').val()
//             };
//             data.action = 'woo_tiny_get_goal_by_channel';
//             data.nonce = woo_tiny.admin_nonce;
//             jQuery.post(woo_tiny.admin_ajax_url, data, function (res) {
//                 if (res.success) {
//                     input.val(res.data);
//                 }
//             });
//         }
//     });
//     jQuery('.datepicker-year').trigger('onChangeMonthYear');
// })
jQuery(document).ready(function () {
    let data_atual = new Date();
    jQuery("#data_meta").val(data_atual.getFullYear() + "-" + ("0" + (data_atual.getMonth() + 1)).slice(-2));
    jQuery("#data_meta").on('change', function () {
        let data_selecionada = jQuery("#data_meta").val();
        let dataF = new Date(data_selecionada + "-10");
        let month = dataF.getMonth() + 1;
        let year = dataF.getFullYear();
        let input = jQuery('#woo_tiny_goal');
        input.attr('name', 'goal_' + year + '_' + month)
        let data = {
            metakey: input.attr('name'),
            postid: jQuery('#post_ID').val()
        };
        data.action = 'woo_tiny_get_goal_by_channel';
        data.nonce = woo_tiny.admin_nonce;
        jQuery.post(woo_tiny.admin_ajax_url, data, function (res) {
            if (res.success) {
                input.val(res.data);
            }
        });

    })
})
jQuery(document).on('click', '#woo_tiny_save_goal_by_channel', function (e) {
    e.preventDefault();
    let input = jQuery('#woo_tiny_goal');
    let data = {
        metakey: input.attr('name'),
        metavalue: input.val(),
        postid: jQuery('#post_ID').val()
    };
    data.action = 'woo_tiny_save_goal_by_channel';
    data.nonce = woo_tiny.admin_nonce;
    jQuery.post(woo_tiny.admin_ajax_url, data, function (res) {
        if (res.success) {
            console.log(res.success)
            alert("Meta atualizada com sucesso")
        }
    });
});

jQuery(document).on('click', 'label[for=order_status]', function (e) {
    let ancor = jQuery(this).children('a');
    ancor.attr('target', '_blank');
});