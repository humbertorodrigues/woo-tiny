'use strict';
jQuery.fn.extend({
    bwLoadContentAdmin: function () {
        let data = this.data();
        let content = this;
        jQuery.get(woo_tiny.admin_ajax_url, data, function (res) {
            if(res.success) {
                content.html(res.data);
            }
        });
    }
});


jQuery(document).on('click', '#action-bw-custom-product-price', function (e) {
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
       if(res.success) {
           form.prepend('<div class="notice notice-success" id="bw-alert"><p>Novo Preço adicionado</p></div>');
           jQuery('#content-bw-custom-product-price').bwLoadContentAdmin();
       }else{
           form.prepend('<div class="notice notice-error" id="bw-alert"><p>Ops! Algo deu errado</p></div>');
       }
       setTimeout(function () {
           jQuery('#bw-alert').remove();
       }, 3600);
   });
});

jQuery('#content-bw-custom-product-price').bwLoadContentAdmin();