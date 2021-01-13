
<?php 
class pedidos{
    public function __construct(){
        // add_action( 'woocommerce_order_status_processing', array($this,'enviar_pedido') ,1);
    }
    public function enviar_pedido( $order_id ) {
        global $wpdb;
        $dados['acao'] = "enviar_pedido";
        $dados['id_pedido'] = $order_id;
        $dados['status'] = "pendente";

        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);
    }
}
?>