
<?php
class pedidos {
    public function __construct() {
        // add_action( 'woocommerce_order_status_processing', array($this,'enviar_pedido') ,1);
        add_action("woocommerce_order_status_processing", array($this, 'separar_pedidos'),0);
        add_filter( 'woocommerce_my_account_my_orders_query', [$this,'exibir_pedidos_vendedor'], 10, 1 );
    }
    public function separar_pedidos($order_id) {
        // var_dump($order_id);
        $produtos_com_estoque = 0;
        $produtos_sem_estoque = 0;
        $order = wc_get_order($order_id);
        $id_produtos_sem_estoque = array();
        foreach ($order->get_items() as $item_id => $item) {
            
            $product = wc_get_product($item['product_id']);
            $estoque = $product->get_stock_quantity();
            $pre_venda = get_post_meta($item['product_id'],"bw_pre_venda",true);
            
            if ($pre_venda == "yes") {
                $id_produtos_sem_estoque[]= $item['product_id'];
                $produtos_sem_estoque++;
            } else {
                $produtos_com_estoque++;
            }
        }
        if(($produtos_sem_estoque > 0 && $produtos_com_estoque > 0) || $produtos_sem_estoque > 1 ){
            $order_id_sem_estoque = array();
            for ($i=0; $i < $produtos_sem_estoque ; $i++) { 
                if($produtos_com_estoque == 0 & $i==0){
                    $order_id_sem_estoque[$i] = $order_id;
                    continue;
                }
                $order_id_sem_estoque[$i] = $this->duplicate_order( $order );
                
                $this->removerProdutosComEstoque($order_id_sem_estoque[$i]);
                if($produtos_com_estoque > 0){

                    $this->removerFretePedidoSemEstoque($order_id_sem_estoque[$i]);
                }else{
                    //Só temos produtos sem estoque. Logo, mantemos o frete no primeiro produto.
                    if($i>0){                        
                        $this->removerFretePedidoSemEstoque($order_id_sem_estoque[$i]);
                    }
                }
            }
            $j = 0;
            foreach ($order_id_sem_estoque as $key => $sem_estoque_id) {
                $produto_remover = false;
                foreach ($id_produtos_sem_estoque as $key_produto_sem_estoque => $id_produto_sem_estoque) {
                    
                    if($j != $key_produto_sem_estoque){
                        $this->orderRemoverProduto($sem_estoque_id,$id_produto_sem_estoque);
                    }
                }
                
                $j++;
                
                update_post_meta($sem_estoque_id,"bw_pedido_pai",$order_id);
            }
            if($produtos_com_estoque > 0){
                $this->removerProdutosSemEstoque($order_id);
            }
            
            update_post_meta($order_id,"bw_pedido_filho",$order_id_sem_estoque);
        }
        // exit("Pronto");
    }
    
