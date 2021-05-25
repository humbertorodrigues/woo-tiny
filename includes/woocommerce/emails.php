<?php
add_filter('woocommerce_email_classes', 'woo_tiny_include_email_classes');
//add_filter('woocommerce_email_recipient_in_revision', 'woo_tiny_set_email_recipient', 10, 3);
global $woocommerce;

function woo_tiny_include_email_classes($emails)
{
    if (!isset($emails['Woo_Tiny_Order_Revision_Email'])) {
        $emails['Woo_Tiny_Order_Revision_Email'] = include WOO_TINY_DIR . 'classes/class-woo-tiny-order-revision-email.php';
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

function woo_tiny_set_email_recipient($recipient, $order, $email)
{
    return $recipient;
}
