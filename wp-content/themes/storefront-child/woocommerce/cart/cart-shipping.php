<?php
/**
 * Shipping Methods Display
 *
 * In 2.1 we show methods per package. This allows for multiple methods per order if so desired.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-shipping.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.6.0
 */

defined('ABSPATH') || exit;

$formatted_destination = isset($formatted_destination) ? $formatted_destination : WC()->countries->get_formatted_address($package['destination'], ', ');
$has_calculated_shipping = !empty($has_calculated_shipping);
$show_shipping_calculator = !empty($show_shipping_calculator);
$calculator_text = '';
?>
<tr class="woocommerce-shipping-totals shipping">
    <th><?php echo wp_kses_post($package_name); ?></th>
    <td data-title="<?php echo esc_attr($package_name); ?>">
        <?php if ($available_methods) : ?>
            <?php
            $pickupPointMethods = [];

            /** @var WC_Shipping_Rate $method */
            foreach ($available_methods as $i => $method) {
                if (strpos($method->get_method_id(), 'local_pickup') === 0) {
                    $pickupPointMethods[] = $method;
                    unset($available_methods[$i]);
                }
            }
            ?>
            <ul id="shipping_method" class="woocommerce-shipping-methods">
                <?php foreach ($available_methods as $method) : ?>
                    <li>
                        <div>
                            <?php
                            if (1 < (count($available_methods) + count($pickupPointMethods))) {
                                printf('<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', $index, esc_attr(sanitize_title($method->id)), esc_attr($method->id), checked($method->id, $chosen_method, false)); // WPCS: XSS ok.
                            } else {
                                printf('<input type="hidden" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" />', $index, esc_attr(sanitize_title($method->id)), esc_attr($method->id)); // WPCS: XSS ok.
                            }
                            printf('<label for="shipping_method_%1$s_%2$s">%3$s</label>', $index, esc_attr(sanitize_title($method->id)), wc_cart_totals_shipping_method_label($method)); // WPCS: XSS ok.
                            do_action('woocommerce_after_shipping_rate', $method, $index);
                            ?>
                        </div>
                    </li>
                <?php endforeach; ?>
                <?php
                $pupSelected = false;
                /** @var WC_Shipping_Rate $method */
                foreach ($pickupPointMethods as $method) {
                    if (checked($method->id, $chosen_method, false)) {
                        $pupSelected = true;
                        break;
                    }
                }
                ?>
                <?php if (count($pickupPointMethods)) { ?>
                    <li>
                        <div>
                            <?php
                            $method = $pickupPointMethods[0];
                            printf('<input type="radio" id="shipping_method_pup" data-index="%1$d" class="shipping_method" %2$s />', $index, $pupSelected ? 'checked' : '');
                            printf('<label for="shipping_method_pup">PUP</label>');
                            ?>
                        </div>
                        <?php

                        printf('<div class="pup-select"><select id="shipping_method_pup_select" class="select" style="display: %1$s;">', $pupSelected ? 'block' : 'none');
                        foreach ($pickupPointMethods as $method) {
                            printf('<option name="shipping_method[%1$d]" data-index="%1$d" data-method="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s>%5$s</option>', $index, esc_attr(sanitize_title($method->id)), esc_attr($method->id), __checked_selected_helper($method->id, $chosen_method, false, 'selected'), wc_cart_totals_shipping_method_label($method)); // WPCS: XSS ok.
                        }
                        echo '</select></div>';
                        foreach ($pickupPointMethods as $method) {
                            echo "<li style='display: none;'>";
                            printf('<input type="radio" name="shipping_method[%1$d]" data-index="%1$d" id="shipping_method_%1$d_%2$s" value="%3$s" class="shipping_method" %4$s />', $index, esc_attr(sanitize_title($method->id)), esc_attr($method->id), checked($method->id, $chosen_method, false)); // WPCS: XSS ok.
                            echo "</li>";
                        }
                        ?>
                    </li>
                <?php } ?>
            </ul>
            <script>
                (() => {
                    let pupList = document.querySelector('#shipping_method_pup_select');
                    let selectMethod = () => {
                        let option = pupList.options[pupList.selectedIndex]
                        if (option.dataset.method) {
                            let pupRadio = document.querySelector('#' + option.dataset.method)
                            if (pupRadio) {
                                pupRadio.click()
                            }
                        }
                    }
                    let pupRadio = document.querySelector('#shipping_method_pup');
                    pupRadio.addEventListener('change' , selectMethod)
                    pupList.addEventListener('change' , selectMethod)
                })()
            </script>
        <?php if (is_cart()) : ?>
            <p class="woocommerce-shipping-destination">
                <?php
                if ($formatted_destination) {
                    // Translators: $s shipping destination.
                    printf(esc_html__('Shipping to %s.', 'woocommerce') . ' ', '<strong>' . esc_html($formatted_destination) . '</strong>');
                    $calculator_text = esc_html__('Change address', 'woocommerce');
                } else {
                    echo wp_kses_post(apply_filters('woocommerce_shipping_estimate_html', __('Shipping options will be updated during checkout.', 'woocommerce')));
                }
                ?>
            </p>
        <?php endif; ?>
        <?php
        elseif (!$has_calculated_shipping || !$formatted_destination) :
            if (is_cart() && 'no' === get_option('woocommerce_enable_shipping_calc')) {
                echo wp_kses_post(apply_filters('woocommerce_shipping_not_enabled_on_cart_html', __('Shipping costs are calculated during checkout.', 'woocommerce')));
            } else {
                echo wp_kses_post(apply_filters('woocommerce_shipping_may_be_available_html', __('Enter your address to view shipping options.', 'woocommerce')));
            }
        elseif (!is_cart()) :
            echo wp_kses_post(apply_filters('woocommerce_no_shipping_available_html', __('There are no shipping options available. Please ensure that your address has been entered correctly, or contact us if you need any help.', 'woocommerce')));
        else :
            // Translators: $s shipping destination.
            echo wp_kses_post(apply_filters('woocommerce_cart_no_shipping_available_html', sprintf(esc_html__('No shipping options were found for %s.', 'woocommerce') . ' ', '<strong>' . esc_html($formatted_destination) . '</strong>')));
            $calculator_text = esc_html__('Enter a different address', 'woocommerce');
        endif;
        ?>

        <?php if ($show_package_details) : ?>
            <?php echo '<p class="woocommerce-shipping-contents"><small>' . esc_html($package_details) . '</small></p>'; ?>
        <?php endif; ?>

        <?php if ($show_shipping_calculator) : ?>
            <?php woocommerce_shipping_calculator($calculator_text); ?>
        <?php endif; ?>
    </td>
</tr>
