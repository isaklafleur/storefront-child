<?php

/**
 * @snippet       Distraction-free Checkout
 * @how-to        Get CustomizeWoo.com FREE
 * @sourcecode    https://businessbloomer.com/?p=111758
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 3.5.4
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */
 
add_action( 'wp', 'bbloomer_nodistraction_checkout' );
 
function bbloomer_nodistraction_checkout() {
   if ( ! is_checkout() ) return;
   remove_action( 'storefront_header', 'storefront_social_icons', 10 );
   remove_action( 'storefront_header', 'storefront_secondary_navigation', 30 );
   remove_action( 'storefront_header', 'storefront_product_search', 40 );
   remove_action( 'storefront_header', 'storefront_primary_navigation', 50 );
   remove_action( 'storefront_header', 'storefront_header_cart', 60 );
   remove_action( 'storefront_footer', 'storefront_footer_widgets', 10 );
}

//////////////
// Hide ALL shipping rates in ALL zones when Free Shipping is available
//////////////
  
add_filter('woocommerce_package_rates', 'bbloomer_unset_shipping_when_free_is_available_all_zones', 10, 2);
   
function bbloomer_unset_shipping_when_free_is_available_all_zones( $rates, $package ) {
$all_free_rates = array();
foreach ( $rates as $rate_id => $rate ) {
      if ( 'free_shipping' === $rate->method_id ) {
         $all_free_rates[ $rate_id ] = $rate;
         break;
      }
}
     
if ( empty( $all_free_rates )) {
        return $rates;
} else {
        return $all_free_rates;
}
}

//////////////
// Add Custom Product Fields
// https://www.cloudways.com/blog/add-custom-product-fields-woocommerce/
//////////////

// The code for displaying WooCommerce Product Custom Fields
add_action( 'woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields' ); 
// Following code Saves  WooCommerce Product Custom Fields
add_action( 'woocommerce_process_product_meta', 'woocommerce_product_custom_fields_save' );

function woocommerce_product_custom_fields () {
    global $woocommerce, $post;
    echo '<div class=" product_custom_field ">';
    // This function has the logic of creating custom field
    //  This function includes input text field, Text area and number field
    echo '</div>';
    }


//////////////
// MY ACCOUNT - EDIT ACCOUNT FORM
//////////////

// Add the custom field "favorite_color"
add_action( 'woocommerce_edit_account_form', 'add_favorite_color_to_edit_account_form' );
function add_favorite_color_to_edit_account_form() {
    $user = wp_get_current_user();
    ?>
        <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="favorite_color"><?php _e( 'Favorite color', 'woocommerce' ); ?></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="favorite_color" id="favorite_color" value="<?php echo esc_attr( $user->favorite_color ); ?>" />
    </p>
    <?php
}

// Save the custom field 'favorite_color' 
add_action( 'woocommerce_save_account_details', 'save_favorite_color_account_details', 12, 1 );
function save_favorite_color_account_details( $user_id ) {
    // For Favorite color
    if( isset( $_POST['favorite_color'] ) )
        update_user_meta( $user_id, 'favorite_color', sanitize_text_field( $_POST['favorite_color'] ) );

    // For Billing email (added related to your comment)
    if( isset( $_POST['account_email'] ) )
        update_user_meta( $user_id, 'billing_email', sanitize_text_field( $_POST['account_email'] ) );
}

//////////////
// ADD BIRTH DATE TO MY ACCOUNT EDIT PAGE
//////////////

add_action( 'woocommerce_edit_account_form', 'action_woocommerce_edit_account_form' );
function action_woocommerce_edit_account_form() {   
    woocommerce_form_field( 'birthday_field', array(
        'type'        => 'date',
        'label'       => __( 'My Birth Date', 'woocommerce' ),
        'placeholder' => __( 'Date of Birth', 'woocommerce' ),
        'required'    => true,
    ), get_user_meta( get_current_user_id(), 'birthday_field', true ));
}


// Validate Birth Date - my account
function action_woocommerce_save_account_details_errors( $args ){
    if ( isset($_POST['birthday_field']) && empty($_POST['birthday_field']) ) {
        $args->add( 'error', __( 'Please provide a birth date', 'woocommerce' ) );
    }
}
add_action( 'woocommerce_save_account_details_errors','action_woocommerce_save_account_details_errors', 10, 1 );

// Save - my account
function action_woocommerce_save_account_details( $user_id ) {  
    if( isset($_POST['birthday_field']) && ! empty($_POST['birthday_field']) ) {
        update_user_meta( $user_id, 'birthday_field', sanitize_text_field($_POST['birthday_field']) );
    }
}
add_action( 'woocommerce_save_account_details', 'action_woocommerce_save_account_details', 10, 1 );

