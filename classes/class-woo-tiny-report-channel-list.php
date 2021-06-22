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
        include WOO_TINY_DIR . 'templates/reports/channel-list.php';
    }

    public function column_default($item, $column_name)
    {

        switch ($column_name) {
            case 'channel':
            case 'type':
                return $item->$column_name;
            case 'fulfilled':
            case 'pre_sale':
            case 'goal':
            case 'in_wallet':
                return wc_price($item->$column_name);
            case 'target':
                $style = 'style="color: red;"';
                if ($item->fulfilled > $item->goal) {
                    $style = 'style="color: blue;"';
                }
                return '<span ' . $style . '>' . abs($item->$column_name) . '%</span>';
            default:
                return '';
        }
    }

    public function get_columns()
    {
        return [
            'channel' => 'Canal',
            'type' => 'Tipo',
            'goal' => 'Objetivo',
            'fulfilled' => 'Realizado',
            'pre_sale' => 'Pré-venda',
            'in_wallet' => 'Em carteira',
            'target' => 'Atingimento meta'
        ];
    }

    public function prepare_items()
    {
        $this->_column_headers = [$this->get_columns(), [], $this->get_sortable_columns()];
        $this->items = [];
        $this->items[] = $this->get_ecommerce_row();
        array_map(function ($item) {
            $channel = new stdClass();
            $channel->id = $item->ID;
            $channel->channel = $item->post_title;
            $channel->type = get_post_meta($channel->id, 'type', true);
            $channel->fulfilled = $this->get_fulfilled_by_order_query($channel->id);
            $channel->in_wallet = $this->get_in_wallet_by_order_query($channel->id);
            $channel->pre_sale = $this->get_pre_sale_by_order_query($channel->id);
            $channel->goal = $this->get_goal($channel->id);
            $channel->target = $channel->goal - $channel->fulfilled;
            if ($channel->fulfilled == 0) {
                $channel->target = $channel->fulfilled;
            } elseif ($channel->goal > 0) {
                $channel->target = round((($channel->target / $channel->goal) * 100), 2);
                $channel->target = round($channel->target - 100,2);
            }
            $this->items[] = $channel;
        }, get_posts([
            'post_type' => 'canal_venda',
            'numberposts' => -1,
            'exclude' => [109033],
            'meta_key' => 'type',
            'orderby' => 'meta_value',
            'order' => 'DESC'
        ]));
    }

    public function display()
    {
        $singular = $this->_args['singular'];

        $this->display_tablenav('top');

        $this->screen->render_screen_reader_content('heading_list');
        ?>
        <table class="wp-list-table <?php echo implode(' ', $this->get_table_classes()); ?>">
            <thead>
            <tr>
                <?php $this->print_column_headers(); ?>
            </tr>
            </thead>

            <tbody id="the-list"
                <?php
                if ($singular) {
                    echo " data-wp-lists='list:$singular'";
                }
                ?>
            >
            <?php $this->display_rows_or_placeholder(); ?>
            </tbody>

            <tfoot>
            <tr>
                <?php $this->get_tfooter(); ?>
            </tr>
            </tfoot>

        </table>
        <?php
        $this->display_tablenav('bottom');
        $this->display_totals();
    }

    public function display_rows()
    {
        $key = 1;
        $type = 'B2C';
        foreach ($this->items as $item) {
            $this->single_row($item);
            if ($key == $this->get_count_type($type)) {
                $total_goal = $this->get_total_type($type, 'goal');
                $total_fulfilled = $this->get_total_type($type, 'fulfilled');
                $total_row = '<tr class="woo-tiny-table-separator">';
                $total_row .= '<td class="channel column-channel has-row-actions column-primary" data-colname="Total ' . $type . '" colspan="2">Total ' . $type . '<button type="button" class="toggle-row"><span class="screen-reader-text">Mostrar mais detalhes</span></button></td>';
                $total_row .= '<td class="goal column-goal" data-colname="Objetivo">' . wc_price($total_goal) . '</td>';
                $total_row .= '<td class="fulfilled column-fulfilled" data-colname="Realizado">' . wc_price($total_fulfilled) . '</td>';
                $total_row .= '<td class="pre_sale column-pre_sale" data-colname="Pré-venda">' . wc_price($this->get_total_type($type, 'pre_sale')) . '</td>';
                $total_row .= '<td class="in_wallet column-in_wallet" data-colname="Em carteira">' . wc_price($this->get_total_type($type, 'in_wallet')) . '</td>';
                $total_target = $total_goal - $total_fulfilled;
                if ($total_fulfilled == 0) {
                    $total_target = $total_fulfilled;
                } elseif ($total_goal > 0) {
                    $total_target = round((($total_target / $total_goal) * 100), 2);
                    $total_target = round($total_target - 100,2);
                }
                $style = 'style="color: red !important"';
                if ($total_fulfilled > $total_goal) {
                    $style = 'style="color: blue !important"';
                }
                $total_row .= '<td class="target column-target" data-colname="Atingimento meta"' . $style . '>' . abs($total_target) . '%</td>';
                $total_row .= '</tr>';
                $key = 1;
                $type = 'B2B';
                echo $total_row;
            } else {
                $key++;
            }
        }
    }

    private function get_tfooter()
    {
        $total_goal = $this->get_total_type('', 'goal');
        $total_fulfilled = $this->get_total_type('', 'fulfilled');
        $total_row = '<td colspan="7"></td></tr><tr class="woo-tiny-table-separator"><th class="manage-column column-primary" data-colname="Total B2B+B2C" colspan="2">Total B2B+B2C</th>';
        $total_row .= '<th class="manage-column" data-colname="Objetivo">' . wc_price($total_goal) . '</th>';
        $total_row .= '<th class="manage-column" data-colname="Realizado">' . wc_price($total_fulfilled) . '</th>';
        $total_row .= '<th class="manage-column" data-colname="Pré-venda">' . wc_price($this->get_total_type('', 'pre_sale')) . '</th>';
        $total_row .= '<th class="manage-column" data-colname="Em carteira">' . wc_price($this->get_total_type('', 'in_wallet')) . '</th>';
        $total_target = $total_goal - $total_fulfilled;
        if ($total_fulfilled == 0) {
            $total_target = $total_fulfilled;
        } elseif ($total_goal > 0) {
            $total_target = round((($total_target / $total_goal) * 100), 2);
            $total_target = round($total_target - 100,2);
        }
        $style = 'style="color: red !important"';
        if ($total_fulfilled > $total_goal) {
            $style = 'style="color: blue !important"';
        }
        $total_row .= '<td class="manage-column" data-colname="Atingimento meta" ' . $style . '>' . abs($total_target) . '%</td>';
        echo $total_row;
    }

    private function get_ecommerce_row()
    {
        // Ecommerce 109033
        $channel = new stdClass();
        $channel->id = 0;
        $channel->channel = 'Ecommerce';
        $channel->type = 'B2C';
        $channel->fulfilled = $this->get_fulfilled_by_order_query($channel->id);
        $channel->in_wallet = $this->get_in_wallet_by_order_query($channel->id);
        $channel->pre_sale = $this->get_pre_sale_by_order_query($channel->id);
        $channel->goal = $this->get_goal($channel->id);
        $channel->target = $channel->goal - $channel->fulfilled;
        if ($channel->fulfilled == 0) {
            $channel->target = $channel->fulfilled;
        } elseif ($channel->goal > 0) {
            $channel->target = round((($channel->target / $channel->goal) * 100), 2);
            $channel->target = round($channel->target - 100,2);
        }
        return $channel;
    }

    private function get_where_meta($channel_id)
    {
        $where_meta = [];
        if ($channel_id > 0) {
            $where_meta[] = [
                'meta_key' => 'bw_canal_venda',
                'operator' => '=',
                'meta_value' => $channel_id
            ];
        } else {
            $channels = array_map(function ($item) {
                return $item->ID;
            }, get_posts([
                'post_type' => 'canal_venda',
                'numberposts' => -1
            ]));
            $where_meta[] = [
                'meta_key' => 'bw_canal_venda',
                'operator' => 'NOT IN',
                'meta_value' => $channels
            ];
        }
        return $where_meta;
    }


    private function get_fulfilled($channel_id)
    {
        global $wpdb;
        $args = [
            'filter_range' => true,
            'order_status' => ['completed', 'processing', 'shipping'],
            'meta_key' => 'tiny_nf', // The postmeta key field
            'meta_compare' => 'EXISTS',
            'where_meta' => $this->get_where_meta($channel_id),
            'data' => [
                '_order_total' => [
                    'type' => 'meta',
                    'function' => 'SUM',
                    'name' => 'fulfilled',
                ],
            ],
        ];
        $query = $this->prepare_query($args);
        $query = implode(' ', $query);
        self::enable_big_selects();
        $result = $wpdb->get_row($query);
        return $result->fulfilled;
    }

    private function get_in_wallet($channel_id)
    {
        global $wpdb;
        $args = [
            'filter_range' => true,
            'order_status' => ['wallet'],
            'where_meta' => $this->get_where_meta($channel_id),
            'data' => [
                '_order_total' => [
                    'type' => 'meta',
                    'function' => 'SUM',
                    'name' => 'in_wallet',
                ]
            ],
        ];
        $query = $this->prepare_query($args);
        $query = implode(' ', $query);
        self::enable_big_selects();
        $result = $wpdb->get_row($query);
        return $result->in_wallet;
    }

    private function get_pre_sale($channel_id)
    {
        global $wpdb;
        $args = [
            'filter_range' => true,
            'order_status' => ['completed', 'processing', 'shipping'],
            'where_meta' => $this->get_where_meta($channel_id),
            'meta_key' => 'tiny_nf', // The postmeta key field
            'meta_compare' => 'NOT EXISTS',
            'data' => [
                '_order_total' => [
                    'type' => 'meta',
                    'function' => 'SUM',
                    'name' => 'pre_sale',
                ]
            ],
        ];
        $query = $this->prepare_query($args);
        $query = implode(' ', $query);
        self::enable_big_selects();
        $result = $wpdb->get_row($query);

        return $result->pre_sale;
    }

    private function get_total_type($type, $field = 'fulfilled')
    {
        if ($this->items != null && is_array($this->items)) {
            $total = 0;
            array_map(function ($item) use ($type, $field, &$total) {
                if ($type == $item->type) {
                    $total += $item->$field;
                } elseif ($type == '') {
                    $total += $item->$field;
                }
            }, $this->items);
            return $total;
        }
        return 0;
    }

    private function get_count_type($type)
    {
        if ($this->items != null && is_array($this->items)) {
            $total = 0;
            array_map(function ($item) use ($type, &$total) {
                if ($type == $item->type) {
                    $total++;
                }
            }, $this->items);
            return $total;
        }
        return 0;
    }

    private function get_goal($channel_id)
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
                $start_date = $this->start_date;
                while ($start_date <= $this->end_date) {
                    $metakey = 'goal_' . date('Y_n', $start_date);
                    $start_date = strtotime("+1 month", $start_date);
                    $metavalue = get_post_meta($channel_id, $metakey, true);
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
                $goal = get_post_meta($channel_id, $metakey, true);
                if ($goal == '') {
                    $goal = 0;
                }
                break;
        }

        $goal = only_numbers($goal);
        return round($goal / 100, 2);
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
            'order_status' => ['completed', 'processing'],
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

    private static function enable_big_selects()
    {
        static $big_selects = false;

        global $wpdb;

        if (!$big_selects) {
            $wpdb->query('SET SESSION SQL_BIG_SELECTS=1');
            $big_selects = true;
        }
    }

    private function check_current_range_nonce($current_range)
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

    private function calculate_current_range($current_range)
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

    private function get_fulfilled_by_order_query($channel_id)
    {
        $query = new WC_Order_Query([
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'date_paid' => $this->start_date . "..." . $this->end_date,  //'2018-02-01...2018-02-28',
            'status' => ['wc-processing', 'wc-completed', 'wc-shipping'],
            'meta_key' => 'tiny_nf', // The postmeta key field
            'meta_compare' => 'EXISTS',

        ]);
        $orders = $query->get_orders();
        $total_vendas = 0;
        foreach ($orders as $order) {
            $order_id = $order->data['id'];
            $total = $order->data['total'];
            if ($channel_id == get_post_meta($order_id, "bw_canal_venda", true)) {
                $total_vendas += $total;
            }
        }
        return $total_vendas;
    }

    private function get_pre_sale_by_order_query($channel_id)
    {
        $query = new WC_Order_Query([
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'date_paid' => $this->start_date . "..." . $this->end_date,  //'2018-02-01...2018-02-28',
            'status' => ['wc-processing', 'wc-completed', 'wc-shipping'],
            'meta_key' => 'tiny_nf', // The postmeta key field
            'meta_compare' => 'NOT EXISTS',

        ]);
        $orders = $query->get_orders();
        $total_vendas = 0;
        foreach ($orders as $order) {
            $order_id = $order->data['id'];
            $total = $order->data['total'];
            if ($channel_id == get_post_meta($order_id, "bw_canal_venda", true)) {
                $total_vendas += $total;
            }
        }
        return $total_vendas;
    }

    private function get_in_wallet_by_order_query($channel_id)
    {
        $query = new WC_Order_Query([
            'limit' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'date_created' => $this->start_date . "..." . $this->end_date,  //'2018-02-01...2018-02-28',
            'status' => ['wc-wallet']
        ]);
        $orders = $query->get_orders();
        $total_vendas = 0;
        foreach ($orders as $order) {
            $order_id = $order->data['id'];
            $total = $order->data['total'];
            if ($channel_id == get_post_meta($order_id, "bw_canal_venda", true)) {
                $total_vendas += $total;
            }
        }
        return $total_vendas;
    }

    private function display_totals(){
        $total_pre_sale = $this->get_total_type('', 'pre_sale');
        $total_fulfilled = $this->get_total_type('', 'fulfilled');
        $total_in_wallet = $this->get_total_type('', 'in_wallet');
        ?>
        <table class="widefat">
            <tbody>
            <tr class="woo-tiny-table-separator">
                <td>Total B2B + B2C</td>
                <td></td>
                <td><?= wc_price($total_fulfilled) ?></td>
            </tr>
            <tr class="woo-tiny-table-separator">
                <td>OBS: á faturar pré-venda (recebidos)</td>
                <td></td>
                <td><?= wc_price($total_pre_sale) ?></td>
            </tr>
            <tr class="woo-tiny-table-separator">
                <td>OBS: á faturar carteira (não recebidos)</td>
                <td></td>
                <td><?= wc_price($total_in_wallet) ?></td>
            </tr>
            <tr class="woo-tiny-table-separator">
                <td>Total B2B + B2C + OBS</td>
                <td></td>
                <td><?= wc_price($total_fulfilled + $total_pre_sale +$total_in_wallet) ?></td>
            </tr>
            </tbody>
        </table>
        <?php
    }


}