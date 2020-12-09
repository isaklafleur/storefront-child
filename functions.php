<?php

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

//////////////////////////////////////////////////////////////
// ADD FIRST NAME, LAST NAME, PHONE NUMBER TO REGISTER FORM //
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
//////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////
// Add field Birth Date - my account
function action_woocommerce_edit_account_form() {   
    woocommerce_form_field( 'birthday_field', array(
        'type'        => 'date',
        'label'       => __( 'My Birth Date', 'woocommerce' ),
        'placeholder' => __( 'Date of Birth', 'woocommerce' ),
        'required'    => true,
    ), get_user_meta( get_current_user_id(), 'birthday_field', true ));
}
add_action( 'woocommerce_edit_account_form', 'action_woocommerce_edit_account_form' );

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

/*
// Add field - admin
function add_user_birtday_field( $user ) {
    ?>
        <h3><?php _e('Birthday','woocommerce' ); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="birthday_field"><?php _e( 'Date of Birth', 'woocommerce' ); ?></label></th>
                <td><input type="date" name="birthday_field" value="<?php echo esc_attr( get_the_author_meta( 'birthday_field', $user->ID )); ?>" class="regular-text" /></td>
            </tr>
        </table>
        <br />
    <?php
}
add_action( 'show_user_profile', 'add_user_birtday_field', 10, 1 );
add_action( 'edit_user_profile', 'add_user_birtday_field', 10, 1 );


// Save field - admin
function save_user_birtday_field( $user_id ) {
    if( ! empty($_POST['birthday_field']) ) {
        update_user_meta( $user_id, 'birthday_field', sanitize_text_field( $_POST['birthday_field'] ) );
    }
}
add_action( 'personal_options_update', 'save_user_birtday_field', 10, 1 );
add_action( 'edit_user_profile_update', 'save_user_birtday_field', 10, 1 );
*/