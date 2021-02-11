<?php 
class contasPagar{
    public function __construct(){
        add_action( "xml_nf_obtido", array($this,'acao_calcular_impostos'));
        // add_action( 'lancar_icms_mensal', array($this,'lancar_icms_mensal') );
        // add_action( 'lancar_ipi_mensal', array($this,'lancar_ipi_mensal') );
        // add_action( 'lancar_pis_mensal', array($this,'lancar_pis_mensal') );
        if(isset($_GET["calcular"])){

            if($_GET["calcular"] == 'icms') {
                global $wpdb;
    
                $dados['acao'] = "lancar_imposto_icms";
                $dados['empresa'] = "bueno";
                $dados['status'] = "pendente";
                $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);
    
                $dados['acao'] = "lancar_imposto_icms";
                $dados['empresa'] = "vinicola";
                $dados['status'] = "pendente";
                $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);
    
            }
        }

    }
    public function lancar_icms_mensal($empresa){
        global $tiny;
        $url = 'https://api.tiny.com.br/api2/conta.pagar.incluir.php';

        $data_atual = new DateTime();
        $data_final = $data_atual->format("Y-m-d");
        $data_inicial = $data_atual->modify('-2 month')->format("Y-m-d");
        $data_atual = new DateTime();
        $mes_lancamento = $data_atual->modify('-1 month')->format('m');
        
        $vencimento_sp = new DateTime();
        $vencimento_rs = new DateTime();
        $vencimento_sp = $vencimento_sp->modify("first day of this month");
        $vencimento_sp = $vencimento_sp->modify("+19 days");
        
        $vencimento_rs = $vencimento_rs->modify("first day of this month");
        $vencimento_rs = $vencimento_rs->modify("+11 days");
        
        
        while ($vencimento_sp->format('N')>=6) {
            $vencimento_sp = $vencimento_sp->modify("+1 day");
        }
        while ($vencimento_rs->format('N')>=6) {
            $vencimento_rs = $vencimento_rs->modify("-1 day");
        }

        $icms_sp = 0;
        $icms_rs = 0;
        $pedido_sp = [];
        $pedido_rs = [];
        $query = new WC_Order_Query( array(
            'limit' => -1,
            'date_paid' => $data_inicial.'...'.$data_final,
            'meta_key'     => 'tiny_xml_nf', // The postmeta key field
            'meta_compare' => 'EXISTS',
        ) );
        $orders = $query->get_orders();

        foreach ($orders as $key => $order) {
            $order_id = $order->data['id'];
            $tiny->setEmpresa($order_id);
            $xml_nf = get_post_meta($order_id,'tiny_xml_nf',true);
            $impostos = simplexml_load_string($xml_nf)->nfeProc->NFe->infNFe->total->ICMSTot;
        
            $numero_nf = simplexml_load_string($xml_nf)->nfeProc->NFe->infNFe->ide->nNF;
            $data_emissao_nf = simplexml_load_string($xml_nf)->nfeProc->NFe->infNFe->ide->dhEmi;

            $ipi = floatval($impostos->vIPI);
            $pis = floatval($impostos->vPIS);
            $cofins = floatval($impostos->vCOFINS);

            $icms = floatval($impostos->vICMS);
            $icms_uf_destino = floatval($impostos->vICMSUFDest);
            $icms_uf_origem = floatval($impostos->vICMSUFRemet);
            $icms_st = floatval($impostos->vST);

            $fcp = floatval($impostos->vFCP);
            $fcp_uf_destino = floatval($impostos->vFCPUFDest);
            $fcp_st = floatval($impostos->vFCPST);
            $data_nf = new DateTime($data_emissao_nf);
            $data_atual = new DateTime();

            if($data_nf->format('m') != $mes_lancamento){
                //Trata-se de uma nota emitida em outro mês. Seguimos em frente
                continue;
            }

            if($tiny->getEmpresa()=="vinicola"){
                $icms_rs += $icms;
            }else{
                $icms_sp += $icms;
            }

            
        }
        if($empresa == "bueno"){
            $data = array (
                'conta' => 
                array (
                    'cliente' => 
                    array (
                        'nome' => 'SEFAZ SP'
                    ),
                    'vencimento' => $vencimento_sp->format('d/m/Y'),
                    'valor' => $icms_sp,
                    'historico' => "ICMS",
                    'categoria' => 'Impostos',
                    'ocorrencia' => 'U',
                ),
            );
        }
        if($empresa == "vinicola"){
            $data = array (
                'conta' => 
                array (
                    'cliente' => 
                    array (
                        'nome' => 'SEFAZ RS'
                    ),
                    'vencimento' => $vencimento_rs->format('d/m/Y'),
                    'valor' => $icms_rs,
                    'historico' => "ICMS",
                    'categoria' => 'Impostos',
                    'ocorrencia' => 'U',
                ),
            );
        }
        $tiny->setEmpresa($empresa);
        $token = $tiny->getToken();
        $dados_url = "token=$token&conta=".json_encode($data)."&formato=json";
        $retorno = json_decode($tiny->enviarREST($url, $dados_url));
        
        
        if($retorno->retorno->status_processamento=="3"){
            return true;
        }else{
            return false;
        }
        return false;
        
    }
    public function acao_calcular_impostos($id_pedido){
        global $wpdb;

        $dados['acao'] = "lancar_imposto_icms_uf_destino";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);

        $dados['acao'] = "lancar_imposto_icms_uf_origem";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);

        $dados['acao'] = "lancar_imposto_icms_st";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);

        $dados['acao'] = "lancar_imposto_fcp";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);

        $dados['acao'] = "lancar_imposto_fcp_uf_destino";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);

        $dados['acao'] = "lancar_imposto_fcp_st";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);
    }

    public function lancarImposto($id_pedido, $imposto){
        global $tiny;
        $url = 'https://api.tiny.com.br/api2/conta.pagar.incluir.php';
        $tiny->setEmpresa($id_pedido);
        $token = $tiny->getToken();
        $empresa = $tiny->getEmpresa();
        
        $order = wc_get_order($id_pedido);
        $endereco_shipping = $order->get_address();
        $estado = $endereco_shipping['state'];
        if($empresa=="vinicola"){
            $estado_origem="RS";
        }else{            
            $estado_origem="SP";
        }
        $xml_nf = get_post_meta($id_pedido,'tiny_xml_nf',true);
        $impostos = simplexml_load_string($xml_nf)->nfeProc->NFe->infNFe->total->ICMSTot;
        
        $numero_nf = simplexml_load_string($xml_nf)->nfeProc->NFe->infNFe->ide->nNF;
        $data_emissao_nf = (simplexml_load_string($xml_nf)->nfeProc->NFe->infNFe->ide->dhEmi);

        $ipi = floatval($impostos->vIPI);
        $pis = floatval($impostos->vPIS);
        $cofins = floatval($impostos->vCOFINS);

        $icms = floatval($impostos->vICMS);
        $icms_uf_destino = floatval($impostos->vICMSUFDest);
        $icms_uf_origem = floatval($impostos->vICMSUFRemet);
        $icms_st = floatval($impostos->vST);

        $fcp = floatval($impostos->vFCP);
        $fcp_uf_destino = floatval($impostos->vFCPUFDest);
        $fcp_st = floatval($impostos->vFCPST);
        $data_atual = new DateTime($data_emissao_nf);
        
        switch ($imposto) {
            // case 'ipi':
            //     $data = array (
            //         'conta' => 
            //         array (
            //           'cliente' => 
            //           array (
            //             'nome' => 'MINISTERIO DA FAZENDA'
            //           ),
            //           'vencimento' => $data_atual->modify('+1 day')->format('d/m/Y'),
            //           'valor' => $ipi,
            //           'historico' => "IPI referente ao Pedido $id_pedido - Nota Fiscal $numero_nf",
            //           'categoria' => 'Impostos',
            //           'ocorrencia' => 'U',
            //         ),
            //     );
            //     break;
            
            // case 'pis':
            //     $data = array (
            //         'conta' => 
            //         array (
            //             'cliente' => 
            //             array (
            //             'nome' => 'MINISTERIO DA FAZENDA'
            //             ),
            //             'vencimento' => $data_atual->modify('+1 day')->format('d/m/Y'),
            //             'valor' => $pis,
            //             'historico' => "IPI referente ao Pedido $id_pedido - Nota Fiscal $numero_nf",
            //             'categoria' => 'Impostos',
            //             'ocorrencia' => 'U',
            //         ),
            //     );
            //     break;
            // case 'cofins':
            //     $data = array (
            //         'conta' => 
            //         array (
            //             'cliente' => 
            //             array (
            //             'nome' => 'MINISTERIO DA FAZENDA'
            //             ),
            //             'vencimento' => $data_atual->modify('+1 day')->format('d/m/Y'),
            //             'valor' => $cofins,
            //             'historico' => "COFINS referente ao Pedido $id_pedido - Nota Fiscal $numero_nf",
            //             'categoria' => 'Impostos',
            //             'ocorrencia' => 'U',
            //         ),
            //     );
            //     break;
            case 'icms_uf_destino':
                $data = array (
                    'conta' => 
                    array (
                        'cliente' => 
                        array (
                        'nome' => 'SEFAZ '.$estado
                        ),
                        'vencimento' => $data_atual->modify('+1 day')->format('d/m/Y'),
                        'valor' => $icms_uf_destino,
                        'historico' => "ICMS UF DESTINO referente ao Pedido $id_pedido - Nota Fiscal $numero_nf",
                        'categoria' => 'Impostos',
                        'ocorrencia' => 'U',
                    ),
                );
                break;
            case 'icms_uf_origem':
                $data = array (
                    'conta' => 
                    array (
                        'cliente' => 
                        array (
                        'nome' => 'SEFAZ '.$estado_origem
                        ),
                        'vencimento' => $data_atual->modify('+1 day')->format('d/m/Y'),
                        'valor' => $icms_uf_origem,
                        'historico' => "ICMS UF ORIGEM referente ao Pedido $id_pedido - Nota Fiscal $numero_nf",
                        'categoria' => 'Impostos',
                        'ocorrencia' => 'U',
                    ),
                );
                break;
            case 'icms_st':
                $data = array (
                    'conta' => 
                    array (
                        'cliente' => 
                        array (
                        'nome' => 'SEFAZ '.$estado_origem
                        ),
                        'vencimento' => $data_atual->modify('+1 day')->format('d/m/Y'),
                        'valor' => $icms_st,
                        'historico' => "ICMS ST referente ao Pedido $id_pedido - Nota Fiscal $numero_nf",
                        'categoria' => 'Impostos',
                        'ocorrencia' => 'U',
                    ),
                );
                break;
            case 'fcp':
                $data = array (
                    'conta' => 
                    array (
                        'cliente' => 
                        array (
                            'nome' => 'SEFAZ '.$estado_origem
                        ),
                        'vencimento' => $data_atual->modify('+1 day')->format('d/m/Y'),
                        'valor' => $fcp,
                        'historico' => "FCP referente ao Pedido $id_pedido - Nota Fiscal $numero_nf",
                        'categoria' => 'Impostos',
                        'ocorrencia' => 'U',
                    ),
                );
                break;
            case 'fcp_st':
                $data = array (
                    'conta' => 
                    array (
                        'cliente' => 
                        array (
                            'nome' => 'SEFAZ '.$estado_origem
                        ),
                        'vencimento' => $data_atual->modify('+1 day')->format('d/m/Y'),
                        'valor' => $fcp_st,
                        'historico' => "FCP ST referente ao Pedido $id_pedido - Nota Fiscal $numero_nf",
                        'categoria' => 'Impostos',
                        'ocorrencia' => 'U',
                    ),
                );
                break;
            case 'fcp_uf_destino':
                $data = array (
                    'conta' => 
                    array (
                        'cliente' => 
                        array (
                            'nome' => 'SEFAZ '.$estado
                        ),
                        'vencimento' => $data_atual->modify('+1 day')->format('d/m/Y'),
                        'valor' => $fcp_uf_destino,
                        'historico' => "FCP UF DESTINO referente ao Pedido $id_pedido - Nota Fiscal $numero_nf",
                        'categoria' => 'Impostos',
                        'ocorrencia' => 'U',
                    ),
                );
                break;
            // case 'icms':
            //     $data = array (
            //         'conta' => 
            //         array (
            //             'cliente' => 
            //             array (
            //                 'nome' => 'SEFAZ '.$estado_origem
            //             ),
            //             'vencimento' => $data_atual->modify('+1 day')->format('d/m/Y'),
            //             'valor' => $icms,
            //             'historico' => "ICMS referente ao Pedido $id_pedido - Nota Fiscal $numero_nf",
            //             'categoria' => 'Impostos',
            //             'ocorrencia' => 'U',
            //         ),
            //     );
            //     break;
        }
        
        
        $dados_url = "token=$token&conta=".json_encode($data)."&formato=json";
        $retorno = json_decode($tiny->enviarREST($url, $dados_url));
        
        
        if($retorno->retorno->status_processamento=="3"){
            return true;
        }else{
            return false;
        }
        return false;
    }
    
}
?>