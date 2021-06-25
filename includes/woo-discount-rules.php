<?php

if (class_exists('\Wdr\App\Models\DBTable')) {
    function woo_tiny_discount_rule_get_channel_id($order_id)
    {
        $channel_id = '';
        $wdrdb = new \Wdr\App\Models\DBTable();
        global $wpdb;
        $table_name = $wpdb->prefix . $wdrdb::ORDER_ITEM_DISCOUNT_TABLE_NAME;
        $rule_ids = [21, 22, 12, 11, 10, 4, 5, 6, 13, 23, 18, 14, 15, 16, 17, 24, 25];
        $rule_ids = implode(', ', $rule_ids);
        $query = "SELECT rule_id FROM {$table_name} WHERE rule_id IN ({$rule_ids}) AND order_id={$order_id}";
        $discount = $wpdb->get_row($query);
        if ($discount) {
            $channel_id = 95290;
        }
        return $channel_id;
    }
}