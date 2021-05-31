<div class="clear"></div>
<div class="woo-tiny-documents">
    <h4>Documentos:</h4>
    <p>
        <?php foreach ($attachments as $attachment): ?>
        <a href='<?= get_attachment_link( $attachment->ID ) ?>' title='<?= $attachment->post_title ?>'><?= $attachment->post_title ?></a>
        <br>
        <?php endforeach; ?>
    </p>

</div>