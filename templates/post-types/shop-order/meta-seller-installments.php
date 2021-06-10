<div class="clear"></div>
<div class="woo-tiny-installments">
    <ul>
        <?php foreach ($installments as $installment_id => $installment): ?>
        <li><strong><?= $installment_id ?>ª parcela:</strong> <?= date('d/m/Y', strtotime($installment['duedate'])) ?></li>
        <?php endforeach; ?>
    </ul>
</div>

