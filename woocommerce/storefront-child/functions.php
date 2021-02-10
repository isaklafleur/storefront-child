<?php

// https://docs.woocommerce.com/document/introduction-to-hooks-actions-and-filters/
// https://woocommerce.github.io/code-reference/

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
// FILTERS
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////

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

/**
 * Show an invalid coupon as valid
 * 
 * @author Ratnakar Dubey <ratnakar.dubey@storeapps.org>
 *
 * @param boolean $is_valid The validity.
 * @param array $args Additional arguments.
 * @return boolean
 */

add_filter('wc_sc_show_as_valid', 'storeapps_wc_sc_show_as_valid', 100, 2);
function storeapps_wc_sc_show_as_valid($is_valid = false, $args = array())
{
    $coupon = (!empty($args['coupon_obj'])) ? $args['coupon_obj'] : null;
    if (is_object($coupon) && is_callable(array($coupon, 'is_valid')) && !$coupon->is_valid()) {
        return true;
    }
    return $is_valid;
}

/**
 * @snippet       Move Email Field To Top @ Checkout Page
 * @author        Rodolfo Melogli
 * @compatible    Woo 4.9
 * @source     https://www.businessbloomer.com/woocommerce-move-email-field-to-top-checkout/
 */

add_filter('woocommerce_billing_fields', 'bbloomer_move_checkout_email_field');

function bbloomer_move_checkout_email_field($address_fields)
{
    $address_fields['billing_email']['priority'] = 1;
    return $address_fields;
}

//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////
// ACTIONS
//////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////


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
 * @snippet       Add Product Volume field on product page
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 4.8
 * @source        https://www.cloudways.com/blog/add-custom-product-fields-woocommerce/ / https://www.ibenic.com/how-to-add-woocommerce-custom-product-fields / https://woocommerce.github.io/code-reference/files/woocommerce-includes-admin-wc-meta-box-functions.html / https://pluginrepublic.com/woocommerce-custom-fields/
 */

// Add WooCommerce Custom Field on Edit Product Page
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

