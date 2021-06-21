
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