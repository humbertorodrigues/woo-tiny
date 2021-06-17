<img src="<?= WOO_TINY_DIR . 'templates/pdfs/assets/images/estimate_header.jpg' ?>" alt="">
<div class="header">
    <h2 class="text-uppercase text-center">Proposta Comercial</h2>
    <p>São Paulo, <?= $estimate_created ?></p>
    <span><strong class="text-uppercase">Cliente: </strong> <?= $estimate_customer ?></span><br>
    <span><strong class="text-uppercase">Projeto: </strong> <?= $estimate_id ?></span><br>
    <span><strong class="text-uppercase">A/C: </strong> <?= $estimate_contact ?></span><br>
    <p>
        <strong>ESCOPO</strong><br>
        <span><?= $estimate_header ?></span>
    </p>
</div>
<table class="products">
    <thead>
    <tr>
        <th class="text-uppercase">Produto</th>
        <th class="text-uppercase">Valor</th>
        <th class="text-uppercase">Quantidade (un)</th>
        <th class="text-uppercase">Total</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($estimate_items as $item): ?>
    <tr>
        <td><?= $item['name'] ?></td>
        <td><?= $item['price'] ?></td>
        <td><?= $item['qtd'] ?></td>
        <td><?= $item['total'] ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="bg-dark">
        <td colspan="3" class="text-uppercase">Descontos</td>
        <td><?= $estimate_discount_total ?></td>
    </tr>
    <tr class="bg-dark">
        <td colspan="3" class="text-uppercase">Total</td>
        <td><?= $estimate_total ?></td>
    </tr>
    </tbody>
</table>
<div class="footer">
    <?= $estimate_footer ?>
</div>
<p>
    <strong>Entrega: </strong><br>
    <span>A definir local de entrega e prazo.</span>
</p>
<p>
    <?= $estimate_seller_name ?><br>
    <a href="mailto:<?= $estimate_seller_email ?>"><?= $estimate_seller_email ?></a><br>
</p>