// Add WooCommerce Custom Field on Display Product Page
add_action('woocommerce_single_product_summary', 'woocommerce_custom_fields_display');
function woocommerce_custom_fields_display()
{
    $user = wp_get_current_user();

    //Run code is the user is a Brand Partner
    if (in_array('brandpartner', (array) $user->roles)) {


        // Display user data
        // woocommerce_display_username();

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

/*
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
*/

/**
 * @snippet       ADD FIRST NAME, LAST NAME, MOBILE NUMBER TO MY ACCOUNT REGISTER FORM
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
        <label for="reg_billing_phone"><?php _e('Mobile phone (number must start with + following your country code, ex +46)', 'woocommerce'); ?><span class="required">*</span></label>
        <input type="tel" class="input-text" name="billing_phone" id="reg_billing_phone" pattern="\+\d{5,}" value="<?php if (!empty($_POST['billing_phone'])) esc_attr_e($_POST['billing_phone']); ?>" />
    </p>
    <p class="form-row form-row-first">
        <label for="reg_role"><?php _e('Customer, Affiliate or Brand Partner?', 'woocommerce'); ?><span class="required">*</span></label>
        <select class="input-text" name="role" id="reg_role">
            <option <?php if (!empty($_POST['role']) && $_POST['role'] == 'customer') esc_attr_e('selected'); ?> value="customer">Customer</option>
            <option <?php if (!empty($_POST['role']) && $_POST['role'] == 'affiliate') esc_attr_e('selected'); ?> value="affiliate">Affiliate</option>
            <option <?php if (!empty($_POST['role']) && $_POST['role'] == 'brandpartner') esc_attr_e('selected'); ?> value="brandpartner">Brand Partner</option>
        </select>
    </p>
    <p class="form-row form-row-last">
        <label for="reg_sponsorID"><?php _e('Sponsor ID (the ID of the person who referred you)', 'woocommerce'); ?><span class="required">*</span></label>
        <input type="text" class="input-text" name="sponsorID" id="reg_sponsorID" value="<?php if (!empty($_POST['sponsorID'])) esc_attr_e($_POST['sponsorID']); ?>" />
    </p>
    <div class="clear"></div>
<?php
}

// Validate extra fields
add_action('woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3);
function wooc_validate_extra_register_fields($username, $email, $validation_errors)
{
    if (isset($_POST['billing_first_name']) && empty($_POST['billing_first_name'])) {
        $validation_errors->add('billing_first_name_error', __('First name is required.', 'woocommerce'));
    }
    if (isset($_POST['billing_last_name']) && empty($_POST['billing_last_name'])) {

        $validation_errors->add('billing_last_name_error', __('Last name is required.', 'woocommerce'));
    }
    if (isset($_POST['billing_phone']) && empty($_POST['billing_phone'])) {

        $validation_errors->add('billing_phone_error', __('Mobile phone is required.', 'woocommerce'));
    }
    if (isset($_POST['role']) && empty($_POST['role'])) {
        $validation_errors->add('role_error', __('Role is required.', 'woocommerce'));
    }
    if (isset($_POST['sponsorID']) && empty($_POST['sponsorID'])) {

        $validation_errors->add('sponsorID_error', __('Sponsor ID is required.', 'woocommerce'));
    }
    return $validation_errors;
}

// Save extra fields
add_action('woocommerce_created_customer', 'wooc_save_extra_register_fields');
function wooc_save_extra_register_fields($customer_id)
{
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
    if (isset($_POST['billing_phone'])) {
        // Mobile phone input filed which is a CUSTOM The Untamed Field.
        update_user_meta($customer_id, 'phone', sanitize_text_field($_POST['billing_phone']));
        // Mobile phone input filed which is used in WooCommerce
        update_user_meta($customer_id, 'billing_phone', sanitize_text_field($_POST['billing_phone']));
    }
    if (isset($_POST['role'])) {
        if ($_POST['role'] == 'affiliate') {
            $user = new WP_User($customer_id);
            $user->set_role('affiliate');
        }
        if ($_POST['role'] == 'brandpartner') {
            $user = new WP_User($customer_id);
            $user->set_role('brandpartner');
        }
    }
    if (isset($_POST['sponsorID'])) {
        // Saves the sponsor id which is a CUSTOM The Untamed field.
        update_user_meta($customer_id, 'sponsorID', sanitize_text_field($_POST['sponsorID']));
        // Mobile phone input filed which is used in WooCommerce
    }
}

/**
 * @snippet       ADD MOBILE NUMBER FIELD TO MY ACCOUNT - EDIT FORM
 * @author        LoicTheAztec
 * @compatible    WooCommerce 4.9
 * @source        https://stackoverflow.com/questions/51103458/add-a-mobile-phone-field-on-my-account-edit-account-in-woocommerce
 */

// Display the mobile phone field
// add_action( 'woocommerce_edit_account_form_start', 'add_billing_mobile_phone_to_edit_account_form' ); // At start
/*
add_action('woocommerce_edit_account_form', 'add_phone_to_edit_account_form'); // After existing fields
function add_phone_to_edit_account_form()
{
    $user = wp_get_current_user();
?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="reg_phone"><?php _e('Mobile phone (number must start with + following your country code, ex +46)', 'woocommerce'); ?> <span class="required">*</span></label>
        <input type="tel" class="woocommerce-Input woocommerce-Input--phone input-text" name="phone" pattern="\+\d{5,}" id="reg_phone" value="<?php echo esc_attr($user->phone); ?>" />
    </p>
<?php
}
*/


// Check and validate the mobile phone & birthdate
add_action('woocommerce_save_account_details_errors', 'phone_birthdate_field_validation', 20, 1);
function phone_birthdate_field_validation($args)
{
    if (isset($_POST['phone']) && empty($_POST['phone'])) {
        $args->add('error', __('Please fill in your mobile phone', 'woocommerce'), '');
    }
    if (isset($_POST['birthdate']) && empty($_POST['birthdate'])) {
        $args->add('error', __('Please fill in your birthdate', 'woocommerce'), '');
    }
}

// Save the mobile phone and birthdate value to user data
add_action('woocommerce_save_account_details', 'my_account_saving_phone_birthdate', 20, 1);
function my_account_saving_phone_birthdate($user_id)
{
    if (isset($_POST['phone']) && !empty($_POST['phone'])) {
        update_user_meta($user_id, 'phone', sanitize_text_field($_POST['phone']));
    }
    if (isset($_POST['birthdate']) && !empty($_POST['birthdate'])) {
        update_user_meta($user_id, 'birthdate', sanitize_text_field($_POST['birthdate']));
    }
}
