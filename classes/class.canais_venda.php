<?php
class canaisVenda {
    public function __construct() {
        add_action('init', [$this, 'cpt_canais_venda']);
        add_filter('woocommerce_product_data_tabs', [$this, 'aba_canais_venda'], 99, 1);
        add_action('woocommerce_product_data_panels', [$this, 'dados_aba_canais_venda']);
        add_action('woocommerce_process_product_meta', [$this, 'salvar_dados_aba_canais_venda']);
    }

    public function cpt_canais_venda() {
        // Set various pieces of text, $labels is used inside the $args array
        $labels = array(
            'name' => "Canais de venda",
            'singular_name' => "Canal de venda",
        );
        // Set various pieces of information about the post type
        $args = array(
            'labels' => $labels,
            'description' => 'Canais de vendas usado pelos vendedores',
            'public' => true,
            'supports' => array("title"),
            'exclude_from_search' => true,
            'publicly_queryable' => false,
            'show_in_nav_menus' => false,
            'menu_icon' => 'data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iaXNvLTg4NTktMSI/Pg0KPCEtLSBHZW5lcmF0b3I6IEFkb2JlIElsbHVzdHJhdG9yIDE5LjAuMCwgU1ZHIEV4cG9ydCBQbHVnLUluIC4gU1ZHIFZlcnNpb246IDYuMDAgQnVpbGQgMCkgIC0tPg0KPHN2ZyB2ZXJzaW9uPSIxLjEiIGlkPSJDYXBhXzEiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIgeG1sbnM6eGxpbms9Imh0dHA6Ly93d3cudzMub3JnLzE5OTkveGxpbmsiIHg9IjBweCIgeT0iMHB4Ig0KCSB2aWV3Qm94PSIwIDAgNDgwLjExMyA0ODAuMTEzIiBzdHlsZT0iZW5hYmxlLWJhY2tncm91bmQ6bmV3IDAgMCA0ODAuMTEzIDQ4MC4xMTM7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxsaW5lYXJHcmFkaWVudCBpZD0iU1ZHSURfMV8iIGdyYWRpZW50VW5pdHM9InVzZXJTcGFjZU9uVXNlIiB4MT0iLTI2LjgyMTkiIHkxPSI1NTcuNDAxMyIgeDI9Ii0yNi44MjE5IiB5Mj0iNjIzLjA3NDMiIGdyYWRpZW50VHJhbnNmb3JtPSJtYXRyaXgoOCAwIDAgLTggNDU0LjYzMTYgNDk0MS41MDYzKSI+DQoJPHN0b3AgIG9mZnNldD0iMCIgc3R5bGU9InN0b3AtY29sb3I6IzAwNkRGMCIvPg0KCTxzdG9wICBvZmZzZXQ9IjEiIHN0eWxlPSJzdG9wLWNvbG9yOiMwMEU3RjAiLz4NCjwvbGluZWFyR3JhZGllbnQ+DQo8cGF0aCBzdHlsZT0iZmlsbDp1cmwoI1NWR0lEXzFfKTsiIGQ9Ik00MzIuMDUzLDI5NC40NTZjLTIwLjUyNy0xMS45NDYtNDYuNzE5LTYuOTM5LTYxLjM5MiwxMS43MzZsLTg1LjY2NC00OS40MzINCgljOS4yOTctMjQuNzEzLTMuMjAxLTUyLjI4My0yNy45MTQtNjEuNThjLTIuOTI0LTEuMS01Ljk0OC0xLjkxMS05LjAzLTIuNDJWOTUuMzM2YzI2LjEzOS00LjQxOCw0My43NDctMjkuMTksMzkuMzI5LTU1LjMyOQ0KCXMtMjkuMTktNDMuNzQ3LTU1LjMyOS0zOS4zMjlzLTQzLjc0NywyOS4xOS0zOS4zMjksNTUuMzI5YzMuNDA1LDIwLjE0MywxOS4xODYsMzUuOTI0LDM5LjMyOSwzOS4zMjl2OTcuNDQNCgljLTI2LjA1LDQuMzA5LTQzLjY3NCwyOC45Mi0zOS4zNjQsNTQuOTdjMC41MSwzLjA4MiwxLjMyMSw2LjEwNywyLjQyLDkuMDNsLTg1LjY2NCw0OS40NDgNCgljLTE0LjY2NC0xOC42ODYtNDAuODU3LTIzLjcwNy02MS4zOTItMTEuNzY4Yy0yMi45NjcsMTMuMjM5LTMwLjg1NCw0Mi41OS0xNy42MTUsNjUuNTU3YzEzLjIzOSwyMi45NjcsNDIuNTksMzAuODU0LDY1LjU1NywxNy42MTUNCgljMjAuMDE4LTExLjUzOSwyOC45NzUtMzUuNzAyLDIxLjMxNC01Ny41bDg1Ljc3Ni00OS41MmM3LjM0MSw4Ljg2NiwxNy42MiwxNC44MDIsMjguOTY4LDE2LjcyOHY5Ny40NA0KCWMtMjYuMTM5LDQuNDE4LTQzLjc0NywyOS4xOS0zOS4zMjksNTUuMzI5YzQuNDE4LDI2LjEzOSwyOS4xOSw0My43NDcsNTUuMzI5LDM5LjMyOXM0My43NDctMjkuMTksMzkuMzI5LTU1LjMyOQ0KCWMtMy40MDUtMjAuMTQzLTE5LjE4Ni0zNS45MjQtMzkuMzI5LTM5LjMyOXYtOTcuNDRjMTEuMzQzLTEuOTE4LDIxLjYyMi03Ljg0MiwyOC45NjgtMTYuNjk2bDg1Ljc3Niw0OS41Mg0KCWMtOC43NzQsMjUuMDE1LDQuMzkyLDUyLjQwOCwyOS40MDcsNjEuMTgyYzI1LjAxNSw4Ljc3NCw1Mi40MDgtNC4zOTIsNjEuMTgyLTI5LjQwNw0KCUM0NjEuMDMxLDMzMC4xMzgsNDUyLjA2NywzMDUuOTg3LDQzMi4wNTMsMjk0LjQ1Nkw0MzIuMDUzLDI5NC40NTZ6IE04OC4wNTMsMzYzLjczNmMtMTUuMjk5LDguODQ4LTM0Ljg3NCwzLjYxOS00My43MjItMTEuNjgNCgljLTguODQ4LTE1LjI5OS0zLjYxOS0zNC44NzQsMTEuNjgtNDMuNzIyczM0Ljg3NC0zLjYxOSw0My43MjIsMTEuNjhjMC4wMDgsMC4wMTQsMC4wMTYsMC4wMjgsMC4wMjQsMC4wNDINCgljOC44OSwxNS4xMSwzLjg0OCwzNC41NjctMTEuMjYzLDQzLjQ1N2MtMC4xNDYsMC4wODYtMC4yOTQsMC4xNzEtMC40NDEsMC4yNTVWMzYzLjczNnogTTIwOC4wNTMsNDguMDU2YzAtMTcuNjczLDE0LjMyNy0zMiwzMi0zMg0KCWMxNy42NzMsMCwzMiwxNC4zMjcsMzIsMzJzLTE0LjMyNywzMi0zMiwzMkMyMjIuMzgsODAuMDU2LDIwOC4wNTMsNjUuNzI5LDIwOC4wNTMsNDguMDU2eiBNMjcyLjA1Myw0MzIuMDU2DQoJYzAsMTcuNjczLTE0LjMyNywzMi0zMiwzMmMtMTcuNjczLDAtMzItMTQuMzI3LTMyLTMyczE0LjMyNy0zMiwzMi0zMkMyNTcuNzI2LDQwMC4wNTYsMjcyLjA1Myw0MTQuMzgzLDI3Mi4wNTMsNDMyLjA1NnoNCgkgTTI0MC4wNTMsMjcyLjA1NmMtMTcuNjczLDAtMzItMTQuMzI3LTMyLTMyczE0LjMyNy0zMiwzMi0zMmMxNy42NzMsMCwzMiwxNC4zMjcsMzIsMzJTMjU3LjcyNiwyNzIuMDU2LDI0MC4wNTMsMjcyLjA1NnoNCgkgTTQzNS43NTcsMzUyLjA1NmMtOC44MzksMTUuMzA0LTI4LjQxMSwyMC41NDUtNDMuNzE1LDExLjcwNmMtMTUuMzA0LTguODM5LTIwLjU0NS0yOC40MTEtMTEuNzA2LTQzLjcxNQ0KCWM0LjI0Mi03LjM0NSwxMS4yMjgtMTIuNzA2LDE5LjQyMS0xNC45MDNjMi43MjUtMC43MjYsNS41MzItMS4wOTEsOC4zNTItMS4wODhjMTcuNjczLDAuMDM2LDMxLjk3MSwxNC4zOTIsMzEuOTM1LDMyLjA2NQ0KCUM0NDAuMDMyLDM0MS43MTYsNDM4LjU1NCwzNDcuMjExLDQzNS43NTcsMzUyLjA1NnoiLz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjxnPg0KPC9nPg0KPGc+DQo8L2c+DQo8Zz4NCjwvZz4NCjwvc3ZnPg0K'
        );
        // Register the movie post type with all the information contained in the $arguments array
        register_post_type('canal_venda', $args);
    }

