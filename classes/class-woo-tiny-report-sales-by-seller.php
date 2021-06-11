<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('WC_Admin_Report')) {
    require_once WC()->plugin_path() . '/includes/admin/reports/class-wc-admin-report.php';
}


class WC_Report_Woo_Tiny_Sales_By_Seller extends WC_Admin_Report
{

    /**
     * Chart colors.
     *
     * @var array
     */
    public $chart_colours = array();

    /**
     * Product ids.
     *
     * @var array
     */
    public $seller_ids = array();

    /**
     * Product ids with titles.
     *
     * @var array
     */
    public $seller_ids_titles = array();

    /**
     * Constructor.
     */
    public function __construct()
    {
        // @codingStandardsIgnoreStart
        if (isset($_GET['seller_id'])) {
            $this->seller_ids = array_filter(array(absint($_GET['seller_id'])));
        } else {
            $this->seller_ids = array_filter(array_map(function ($seller) {
                return absint($seller->ID);
            }, get_users(['role__in' => ['vendedores_bw']])));
        }
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get the legend for the main chart sidebar.
     *
     * @return array
     */
    public function get_chart_legend()
    {

        if (empty($this->seller_ids)) {
            return [];
        }

        $legend = [];

        $query = [
            'query_type' => 'get_var',
            'filter_range' => true,
            'order_status' => ['completed', 'processing', 'on-hold', 'refunded', 'revision'],
        ];
        $total_sales = $this->get_order_report_data(array_merge($query, [
            'data' => [
                '_line_total' => [
                    'type' => 'order_item_meta',
                    'order_item_type' => 'line_item',
                    'function' => 'SUM',
                    'name' => 'order_item_amount',
                ],
            ],
        ]));

        $total_items = absint(
            $this->get_order_report_data(array_merge($query, [
                'data' => [
                    '_qty' => [
                        'type' => 'order_item_meta',
                        'order_item_type' => 'line_item',
                        'function' => 'SUM',
                        'name' => 'order_item_count',
                    ],
                ],
            ])));

        $legend[] = array(
            /* translators: %s: total items sold */
            'title' => sprintf(__('%s sales for the selected items', 'woocommerce'), '<strong>' . wc_price($total_sales) . '</strong>'),
            'color' => $this->chart_colours['sales_amount'],
            'highlight_series' => 1,
        );

        $legend[] = array(
            /* translators: %s: total items purchased */
            'title' => sprintf(__('%s purchases for the selected items', 'woocommerce'), '<strong>' . ($total_items) . '</strong>'),
            'color' => $this->chart_colours['item_count'],
            'highlight_series' => 0,
        );

        return $legend;
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

        $this->chart_colours = array(
            'sales_amount' => '#3498db',
            'item_count' => '#d4d9dc',
        );

        $current_range = !empty($_GET['range']) ? sanitize_text_field(wp_unslash($_GET['range'])) : '7day'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if (!in_array($current_range, array('custom', 'year', 'last_month', 'month', '7day'), true)) {
            $current_range = '7day';
        }

        $this->check_current_range_nonce($current_range);
        $this->calculate_current_range($current_range);

        include WC()->plugin_path() . '/includes/admin/views/html-report-by-date.php';
    }

    /**
     * Get chart widgets.
     *
     * @return array
     */
    public function get_chart_widgets()
    {

        $widgets = array();

        $widgets[] = array(
            'title' => '',
            'callback' => array($this, 'sellers_widget'),
        );

        if (!empty($this->seller_ids)) {
            $widgets[] = array(
                'title' => __('Showing reports for:', 'woocommerce'),
                'callback' => array($this, 'current_filters'),
            );
        }

        return $widgets;
    }

    /**
     * Output current filters.
     */
    public function current_filters()
    {

        $this->seller_ids_titles = array();

        foreach ($this->seller_ids as $seller_id) {

            $seller = get_user_by('ID', $seller_id);

            if ($seller) {
                $this->seller_ids_titles[] = $seller->display_name;
            } else {
                $this->seller_ids_titles[] = '#' . $seller_id;
            }
        }

        echo '<p><strong>' . implode('</strong></p><hr><p><strong>', $this->seller_ids_titles) . '</strong></p>';
        echo '<p><a class="button" href="' . esc_url(remove_query_arg('seller_id')) . '">' . esc_html__('Reset', 'woocommerce') . '</a></p>';
    }

    /**
     * Output sellers widget.
     */
    public function sellers_widget()
    {
        $sellers = get_users(['role__in' => ['vendedores_bw']]);
        ?>
        <h4 class="section_title open"><span>Pesquisar vendedor</span></h4>
        <div class="section">
            <form method="GET">
                <div>
                    <?php // @codingStandardsIgnoreStart
                    ?>
                    <select class="regular-text" style="width:203px;" id="seller_id" name="seller_id">
                        <?php foreach ($sellers as $seller): ?>
                            <option value="<?= $seller->ID ?>"><?= $seller->display_name ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="submit button"
                            value="<?php esc_attr_e('Show', 'woocommerce'); ?>"><?php esc_html_e('Show', 'woocommerce'); ?></button>
                    <input type="hidden" name="range"
                           value="<?php echo (!empty($_GET['range'])) ? esc_attr($_GET['range']) : ''; ?>"/>
                    <input type="hidden" name="start_date"
                           value="<?php echo (!empty($_GET['start_date'])) ? esc_attr($_GET['start_date']) : ''; ?>"/>
                    <input type="hidden" name="end_date"
                           value="<?php echo (!empty($_GET['end_date'])) ? esc_attr($_GET['end_date']) : ''; ?>"/>
                    <input type="hidden" name="page"
                           value="<?php echo (!empty($_GET['page'])) ? esc_attr($_GET['page']) : ''; ?>"/>
                    <input type="hidden" name="tab"
                           value="<?php echo (!empty($_GET['tab'])) ? esc_attr($_GET['tab']) : ''; ?>"/>
                    <input type="hidden" name="report"
                           value="<?php echo (!empty($_GET['report'])) ? esc_attr($_GET['report']) : ''; ?>"/>
                    <?php wp_nonce_field('custom_range', 'wc_reports_nonce', false); ?>
                    <?php // @codingStandardsIgnoreEnd
                    ?>
                </div>
            </form>
        </div>
        <?php
    }

    public function get_export_button()
    {

        $current_range = !empty($_GET['range']) ? sanitize_text_field(wp_unslash($_GET['range'])) : '7day'; //phpcs:ignore WordPress.Security.NonceVerification.Recommended
        ?>
        <a
                href="#"
                download="report-<?php echo esc_attr($current_range); ?>-<?php echo esc_html(date_i18n('Y-m-d', current_time('timestamp'))); ?>.csv"
                class="export_csv"
                data-export="chart"
                data-xaxes="<?php esc_attr_e('Date', 'woocommerce'); ?>"
                data-groupby="<?php echo $this->chart_groupby; ?>"<?php // @codingStandardsIgnoreLine
        ?>
        >
            <?php esc_html_e('Export CSV', 'woocommerce'); ?>
        </a>
        <?php
    }

    public function get_main_chart()
    {
        global $wp_locale;

        if (empty($this->seller_ids)) {
            ?>
            <div class="chart-container">
                <p class="chart-prompt"><?php esc_html_e('Choose a seller to view stats', 'woocommerce'); ?></p>
            </div>
            <?php
        } else {
            // Get orders and dates in range - we want the SUM of order totals, COUNT of order items, COUNT of orders, and the date.
            $order_item_counts = $this->get_order_report_data(
                array(
                    'data' => array(
                        '_qty' => array(
                            'type' => 'order_item_meta',
                            'order_item_type' => 'line_item',
                            'function' => 'SUM',
                            'name' => 'order_item_count',
                        ),
                        'post_date' => array(
                            'type' => 'post_data',
                            'function' => '',
                            'name' => 'post_date',
                        ),
                        'bw_id_vendedor' => array(
                            'type' => 'order_item_meta',
                            'order_item_type' => 'line_item',
                            'function' => '',
                            'name' => 'seller_id',
                        ),
                    ),
                    'where_meta' => array(
                        'relation' => 'OR',
                        array(
                            'type' => 'order_item_meta',
                            'meta_key' => 'bw_id_vendedor', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                            'meta_value' => $this->seller_ids, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                            'operator' => 'IN',
                        ),
                    ),
                    'group_by' => 'seller_id,' . $this->group_by_query,
                    'order_by' => 'post_date ASC',
                    'query_type' => 'get_results',
                    'filter_range' => true,
                    'order_status' => array('completed', 'processing', 'on-hold', 'refunded'),
                )
            );

            $order_item_amounts = $this->get_order_report_data(
                array(
                    'data' => array(
                        '_line_total' => array(
                            'type' => 'order_item_meta',
                            'order_item_type' => 'line_item',
                            'function' => 'SUM',
                            'name' => 'order_item_amount',
                        ),
                        'post_date' => array(
                            'type' => 'post_data',
                            'function' => '',
                            'name' => 'post_date',
                        ),
                        '_seller_id' => array(
                            'type' => 'order_item_meta',
                            'order_item_type' => 'line_item',
                            'function' => '',
                            'name' => 'seller_id',
                        ),
                    ),
                    'where_meta' => array(
                        'relation' => 'OR',
                        array(
                            'type' => 'order_item_meta',
                            'meta_key' => array('_seller_id', '_variation_id'), // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                            'meta_value' => $this->seller_ids, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
                            'operator' => 'IN',
                        ),
                    ),
                    'group_by' => 'seller_id, ' . $this->group_by_query,
                    'order_by' => 'post_date ASC',
                    'query_type' => 'get_results',
                    'filter_range' => true,
                    'order_status' => array('completed', 'processing', 'on-hold', 'refunded'),
                )
            );

            // Prepare data for report.
            $order_item_counts = $this->prepare_chart_data($order_item_counts, 'post_date', 'order_item_count', $this->chart_interval, $this->start_date, $this->chart_groupby);
            $order_item_amounts = $this->prepare_chart_data($order_item_amounts, 'post_date', 'order_item_amount', $this->chart_interval, $this->start_date, $this->chart_groupby);

            // Encode in json format.
            $chart_data = wp_json_encode(
                array(
                    'order_item_counts' => array_values($order_item_counts),
                    'order_item_amounts' => array_values($order_item_amounts),
                )
            );
            ?>
            <div class="chart-container">
                <div class="chart-placeholder main"></div>
            </div>
            <?php // @codingStandardsIgnoreStart ?>
            <script type="text/javascript">
                var main_chart;

                jQuery(function () {
                    var order_data = JSON.parse(decodeURIComponent('<?php echo rawurlencode($chart_data); ?>'));

                    var drawGraph = function (highlight) {

                        var series = [
                            {
                                label: "<?php echo esc_js(__('Number of items sold', 'woocommerce')) ?>",
                                data: order_data.order_item_counts,
                                color: '<?php echo $this->chart_colours['item_count']; ?>',
                                bars: {
                                    fillColor: '<?php echo $this->chart_colours['item_count']; ?>',
                                    fill: true,
                                    show: true,
                                    lineWidth: 0,
                                    barWidth: <?php echo $this->barwidth; ?> *
                                    0.5,
                                    align: 'center'
                                },
                                shadowSize: 0,
                                hoverable: false
                            },
                            {
                                label: "<?php echo esc_js(__('Sales amount', 'woocommerce')) ?>",
                                data: order_data.order_item_amounts,
                                yaxis: 2,
                                color: '<?php echo $this->chart_colours['sales_amount']; ?>',
                                points: {show: true, radius: 5, lineWidth: 3, fillColor: '#fff', fill: true},
                                lines: {show: true, lineWidth: 4, fill: false},
                                shadowSize: 0,
                                <?php echo $this->get_currency_tooltip(); ?>
                            }
                        ];

                        if (highlight !== 'undefined' && series[highlight]) {
                            highlight_series = series[highlight];

                            highlight_series.color = '#9c5d90';

                            if (highlight_series.bars)
                                highlight_series.bars.fillColor = '#9c5d90';

                            if (highlight_series.lines) {
                                highlight_series.lines.lineWidth = 5;
                            }
                        }

                        main_chart = jQuery.plot(
                            jQuery('.chart-placeholder.main'),
                            series,
                            {
                                legend: {
                                    show: false
                                },
                                grid: {
                                    color: '#aaa',
                                    borderColor: 'transparent',
                                    borderWidth: 0,
                                    hoverable: true
                                },
                                xaxes: [{
                                    color: '#aaa',
                                    position: "bottom",
                                    tickColor: 'transparent',
                                    mode: "time",
                                    timeformat: "<?php echo ('day' === $this->chart_groupby) ? '%d %b' : '%b'; ?>",
                                    monthNames: JSON.parse(decodeURIComponent('<?php echo rawurlencode(wp_json_encode(array_values($wp_locale->month_abbrev))); ?>')),
                                    tickLength: 1,
                                    minTickSize: [1, "<?php echo $this->chart_groupby; ?>"],
                                    font: {
                                        color: "#aaa"
                                    }
                                }],
                                yaxes: [
                                    {
                                        min: 0,
                                        minTickSize: 1,
                                        tickDecimals: 0,
                                        color: '#ecf0f1',
                                        font: {color: "#aaa"}
                                    },
                                    {
                                        position: "right",
                                        min: 0,
                                        tickDecimals: 2,
                                        alignTicksWithAxis: 1,
                                        color: 'transparent',
                                        font: {color: "#aaa"}
                                    }
                                ],
                            }
                        );

                        jQuery('.chart-placeholder').resize();
                    }

                    drawGraph();

                    jQuery('.highlight_series').hover(
                        function () {
                            drawGraph(jQuery(this).data('series'));
                        },
                        function () {
                            drawGraph();
                        }
                    );
                });
            </script>
            <?php
            // @codingStandardsIgnoreEnd
        }
    }
}
