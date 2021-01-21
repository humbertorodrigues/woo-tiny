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
        add_action( 'init', function(){
            add_action('atualizar_estoque_tiny_novo',array($this,'atualizar_estoque_tiny'));
            if ( ! wp_next_scheduled( 'atualizar_estoque_tiny_novo' ) ) {
                wp_schedule_event( time(), 'hourly', 'atualizar_estoque_tiny_novo');
            }
        } );
        add_shortcode('estoque',array($this,"shortcode_estoque"));
    }
    public function shortcode_estoque(){

        $query = new WC_Product_Query( array(
            'limit' => -1,
            'return' => 'ids',
        ) );
        $products = $query->get_products();
        $dados_estoque = array();
        foreach ($products as $key => $product) {
            $estoque = get_post_meta($product,"tiny_estoque",true);
            if($estoque == ""){
                continue;
            }
            $dados_estoque[$key]['id_produto'] = $product;
            $dados_estoque[$key]['estoque'] = $estoque;
            
            
        }
        ?>
        <h1>Saldo estoques</h1>
        <table style="margin:20px 0 0 20px">
        <?php 
        foreach ($dados_estoque as $key => $dado_estoque): 
            
        ?>
            <?php $total_estoque = 0  ?>
            
            <tr>
                <td style="padding-right:20px"><?php echo get_the_title($dado_estoque['id_produto']) ?></td>
                <td>
                    <table>
                        <?php foreach( $dado_estoque['estoque'] as $deposito): 
                            if($deposito->deposito->saldo==0){
                                continue;
                            }
                            ?>
                        <?php $total_estoque += $deposito->deposito->saldo ?>
                            <tr>
                                <td><?php echo ($deposito->deposito->nome) ?></td>
                                <td style="text-align:right"><?php echo ($deposito->deposito->saldo) ?></td>
                            </tr>        
                        <?php endforeach; ?>
                        <tr style="font-weight: bold;">
                            <td>Total</td>
                            <td><?php echo $total_estoque ?></td>
                        </tr>
                    </table>
                </td>
                
            </tr>
            <tr>
                <td colspan="2"><hr></td>
            </tr>
        <?php 
        endforeach;
        ?>
        </table>
        <?php
        
    }
    public function atualizar_estoque_tiny(){
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