//////////////
// GET USER LOCATION BY IP
//////////////

function get_user_geo_country(){
    $geo      = new WC_Geolocation(); // Get WC_Geolocation instance object
    $user_ip  = $geo->get_ip_address(); // Get user IP
    $user_geo = $geo->geolocate_ip( $user_ip ); // Get geolocated user data.
    $country  = $user_geo['country']; // Get the country code
    return sprintf( '<p>' . __('We ship to %s', 'woocommerce') . '</p>', WC()->countries->countries[ $country ] );
}
add_shortcode('geoip_country', 'get_user_geo_country');

//////////////
// ADD LOGIN & LOGOUT LINK TO PRIMARY MENU
//////////////

add_filter( 'wp_nav_menu_items', 'add_loginout_link', 10, 2 );
function add_loginout_link( $items, $args ) {
    if (is_user_logged_in() && $args->theme_location == 'primary') {
        $items .= '<li><a href="'. wp_logout_url( get_permalink(wc_get_page_id('myaccount'))) .'">Log Out</a></li>';
    }
    elseif (!is_user_logged_in() && $args->theme_location == 'primary') {
        $items .= '<li><a href="'. get_permalink( wc_get_page_id( 'myaccount' ) ) .'">Log In</a></li>';
    }
    return $items;
}

//////////////
// ADD FIRST NAME, LAST NAME, PHONE NUMBER TO MY ACCOUNT REGISTER FORM
//////////////

function wooc_extra_register_fields() {?>
    <p class="form-row form-row-first">
    <label for="reg_billing_first_name"><?php _e( 'First name', 'woocommerce' ); ?><span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_first_name" id="reg_billing_first_name" value="<?php if ( ! empty( $_POST['billing_first_name'] ) ) esc_attr_e( $_POST['billing_first_name'] ); ?>" />
    </p>
    <p class="form-row form-row-last">
    <label for="reg_billing_last_name"><?php _e( 'Last name', 'woocommerce' ); ?><span class="required">*</span></label>
    <input type="text" class="input-text" name="billing_last_name" id="reg_billing_last_name" value="<?php if ( ! empty( $_POST['billing_last_name'] ) ) esc_attr_e( $_POST['billing_last_name'] ); ?>" />
    </p>
    <p class="form-row form-row-wide">
    <label for="reg_billing_phone"><?php _e( 'Phone', 'woocommerce' ); ?></label>
    <input type="text" class="input-text" name="billing_phone" id="reg_billing_phone" value="<?php esc_attr_e( $_POST['billing_phone'] ); ?>" />
    </p>
    <div class="clear"></div>
    <?php
}
add_action( 'woocommerce_register_form_start', 'wooc_extra_register_fields' );

/**
* register fields Validating.
*/

function wooc_validate_extra_register_fields( $username, $email, $validation_errors ) {
    if ( isset( $_POST['billing_first_name'] ) && empty( $_POST['billing_first_name'] ) ) {
           $validation_errors->add( 'billing_first_name_error', __( '<strong>Error</strong>: First name is required!', 'woocommerce' ) );
    }
    if ( isset( $_POST['billing_last_name'] ) && empty( $_POST['billing_last_name'] ) ) {

           $validation_errors->add( 'billing_last_name_error', __( '<strong>Error</strong>: Last name is required!.', 'woocommerce' ) );
    }
       return $validation_errors;
}
add_action( 'woocommerce_register_post', 'wooc_validate_extra_register_fields', 10, 3 );

/**
* Below code save extra fields.
*/
function wooc_save_extra_register_fields( $customer_id ) {
    if ( isset( $_POST['billing_phone'] ) ) {
                 // Phone input filed which is used in WooCommerce
                 update_user_meta( $customer_id, 'billing_phone', sanitize_text_field( $_POST['billing_phone'] ) );
          }
      if ( isset( $_POST['billing_first_name'] ) ) {
             //First name field which is by default
             update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
             // First name field which is used in WooCommerce
             update_user_meta( $customer_id, 'billing_first_name', sanitize_text_field( $_POST['billing_first_name'] ) );
      }
      if ( isset( $_POST['billing_last_name'] ) ) {
             // Last name field which is by default
             update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
             // Last name field which is used in WooCommerce
             update_user_meta( $customer_id, 'billing_last_name', sanitize_text_field( $_POST['billing_last_name'] ) );
      }

}
add_action( 'woocommerce_created_customer', 'wooc_save_extra_register_fields' );