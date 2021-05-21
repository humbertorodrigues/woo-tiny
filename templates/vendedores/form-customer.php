<h3>Dados do vendedor no tiny</h3>
<table class="form-table">
    <tr>
        <th><label for="bw_id_vendedor_tiny_bw">Código vendedor (conta Bueno Wines)</label></th>
        <td>
            <input type="text" name="bw_id_vendedor_tiny_bw" id="bw_id_vendedor_tiny_bw"
                   value="<?= $bw_id_vendedor_tiny_bw ?>" class="regular-text"/><br/>

        </td>
    </tr>
    <tr>
        <th><label for="bw_id_vendedor_tiny_vinicola">Código vendedor (conta Vinícola)</label></th>
        <td>
            <input type="text" name="bw_id_vendedor_tiny_vinicola" id="bw_id_vendedor_tiny_vinicola"
                   value="<?= $bw_id_vendedor_tiny_vinicola ?>" class="regular-text"/><br/>

        </td>
    </tr>
</table>


<div class="container bw-custom-product-price">
    <div class="col bw-custom-product-price">
        <h3>Alterar preços produtos</h3>
        <table class="form-table" id="form-bw-custom-product-price">
            <tr>
                <th scope="row"><label for="product_id">Produto</label></th>
                <td>
                    <select id="product_id" class="input bw-custom-product-price">
                        <option value="" selected>Selecione um produto...</option>
                        <?php foreach ($produtos as $produto): ?>
                            <option value="<?= $produto->get_id() ?>"><?= $produto->get_name() ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="channel_id">Canal de venda</label></th>
                <td>
                    <select id="channel_id" class="input bw-custom-product-price">
                        <option value="" selected>Selecione um canal...</option>
                        <?php foreach ($canais as $canal): ?>
                            <option value="<?= $canal->ID ?>"><?= $canal->post_title ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th scope="row"><label for="new_price">Novo preço</label></th>
                <td>
                    <input type="text" id="new_price" class="regular-text input bw-custom-product-price">
                </td>
            </tr>
            <tr>
                <th scope="row"></th>
                <td class="button-container bw-custom-product-price">
                    <button type="button" class="button button-primary" id="add-bw-custom-product-price">Adicionar</button>
                </td>
            </tr>
        </table>
    </div>
    <div class="col bw-custom-product-price">
        <h3>Produtos alterados</h3>
        <table class="wp-list-table widefat fixed striped">
            <thead>
            <th>Produto</th>
            <th>Canal</th>
            <th>Preço</th>
            <th>Ação</th>
            </thead>
            <tbody id="content-bw-custom-product-price" data-action="woo_tiny_customer_load_content_custom_product_price" data-userId="<?= $user->ID ?>">
                <?php foreach ($custom_products_prices as $key => $products_price): ?>
                <tr>
                    <td><?= $products_price['product_name'] ?></td>
                    <td><?= $products_price['channel_name'] ?></td>
                    <td><?= $products_price['new_price'] ?></td>
                    <td><a href="javascript:;" data-productPrice="<?= $key ?>" data-userId="<?= $user->ID ?>" id="delete-bw-custom-product-price">&times;</a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>