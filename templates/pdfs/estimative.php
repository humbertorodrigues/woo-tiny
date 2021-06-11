<img src="<?= WOO_TINY_DIR . 'templates/pdfs/assets/images/estimate_header.jpg' ?>" alt="">
<?= $estimate_doc['header'] ?? '' ?>
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
    <?php foreach($items as $item): ?>
    <tr>
        <td><?= $item->get_name() ?></td>
        <td><?= 'R$ ' . number_format($item->get_total() / $item->get_quantity(), 2, ',', '.') ?></td>
        <td><?= $item->get_quantity() ?></td>
        <td><?= 'R$ ' . number_format($item->get_total(), 2, ',', '.') ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="bg-dark">
        <td colspan="3" class="text-uppercase">Descontos</td>
        <td><?= 'R$ ' . number_format($estimate->get_discount_total(), 2, ',', '.') ?></td>
    </tr>
    <tr class="bg-dark">
        <td colspan="3" class="text-uppercase">Total</td>
        <td><?= 'R$ ' . number_format($estimate->get_total(), 2, ',', '.') ?></td>
    </tr>
    </tbody>
</table>
<?php echo $estimate_doc['footer'] ?? '';