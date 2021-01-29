<?php

// https://docs.woocommerce.com/document/introduction-to-hooks-actions-and-filters/
// https://woocommerce.github.io/code-reference/

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
// FILTERS
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

/**
 * @snippet        Change Product Tab Titles and Headings @ WooCommerce Checkout
 * @author         Misha Rudrastyh
 * @compatible     WC 4.9
 * @source         https://rudrastyh.com/woocommerce/rename-product-tabs-and-heading.html
 */

add_filter('woocommerce_product_tabs', 'rename_reviews_tab');
function rename_reviews_tab($tabs)
{
    global $product;
    $tabs['reviews']['title'] = 'Reviews & Questions (' . $product->get_review_count() . ') ';
    return $tabs;
}

/**
 * @snippet        Remove Order Notes @ WooCommerce Checkout
 * @author         Rodolfo Melogli
 * @compatible     WC 4.9
 * @source         https://www.businessbloomer.com/woocommerce-remove-order-notes-checkout-page/
 */

add_filter('woocommerce_enable_order_notes_field', '__return_false');

/**
 * @snippet       If a store owner wants to force the country display under order page
 * @author        Ron Rennick
 * @compatible    WooCommerce 4.8
 * @source        https://github.com/woocommerce/woocommerce/issues/22158#issuecomment-447852936
 */

add_filter('woocommerce_formatted_address_force_country_display', '__return_true');

/**
 * @snippet       Remove Built with Storefront & Woocommerce Footer Link
 * @author        JOE NJENGA
 * @compatible    WooCommerce 4.8
 * @source        https://njengah.com/remove-built-with-storefront-woocommerce/
 */

/**
 * @snippet       Hide ALL shipping rates in ALL zones when Free Shipping is available
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 4.8
 * @source        https://www.businessbloomer.com/woocommerce-hide-shipping-options-free-shipping-available/
 */

add_filter('woocommerce_package_rates', 'bbloomer_unset_shipping_when_free_is_available_all_zones', 10, 2);
function bbloomer_unset_shipping_when_free_is_available_all_zones($rates, $package)
{
    $all_free_rates = array();
    foreach ($rates as $rate_id => $rate) {
        if ('free_shipping' === $rate->method_id) {
            $all_free_rates[$rate_id] = $rate;
            break;
        }
    }

    if (empty($all_free_rates)) {
        return $rates;
    } else {
        return $all_free_rates;
    }
}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
// ACTIONS
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////


/**
 * @snippet        Check what user role the user has, if it has user role xxx do this.
 * @author         Isak Engdahl
 * @compatible     WC 4.9
 * @source         
 */

/*
 add_action('woocommerce_single_product_summary', 'woocommerce_check_user_role');
function woocommerce_check_user_role()
{
    $user = wp_get_current_user();
    if (in_array('brandpartner', (array) $user->roles)) {
        //The user has the "author" role
        // Show Role
        // Show Subscriber Image
        echo "Welcome Brand Partner";
    } else {
        echo "You are not welcome, because you are not a brand partner...";
    }
}
*/

add_action('wp', 'njenga_remove_storefront_credit');
function njenga_remove_storefront_credit()
{
    remove_action('storefront_footer', 'storefront_credit', 20);
}

/**
 * @snippet       Remove "Default Sorting" Dropdown @ StoreFront Shop & Archive Pages
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 4.8
 * @source        https://businessbloomer.com/bloomer-armada/
 */

add_action('wp', 'bbloomer_remove_default_sorting_storefront');
function bbloomer_remove_default_sorting_storefront()
{
    remove_action('woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 10);
    remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 10);
}

/**
 * @snippet       Remove “Showing all X results” from Shop and Product Archive Pages
 * @author        xxxx
 * @compatible    WooCommerce 4.8
 * @source    https://rudrastyh.com/woocommerce/remove-result-count.html
 */

add_action('wp', 'bbloomer_remove_result_count_storefront');
function bbloomer_remove_result_count_storefront()
{
    remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
    remove_action('woocommerce_after_shop_loop', 'woocommerce_result_count', 20);
}

/**
 * @snippet       Add Custom Product Fields
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 4.8
 * @source        https://www.cloudways.com/blog/add-custom-product-fields-woocommerce/ / https://www.ibenic.com/how-to-add-woocommerce-custom-product-fields / https://woocommerce.github.io/code-reference/files/woocommerce-includes-admin-wc-meta-box-functions.html / https://pluginrepublic.com/woocommerce-custom-fields/
 */

// Add WooCommerce Custom Fields on Edit Product Page
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');
function woocommerce_product_custom_fields()
{
    echo '<div class="options_group mlm_product_volume" style="background-color: #ffcccb;">';
?>
<?php
    $args = array(
        'id' => 'mlm_product_volume',
        'label' => __('Product volume', 'woocommerce-mlm'),
        'placeholder' => __('Enter product volume for the product', 'woocommerce-mlm'),
        'type' => 'number',
        'desc'              => __('Enter the the product volume that is related to the product.', 'woocommerce-mlm'),
        'desc_tip'          => true,
        'custom_attributes' => array(
            'step' => '1',
            'min' => '0'
        )
    );
    woocommerce_wp_text_input($args);
    echo '</div>';
}