    public function removerFretePedidoSemEstoque($order_id){
        $order = wc_get_order($order_id);
        $items  = (array) $order->get_items('shipping');
        
        foreach ($items as $key => $item) {
            
            $item->set_total(0);
            $item->save();
    
        }
        $order->calculate_totals();
        
    }
    public function orderRemoverProduto($order_id,$id_produto){
        $order = wc_get_order($order_id);

        foreach ($order->get_items() as $item_id => $item) {

            // $product = wc_get_product($item['product_id']);

            if($item['product_id']==$id_produto){
                $order->remove_item($item_id);

            }

        }
        $order->calculate_totals();


    }
    public function removerProdutosSemEstoque($order_id){
        $order = wc_get_order($order_id);

        foreach ($order->get_items() as $item_id => $item) {

            $product = wc_get_product($item['product_id']);
            $estoque = $product->get_stock_quantity();
            $pre_venda = get_post_meta($item['product_id'],"bw_pre_venda",true);

            if ($pre_venda=="yes") {
                $order->remove_item($item_id);
            }

        }
        $order->calculate_totals();


    }
    public function removerProdutosComEstoque($order_id){
        $order = wc_get_order($order_id);

        foreach ($order->get_items() as $item_id => $item) {

            $product = wc_get_product($item['product_id']);
            $estoque = $product->get_stock_quantity();
            $pre_venda = get_post_meta($item['product_id'],"bw_pre_venda",true);

            if ($pre_venda!="yes") {
                $order->remove_item($item_id);
            }

        }
        $order->calculate_totals();
    }
    public function duplicate_order($post) {
        global $wpdb;

        $original_order_id = $post->id;
        $original_order = $post;

        $order_id = $this->create_order($original_order_id);

        if (is_wp_error($order_id)) {
            $msg = 'Unable to create order: ' . $order_id->get_error_message();
            throw new Exception($msg);
        } else {

            $order = new WC_Order($order_id);

            $this->duplicate_order_header($original_order_id, $order_id);
            $this->duplicate_billing_fieds($original_order_id, $order_id);
            $this->duplicate_shipping_fieds($original_order_id, $order_id);

            $this->duplicate_line_items($original_order, $order_id);
            $this->duplicate_shipping_items($original_order, $order_id);
            $this->duplicate_coupons($original_order, $order_id);
            $this->duplicate_payment_info($original_order_id, $order_id, $order);
            $order->calculate_taxes();
            $this->add_order_note($original_order_id, $order);

            return $order_id;
        }
    }
    private function duplicate_order_header($original_order_id, $order_id) {
        update_post_meta($order_id, '_order_shipping',         get_post_meta($original_order_id, '_order_shipping', true));
        update_post_meta($order_id, '_order_discount',         get_post_meta($original_order_id, '_order_discount', true));
        update_post_meta($order_id, '_cart_discount',          get_post_meta($original_order_id, '_cart_discount', true));
        update_post_meta($order_id, '_order_tax',              get_post_meta($original_order_id, '_order_tax', true));
        update_post_meta($order_id, '_order_shipping_tax',     get_post_meta($original_order_id, '_order_shipping_tax', true));
        update_post_meta($order_id, '_order_total',            get_post_meta($original_order_id, '_order_total', true));

        update_post_meta($order_id, '_order_key',              'wc_' . apply_filters('woocommerce_generate_order_key', uniqid('order_')));
        update_post_meta($order_id, '_customer_user',          get_post_meta($original_order_id, '_customer_user', true));
        update_post_meta($order_id, '_order_currency',         get_post_meta($original_order_id, '_order_currency', true));
        update_post_meta($order_id, '_prices_include_tax',     get_post_meta($original_order_id, '_prices_include_tax', true));
        update_post_meta($order_id, '_customer_ip_address',    get_post_meta($original_order_id, '_customer_ip_address', true));
        update_post_meta($order_id, '_customer_user_agent',    get_post_meta($original_order_id, '_customer_user_agent', true));
    }

    private function duplicate_billing_fieds($original_order_id, $order_id) {
        update_post_meta($order_id, '_billing_city',           get_post_meta($original_order_id, '_billing_city', true));
        update_post_meta($order_id, '_billing_state',          get_post_meta($original_order_id, '_billing_state', true));
        update_post_meta($order_id, '_billing_postcode',       get_post_meta($original_order_id, '_billing_postcode', true));
        update_post_meta($order_id, '_billing_email',          get_post_meta($original_order_id, '_billing_email', true));
        update_post_meta($order_id, '_billing_phone',          get_post_meta($original_order_id, '_billing_phone', true));
        update_post_meta($order_id, '_billing_address_1',      get_post_meta($original_order_id, '_billing_address_1', true));
        update_post_meta($order_id, '_billing_address_2',      get_post_meta($original_order_id, '_billing_address_2', true));
        update_post_meta($order_id, '_billing_country',        get_post_meta($original_order_id, '_billing_country', true));
        update_post_meta($order_id, '_billing_first_name',     get_post_meta($original_order_id, '_billing_first_name', true));
        update_post_meta($order_id, '_billing_last_name',      get_post_meta($original_order_id, '_billing_last_name', true));
        update_post_meta($order_id, '_billing_company',        get_post_meta($original_order_id, '_billing_company', true));
        update_post_meta($order_id, '_billing_cpf',            get_post_meta($original_order_id, '_billing_cpf', true));
        update_post_meta($order_id, '_billing_cnpj',           get_post_meta($original_order_id, '_billing_cnpj', true));
        update_post_meta($order_id, '_billing_persontype',     get_post_meta($original_order_id, '_billing_persontype', true));
    }

