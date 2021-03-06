<?php 
class tiny{
    private $token;
    private $empresa;
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
            
            /*Tivemos uma excessão onde todos pedidos foram encaminhados para a vinicola. Pedidos nessas condições tem a tratativa especial abaixo */
            $excessao = get_post_meta($id_pedido,"excessao_cnpj",true);
            if($excessao == "vinicola"){
                $empresa = "vinicola";
            }
            $this->empresa = $empresa;
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
    public function getEmpresa(){
        return $this->empresa;
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
        global $contasPagar;
        global $pedidos;
        $acoes = $wpdb->get_results("SELECT * FROM ".$wpdb->prefix."acoes_tiny WHERE status='pendente' ORDER BY ultima_execucao ASC LIMIT 50");
        foreach ($acoes as $key => $acao) {
            $id_acao = $acao->id;
            $texto_acao = $acao->acao;
            $id_pedido = $acao->id_pedido;
            
            if($texto_acao=="obter_xml_nf"){
                $resultado = $notasFiscais->obterXML($id_pedido);
                if($resultado===true){
                    $this->marcarConcluido($id_acao);
                    $wpdb->query("UPDATE ".$wpdb->prefix."acoes_tiny SET status='concluido', data_execucao=NOW() WHERE id=".$id_acao."");
                }else{
                    
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="atualizar_estoque"){                
                $retorno_estoque = $estoque->atualizarEstoque($acao->id_produto);
                if($retorno_estoque!==false){

                    $this->marcarConcluido($id_acao);
                    
                }else{
                    
                    $this->marcarExecutado($id_acao);
                }
            }

            if($texto_acao=="lancar_imposto_icms_uf_destino"){                
                $retorno_imposto = $contasPagar->lancarImposto($acao->id_pedido, 'icms_uf_destino');
                if($retorno_imposto!==false){
                    $this->marcarConcluido($id_acao);
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="lancar_imposto_icms_uf_origem"){                
                $retorno_imposto = $contasPagar->lancarImposto($acao->id_pedido, 'icms_uf_origem');
                if($retorno_imposto!==false){
                    $this->marcarConcluido($id_acao);
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="lancar_imposto_icms_st"){                
                $retorno_imposto = $contasPagar->lancarImposto($acao->id_pedido, 'icms_st');
                if($retorno_imposto!==false){
                    $this->marcarConcluido($id_acao);
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="lancar_imposto_fcp"){                
                $retorno_imposto = $contasPagar->lancarImposto($acao->id_pedido, 'fcp');
                if($retorno_imposto!==false){
                    $this->marcarConcluido($id_acao);
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="lancar_imposto_fcp_uf_destino"){                
                $retorno_imposto = $contasPagar->lancarImposto($acao->id_pedido, 'fcp_uf_destino');
                if($retorno_imposto!==false){
                    $this->marcarConcluido($id_acao);
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="lancar_imposto_fcp_st"){                
                $retorno_imposto = $contasPagar->lancarImposto($acao->id_pedido, 'fcp_st');
                if($retorno_imposto!==false){
                    $this->marcarConcluido($id_acao);
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="lancar_imposto_icms"){       
                $empresa = $acao->empresa;         
                $retorno_imposto = $contasPagar->lancar_icms_mensal($empresa);
                if($retorno_imposto!==false){
                    $this->marcarConcluido($id_acao);
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="lancar_imposto_pis"){       
                $empresa = $acao->empresa;         
                $retorno_imposto = $contasPagar->lancar_pis_mensal($empresa);
                if($retorno_imposto!==false){
                    $this->marcarConcluido($id_acao);
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="lancar_imposto_cofins"){       
                $empresa = $acao->empresa;         
                $retorno_imposto = $contasPagar->lancar_cofins_mensal($empresa);
                if($retorno_imposto!==false){
                    $this->marcarConcluido($id_acao);
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="lancar_imposto_ipi"){       
                $empresa = $acao->empresa;         
                $retorno_imposto = $contasPagar->lancar_ipi_mensal($empresa);
                if($retorno_imposto!==false){
                    $this->marcarConcluido($id_acao);
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
            if($texto_acao=="contratar_frete"){       
                $flexas_is_packed = get_post_meta($id_pedido,"bw_status_is_packed",true);
                if($flexas_is_packed==="1"){
                    $retorno_frete = contratar_frete($id_pedido);
                    if($retorno_frete !==false){
                        $this->marcarConcluido($id_acao);
                        $dados['acao'] = "atualizar_rastreio";
                        $dados['id_pedido'] = $id_pedido;
                        $dados['status'] = "pendente";
                        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);
                    }else{
                        $this->marcarExecutado($id_acao);
                }
                }
            }
            if($texto_acao=="atualizar_rastreio"){       
                $retorno_rastreio = $pedidos->atualizarRastreio($id_pedido);
                
                if($retorno_rastreio !==false){
                    $this->marcarConcluido($id_acao);                    
                }else{
                    $this->marcarExecutado($id_acao);
                }
            }
        }
        
    }
    public function marcarConcluido($id_acao){
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."acoes_tiny SET status='concluido', data_execucao=NOW() WHERE id=".$id_acao."");

    }
    public function marcarExecutado($id_acao){
        global $wpdb;
        $wpdb->query("UPDATE ".$wpdb->prefix."acoes_tiny SET ultima_execucao=NOW() WHERE id=".$id_acao."");

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