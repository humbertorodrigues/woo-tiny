<style>
@media print{
    .column-pre_sale,.column-in_wallet{
        display: none;
    }
}
</style>
<div id="poststuff" class="woocommerce-reports-wide">
    <div class="postbox">

        <?php if ('custom' === $current_range && isset($_GET['start_date'], $_GET['end_date'])) : ?>
            <h3 class="screen-reader-text">
                <?php
                /* translators: 1: start date 2: end date */
                printf(
                    esc_html__('From %1$s to %2$s', 'woocommerce'),
                    esc_html(wc_clean(wp_unslash($_GET['start_date']))),
                    esc_html(wc_clean(wp_unslash($_GET['end_date'])))
                );
                ?>
            </h3>
        <?php else : ?>
            <h3 class="screen-reader-text"><?php echo esc_html($ranges[$current_range]); ?></h3>
        <?php endif; ?>

        <div class="stats_range">
            <?php $this->get_export_button(); ?>
            <ul>
                <?php
                foreach ($ranges as $range => $name) {
                    echo '<li class="' . ($current_range == $range ? 'active' : '') . '"><a href="' . esc_url(remove_query_arg(array('start_date', 'end_date'), add_query_arg('range', $range))) . '">' . esc_html($name) . '</a></li>';
                }
                ?>
                <li class="custom <?php echo ('custom' === $current_range) ? 'active' : ''; ?>">
                    <?php esc_html_e('Custom:', 'woocommerce'); ?>
                    <form method="GET">
                        <div>
                            <?php
                            // Maintain query string.
                            foreach ($_GET as $key => $value) {
                                if (is_array($value)) {
                                    foreach ($value as $v) {
                                        echo '<input type="hidden" name="' . esc_attr(sanitize_text_field($key)) . '[]" value="' . esc_attr(sanitize_text_field($v)) . '" />';
                                    }
                                } else {
                                    echo '<input type="hidden" name="' . esc_attr(sanitize_text_field($key)) . '" value="' . esc_attr(sanitize_text_field($value)) . '" />';
                                }
                            }
                            ?>
                            <input type="hidden" name="range" value="custom"/>
                            <input type="text" size="11" placeholder="yyyy-mm-dd"
                                   value="<?php echo (!empty($_GET['start_date'])) ? esc_attr(wp_unslash($_GET['start_date'])) : ''; ?>"
                                   name="start_date" class="range_datepicker from"
                                   autocomplete="off"/><?php //@codingStandardsIgnoreLine ?>
                            <span>&ndash;</span>
                            <input type="text" size="11" placeholder="yyyy-mm-dd"
                                   value="<?php echo (!empty($_GET['end_date'])) ? esc_attr(wp_unslash($_GET['end_date'])) : ''; ?>"
                                   name="end_date" class="range_datepicker to"
                                   autocomplete="off"/><?php //@codingStandardsIgnoreLine ?>
                            <button type="submit" class="button"
                                    value="<?php esc_attr_e('Go', 'woocommerce'); ?>"><?php esc_html_e('Go', 'woocommerce'); ?></button>
                            <?php wp_nonce_field('custom_range', 'wc_reports_nonce', false); ?>
                        </div>
                    </form>
                </li>
            </ul>
        </div>
        <div style="width: 100%; display: flex">
            <div class="inside" style="width: 15%">
                <ul>
                    <li><strong>Total B2B: </strong> <?= wc_price($total_b2b) ?></li>
                    <li><strong>Total B2C: </strong> <?= wc_price($total_b2c) ?></li>
                    <li><strong>Total B2B+B2C: </strong> <?= wc_price($total_b2b_b2c) ?></li>
                </ul>
            </div>
            <div class="main" style="width: 85%; margin-right: .5rem">
                <?php $this->display(); ?>
            </div>
        </div>
    </div>
</div>