<?php defined('ABSPATH') || exit; ?>

<h2>
    <?php
    /* translators: %s: Order ID. */
    echo wp_kses_post(
        sprintf(
            __(
                '(Order #%s)', 'lmfwc') . ' (<time datetime="%s">%s</time>)',
                $order->get_order_number(),
                $order->get_date_created()->format('c'),
                wc_format_datetime($order->get_date_created()
            )
        )
    );
    ?>
</h2>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
            <tr>
                <th class="td" scope="col" style="text-align: left;"><?php esc_html_e('Product', 'lmfwc'); ?></th>
                <th class="td" scope="col" style="text-align: left;"><?php esc_html_e('Quantity', 'lmfwc'); ?></th>
                <th class="td" scope="col" style="text-align: left;"><?php esc_html_e('Price', 'lmfwc'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            echo wc_get_email_order_items(
                $order,
                array(
                    'show_sku'      => false,
                    'show_image'    => false,
                    'image_size'    => array(32, 32),
                    'plain_text'    => $plain_text,
                    'sent_to_admin' => false,
                )
            );
            ?>
        </tbody>
        <tfoot>
            <?php
            $totals = $order->get_order_item_totals();

            if ($totals) {
                $i = 0;
                foreach ($totals as $total) {
                    $i++;
                    ?>
                    <tr>
                        <th class="td" scope="row" colspan="2" style="text-align: left; <?php echo ($i === 1) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post($total['label']); ?></th>
                        <td class="td" style="text-align: left; <?php echo ($i === 1) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post($total['value']); ?></td>
                    </tr>
                    <?php
                }
            }

            if ($order->get_customer_note()) {
                ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align: left;"><?php esc_html_e('Note', 'lmfwc'); ?>></th>
                    <td class="td" style="text-align: left;"><?php echo wp_kses_post(wptexturize($order->get_customer_note())); ?></td>
                </tr>
                <?php
            }
            ?>
        </tfoot>
    </table>
</div>