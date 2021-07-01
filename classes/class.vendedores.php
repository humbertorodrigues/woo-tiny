<?php

class vendedores
{
    public function __construct()
    {
        add_shortcode('pagina_vendedores', [$this, 'pagina_vendedores']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', [$this, 'admin_enqueue_scripts']);
        add_action('init', [$this, 'role_vendedores']);
        add_action('show_user_profile', [$this, 'extra_user_profile_fields']);
        add_action('edit_user_profile', [$this, 'extra_user_profile_fields']);
        add_action('user_new_form', [$this, 'extra_user_profile_fields']);
        add_action('user_register', [$this, 'salva_dados_vendedor']);
        add_action('profile_update', [$this, 'salva_dados_vendedor']);
        add_action('personal_options_update', [$this, 'salva_dados_vendedor']);
        add_action('edit_user_profile_update', [$this, 'salva_dados_vendedor']);
        add_filter('login_redirect', [$this, 'login_redirect'], 1, 3);
    }

    function login_redirect($redirect_to, $request, $user)
    {
        if (isset($user->roles)) {
            if (in_array("vendedores_bw", $user->roles) !== false || (in_array("bw_supervisor", $user->roles) !== false)) {
                $redirect_to =  site_url("vendedores");
            }
        }
        return $redirect_to;
    }

    public function role_vendedores()
    {

        add_role('vendedores_bw', 'Vendedores', array('read' => true));

    }

    public function enqueue_scripts()
    {
        global $post;

        if ($post->ID == url_to_postid(site_url('vendedores'))) {

            wp_enqueue_style('bootstrap', WOO_TINY_URL . 'templates/vendedores/assets/bootstrap/css/bootstrap.min.css');
            wp_enqueue_style('vendedores', WOO_TINY_URL . 'templates/vendedores/assets/css/vendedores.css', ['bootstrap']);
            wp_enqueue_script('validate', WOO_TINY_URL . 'templates/vendedores/assets/js/jquery.validate.min.js', array("jquery"));
            wp_enqueue_script('bootstrap', WOO_TINY_URL . 'templates/vendedores/assets/bootstrap/js/bootstrap.min.js', array("jquery"));
            wp_enqueue_script('validate-cpf-cnpj', WOO_TINY_URL . 'templates/vendedores/assets/js/brdocs.cpfcnpjValidator.js', array("jquery", "validate"));
            wp_enqueue_script('mask', WOO_TINY_URL . 'templates/vendedores/assets/js/jquery.mask.min.js', array("jquery"));
            wp_enqueue_script('viacep', WOO_TINY_URL . 'templates/vendedores/assets/js/jquery.viacep.js', ['jquery']);
            wp_enqueue_script('multi-file', WOO_TINY_URL . 'templates/vendedores/assets/js/jquery.MultiFile.min.js', ['jquery']);
            wp_enqueue_script('file-download', WOO_TINY_URL . 'assets/modules/jquery-download/jquery.fileDownload.js', ['jquery']);
            wp_enqueue_script('vendedores', WOO_TINY_URL . 'templates/vendedores/assets/js/jquery.vendedores.js', ['jquery', 'multi-file', 'file-download', 'bootstrap']);
            wp_localize_script( 'vendedores', 'woo_tiny', [
                    'ajax_url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce('woo-tiny-ajax')
                ]
            );
        }

    }

    public function admin_enqueue_scripts(){
        wp_register_style( 'jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/smoothness/jquery-ui.css' );
        wp_enqueue_style( 'jquery-ui');
        wp_enqueue_style('vendedores', WOO_TINY_URL . 'templates/vendedores/assets/admin/css/vendedores.css', ['login', 'jquery-ui']);
        wp_enqueue_script('jquery-mask-money', WOO_TINY_URL . 'templates/vendedores/assets/admin/js/jquery.maskMoney.min.js', ['jquery']);
        wp_enqueue_script('file-download', WOO_TINY_URL . 'assets/modules/jquery-download/jquery.fileDownload.js', ['jquery']);
        wp_enqueue_script( 'jquery-ui-datepicker');
        wp_enqueue_script('vendedores', WOO_TINY_URL . 'templates/vendedores/assets/admin/js/jquery.vendedores.js', ['jquery', 'jquery-ui-datepicker', 'jquery-mask-money']);
        wp_localize_script( 'vendedores', 'woo_tiny', [
                'admin_ajax_url' => admin_url( 'admin-ajax.php' ),
                'admin_nonce' => wp_create_nonce('woo-tiny-admin-ajax')
            ]
        );
    }

    public function pagina_vendedores()
    {
        // Get out of stock products.
        $args = array(
            'limit' => -1,
            // 'stock_status' => 'instock',
        );
        $produtos = wc_get_products($args);

        $canais_vendas = get_posts(array(
            'exclude' => [],
            'post_type' => 'canal_venda',
            'numberposts' => -1
        ));
        $precos_por_canal = array();

        foreach ($canais_vendas as $canal_venda) {
            $id_canal_venda = $canal_venda->ID;
            foreach ($produtos as $key => $produto) {
                $id_produto = $produto->get_ID();
                $precos_canal_venda = get_post_meta($id_produto, 'canais_venda', true);
                if (is_array($precos_canal_venda)) {
                    if (isset($precos_canal_venda[$id_canal_venda]) && $precos_canal_venda[$id_canal_venda] > 0) {
                        $precos_por_canal[$id_produto][$id_canal_venda] = str_replace(",", ".", $precos_canal_venda[$id_canal_venda]);
                    } else {
                        $precos_por_canal[$id_produto][$id_canal_venda] = $produto->get_price();
                    }
                } else {
                    $precos_por_canal[$id_produto][$id_canal_venda] = $produto->get_price();
                }
            }
        }

        $payment_options = get_posts(array(
            'post_type' => 'bw-payment-options',
            'numberposts' => -1
        ));

        include(WOO_TINY_DIR . "/templates/vendedores/pagina_vendedores.php");

    }


    function extra_user_profile_fields($user)
    {
        $args = array(
            'limit' => -1,
            // 'stock_status' => 'instock',
        );
        $produtos = wc_get_products($args);

        $canais = get_posts(array(
            'post_type' => 'canal_venda',
            'numberposts' => -1
        ));

        $bw_id_vendedor_tiny_vinicola = "";
        $bw_id_vendedor_tiny_bw = "";

        if (isset($user->ID)) {
            $bw_id_vendedor_tiny_vinicola = get_user_meta($user->ID, "bw_id_vendedor_tiny_vinicola", true);
            $bw_id_vendedor_tiny_bw = get_user_meta($user->ID, "bw_id_vendedor_tiny_bw", true);
        }

        $custom_products_prices = [];
        if(isset($user->ID)){
            $custom_products_prices = get_user_meta($user->ID, 'bw_custom_product_prices', true);
            if(empty($custom_products_prices) && !is_array($custom_products_prices)) $custom_products_prices = [];
            $custom_products_prices = array_map(function ($item){
                $item['product_name'] = get_the_title($item['product_id']);
                $item['channel_name'] = get_the_title($item['channel_id']);
                return $item;
            }, $custom_products_prices);
        }

        include WOO_TINY_DIR . '/templates/vendedores/form-user-profile.php';
    }

    public function salva_dados_vendedor($user_id)
    {
        update_user_meta($user_id, "bw_id_vendedor_tiny_vinicola", $_POST['bw_id_vendedor_tiny_vinicola'] ?? '');
        update_user_meta($user_id, "bw_id_vendedor_tiny_bw", $_POST['bw_id_vendedor_tiny_bw'] ?? '');
        update_user_meta($user_id, "bw_regional", $_POST['bw_regional'] ?? '');
    }
}

?>