<?php

// https://docs.woocommerce.com/document/introduction-to-hooks-actions-and-filters/
// https://woocommerce.github.io/code-reference/hooks/hooks.html


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
 * @snippet       Distraction-free Checkout
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 4.8
 * @source    https://businessbloomer.com/?p=111758
 */

add_action('wp', 'bbloomer_nodistraction_checkout');

function bbloomer_nodistraction_checkout()
{
    if (!is_checkout()) return;
    remove_action('storefront_header', 'storefront_social_icons', 10);
    remove_action('storefront_header', 'storefront_secondary_navigation', 30);
    remove_action('storefront_header', 'storefront_product_search', 40);
    remove_action('storefront_header', 'storefront_primary_navigation', 50);
    remove_action('storefront_header', 'storefront_header_cart', 60);
    remove_action('storefront_footer', 'storefront_footer_widgets', 10);
}

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
 * @snippet       Add Custom Product Fields
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 4.8
 * @source        https://www.cloudways.com/blog/add-custom-product-fields-woocommerce/
 */

// The code for displaying WooCommerce Product Custom Fields
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');
// Following code Saves  WooCommerce Product Custom Fields
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');

function woocommerce_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class=" product_custom_field ">';
    // This function has the logic of creating custom field
    //  This function includes input text field, Text area and number field
    echo '</div>';
}

/**
 * @snippet       MY ACCOUNT - EDIT ACCOUNT FORM - add custom field "favorite_color"
 * @author        xxx
 * @compatible    WooCommerce 4.8
 * @source        xxx
 */

// Add the custom field "favorite_color"
add_action('woocommerce_edit_account_form', 'add_favorite_color_to_edit_account_form');
function add_favorite_color_to_edit_account_form()
{
    $user = wp_get_current_user();
?>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="favorite_color"><?php _e('Favorite color', 'woocommerce'); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="favorite_color" id="favorite_color" value="<?php echo esc_attr($user->favorite_color); ?>" />
    </p>
<?php
}

// Save the custom field 'favorite_color' 
add_action('woocommerce_save_account_details', 'save_favorite_color_account_details', 12, 1);
function save_favorite_color_account_details($user_id)
{
    // For Favorite color
    if (isset($_POST['favorite_color']))
        update_user_meta($user_id, 'favorite_color', sanitize_text_field($_POST['favorite_color']));

    // For Billing email (added related to your comment)
    if (isset($_POST['account_email']))
        update_user_meta($user_id, 'billing_email', sanitize_text_field($_POST['account_email']));
}

/**
 * @snippet       ADD BIRTH DATE TO MY ACCOUNT EDIT PAGE
 * @author        xxx
 * @compatible    WooCommerce 4.8
 * @source        xxx
 */

add_action('woocommerce_edit_account_form', 'action_woocommerce_edit_account_form');
function action_woocommerce_edit_account_form()
{
    woocommerce_form_field('birthday_field', array(
        'type'        => 'date',
        'label'       => __('My Birth Date', 'woocommerce'),
        'placeholder' => __('Date of Birth', 'woocommerce'),
        'required'    => true,
    ), get_user_meta(get_current_user_id(), 'birthday_field', true));
}


// Validate Birth Date - my account
function action_woocommerce_save_account_details_errors($args)
{
    if (isset($_POST['birthday_field']) && empty($_POST['birthday_field'])) {
        $args->add('error', __('Please provide a birth date', 'woocommerce'));
    }
}
add_action('woocommerce_save_account_details_errors', 'action_woocommerce_save_account_details_errors', 10, 1);

// Save - my account
function action_woocommerce_save_account_details($user_id)
{
    if (isset($_POST['birthday_field']) && !empty($_POST['birthday_field'])) {
        update_user_meta($user_id, 'birthday_field', sanitize_text_field($_POST['birthday_field']));
    }
}
add_action('woocommerce_save_account_details', 'action_woocommerce_save_account_details', 10, 1);

/**
 * @snippet       GET USER LOCATION BY IP
 * @author        xxx
 * @compatible    WooCommerce 4.8
 * @source        xxx
 */

add_shortcode('geoip_country', 'get_user_geo_country');
function get_user_geo_country()
{
    $geo      = new WC_Geolocation(); // Get WC_Geolocation instance object
    $user_ip  = $geo->get_ip_address(); // Get user IP
    $user_geo = $geo->geolocate_ip($user_ip); // Get geolocated user data.
    $country  = $user_geo['country']; // Get the country code
    $state   = isset($user_geodata['state']) ? $user_geodata['state'] : ''; // Get current user GeoIP State
    return sprintf('<p>' . __('We ship to %s', 'woocommerce') . '</p>', WC()->countries->countries[$country]);
}

/**
 * @snippet       WooCommerce User Countery Name Shortcode
 * @author        xxx
 * @compatible    WooCommerce 4.8
 * @source        xxx
 */

add_shortcode('wc_geo_country_name', 'wcct_custom_get_user_geo_country_name');
function wcct_custom_get_user_geo_country_name()
{
    $geo      = new WC_Geolocation(); // Get WC_Geolocation instance object
    $user_ip  = $geo->get_ip_address(); // Get user IP
    $user_geo = $geo->geolocate_ip($user_ip); // Get geolocated user data.
    $country  = $user_geo['country']; // Get the country code

    return WC()->countries->countries[$country];
}

/**
 * @snippet       ADD LOGIN & LOGOUT LINK TO PRIMARY MENU
 * @author        xxx
 * @compatible    WooCommerce 4.8
 * @source        xxx
 */

add_filter('wp_nav_menu_items', 'add_loginout_link', 10, 2);
function add_loginout_link($items, $args)
{
    if (is_user_logged_in() && $args->theme_location == 'primary') {
        $items .= '<li><a href="' . wp_logout_url(get_permalink(wc_get_page_id('myaccount'))) . '">Log Out</a></li>';
    } elseif (!is_user_logged_in() && $args->theme_location == 'primary') {
        $items .= '<li><a href="' . get_permalink(wc_get_page_id('myaccount')) . '">Log In</a></li>';
    }
    return $items;
}

/**
 * @snippet       ADD FIRST NAME, LAST NAME, PHONE NUMBER TO MY ACCOUNT REGISTER FORM
 * @author        xxx
 * @compatible    WooCommerce 4.8
 * @source        xxx
 */

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
add_action('woocommerce_register_form_start', 'wooc_extra_register_fields');

// register fields Validating
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
add_action('woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3);

// Below code save extra fields.
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
add_action('woocommerce_created_customer', 'wooc_save_extra_register_fields');
