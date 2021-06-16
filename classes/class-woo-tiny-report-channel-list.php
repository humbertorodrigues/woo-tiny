<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('WP_List_Table')) {
    require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class WC_Report_Woo_Tiny_Channel_List extends WP_List_Table
{

    private $start_date;
    private $end_date;

    public function __construct()
    {

        parent::__construct(
            array(
                'singular' => 'channel',
                'plural' => 'channels',
                'ajax' => false,
            )
        );
    }


    public function no_items()
    {
        echo 'nenhum canal encontrado';
    }

    /**
     * Output the report.
     */
    public function output_report()
    {
        $ranges = array(
            'year' => __('Year', 'woocommerce'),
            'last_month' => __('Last month', 'woocommerce'),
            'month' => __('This month', 'woocommerce'),
            '7day' => __('Last 7 days', 'woocommerce'),
        );

        $current_range = !empty($_GET['range']) ? sanitize_text_field(wp_unslash($_GET['range'])) : '7day'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if (!in_array($current_range, array('custom', 'year', 'last_month', 'month', '7day'), true)) {
            $current_range = '7day';
        }

        $this->check_current_range_nonce($current_range);
        $this->calculate_current_range($current_range);
        $this->prepare_items();

        $total_b2b = $this->get_total_type('B2B');
        $total_b2c = $this->get_total_type('B2C');
        $total_b2b_b2c = $total_b2b + $total_b2c;
        include WOO_TINY_DIR . 'templates/reports/channel-list.php';
    }

    public function column_default($item, $column_name)
    {
        $goal = $this->get_goal($item);
        switch ($column_name) {
            case 'channel':
                return get_post_field('post_title', $item->$column_name);
            case 'type':
                return get_post_meta($item->channel, $column_name, true);
            case 'goal':
                return wc_price($goal);
            case 'fulfilled':
                return wc_price($item->$column_name);
            case 'target':
                $balance = $goal - round((float) $item->fulfilled, 2);
                $balance = round((($balance / $goal) * 100), 2);
                if($balance > 0){
                    return '<span style="color: red;">' . $balance . '%</span>';
                }
                return '<span style="color: blue" >' . abs($balance) . '%</span>';
            default:
                return '';
        }
    }

    private function get_goal($item)
    {
        $current_range = !empty($_GET['range']) ? sanitize_text_field(wp_unslash($_GET['range'])) : '7day';

        if (!in_array($current_range, array('custom', 'year', 'last_month', 'month', '7day'), true)) {
            $current_range = '7day';
        }

        $this->calculate_current_range($current_range);

        switch ($current_range) {
            case 'custom':
            case 'year':
                $goal = 0;
                while ($this->start_date <= $this->end_date) {
                    $metakey = 'goal_' . date('Y_n', $this->start_date);
                    $this->start_date = strtotime("+1 month", $this->start_date);
                    $metavalue = get_post_meta($item->channel, $metakey, true);
                    if ($metavalue != '') {
                        $metavalue = (int)only_numbers($metavalue);
                        $goal += $metavalue;
                    }
                }
                break;
            case 'last_month':
            case 'month':
            case '7day':
            default:
                $metakey = 'goal_' . date('Y_n', $this->end_date);
                $goal = get_post_meta($item->channel, $metakey, true);
                if ($goal == '') {
                    $goal = 0;
                }
                break;
        }

        $goal = only_numbers($goal);
        return round($goal / 100, 2);
    }

    public function get_columns()
    {
        return [
            'channel' => 'Canal',
            'type' => 'Tipo',
            'goal' => 'Objetivo',
            'fulfilled' => 'Realizado',
            'target' => 'Percentual de atingimento'
        ];
    }

    
    public function prepare_items()
    {
        global $wpdb;
        $current_page = absint($this->get_pagenum());
        $per_page = 20;
        $channels = array_map(function($item) {return $item->ID;}, get_posts([
            'post_type' => 'canal_venda',
            'numberposts' => -1
        ]));
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
        $args = [
            'group_by' => 'channel',
            'order_by' => 'type DESC',
            'filter_range' => true,
            'order_status' => ['completed', 'processing', 'on-hold', 'refunded', 'revision'],
            'where_meta' => [
                [
                    'meta_key' => 'bw_canal_venda',
                    'operator' => 'IN',
                    'meta_value' => $channels
                ],
            ],
            'data' => [
                'bw_canal_venda' => [
                    'type' => 'meta',
                    'function' => '',
                    'name' => 'channel',
                ],
                '_order_total' => [
                    'type' => 'meta',
                    'function' => 'SUM',
                    'name' => 'fulfilled',
                ],
                'post_date' => [
                    'type' => 'post_data',
                    'function' => '',
                    'name' => 'post_date',
                ],
            ],
        ];
        $query = $this->prepare_query($args);
        $query['select'] .= ', meta_type.meta_value as type';
        $query['join'] .= " INNER JOIN {$wpdb->postmeta} AS meta_type ON ( meta_type.post_id = meta_bw_canal_venda.meta_value AND meta_type.meta_key = 'type' )";
        $query = implode(' ', $query);
        self::enable_big_selects();
        $this->items = $wpdb->get_results($query);
        $this->set_pagination_args([
            'total_items' => $wpdb->total_users,
            'per_page' => $per_page,
            'total_pages' => ceil($wpdb->total_users / $per_page),
        ]);
    }

    public function get_total_type($type){
        if($this->items != null && is_array($this->items)){
            $total = 0;
            array_map(function ($item) use ($type, &$total){
                if($type == get_post_meta($item->channel, 'type', true)){
                    $total += $item->fulfilled;
                }
            }, $this->items);
            return $total;
        }
        return 0;
    }

    private function prepare_query($args = [])
    {
        global $wpdb;

        $default_args = [
            'data' => [],
            'where' => [],
            'where_meta' => [],
            'group_by' => '',
            'order_by' => '',
            'limit' => '',
            'filter_range' => false,
            'order_types' => wc_get_order_types('reports'),
            'order_status' => ['completed', 'processing', 'on-hold'],
            'parent_order_status' => false,
        ];
        $args = wp_parse_args($args, $default_args);

        extract($args);

        if (empty($data)) {
            return '';
        }

        $query = [];
        $select = [];

        foreach ($data as $raw_key => $value) {
            $key = sanitize_key($raw_key);
            $distinct = '';

            if (isset($value['distinct'])) {
                $distinct = 'DISTINCT';
            }

            switch ($value['type']) {
                case 'meta':
                    $get_key = "meta_{$key}.meta_value";
                    break;
                case 'parent_meta':
                    $get_key = "parent_meta_{$key}.meta_value";
                    break;
                case 'post_data':
                    $get_key = "posts.{$key}";
                    break;
                case 'order_item_meta':
                    $get_key = "order_item_meta_{$key}.meta_value";
                    break;
                case 'order_item':
                    $get_key = "order_items.{$key}";
                    break;
            }

            if (empty($get_key)) {
                // Skip to the next foreach iteration else the query will be invalid.
                continue;
            }

            if ($value['function']) {
                $get = "{$value['function']}({$distinct} {$get_key})";
            } else {
                $get = "{$distinct} {$get_key}";
            }

            $select[] = "{$get} as {$value['name']}";
        }

        $query['select'] = 'SELECT ' . implode(',', $select);
        $query['from'] = "FROM {$wpdb->posts} AS posts";

        // Joins
        $joins = array();

        foreach (($data + $where) as $raw_key => $value) {
            $join_type = isset($value['join_type']) ? $value['join_type'] : 'INNER';
            $type = isset($value['type']) ? $value['type'] : false;
            $key = sanitize_key($raw_key);

            switch ($type) {
                case 'meta':
                    $joins["meta_{$key}"] = "{$join_type} JOIN {$wpdb->postmeta} AS meta_{$key} ON ( posts.ID = meta_{$key}.post_id AND meta_{$key}.meta_key = '{$raw_key}' )";
                    break;
                case 'parent_meta':
                    $joins["parent_meta_{$key}"] = "{$join_type} JOIN {$wpdb->postmeta} AS parent_meta_{$key} ON (posts.post_parent = parent_meta_{$key}.post_id) AND (parent_meta_{$key}.meta_key = '{$raw_key}')";
                    break;
                case 'order_item_meta':
                    $joins['order_items'] = "{$join_type} JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON (posts.ID = order_items.order_id)";

                    if (!empty($value['order_item_type'])) {
                        $joins['order_items'] .= " AND (order_items.order_item_type = '{$value['order_item_type']}')";
                    }

                    $joins["order_item_meta_{$key}"] = "{$join_type} JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_{$key} ON " .
                        "(order_items.order_item_id = order_item_meta_{$key}.order_item_id) " .
                        " AND (order_item_meta_{$key}.meta_key = '{$raw_key}')";
                    break;
                case 'order_item':
                    $joins['order_items'] = "{$join_type} JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";
                    break;
            }
        }

        if (!empty($where_meta)) {
            foreach ($where_meta as $value) {
                if (!is_array($value)) {
                    continue;
                }
                $join_type = isset($value['join_type']) ? $value['join_type'] : 'INNER';
                $type = isset($value['type']) ? $value['type'] : false;
                $key = sanitize_key(is_array($value['meta_key']) ? $value['meta_key'][0] . '_array' : $value['meta_key']);

                if ('order_item_meta' === $type) {

                    $joins['order_items'] = "{$join_type} JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";
                    $joins["order_item_meta_{$key}"] = "{$join_type} JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_{$key} ON order_items.order_item_id = order_item_meta_{$key}.order_item_id";

                } else {
                    // If we have a where clause for meta, join the postmeta table
                    $joins["meta_{$key}"] = "{$join_type} JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";
                }
            }
        }

        if (!empty($parent_order_status)) {
            $joins['parent'] = "LEFT JOIN {$wpdb->posts} AS parent ON posts.post_parent = parent.ID";
        }

        $query['join'] = implode(' ', $joins);

        $query['where'] = "
			WHERE 	posts.post_type 	IN ( '" . implode("','", $order_types) . "' )
			";

        if (!empty($order_status)) {
            $query['where'] .= "
				AND 	posts.post_status 	IN ( 'wc-" . implode("','wc-", $order_status) . "')
			";
        }

        if (!empty($parent_order_status)) {
            if (!empty($order_status)) {
                $query['where'] .= " AND ( parent.post_status IN ( 'wc-" . implode("','wc-", $parent_order_status) . "') OR parent.ID IS NULL ) ";
            } else {
                $query['where'] .= " AND parent.post_status IN ( 'wc-" . implode("','wc-", $parent_order_status) . "') ";
            }
        }

        if ($filter_range) {
            $query['where'] .= "
				AND 	posts.post_date >= '" . date('Y-m-d H:i:s', $this->start_date) . "'
				AND 	posts.post_date < '" . date('Y-m-d H:i:s', strtotime('+1 DAY', $this->end_date)) . "'
			";
        }

        if (!empty($where_meta)) {

            $relation = isset($where_meta['relation']) ? $where_meta['relation'] : 'AND';

            $query['where'] .= ' AND (';

            foreach ($where_meta as $index => $value) {

                if (!is_array($value)) {
                    continue;
                }

                $key = sanitize_key(is_array($value['meta_key']) ? $value['meta_key'][0] . '_array' : $value['meta_key']);

                if (strtolower($value['operator']) == 'in' || strtolower($value['operator']) == 'not in') {

                    if (is_array($value['meta_value'])) {
                        $value['meta_value'] = implode("','", $value['meta_value']);
                    }

                    if (!empty($value['meta_value'])) {
                        $where_value = "{$value['operator']} ('{$value['meta_value']}')";
                    }
                } else {
                    $where_value = "{$value['operator']} '{$value['meta_value']}'";
                }

                if (!empty($where_value)) {
                    if ($index > 0) {
                        $query['where'] .= ' ' . $relation;
                    }

                    if (isset($value['type']) && 'order_item_meta' === $value['type']) {

                        if (is_array($value['meta_key'])) {
                            $query['where'] .= " ( order_item_meta_{$key}.meta_key   IN ('" . implode("','", $value['meta_key']) . "')";
                        } else {
                            $query['where'] .= " ( order_item_meta_{$key}.meta_key   = '{$value['meta_key']}'";
                        }

                        $query['where'] .= " AND order_item_meta_{$key}.meta_value {$where_value} )";
                    } else {

                        if (is_array($value['meta_key'])) {
                            $query['where'] .= " ( meta_{$key}.meta_key   IN ('" . implode("','", $value['meta_key']) . "')";
                        } else {
                            $query['where'] .= " ( meta_{$key}.meta_key   = '{$value['meta_key']}'";
                        }

                        $query['where'] .= " AND meta_{$key}.meta_value {$where_value} )";
                    }
                }
            }

            $query['where'] .= ')';
        }

        if (!empty($where)) {

            foreach ($where as $value) {

                if (strtolower($value['operator']) == 'in' || strtolower($value['operator']) == 'not in') {

                    if (is_array($value['value'])) {
                        $value['value'] = implode("','", $value['value']);
                    }

                    if (!empty($value['value'])) {
                        $where_value = "{$value['operator']} ('{$value['value']}')";
                    }
                } else {
                    $where_value = "{$value['operator']} '{$value['value']}'";
                }

                if (!empty($where_value)) {
                    $query['where'] .= " AND {$value['key']} {$where_value}";
                }
            }
        }

        if ($group_by) {
            $query['group_by'] = "GROUP BY {$group_by}";
        }

        if ($order_by) {
            $query['order_by'] = "ORDER BY {$order_by}";
        }

        if ($limit) {
            $query['limit'] = "LIMIT {$limit}";
        }


        return $query;
    }

    protected static function enable_big_selects()
    {
        static $big_selects = false;

        global $wpdb;

        if (!$big_selects) {
            $wpdb->query('SET SESSION SQL_BIG_SELECTS=1');
            $big_selects = true;
        }
    }

    public function check_current_range_nonce($current_range)
    {
        if ('custom' !== $current_range) {
            return;
        }

        if (!isset($_GET['wc_reports_nonce']) || !wp_verify_nonce(sanitize_key($_GET['wc_reports_nonce']), 'custom_range')) { // WPCS: input var ok, CSRF ok.
            wp_die(
            /* translators: %1$s: open link, %2$s: close link */
                sprintf(esc_html__('This report link has expired. %1$sClick here to view the filtered report%2$s.', 'woocommerce'), '<a href="' . esc_url(wp_nonce_url(esc_url_raw(wp_unslash($_SERVER['REQUEST_URI'])), 'custom_range', 'wc_reports_nonce')) . '">', '</a>'), // @codingStandardsIgnoreLine.
                esc_attr__('Confirm navigation', 'woocommerce')
            );
            exit;
        }
    }

    public function calculate_current_range($current_range)
    {
        switch ($current_range) {
            case 'custom':
                $this->start_date = max(strtotime('-20 years'), strtotime(sanitize_text_field($_GET['start_date'])));
                if (empty($_GET['end_date'])) {
                    $this->end_date = strtotime('midnight', current_time('timestamp'));
                } else {
                    $this->end_date = strtotime('midnight', strtotime(sanitize_text_field($_GET['end_date'])));
                }
                break;
            case 'year':
                $this->start_date = strtotime(date('Y-01-01', current_time('timestamp')));
                $this->end_date = strtotime('midnight', current_time('timestamp'));
                break;
            case 'last_month':
                $first_day_current_month = strtotime(date('Y-m-01', current_time('timestamp')));
                $this->start_date = strtotime(date('Y-m-01', strtotime('-1 DAY', $first_day_current_month)));
                $this->end_date = strtotime(date('Y-m-t', strtotime('-1 DAY', $first_day_current_month)));
                break;
            case 'month':
                $this->start_date = strtotime(date('Y-m-01', current_time('timestamp')));
                $this->end_date = strtotime('midnight', current_time('timestamp'));
                break;
            case '7day':
            default:
                $this->start_date = strtotime('-6 days', strtotime('midnight', current_time('timestamp')));
                $this->end_date = strtotime('midnight', current_time('timestamp'));
                break;
        }
    }
}