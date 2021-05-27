<?php
add_action('woo_tiny_cron_admin_orders_await_revision_email_schedule', 'woo_tiny_trigger_admin_orders_await_revision_email');


if ( ! wp_next_scheduled( 'woo_tiny_cron_admin_orders_await_revision_email_schedule' ) ) {
    wp_schedule_event( time(), 'daily', 'woo_tiny_cron_admin_orders_await_revision_email_schedule' );
}