    private function duplicate_shipping_fieds($original_order_id, $order_id) {
        update_post_meta($order_id, '_shipping_country',       get_post_meta($original_order_id, '_shipping_country', true));
        update_post_meta($order_id, '_shipping_first_name',    get_post_meta($original_order_id, '_shipping_first_name', true));
        update_post_meta($order_id, '_shipping_last_name',     get_post_meta($original_order_id, '_shipping_last_name', true));
        update_post_meta($order_id, '_shipping_company',       get_post_meta($original_order_id, '_shipping_company', true));
        update_post_meta($order_id, '_shipping_address_1',     get_post_meta($original_order_id, '_shipping_address_1', true));
        update_post_meta($order_id, '_shipping_address_2',     get_post_meta($original_order_id, '_shipping_address_2', true));
        update_post_meta($order_id, '_shipping_city',          get_post_meta($original_order_id, '_shipping_city', true));
        update_post_meta($order_id, '_shipping_state',         get_post_meta($original_order_id, '_shipping_state', true));
        update_post_meta($order_id, '_shipping_postcode',      get_post_meta($original_order_id, '_shipping_postcode', true));
    }
    private function duplicate_line_items($original_order, $order_id) {
        foreach ($original_order->get_items() as $originalOrderItem) {
            $itemName = $originalOrderItem['name'];
            $qty = $originalOrderItem['qty'];
            $lineTotal = $originalOrderItem['line_total'];
            $lineTax = $originalOrderItem['line_tax'];
            $productID = $originalOrderItem['product_id'];

            $item_id = wc_add_order_item($order_id, array(
                'order_item_name'       => $itemName,
                'order_item_type'       => 'line_item'
            ));

            wc_add_order_item_meta($item_id, '_qty', $qty);
            wc_add_order_item_meta($item_id, '_tax_class', $originalOrderItem['tax_class']);
            wc_add_order_item_meta($item_id, '_product_id', $productID);
            wc_add_order_item_meta($item_id, '_variation_id', $originalOrderItem['variation_id']);
            wc_add_order_item_meta($item_id, '_line_subtotal', wc_format_decimal($lineTotal));
            wc_add_order_item_meta($item_id, '_line_total', wc_format_decimal($lineTotal));
            /* wc_add_order_item_meta( $item_id, '_line_tax', wc_format_decimal( '0' ) ); */
            wc_add_order_item_meta($item_id, '_line_tax', wc_format_decimal($lineTax));
            /* wc_add_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( '0' ) ); */
            wc_add_order_item_meta($item_id, '_line_subtotal_tax', wc_format_decimal($originalOrderItem['line_subtotal_tax']));
        }


        // Can it be reused or refactored into own function?
        //
        // Copy products from the order to the cart
        /* foreach ( $order->get_items() as $item ) { */
        /* 	// Load all product info including variation data */
        /* 	$product_id   = (int) apply_filters( 'woocommerce_add_to_cart_product_id', $item['product_id'] ); */
        /* 	$quantity     = (int) $item['qty']; */
        /* 	$variation_id = (int) $item['variation_id']; */
        /* 	$variations   = array(); */
        /* 	$cart_item_data = apply_filters( 'woocommerce_order_again_cart_item_data', array(), $item, $order ); */

        /* 	foreach ( $item['item_meta'] as $meta_name => $meta_value ) { */
        /* 		if ( taxonomy_is_product_attribute( $meta_name ) ) { */
        /* 			$variations[ $meta_name ] = $meta_value[0]; */
        /* 		} elseif ( meta_is_product_attribute( $meta_name, $meta_value[0], $product_id ) ) { */
        /* 			$variations[ $meta_name ] = $meta_value[0]; */
        /* 		} */
        /* 	} */
    }

