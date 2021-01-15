<?php 
class contasPagar{
    public function __construct(){
        add_action("xml_nf_obtido", array($this,'acao_calcular_impostos'));
    }

    public function acao_calcular_impostos($id_pedido){
        global $wpdb;
        $dados['acao'] = "lancar_imposto_ipi";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);

        $dados['acao'] = "lancar_imposto_pis";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);

        $dados['acao'] = "lancar_imposto_cofins";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);

        $dados['acao'] = "lancar_imposto_icms_st";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);

        $dados['acao'] = "lancar_imposto_difal";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);

        $dados['acao'] = "lancar_imposto_fcp";
        $dados['id_pedido'] = $id_pedido;
        $dados['id_tiny'] = get_post_meta($id_pedido,"codigo_tiny",true);
        $dados['status'] = "pendente";
        $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);
    }
}
?>