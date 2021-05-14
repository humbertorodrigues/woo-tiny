'use strict';
let $ = window.jQuery || window.$;
$(document).on('keyup blur', '[data-load-address]', function (e) {
    e.preventDefault();
    let inputDefault = $(this);
    if (e.type === 'blur' && inputDefault.data('load-address') === 'filled') return false;
    let zipCode = inputDefault.val().replace(/\D/g, '');
    if (zipCode.length === 8) {
        inputDefault.attr('data-load-address', '');
        let inputs = inputDefault.closest(".form-address").find('[data-address]');
        inputs.prop('readonly', true);
        inputs.val('...');
        $.get('https://viacep.com.br/ws/' + zipCode + '/json/', function (res) {
            inputs.each(function (i, value) {
                let input = $(value);
                
                if(input.attr('data-readonly')=="false"){
                    
                    input.prop('readonly', false);
                }
                input.val('');
                let key = input.data('address');
                input.val(res[key]);
                if (!input.val()) {
                    input.prop('readonly', false);
                }
            }).firstEmptyFieldFocus();
            inputDefault.attr('data-load-address', 'filled');
        }).fail(function () {
            inputs.val('');
            inputs.prop('readonly', false);
            inputs.firstEmptyFieldFocus();
        });
    }
});

$.fn.extend({
    firstEmptyFieldFocus: function () {
        return this.filter(function (i, v) {
            let input = $(v);
            return !input.val();
        }).first().focus();
    },
    triggerMask: function () {
        this.each(function (e, t) {
            if (!$(t).hasClass('no-trigger')) {
                $(t).trigger('keyup');
            }
        });
    }
});