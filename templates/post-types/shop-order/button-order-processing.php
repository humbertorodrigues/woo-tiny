<div class="clear"></div>
<div class="button-order-processing">
    <p>
        <a href="<?= wp_nonce_url( admin_url( 'admin-ajax.php?action=woocommerce_mark_order_status&status=processing&order_id=' . $order->get_id() ), 'woocommerce-mark-order-status' ) ?>" class="button button-primary wc-action-button wc-action-button-processing processing">Aprovar Pedido</a>
    </p>
</div>