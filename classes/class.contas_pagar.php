<?php 
class contasPagar{
    public function __construct(){
        add_action( 'woocommerce_order_status_processing', array($this,'acao_obter_xml') );
    }
    public function acao_obter_xml( $order_id ) {

    }
}
?>