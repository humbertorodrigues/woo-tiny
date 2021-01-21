<?php
class estoque {
    public function __construct() {
        add_action('woocommerce_product_options_general_product_data', function () {
            
            woocommerce_wp_text_input([
                'id' => 'id_produto_tiny',
                'label' => 'Id do produto no tiny (Utilize o código do produto no cadastro da Bueno Wines no Tiny)',
            ]);
        }, 9);
        add_action('woocommerce_process_product_meta', function ($post_id) {
            $product = wc_get_product($post_id);
            $id_produto_tiny = isset($_POST['id_produto_tiny']) ? $_POST['id_produto_tiny'] : '';
            $product->update_meta_data('id_produto_tiny', sanitize_text_field($id_produto_tiny));

            $product->save();
        });
        add_action( 'init', (array($this,'acao_atualizar_estoque')) );
        if ( ! wp_next_scheduled( 'atualizar_estoque_tiny' ) ) {
            wp_schedule_event( time(), 'hourly', 'atualizar_estoque_tiny' );
        }
    }
    public function acao_atualizar_estoque(){
        global $wpdb;
        $query = new WC_Product_Query( array(
            'limit' => -1,
            'return' => 'ids',
        ) );
        $products = $query->get_products();
        foreach ($products as $key => $product) {
            
            $dados['acao'] = "atualizar_estoque";
            $dados['id_produto'] = $product;
            $dados['status'] = "pendente";
            $wpdb->insert($wpdb->prefix."acoes_tiny",$dados);
            
        }


    }
    public function atualizarEstoque($id_produto){
        global $tiny;
        $id_produto_tiny = get_post_meta($id_produto,"id_produto_tiny", true);
        $total_estoque = 0;
        if(!is_numeric($id_produto_tiny)){
            
            return 0;
        }
        $url = 'https://api.tiny.com.br/api2/produto.obter.estoque.php';
        if(is_numeric($id_produto_tiny)){
            $tiny->setEmpresa("bueno");
            $token = $tiny->getToken();
            $data = "token=$token&id=$id_produto_tiny&formato=json";
            $retorno_estoque = json_decode($tiny->enviarREST($url, $data));
            if($retorno_estoque->retorno->status_processamento=="3"){
                
                $estoques  = $retorno_estoque->retorno->produto->depositos;
                foreach ($estoques as $key => $estoque) {
                    $total_estoque += $estoque->deposito->saldo;
                }
                update_post_meta($id_produto,"tiny_estoque",$estoques);
                wc_update_product_stock($id_produto,$total_estoque);
                return $total_estoque;

            }else{
                return false;
            }
        }
        
    }
}
