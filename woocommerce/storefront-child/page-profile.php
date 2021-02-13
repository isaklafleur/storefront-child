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
$user = get_user_by('id', 1); // this id is the sponsor, the one that send the referal link. So we must store referal code in Wordpress database so we can look it up and get the data from Wordpress instead of MLMSoft.

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

// echo WC()->countries->countries['AU']; //To get country name by code

$current_role = $user->roles[0];
$all_roles = $wp_roles->roles;
foreach ($all_roles as $role_key => $role_details) {
    if ($role_key == $current_role) $current_role_name = $role_details['name'];
}

echo $user->first_name . ' ' . $user->last_name . ' is an ' . $current_role_name . '. He lives in ' . $user->billing_city . ' in ' . WC()->countries->countries[$user->billing_country];
echo get_wp_user_avatar($user->ID, 'medium');

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