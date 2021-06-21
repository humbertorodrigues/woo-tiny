<div class="clear"></div>
<div class="woo-tiny-seller">
    <p>
        <strong>Vendedor: </strong> <?= $seller ?>
        <br>
        <strong>Forma de Pagamento: </strong> <?= $payment_method ?>
        <br>
        <strong>Canal: </strong>
        <select name="bw_canal_venda" id="">
            <option value=""></option>
            <?php foreach ($channels as $key => $ch) {
                ?> <option <?php echo $ch->ID==$channel_id?"selected":"" ?> value="<?php echo $ch->ID ?>"><?php echo $ch->post_title; ?></option>
                <?php
            } ?>
        </select>
        <br>
    </p>

</div>