<?php 
class notasFiscais{
    public function __construct(){
        add_action( 'woocommerce_order_status_processing', array($this,'acao_obter_xml') );
    }
    public function acao_obter_xml( $order_id ) {
        global $wpdb;
        $dados['acao'] = "obter_xml_nf";
        $dados['id_pedido'] = $order_id;
        $dados['id_tiny'] = get_post_meta($order_id,"codigo_tiny",true);
        $dados['status'] = "pendente";

        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);
    }
    public function obterXML($id_pedido){
        global $tiny;
        $url = 'https://api.tiny.com.br/api2/nota.fiscal.obter.xml.php';
        $tiny->setEmpresa($id_pedido);
        $token = $tiny->getToken();
        $id_nota = get_post_meta($id_pedido,"tiny_nf",true);
        //Encerramos, ainda não temos o número da nota fiscal;
        if(!is_numeric($id_nota)){
            return false;
        }
        $id = $id_nota;
        $data = "token=$token&id=$id";
        
        $retorno = $tiny->enviarREST($url, $data);
        $xml = simplexml_load_string($retorno); 
        if($xml->status_processamento=="3"){
            $xml_nf = $xml->xml_nfe->asXML();
            do_action("xml_nf_obtido", $id_pedido);
            update_post_meta($id_pedido,"tiny_xml_nf",$xml_nf);
            return true;
        }else{
            return false;
        }
    }
}
?>