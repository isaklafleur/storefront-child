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
 * @snippet       Hide the coupon code field on the WooCommerce Cart page
 * @author        Komal Maru
 * @compatible    Woo 4.9
 * @source        https://www.tychesoftwares.com/how-to-hide-the-woocommerce-coupon-code-field/#:~:text=The%20store%20owner%20can%20disable,%3ESettings%2D%3EGeneral%20tab.

 */

add_filter('woocommerce_coupons_enabled', 'disable_coupon_field_on_cart');

function disable_coupon_field_on_cart($enabled)
{
    if (is_cart()) {
        $enabled = false;
    }
    return $enabled;
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

    //Run code if the user is a Brand Partner
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
 * @snippet       ADD FIRST NAME, LAST NAME, MOBILE NUMBER TO MY ACCOUNT REGISTER FORM
 * @author        xxx
 * @compatible    WooCommerce 4.8
 * @source        xxx
 */

// Add extra fields to Registration form
add_action('woocommerce_register_form_start', 'wooc_extra_register_fields');
function wooc_extra_register_fields()
{
    $location = WC_Geolocation::geolocate_ip();
    $country = $location['country']; // example "SE"
    $isRefUser = isset($_SESSION['referral_data']) && !empty($_SESSION['referral_data']) && !empty($_SESSION['referral_data']['id']);
    $sponsorId = '';
    if ($isRefUser) {
        $sponsorId = $_SESSION['referral_data']['id'];
    } elseif (!empty($_POST['mlmsoftsponsorid'])) {
        $sponsorId = $_POST['mlmsoftsponsorid'];
    }
    $rowClass = $isRefUser ? 'form-row-wide' : 'form-row-first';
?>
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
    <p class="form-row <?php echo $rowClass ?>">
        <label for="reg_role"><?php _e('Customer, Affiliate or Brand Partner?', 'woocommerce'); ?><span class="required">*</span></label>
        <select class="input-text" name="role" id="reg_role">
            <option <?php if (!empty($_POST['role']) && $_POST['role'] == 'customer') esc_attr_e('selected'); ?> value="customer">Customer</option>
            <option <?php if (!empty($_POST['role']) && $_POST['role'] == 'affiliate') esc_attr_e('selected'); ?> value="affiliate">Affiliate</option>
            <option <?php if (!empty($_POST['role']) && $_POST['role'] == 'brandpartner') esc_attr_e('selected'); ?> value="brandpartner">Brand Partner</option>
        </select>
    </p>
    <?php
    if ($isRefUser) { ?>
        <input type="hidden" class="input-text" name="mlmsoftsponsorid" id="reg_sponsorID" value="<?php echo $sponsorId ?>" />
    <?php
    } else { ?>
        <p class="form-row form-row-last">
            <label for="reg_sponsorID"><?php _e('Sponsor ID (the ID of the person who referred you)', 'woocommerce'); ?></label>
            <input type="number" class="input-text" name="mlmsoftsponsorid" id="reg_sponsorID" value="<?php echo $sponsorId ?>" />
        </p>
    <?php
    } ?>
    <input type="hidden" class="input-text" name="billing_country" id="billing_country" value="<?php echo $country ? $country : 'SE' ?>" />
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
    if (isset($_POST['mlmsoftsponsorid']) && empty($_POST['mlmsoftsponsorid'])) {
        $_REQUEST['mlmsoftsponsorid'] = '1';
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
    $user = get_userdata($customer_id);
    $user->display_name = $user->first_name . ' ' . $user->last_name;
    wp_update_user($user);
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

// Set "required" option to false in sponsor id field
add_action('woocommerce_checkout_fields', 'mlmsoft_woocommerce_checkout_fields', 20, 1);
function mlmsoft_woocommerce_checkout_fields($fields)
{
    if (isset($fields['account']['mlmsoftsponsorid'])) {
        $fields['account']['mlmsoftsponsorid']['required'] = false;
    }
    return $fields;
}

add_action('woocommerce_cart_totals_after_order_total', 'woocommerce_cart_totals_after_order_total_add_pv', 20, 1);
function woocommerce_cart_totals_after_order_total_add_pv($arg)
{
    if (!check_pv_show()) {
        return;
    }
    $totalPV = 0;
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
            $pv = $_product->get_meta('mlm_product_volume');
            $totalPV += ($pv ? $pv * $cart_item['quantity'] : 0);
        }
    }
?>
    <tr class="order-total">
        <th><?php esc_html_e('Total PV', 'woocommerce'); ?></th>
        <td data-title="<?php esc_attr_e('Total PV', 'woocommerce'); ?>"><b><?php echo $totalPV ?></b></td>
    </tr>
<?php
}

function wc_card_totals_order_total_pv_html()
{
    if (!check_pv_show()) {
        return;
    }
    $totalPV = 0;
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $_product   = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
            $pv = $_product->get_meta('mlm_product_volume');
            $totalPV += ($pv ? $pv * $cart_item['quantity'] : 0);
        }
    }
