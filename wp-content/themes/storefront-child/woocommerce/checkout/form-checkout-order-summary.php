<?php
if (!defined('ABSPATH')) {
    exit;
}

$showPV = check_pv_show();
?>

<div class="woocommerce-checkout-review-order-table-mobile">
    <input type="checkbox" id="switcher" class="section-switch">
    <label for="switcher">
        <span class="order-summary-text-show"><?php _e('Show')?></span>
        <span class="order-summary-text-hide"><?php _e('Hide')?></span>
        order summary (<?php wc_cart_totals_order_total_html(); ?>) <i class="arrow"></i></label>
    <div class="checkout-order-summary">
        <table class="shop_table woocommerce-checkout-review-order-table1">
            <thead>
            <tr>
                <th class="product-name"><?php esc_html_e('Product', 'woocommerce'); ?></th>
                <th class="product-total"><?php esc_html_e('Subtotal', 'woocommerce'); ?></th>
                <?php if ($showPV) { ?>
                    <th class="product-total-pv"><?php esc_html_e('PV', 'woocommerce'); ?></th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php
            do_action('woocommerce_review_order_before_cart_contents');

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);

                if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key)) {
                    ?>
                    <tr class="<?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                        <td class="product-name">
                            <?php echo wp_kses_post(apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key)) . '&nbsp;';
                            ?>
                            <?php echo apply_filters('woocommerce_checkout_cart_item_quantity', ' <strong class="product-quantity">' . sprintf('&times;&nbsp;%s', $cart_item['quantity']) . '</strong>', $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            ?>
                            <?php echo wc_get_formatted_cart_item_data($cart_item); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            ?>
                        </td>
                        <td class="product-total">
                            <?php echo apply_filters('woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal($_product, $cart_item['quantity']), $cart_item, $cart_item_key); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                            ?>
                        </td>
                        <?php if ($showPV) { ?>
                            <td class="product-total">
                                <?php
                                $pv = (int)$_product->get_meta('mlm_product_volume');
                                echo apply_filters('woocommerce_cart_item_subtotal_pv', $cart_item['quantity'] * $pv, $cart_item, $cart_item_key); // PHPCS: XSS ok.;
                                ?>
                            </td>
                        <?php } ?>
                    </tr>
                    <?php
                }
            }

            do_action('woocommerce_review_order_after_cart_contents');
            ?>
            </tbody>
        </table>
    </div>
</div>