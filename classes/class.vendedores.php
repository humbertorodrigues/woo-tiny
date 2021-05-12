<?php

class vendedores
{
    public function __construct()
    {
        add_shortcode('pagina_vendedores', [$this, 'pagina_vendedores']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_css']);
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
            if (array_search("vendedores_bw", $user->roles) !== false) {
                return site_url("vendedores");
            }
        }
    }

    public function role_vendedores()
    {

        add_role('vendedores_bw', 'Vendedores', array('read' => true));

    }

    public function enqueue_css()
    {
        global $post;

        if ($post->ID == url_to_postid(site_url('vendedores'))) {

            wp_enqueue_style('bootstrap', WOO_TINY_URL . '/templates/vendedores/assets/bootstrap/css/bootstrap.min.css');
            wp_enqueue_script('validate', WOO_TINY_URL . '/templates/vendedores/assets/js/jquery.validate.min.js', array("jquery"));
            wp_enqueue_script('validate-cpf-cnpj', WOO_TINY_URL . '/templates/vendedores/assets/js/brdocs.cpfcnpjValidator.js', array("jquery", "validate"));
            wp_enqueue_script('mask', WOO_TINY_URL . '/templates/vendedores/assets/js/jquery.mask.min.js', array("jquery"));
            wp_enqueue_script('viacep', WOO_TINY_URL . '/templates/vendedores/assets/js/jquery.viacep.js', ['jquery']);
            wp_enqueue_script('vendedores', WOO_TINY_URL . '/templates/vendedores/assets/js/jquery.vendedores.js', ['jquery']);
        }

    }

    public function pagina_vendedores()
    {
        // Get out of stock products.
        $args = array(
            'limit' => -1,
            // 'stock_status' => 'instock',
        );
        $produtos = wc_get_products($args);

        ?>

        <?php
        include(WOO_TINY_DIR . "/templates/vendedores/pagina_vendedores.php");

    }


    function extra_user_profile_fields($user)
    { ?>
        <h3>Dados do vendedor no tiny</h3>
        <?php

        if (isset($user->ID)) {

            $bw_id_vendedor_tiny_vinicola = get_user_meta($user->ID, "bw_id_vendedor_tiny_vinicola", true);
            $bw_id_vendedor_tiny_bw = get_user_meta($user->ID, "bw_id_vendedor_tiny_bw", true);
        } else {
            $bw_id_vendedor_tiny_vinicola = "";
            $bw_id_vendedor_tiny_bw = "";
        }
        ?>
        <table class="form-table">
            <tr>
                <th><label for="bw_id_vendedor_tiny_bw">Código vendedor (conta Bueno Wines)</label></th>
                <td>
                    <input type="text" name="bw_id_vendedor_tiny_bw" id="bw_id_vendedor_tiny_bw"
                           value="<?php echo $bw_id_vendedor_tiny_bw ?>" class="regular-text"/><br/>

                </td>
            </tr>
            <tr>
                <th><label for="bw_id_vendedor_tiny_vinicola">Código vendedor (conta Vinícola)</label></th>
                <td>
                    <input type="text" name="bw_id_vendedor_tiny_vinicola" id="bw_id_vendedor_tiny_vinicola"
                           value="<?php echo $bw_id_vendedor_tiny_vinicola ?>" class="regular-text"/><br/>

                </td>
            </tr>
        </table>
    <?php }

    public function salva_dados_vendedor($user_id)
    {
        update_user_meta($user_id, "bw_id_vendedor_tiny_vinicola", $_POST['bw_id_vendedor_tiny_vinicola'] ?? '');
        update_user_meta($user_id, "bw_id_vendedor_tiny_bw", $_POST['bw_id_vendedor_tiny_bw'] ?? '');
    }
}

?>