    public function aba_canais_venda($product_data_tabs) {
        $product_data_tabs['canais-venda'] = array(
            'label' => "Canais de venda",
            'target' => 'canais_venda',
        );
        return $product_data_tabs;
    }

    public function dados_aba_canais_venda() {
        global $post;
        $post_id = $post->ID;
        $canais_vendas = get_posts(array(
            'post_type' => 'canal_venda',
            'numberposts' => -1
        ));
        $dados_salvos = get_post_meta($post_id, 'canais_venda', true);

?>

        <div id="canais_venda" class="panel woocommerce_options_panel">
            <?php

            foreach ($canais_vendas as $key => $canal_venda) :
                $canal_id = $canal_venda->ID;
                $nome_canal = $canal_venda->post_title;
                woocommerce_wp_text_input(array(
                    'id'            => 'canal_venda_' . $canal_id,
                    'name'            => 'canais_venda[' . $canal_id . ']',
                    'label'         => $nome_canal,
                    'value'       => isset($dados_salvos[$canal_id]) ? $dados_salvos[$canal_id] : "",
                    'desc_tip'      => false,
                ));
            endforeach;
            ?>
        </div>
<?php
    }
    public function salvar_dados_aba_canais_venda($post_id) {
        // Custom Product Text Field
        $canais_venda = $_POST['canais_venda'];
        if (isset($canais_venda)) {
            update_post_meta($post_id, 'canais_venda', $canais_venda);
        }
    }
}

?>