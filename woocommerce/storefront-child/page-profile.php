<?php
/* Template Name: Profile Page */

/**
 * The Template for displaying the User Profile of the Affiliate or Brand Partner
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.0.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

get_header('shop'); ?>

<header>
    <h1 style="text-align: center;">Profile</h1>
</header>

<?php
// INSTRUCTIONS:
// It should take the ID of the sponsor who sent the referal URL to the prospect.
// We must store referal code in Wordpress database and this code should not be able to be changed
// So we can look it up and get the data from Wordpress instead of MLMSoft.
// like lbrtybeauty.com/profile/[refidfromsponsor]
// script should find the sponsor based on this [refidfromsponsor] and display user data from sponsor
$userBySponsorId = get_user_by('id', 1);

/*
$user_id = 1;
$all_meta_for_user = array_map(function ($a) {
    return $a[0];
}, get_user_meta($user_id));
print_r($all_meta_for_user);
*/

/*
$user_id = 1;
$key = 'last_name';
$single = true;
$user_last = get_user_meta($user_id, $key, $single);
echo '<p>The ' . $key . ' value for user id ' . $user_id . ' is: ' . $user_last . '</p>';
*/

// Find user role of user and display name of user role.
$current_role = $userBySponsorId->roles[0];
$all_roles = $wp_roles->roles;
foreach ($all_roles as $role_key => $role_details) {
    if ($role_key == $current_role) $current_role_name = $role_details['name'];
}

// Print out details of sponsor based on the [refidfromsponsor]
echo $userBySponsorId->first_name . ' ' . $userBySponsorId->last_name . ' is an ' . $current_role_name . '. He lives in ' . $userBySponsorId->billing_city . ' in ' . WC()->countries->countries[$userBySponsorId->billing_country];

// This function uses currently the current users ID, need to change to use id of userBySponsorId.
display_image();
?>

<?php
/**
 * woocommerce_before_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */

do_action('woocommerce_before_main_content');
?>


<?php
/**
 * woocommerce_after_main_content hook.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');
?>

<?php
/**
 * woocommerce_sidebar hook.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action('woocommerce_sidebar');
?>

<?php
get_footer('shop');

/* Omit closing PHP tag at the end of PHP files to avoid "headers already sent" issues. */