?>
    <b><?php echo $totalPV; ?></b>
    <?php
}

function check_pv_show()
{
    $user = wp_get_current_user();
    return in_array('brandpartner', $user->roles);
}

/**
 * @snippet       Add a profile picture (file upload) on My account > edit account in WooCommerce
 * @author        7uc1f3r
 * @compatible    WooCommerce 5.0
 * @source        https://stackoverflow.com/questions/62016183/add-a-profile-picture-file-upload-on-my-account-edit-account-in-woocommerce
 */


// Display Image on Edit account form.
add_action('woocommerce_edit_account_form_start', 'display_image');
function display_image()
{
    // Get current user id
    $user_id = get_current_user_id();

    // Get attachment id
    $attachment_id = get_user_meta($user_id, 'image', true);
    // True
    if ($attachment_id) {
        // $original_image_url = wp_get_attachment_url($attachment_id);

        // Display Image instead of URL
    ?>
        <div class="image-upload">
            <label for="file-input">
                <?php echo wp_get_attachment_image($attachment_id, $size = 'thumbnail'); ?>
                <p class="img__description">Click here to upload new profile photo. Dont forget to click on button Save changes below.</p>
            </label>
            <input id="file-input" type="file" name="image" accept="image/x-png,image/gif,image/jpeg">
        </div>
    <?php
    }
}


/**
 * @snippet       Add a profile picture (file upload) on My account > edit account in WooCommerce
 * @author        7uc1f3r
 * @compatible    WooCommerce 5.0
 * @source        https://stackoverflow.com/questions/62016183/add-a-profile-picture-file-upload-on-my-account-edit-account-in-woocommerce
 */

// Add image upload field in Edit account form
add_action('woocommerce_edit_account_form_start', 'action_woocommerce_edit_account_form_start');
function action_woocommerce_edit_account_form_start()
{
    // Get current user id
    $user_id = get_current_user_id();

    // Get attachment id
    $attachment_id = get_user_meta($user_id, 'image', true);
    if (!$attachment_id) {
    ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
            <label for="image"><?php esc_html_e('Profile Image', 'woocommerce'); ?>&nbsp;<span class="required">*</span></label>
            <input type="file" class="woocommerce-Input" name="image" accept="image/x-png,image/gif,image/jpeg">
        </p>
<?php
    }
}

// Validate image upload field
add_action('woocommerce_save_account_details_errors', 'action_woocommerce_save_account_details_errors', 10, 1);
function action_woocommerce_save_account_details_errors($args)
{
    if (isset($_POST['image']) && empty($_POST['image'])) {
        $args->add('image_error', __('Please provide a valid image', 'woocommerce'));
    }
}

// Save image upload field
add_action('woocommerce_save_account_details', 'action_woocommerce_save_account_details', 10, 1);
function action_woocommerce_save_account_details($user_id)
{
    if (isset($_FILES['image'])) {
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $attachment_id = media_handle_upload('image', 0);

        if (is_wp_error($attachment_id)) {
            update_user_meta($user_id, 'image', $_FILES['image'] . ": " . $attachment_id->get_error_message());
        } else {
            $oldAttachment_id = get_user_meta($user_id, 'image', true);
            // True
            if ($oldAttachment_id) {
                wp_delete_attachment($oldAttachment_id, $force_delete);
            }
            update_user_meta($user_id, 'image', $attachment_id);
        }
    }
}

// Add enctype to form to allow image upload
function action_woocommerce_edit_account_form_tag()
{
    echo 'enctype="multipart/form-data"';
}
add_action('woocommerce_edit_account_form_tag', 'action_woocommerce_edit_account_form_tag');
