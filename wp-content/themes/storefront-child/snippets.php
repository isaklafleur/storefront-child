<?php


/* Remove Categories from Single Products */
remove_action(
    'woocommerce_single_product_summary',
    'woocommerce_template_single_meta',
    40
);

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
 * @snippet       Removing company name from WooCommerce checkout
 * @author        James Thomas
 * @compatible    Woo 4.9
 * @source        https://squareinternet.co/removing-company-name-from-woocommerce-checkout/#:~:text=hooks%20and%20filters.-,To%20remove%20the%20company%20name%20field%20from%20the%20WooCommerce%20checkout,field%20from%20the%20array%20returned.

 */


add_filter('woocommerce_checkout_fields', 'remove_company_name');

function remove_company_name($fields)
{
    unset($fields['billing']['billing_company']);
    return $fields;
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

/**
 * @snippet        Check what user role the user has, if it has user role xxx do this.
 * @author         Isak Engdahl
 * @compatible     WC 4.9
 * @source         
 */

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
 * @snippet MY ACCOUNT - EDIT ACCOUNT FORM - add custom field "favorite_color"
 * @author LoicTheAztec
 * @compatible WooCommerce 4.8
 * @source https://stackoverflow.com/questions/47604357/adding-an-additional-custom-field-in-woocommerce-edit-account-page
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

// Add Birth Date field - my account
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


// Validate Birth Date field - my account
add_action('woocommerce_save_account_details_errors', 'action_woocommerce_save_account_details_errors', 10, 1);
function action_woocommerce_save_account_details_errors($args)
{
    if (isset($_POST['birthday_field']) && empty($_POST['birthday_field'])) {
        $args->add('error', __('Please provide a birth date', 'woocommerce'));
    }
}


// Save Birth Date field - my account
add_action('woocommerce_save_account_details', 'action_woocommerce_save_account_details', 10, 1);
function action_woocommerce_save_account_details($user_id)
{
    if (isset($_POST['birthday_field']) && !empty($_POST['birthday_field'])) {
        update_user_meta($user_id, 'birthday_field', sanitize_text_field($_POST['birthday_field']));
    }
}


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



// The code for displaying WooCommerce Product Custom Fields
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');
// Following code Saves WooCommerce Product Custom Fields
add_action('woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save');

function woocommerce_product_custom_fields()
{
    global $woocommerce, $post;
    echo '<div class=" product_custom_field ">';
    // This function has the logic of creating custom field
    // This function includes input text field, Text area and number field
    // Custom Product Text Field
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_text_field',
            'label' => __('My Text Field', 'woocommerce'),
            'placeholder' => 'Custom Product Text Field',
            'desc_tip' => 'true'
        )

    );
    // Custom Product Number Field
    woocommerce_wp_text_input(
        array(
            'id' => '_custom_product_number_field',
            'placeholder' => 'Custom Product Number Field',
            'label' => __('Custom Product Number Field', 'woocommerce'),
            'type' => 'number',
            'custom_attributes' => array(
                'step' => 'any',
                'min' => '0'
            )
        )
    );
    // Custom Product Textarea Field
    woocommerce_wp_textarea_input(
        array(
            'id' => '_custom_product_textarea',
            'placeholder' => 'Custom Product Textarea',
            'label' => __('Custom Product Textarea', 'woocommerce')
        )
    );
    echo '</div>';
}

function woocommerce_product_custom_fields_save($post_id)
{
    // Custom Product Text Field
    $woocommerce_custom_product_text_field = $_POST['_custom_product_text_field'];
    if (!empty($woocommerce_custom_product_text_field))
        update_post_meta($post_id, '_custom_product_text_field', esc_attr($woocommerce_custom_product_text_field));
    // Custom Product Number Field
    $woocommerce_custom_product_number_field = $_POST['_custom_product_number_field'];
    if (!empty($woocommerce_custom_product_number_field))
        update_post_meta($post_id, '_custom_product_number_field', esc_attr($woocommerce_custom_product_number_field));
    // Custom Product Textarea Field
    $woocommerce_custom_procut_textarea = $_POST['_custom_product_textarea'];
    if (!empty($woocommerce_custom_procut_textarea))
        update_post_meta($post_id, '_custom_product_textarea', esc_html($woocommerce_custom_procut_textarea));
}