    private function duplicate_shipping_items($original_order, $order_id) {
        $original_order_shipping_items = $original_order->get_items('shipping');

        foreach ($original_order_shipping_items as $original_order_shipping_item) {
            $item_id = wc_add_order_item($order_id, array(
                'order_item_name'       => $original_order_shipping_item['name'],
                'order_item_type'       => 'shipping'
            ));
            if ($item_id) {
                wc_add_order_item_meta($item_id, 'method_id', $original_order_shipping_item['method_id']);
                wc_add_order_item_meta($item_id, 'cost', wc_format_decimal($original_order_shipping_item['cost']));

                /* wc_add_order_item_meta( $item_id, 'taxes', $original_order_shipping_item['taxes'] ); */
            }
        }
    }

    private function duplicate_coupons($original_order, $order_id) {
        $original_order_coupons = $original_order->get_items('coupon');
        foreach ($original_order_coupons as $original_order_coupon) {
            $item_id = wc_add_order_item($order_id, array(
                'order_item_name'       => $original_order_coupon['name'],
                'order_item_type'       => 'coupon'
            ));
            // Add line item meta
            if ($item_id) {
                wc_add_order_item_meta($item_id, 'discount_amount', $original_order_coupon['discount_amount']);
            }
        }
    }

    private function duplicate_payment_info($original_order_id, $order_id, $order) {
        update_post_meta($order_id, '_payment_method',         get_post_meta($original_order_id, '_payment_method', true));
        update_post_meta($order_id, '_payment_method_title',   get_post_meta($original_order_id, '_payment_method_title', true));
        /* update_post_meta( $order->id, 'Transaction ID',         get_post_meta($original_order_id, 'Transaction ID', true) ); */
        /* $order->payment_complete(); */
    }
    private function create_order($original_order_id) {
        $new_post_author    = wp_get_current_user();
        $new_post_date      = current_time('mysql');
        $new_post_date_gmt  = get_gmt_from_date($new_post_date);

        $original_order = wc_get_order($original_order_id);
        $original_order_status = $original_order->get_status();
        // $order_data =  array(
        //     'post_author'   => $new_post_author->ID,
        //     'post_date'     => $new_post_date,
        //     'post_date_gmt' => $new_post_date_gmt,
        //     'post_type'     => 'shop_order',
        //     'post_title'    => __('Duplicate Order', 'woocommerce'),
        //     /* 'post_status'   => 'draft', */
        //     'post_status'   => 'pending',
        //     'ping_status'   => 'closed',
        //     /* 'post_excerpt'  => 'Duplicate Order based on original order ' . $original_order_id, */
        //     'post_password' => uniqid('order_'),   // Protects the post just in case
        //     'post_modified'             => $new_post_date,
        //     'post_modified_gmt'         => $new_post_date_gmt
        // );
        
        // $new_post_id = wp_insert_post($order_data, true);
        $new_order = wc_create_order();
        
        $r = $new_order->update_status($original_order_status);
        
        return $new_order->get_id();
        
    }
    public function enviar_pedido($order_id) {
        global $wpdb;
        $dados['acao'] = "enviar_pedido";
        $dados['id_pedido'] = $order_id;
        $dados['status'] = "pendente";

        $wpdb->insert($wpdb->prefix . "acoes_tiny", $dados);
    }
    private function add_order_note($original_order_id, $order) {
        $updateNote = 'This order was duplicated from order ' . $original_order_id . '.';
        /* $order->update_status('processing'); */
        $order->add_order_note($updateNote);
    }
    
    public function exibir_pedidos_vendedor( $args ) {
        
        $user = wp_get_current_user();
        
        if ( in_array( 'vendedores_bw', (array) $user->roles ) ) {
            
                unset($args['customer']);            
                $args['meta_key'] = 'bw_id_vendedor';
                $args['meta_value'] = get_current_user_id();  
        }
        
        return $args;
    }
}
?>