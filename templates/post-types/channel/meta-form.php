
<table class="form-table">
    <tbody>
    <tr>
        <th scope="row"><label for="type">Tipo</label></th>
        <td>
            <select name="type" id="type" class="regular-text" required>
                <option value="B2B" <?php if ('B2B' == bw_get_meta_field('type')): ?>selected<?php endif; ?>>B2B
                </option>
                <option value="B2C" <?php if ('B2C' == bw_get_meta_field('type')): ?>selected<?php endif; ?>>B2C
                </option>
            </select>
            <br>
            <small>Selecione um tipo</small>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="payment_method">Meios de Pagamento</label></th>
        <td>
            <select name="payment_methods[]" id="payment_method" class="regular-text" multiple>
                <?php foreach ($payment_methods as $payment_method): ?>
                    <option value="<?= $payment_method->ID ?>" <?php if(in_array($payment_method->ID, explode(',', bw_get_meta_field('payment_methods')))): ?>selected<?php endif; ?>><?= $payment_method->post_title ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <small>Selecione um meio de pagamento</small>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="woo_tiny_goal">Meta</label></th>
        <td>
            <input id="data_meta" type="month"/>
                    <br>
            <!-- <input type="text" placeholder="Data"  value="<?php echo date('F Y') ?>" class="datepicker-year"> -->
            <br>
            <?php $name = 'goal_' . date('Y_n') ?>
            <input name="<?= $name ?>" id="woo_tiny_goal" type="text"
                   value="<?= bw_get_meta_field($name) ?>"
                   class="regular-text bw-custom-product-price" required>
            <br>
            <div>
                <br>
                <button type="button" class="button button-primary button-sm" id="woo_tiny_save_goal_by_channel">Salvar Meta</button>
            </div>
        </td>
    </tr>
    </tbody>
</table>