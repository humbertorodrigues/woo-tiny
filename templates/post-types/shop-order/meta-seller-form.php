<p class="form-field form-field-wide woo-tiny-seller">
    <label for="woo-tiny-seller">Vendedor</label>
    <select name="bw_id_vendedor" id="woo-tiny-seller">
        <option value="">Selecionar vendedor</option>
        <?php foreach ($sellers as $user): ?>
            <option value="<?= $user->ID ?>" <?php if($user->ID == $seller_id): ?>selected<?php endif; ?>><?= $user->display_name ?></option>
        <?php endforeach; ?>
    </select>
</p>
<p class="form-field form-field-wide woo-tiny-pay-method">
    <label for="woo-tiny-pay-method">Meio de Pagamento</label>
    <select name="bw_forma_pagamento_id" id="woo-tiny-pay-method">
        <option value="">Selecionar meio de pagamento</option>
        <?php foreach ($payment_methods as $pay_method): ?>
            <option value="<?= $pay_method->ID ?>" <?php if($pay_method->ID == $payment_method_id): ?>selected<?php endif; ?>><?= $pay_method->post_title ?></option>
        <?php endforeach; ?>
    </select>
</p>
<p class="form-field form-field-wide woo-tiny-channel">
    <label for="woo-tiny-channel">Canal</label>
    <select name="bw_canal_venda" id="woo-tiny-channel">
        <option value="">Selecionar canal</option>
        <?php foreach ($channels as $chn): ?>
            <option value="<?= $chn->ID ?>" <?php if($chn->ID == $channel_id): ?>selected<?php endif; ?>><?= $chn->post_title ?></option>
        <?php endforeach; ?>
    </select>
</p>