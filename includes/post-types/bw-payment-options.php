<?php
add_action('init', 'bw_payment_options');
add_action('save_post_bw-payment-options', 'bw_payment_options_meta_save');
add_filter("manage_bw-payment-options_posts_columns", "bw_payment_options_edit_columns");
add_action("manage_posts_custom_column", "bw_payment_options_custom_columns");


function bw_payment_options()
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
        'register_meta_box_cb' => 'bw_payment_options_add_meta_box',
        'menu_icon' => ''
    ];

    register_post_type('bw-payment-options', $args);
}

function bw_payment_options_add_meta_box()
{
    add_meta_box('bw_payment_options_meta', 'Formas de pagamento', 'bw_payment_options_meta_content',
        'bw-payment-options', 'normal', 'default');
    remove_meta_box('wpseo_meta', 'bw-payment-options', 'normal');
}

function bw_payment_options_meta_content()
{
    $form = '<table class="form-table"><tbody>';
    $form .= '<tr><th scope="row"><label for="discount">Desconto (%)</label></th><td><input name="discount" type="text" id="discount" value="' . bw_get_meta_field('discount') . '" class="regular-text" required><br><small>Digite um número inteiro</small></td></tr>';
    $form .= '</tbody></table>';
    echo $form;
}

function bw_payment_options_meta_save()
{
    global $post;

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;

    if (get_post_type($post) !== 'bw-payment-options') return;

    $_POST['discount'] = $_POST['discount'] ?? '0';
    $_POST['discount'] = preg_replace('/\D/', '', $_POST['discount']);
    update_post_meta($post->ID, "discount", $_POST['discount']);
}

function bw_payment_options_edit_columns($columns)
{
    unset($columns['date']);
    $columns['discount'] = 'Desconto (%)';
    $columns['date'] = 'Data';
    return $columns;
}

function bw_payment_options_custom_columns($column)
{
    switch ($column) {
        case 'discount':
            echo bw_get_meta_field('discount') . '%';
            break;
        default:
            break;
    }
}