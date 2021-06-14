<?php
function woo_tiny_get_seller_order_count($seller_id){
    $count = get_user_meta( $seller_id, '_order_count', true );

    if ( '' === $count ) {
        global $wpdb;

        $count = $wpdb->get_var(
        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
            "SELECT COUNT(*)
				FROM $wpdb->posts as posts
				LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
				WHERE   meta.meta_key = 'bw_id_vendedor'
				AND     posts.post_type = 'shop_order'
				AND     posts.post_status IN ( '" . implode( "','", array_map( 'esc_sql', array_keys( wc_get_order_statuses() ) ) ) . "' )
				AND     meta_value = '" . esc_sql( $seller_id ) . "'"
        // phpcs:enable
        );
        update_user_meta( $seller_id, '_order_count', $count );
    }

    return absint( $count );
}

function woo_tiny_get_seller_total_spent($seller_id){
    $spent = get_user_meta( $seller_id, '_money_spent', true );

    if ( '' === $spent ) {
        global $wpdb;
        $statuses = wc_get_is_paid_statuses();
        $statuses[] = 'revision';
        $statuses = array_map( 'esc_sql', $statuses);
        $spent    = $wpdb->get_var(
        // phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
                "SELECT SUM(meta2.meta_value)
					FROM $wpdb->posts as posts
					LEFT JOIN {$wpdb->postmeta} AS meta ON posts.ID = meta.post_id
					LEFT JOIN {$wpdb->postmeta} AS meta2 ON posts.ID = meta2.post_id
					WHERE   meta.meta_key       = 'bw_id_vendedor'
					AND     meta.meta_value     = '" . esc_sql( $seller_id ) . "'
					AND     posts.post_type     = 'shop_order'
					AND     posts.post_status   IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
					AND     meta2.meta_key      = '_order_total'"
        // phpcs:enable
        );

        if ( ! $spent ) {
            $spent = 0;
        }
        update_user_meta( $seller_id, '_money_spent', $spent );
    }

    return wc_format_decimal( $spent, 2 );
}