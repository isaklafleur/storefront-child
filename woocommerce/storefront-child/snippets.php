<?php

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
