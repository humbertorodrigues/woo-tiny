<?php 
class tiny{
    private $token;
    public function __construct(){
    }
    public function setEmpresa($id_pedido){
        if(is_numeric($id_pedido)){

            $pedido = wc_get_order( $id_pedido );
            $dados_cliente  = $pedido->data['shipping'];
            $estado = $dados_cliente['state'];
            
            if($estado == "RS" || $estado =="PR" || $estado=="SC" ){
                $empresa="vinicola";
            }else{
                $empresa="bueno";
            }
    
            $this->token = get_option("token_tiny_".$empresa);
        }else{ // Ao invés de passar o id do pedido, passamos qual empresa queremos
            if($id_pedido=="bueno"){

                $empresa = "bueno";
            }else{
                $empresa = "vinicola";

            }

            $this->token = get_option("token_tiny_".$empresa);
        }
    }
    public function getToken(){

        if(empty($this->token)){
            throw new Exception("Defina a empresa");
        }else{

            return $this->token;
        }
    }
    public function cron_acoes(){
        global $wpdb;
        global $notasFiscais;
        global $estoque;
        $acoes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."acoes_tiny WHERE status='pendente' ORDER BY id ASC, data ASC LIMIT 10");
        foreach ($acoes as $key => $acao) {
            $id_acao = $acao->id;
            $texto_acao = $acao->acao;
            $id_pedido = $acao->id_pedido;
            
            if($texto_acao=="obter_xml_nf"){
                $resultado = $notasFiscais->obterXML($id_pedido);
                if($resultado===true){
                    $this->marcarConcluido($id_acao);
                    $wpdb->query("UPDATE ".$wpdb->prefix."acoes_tiny SET status='concluido', data_execucao=NOW() WHERE id=".$id_acao."");
                }
            }
            if($texto_acao=="atualizar_estoque"){
                
                $retorno_estoque = $estoque->atualizarEstoque($acao->id_produto);
                if($retorno_estoque!==false){

                    $this->marcarConcluido($id_acao);
                    
                }
            }
        }
        
    }
    public function marcarConcluido($id_acao){
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."acoes_tiny SET status='concluido', data_execucao=NOW() WHERE id=".$id_acao."");

    }






    public function enviarREST($url, $data, $optional_headers = null){
        $params = array('http' => array(
            'method' => 'POST',
            'content' => $data
        ));

        if ($optional_headers !== null) {
            $params['http']['header'] = $optional_headers;
        }

        $ctx = stream_context_create($params);
        $fp = @fopen($url, 'rb', false, $ctx);
        if (!$fp) {
            throw new Exception("Problema com $url, $php_errormsg");
        }
        $response = @stream_get_contents($fp);
        if ($response === false) {
            throw new Exception("Problema obtendo retorno de $url, $php_errormsg");
        }
        
        return $response;
    }
}

?> 