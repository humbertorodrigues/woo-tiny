<?php
add_action( 'init', 'woo_tiny_register_new_order_statuses' );
add_filter( 'wc_order_statuses', 'woo_tiny_new_wc_order_statuses' );

function woo_tiny_register_new_order_statuses() {
    register_post_status( 'wc-revision', array(
        'label'                     => _x( 'Em revisão', 'Order status', 'woocommerce' ),
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Em revisão <span class="count">(%s)</span>', 'Em revisão <span class="count">(%s)</span>', 'woocommerce' )
    ) );
}


function woo_tiny_new_wc_order_statuses( $order_statuses ) {
    $order_statuses['wc-revision'] = _x( 'Em revisão', 'Order status', 'woocommerce' );

    return $order_statuses;
}