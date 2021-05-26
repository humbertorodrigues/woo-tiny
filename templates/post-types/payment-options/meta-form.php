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
        <th scope="row"><label for="installments">Parcelas</label></th>
        <td>
            <input name="installments" type="number" min="1" id="installments" value="<?= bw_get_meta_field('installments') ?>"
                   class="regular-text" required>
            <br>
            <small>Digite um número inteiro</small>
        </td>
    </tr>
    </tbody>
</table>