// Save WooCommerce Custom Fields
add_action('woocommerce_process_product_meta', 'save_woocommerce_product_custom_fields');
function save_woocommerce_product_custom_fields($post_id)
{
    $mlm_product_volume_value = isset($_POST['mlm_product_volume']) ? $_POST['mlm_product_volume'] : '';
    $product = wc_get_product($post_id);
    $product->update_meta_data('mlm_product_volume', sanitize_text_field($mlm_product_volume_value));
    $product->save();
}

// Add WooCommerce Custom Fields on Display Product Page
add_action('woocommerce_single_product_summary', 'woocommerce_custom_fields_display');
function woocommerce_custom_fields_display()
{
    $user = wp_get_current_user();

    //Run code is the user is a Brand Partner
    if (in_array('brandpartner', (array) $user->roles)) {


        // Display user data
        woocommerce_display_username();

        global $post;
        $product = wc_get_product($post->ID);
        $mlm_product_volume_value = $product->get_meta('mlm_product_volume');

        if ($mlm_product_volume_value) {
            // Only display our field if we've got a value for the field title
            printf(
                '<p class="price">%s PV</p>',
                esc_html($mlm_product_volume_value)
            );
        }
    } else {
        echo "No PV, because your are not logged in as Brand Partner... :(";
        echo "<br/>";
    }
}

/**
 * @snippet       Display logged-in username IF logged-in
 * @author        Travis Pflanz
 * @compatible    WooCommerce 4.8
 * @source        https://wordpress.stackexchange.com/a/49688/200418
 */

// Display $current_user variable data
function woocommerce_display_username()
{
    global $current_user;
    wp_get_current_user();
    if (is_user_logged_in()) {
        //var_dump($current_user);
        echo 'Username: ' . $current_user->user_login . "\n";
        echo 'User display name: ' . $current_user->display_name . "\n";
    } else {
        wp_loginout();
    }
}

/**
 * @snippet       ADD FIRST NAME, LAST NAME, PHONE NUMBER TO MY ACCOUNT REGISTER FORM
 * @author        xxx
 * @compatible    WooCommerce 4.8
 * @source        xxx
 */

// Add extra fields to Registration form
add_action('woocommerce_register_form_start', 'wooc_extra_register_fields');
function wooc_extra_register_fields()
{ ?>
    <p class="form-row form-row-first">
        <label for="reg_billing_first_name"><?php _e('First name', 'woocommerce'); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if (!empty($_POST['billing_first_name'])) esc_attr_e($_POST['billing_first_name']); ?>" />
    </p>
    <p class="form-row form-row-last">
        <label for="reg_billing_last_name"><?php _e('Last name', 'woocommerce'); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if (!empty($_POST['billing_last_name'])) esc_attr_e($_POST['billing_last_name']); ?>" />
    </p>
    <p class="form-row form-row-wide">
        <label for="reg_billing_phone"><?php _e('Phone', 'woocommerce'); ?></label>
        <input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e($_POST['billing_phone']); ?>" />
    </p>
    <div class="clear"></div>
<?php
}

// Validate extra fields
add_action('woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3);
function wooc_validate_extra_register_fields($username, $email, $validation_errors)
{
    if (isset($_POST['billing_first_name']) && empty($_POST['billing_first_name'])) {
        $validation_errors->add('billing_first_name_error', __('<strong>Error</strong>: First name is required!', 'woocommerce'));
    }
    if (isset($_POST['billing_last_name']) && empty($_POST['billing_last_name'])) {

        $validation_errors->add('billing_last_name_error', __('<strong>Error</strong>: Last name is required!.', 'woocommerce'));
    }
    return $validation_errors;
}

// Save extra fields
add_action('woocommerce_created_customer', 'wooc_save_extra_register_fields');
function wooc_save_extra_register_fields($customer_id)
{
    if (isset($_POST['billing_phone'])) {
        // Phone input filed which is used in WooCommerce
        update_user_meta($customer_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
    }
    if (isset($_POST['billing_first_name'])) {
        //First name field which is by default
        update_user_meta($customer_id, 'first_name', sanitize_text_field($_POST['billing_first_name']));
        // First name field which is used in WooCommerce
        update_user_meta($customer_id, 'billing_first_name', sanitize_text_field($_POST['billing_first_name']));
    }
    if (isset($_POST['billing_last_name'])) {
        // Last name field which is by default
        update_user_meta($customer_id, 'last_name', sanitize_text_field($_POST['billing_last_name']));
        // Last name field which is used in WooCommerce
        update_user_meta($customer_id, 'billing_last_name', sanitize_text_field($_POST['billing_last_name']));
    }
}
