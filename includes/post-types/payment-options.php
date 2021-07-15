<?php
add_action('init', 'woo_tiny_payment_options');
add_action('save_post_bw-payment-options', 'woo_tiny_payment_options_meta_save');
add_filter("manage_bw-payment-options_posts_columns", "woo_tiny_payment_options_edit_columns");
add_action("manage_posts_custom_column", "woo_tiny_payment_options_custom_columns");


function woo_tiny_payment_options()
{

    $labels = [
        'name' => 'Formas de Pagamento',
        'singular_name' => 'Formas de Pagamento',
    ];

    $args = [
        'labels' => $labels,
        'description' => 'Formas de pagamento usado pelos vendedores',
        'public' => true,
        'supports' => ['title'],
        'exclude_from_search' => true,
        'publicly_queryable' => false,
        'show_in_nav_menus' => false,
        'register_meta_box_cb' => 'woo_tiny_payment_options_add_meta_box',
        'menu_icon' => ''
    ];

    register_post_type('bw-payment-options', $args);
}

function woo_tiny_payment_options_add_meta_box()
{
    add_meta_box('bw_payment_options_meta', 'Formas de pagamento', 'woo_tiny_payment_options_meta_content',
        'bw-payment-options', 'normal', 'default');
    remove_meta_box('wpseo_meta', 'bw-payment-options', 'normal');
}

function woo_tiny_payment_options_meta_content()
{
    $payment_methods = woo_tiny_get_payment_methods();
    $payment_gateways = array_map(function ($gateway){
        return $gateway->method_title;
    }, WC()->payment_gateways->payment_gateways());
    include WOO_TINY_DIR . 'templates/post-types/payment-options/meta-form.php';
}

function woo_tiny_payment_options_meta_save()
{
    global $post;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (get_post_type($post) !== 'bw-payment-options') return;

    $_POST['discount'] = $_POST['discount'] ?? '0';
    $_POST['discount'] = preg_replace('/\D/', '', $_POST['discount']);
    update_post_meta($post->ID, "discount", $_POST['discount']);
    update_post_meta($post->ID, "payment_method", $_POST['payment_method']);
    update_post_meta($post->ID, "payment_gateway", $_POST['payment_gateway']);
    update_post_meta($post->ID, "installments", $_POST['installments']);
}

function woo_tiny_payment_options_edit_columns($columns)
{
    unset($columns['date']);
    $columns['discount'] = 'Desconto (%)';
    $columns['payment_method'] = 'Meio de Pagamento';
    $columns['installments'] = 'Parcelas';
    $columns['date'] = 'Data';
    return $columns;
}

function woo_tiny_payment_options_custom_columns($column)
{
    switch ($column) {
        case 'discount':
            echo bw_get_meta_field('discount') . '%';
            break;
        case 'payment_method':
            $method = woo_tiny_get_payment_methods(bw_get_meta_field('payment_method'));
            echo is_array($method) ? '' : $method;
            break;
        case 'installments':
            echo bw_get_meta_field('installments');
            break;
        default:
            break;
    }
}

function woo_tiny_get_payment_methods($code = '')
{
    $payment_methods = [
        'multiplas' => 'Múltiplas',
        'dinheiro' => 'Dinheiro',
        'credito' => 'Crédito',
        'debito' => 'Débito',
        'boleto' => 'Boleto',
        'Depósito bancário' => 'Depósito',
        'cheque' => 'Cheque',
        'crediario' => 'Crediário',
        'duplicata_mercantil' => 'Duplicata Mercantil'
    ];
    if($code == '') return $payment_methods;
    if(!array_key_exists($code, $payment_methods)) return '';
    return $payment_methods[$code];
}