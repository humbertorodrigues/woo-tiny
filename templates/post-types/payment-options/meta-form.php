<table class="form-table">
    <tbody>
    <tr>
        <th scope="row"><label for="discount">Desconto (%)</label></th>
        <td>
            <input name="discount" type="text" id="discount" value="<?= bw_get_meta_field('discount') ?>"
                   class="regular-text" required>
            <br>
            <small>Digite um número inteiro</small>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="payment_method">Meio de Pagamento</label></th>
        <td>
            <select name="payment_method" id="payment_method" class="regular-text" required>
                <?php foreach ($payment_methods as $val => $payment_method): ?>
                    <option value="<?= $val ?>" <?php if($val == bw_get_meta_field('payment_method')): ?>selected<?php endif; ?>><?= $payment_method ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <small>Selecione um meio de pagamento</small>
        </td>
    </tr>
    <tr>
        <th scope="row"><label for="payment_gateways">Gateway de Pagamento</label></th>
        <td>
            <select name="payment_gateway" id="payment_gateways" class="regular-text" required>
                <?php foreach ($payment_gateways as $val => $payment_gateway): ?>
                    <option value="<?= $val ?>" <?php if($val == bw_get_meta_field('payment_gateway')): ?>selected<?php endif; ?>><?= $payment_gateway ?></option>
                <?php endforeach; ?>
            </select>
            <br>
            <small>Selecione um gateway de pagamento</small>
        </td>
    </tr>
    <tr>
        <th scope="row"><label>Parcelas</label></th>
        <td>
            <div>
                <span aria-hidden="true" class="bw-installment-value" data-value="0">Não</span>
                <input type="range" min="0" max="1" id="installments" name="installments" value="<?= bw_get_meta_field('installments') ?>" class="bw-input-yes-no">
                <span aria-hidden="true" class="bw-installment-value" data-value="1">Sim</span>
            </div>
            <br>
        </td>
    </tr>
    </tbody>
</table>