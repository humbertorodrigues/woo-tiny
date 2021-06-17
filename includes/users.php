<?php
//10589
add_filter('request', 'woo_tiny_limiter_order_view_by_users');

function woo_tiny_limiter_order_view_by_users($query_vars){
    $users = [
        10589
    ];
    if(in_array(get_current_user_id(), $users)) {
        $screen_id = false;
        if (function_exists('get_current_screen')) {
            $screen = get_current_screen();
            $screen_id = isset($screen, $screen->id) ? $screen->id : '';
        }

        if (!empty($_REQUEST['screen'])) {
            $screen_id = wc_clean(wp_unslash($_REQUEST['screen']));
        }

        if ($screen_id == 'edit-shop_order') {
            if (!isset($query['meta_query'])) {
                $query_vars['meta_query'] = [];
            }
            $query_vars['meta_query'][] =  [
                'relation' => 'AND',
                [
                    'key' => 'bw_id_vendedor',
                    'compare' => 'EXISTS'
                ],
                [
                    'key' => 'bw_id_vendedor',
                    'compare' => '!=',
                    'value' => ''
                ],
            ];
        }
    }
    return $query_vars;
}

function woo_tiny_get_user_field(int $customer_id, string $field = '')
{
    $user = get_userdata($customer_id);
    if (!is_a($user, 'WP_User')) {
        $user = new WP_User($customer_id);
    }
    if ($user->has_prop($field)) {
        return $user->get($field);
    }
    return $user->data;
}