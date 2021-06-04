<html>
<head>
    <style>
        h1{
            text-align: center;
            font-size: 18px;
        }
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
<h1 class="text-uppercase">Proposta Comercial</h1>
<p>São Paulo, <?= convert_date($estimate->get_date_created(), false, 'full') ?></p>
<span><strong class="text-uppercase">Cliente: </strong> Fulano de Tal</span><br>
<span><strong class="text-uppercase">Projeto: </strong> Teste #<?= $estimate_id ?></span><br>
<span><strong class="text-uppercase">A/C: </strong> Ciclano de Tal</span><br>
<p>
    <strong>ESCOPO</strong><br>
    <span>Conforme solicitado, segue abaixo proposta para garrafas do rótulo Paralelo 31 a ser comercializado em um bundle junto o produto Wine Popper Black, tanto em pré-venda quanto na fase de vendas diretamente.</span>
</p>
<table class="products">
    <thead>
    <tr>
        <th class="text-uppercase">Produto</th>
        <th class="text-uppercase">Preço Cheio B2B</th>
        <th class="text-uppercase">Preço (35%)</th>
        <th class="text-uppercase">Quantidade (un)</th>
        <th class="text-uppercase">Total</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach($items as $item): ?>
    <tr>
        <td><?= $item->get_name() ?></td>
        <td><?= $item->get_total() / $item->get_quantity() ?></td>
        <td><?= ($item->get_total() / $item->get_quantity()) ?></td>
        <td><?= $item->get_quantity() ?></td>
        <td><?= $item->get_total() ?></td>
    </tr>
    <?php endforeach; ?>
    <tr class="bg-dark">
        <td colspan="4" class="text-uppercase">Total</td>
        <td><?= $estimate->get_total() ?></td>
    </tr>
    </tbody>
</table>
<p>
    Para a volumetria sugerida acima, aplicam-se as seguintes negociações:
</p>
<ul>
    <li>35% off no valor cheio praticado para o B2B;</li>
    <li>As primeiras 100 garrafas pagas no prato de pagamento acordado;</li>
    <li>As 200 garrafas restantes pagas conforme comercialização do cliente;</li>
    <li>As garrafas que não forem vendidas no bundle poderão ser disponibilizadas no site futurewine.com.br individualmente;</li>
    <li>Cessão dos direitos de uso de trecho do material já produzido pelo Galvão Bueno utilizando o Wine Popper para veiculação em mídia, com cortes e edição por conta do cliente;</li>
    <li>Comercialização do bundle será feita no e-commerce da Wine Popper e no da Bueno Wines, com redirect para transacional no checkout do cliente;</li>
    <li>Serão enviadas caixas para transporte do vinho, mas logística de entrega para ao bundle será de responsabilidade do cliente.</li>
</ul>
<p>
    <strong>Condições de pagamento: </strong><br>
    <span>14, 21 e 28 ddl, preferencialmente em boleto.</span>
</p>
<p>
    <strong>Entrega: </strong><br>
    <span>A definir local de entrega e prazo.</span>
</p>
<p>
    Victória Galvão Bueno<br>
    Head de Growth<br>
    <a href="mailto:victoria@buenowines.com.br">victoria@buenowines.com.br</a><br>
    11 985224308
</p>
<img src="<?= WOO_TINY_DIR . 'templates/pdfs/assets/images/estimate_footer.jpg' ?>">
</body>
</html>
