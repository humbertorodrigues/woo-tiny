<html>
<head>
    <style>
        body{
            font-family: sans-serif;
        }
        table.products{
            width: 100%;
            font-size: 12px;
        }
        table.products tr th,
        table.products tr td{
            padding: .5rem;
        }
        table.products,
        table.products tr th,
        table.products tr td{
            border: solid 1px black;
            border-collapse: collapse;
        }
        .text-uppercase{
            text-transform: uppercase !important;
        }
        .bg-dark,
        .bg-dark td{
            background-color: black !important;
            color: white !important;
            font-weight: bold;
        }
    </style>
</head>
<body>
<img src="<?= WOO_TINY_DIR . 'templates/pdfs/assets/images/estimate_header.jpg' ?>">
<?= $estimate_doc['header'] ?? '' ?>
<table class="products">
    <thead>
    <tr>
        <th class="text-uppercase">Produto</th>
        <th class="text-uppercase">Preço Cheio B2B</th>
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
<?= $estimate_doc['footer'] ?? '' ?>
<img src="<?= WOO_TINY_DIR . 'templates/pdfs/assets/images/estimate_footer.jpg' ?>">
</body>
</html>
