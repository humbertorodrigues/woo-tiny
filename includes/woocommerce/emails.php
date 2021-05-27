<?php
add_filter('woocommerce_email_classes', 'woo_tiny_include_email_classes');
global $woocommerce;

function woo_tiny_include_email_classes($emails)
{
    if (!isset($emails['Woo_Tiny_Order_Revision_Email'])) {
        $emails['Woo_Tiny_Order_Revision_Email'] = include WOO_TINY_DIR . 'classes/class-woo-tiny-order-revision-email.php';
        $emails['Woo_Tiny_Admin_Orders_Await_Revision_Email'] = include WOO_TINY_DIR . 'classes/class-woo-tiny-admin-orders-await-revision-email.php';
    }
    return $emails;
}

function woo_tiny_trigger_order_revision_email($order)
{
    $mailer = WC()->mailer();
    $notification = $mailer->emails['Woo_Tiny_Order_Revision_Email'];
    if (method_exists($order, 'get_id')) {
        $notification->trigger($order->get_id(), $order);
    } else {
        $notification->trigger($order->id, $order);
    }
}

function woo_tiny_trigger_admin_orders_await_revision_email(){
    $mailer = WC()->mailer();
    $notification = $mailer->emails['Woo_Tiny_Admin_Orders_Await_Revision_Email'];
    $notification->trigger();
}
