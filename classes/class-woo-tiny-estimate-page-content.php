<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Woo_Tiny_Estimate_Page_Content extends WP_List_Table
{
    private static $db;
    private static $table;
    private static $status;

    private $_actions = [
        'woo_tiny_estimate_show',
        'woo_tiny_estimate_update_status',
        'woo_tiny_estimate_delete',
    ];

    public function __construct()
    {
        parent::__construct([
            'singular' => 'estimate',
            'plural' => 'estimates',
            'screen' => 'woo_tiny_estimates',
        ]);
        global $wpdb;
        self::$db = $wpdb;
        self::$table = $wpdb->posts;
        self::$status = 'wc-estimate';
    }

    public function get_bulk_actions()
    {
        $actions = [
            'bulk-delete' => 'Excluir'
        ];
        return $actions;
    }

    public function process_bulk_action()
    {
        $referer = admin_url('admin.php?page=woo_tiny_estimates');
        if (in_array($this->current_action(), $this->_actions)) {
            $nonce = esc_attr($_REQUEST['_wpnonce']);
            if (!wp_verify_nonce($nonce, 'woo_tiny_estimate_nonce')) {
                die('Go get a life script kiddies');
            } else {
                switch ($this->current_action()) {
                    case 'woo_tiny_estimate_show':
                        $this->show_item(absint($_GET['item']));
                        break;
                    case 'woo_tiny_estimate_update_status':
                        self::update_status_item(absint($_GET['item']));
                        break;
                    case 'woo_tiny_estimate_delete':
                        self::delete_item(absint($_GET['item']));
                        break;
                    default:
                        break;
                }
                wp_redirect($referer);
                exit;
            }
        }

        if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete') || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')) {
            $delete_ids = esc_sql($_POST['items']);
            foreach ($delete_ids as $id) {
                self::delete_item($id);
            }
            wp_redirect($referer);
            exit;
        }
    }

    protected function extra_tablenav($which)
    {
        if ($which == "top") {
            //
        }
        if ($which == "bottom") {
            //
        }
    }

    public function get_columns()
    {
        return [
            'cb' => '<input type="checkbox">',
            'number' => 'Número',
            'total' => 'Total',
            'customer' => 'Cliente',
            'seller' => 'Vendedor',
            'date' => 'Data',
        ];
    }

    public function get_sortable_columns()
    {
        return [
            'number' => ['ID', true],
            'date' => ['post_date', false],
        ];
    }

    protected function column_default($item, $column_name)
    {
        return $item[$column_name] ?? '';
    }

    protected function column_cb($item)
    {
        return sprintf('<input type="checkbox" name="items[]" value="%d">', $item['number']);
    }

    public function column_number($item)
    {
        $estimate_nonce = wp_create_nonce('woo_tiny_estimate_nonce');
        $title = '#' . $item['number'] . '';
        $actions = [
            'show' => sprintf('<a href="?page=%s&action=%s&item=%d&_wpnonce=%s" target="_blank">Ver</a>', esc_attr($_REQUEST['page']), 'woo_tiny_estimate_show', absint($item['number']), $estimate_nonce),
            //'edit' => sprintf('<a href="post.php?post=%d&action=edit">Editar</a>', absint($item['number'])),
            'update-status' => sprintf('<a href="?page=%s&action=%s&item=%d&_wpnonce=%s">Transformar em Pedido</a>', esc_attr($_REQUEST['page']), 'woo_tiny_estimate_update_status', absint($item['number']), $estimate_nonce),
            'delete' => sprintf('<a href="?page=%s&action=%s&item=%d&_wpnonce=%s">Excluir</a>', esc_attr($_REQUEST['page']), 'woo_tiny_estimate_delete', absint($item['number']), $estimate_nonce)
        ];
        return $title . $this->row_actions($actions);
    }

    public function column_total($item)
    {
        return 'R$ ' . number_format($item['total'], 2, ',', '.');
    }

    public function column_date($item)
    {
        return date('d/m/Y H:i:s', strtotime($item['date']));
    }

    public static function get_items($per_page = 20, $page_number = 1)
    {
        $sql = "SELECT * FROM " . self::$table . " WHERE post_type='shop_order' AND post_status='" . self::$status . "'";
        if (!empty($_REQUEST['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($_REQUEST['orderby']);
            $sql .= !empty($_REQUEST['order']) ? ' ' . esc_sql($_REQUEST['order']) : ' ASC';
        } else {
            $sql .= ' ORDER BY post_date DESC';
        }
        $sql .= " LIMIT {$per_page}";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        return array_map(function ($item) {
            $estimate = wc_get_order($item['ID']);
            $customer = get_userdata($estimate->get_customer_id());
            $customer = $customer ? $customer->display_name : '';
            $seller = get_userdata(get_post_meta($item['ID'], 'bw_id_vendedor', true));
            $seller = $seller ? $seller->display_name : '';
            return [
                'cb' => '',
                'number' => $item['ID'],
                'total' => $estimate->get_total(),
                'customer' => $customer,
                'seller' => $seller,
                'date' => $item['post_date'],
            ];
        }, (array)self::$db->get_results($sql, 'ARRAY_A'));
    }

    public function no_items()
    {
        echo 'Sem orçamentos por enquanto';
    }

    public function prepare_items()
    {
        $this->_column_headers = $this->get_column_info();
        /** Process bulk action */
        $this->process_bulk_action();
        $per_page = $this->get_items_per_page('items_per_page', 15);
        $current_page = $this->get_pagenum();
        $total_items = self::record_count();
        $this->set_pagination_args([
            'total_items' => $total_items,
            'per_page' => $per_page
        ]);
        $this->items = self::get_items($per_page, $current_page);
    }

    public static function record_count()
    {

        $sql = "SELECT COUNT(*) FROM " . self::$table . " WHERE post_type='shop_order' AND post_status='" . self::$status . "'";
        return self::$db->get_var($sql);
    }

    public static function delete_item($id)
    {
        $table_meta = self::$db->prefix . 'postmeta';
        self::$db->delete(
            $table_meta,
            ['post_id' => $id],
            ['%d']
        );
        self::$db->delete(
            self::$table,
            ['ID' => $id],
            ['%d']
        );
    }

    public static function update_status_item($id)
    {
        self::$db->update(self::$table, ['post_status' => 'wc-revision'], ['ID' => $id], ['%s'], ['%d']);
    }

    public function show_item($id)
    {
        woo_tiny_estimate_generate_pdf($id);
    }
}