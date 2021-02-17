<?php
/* Template Name: Profile Page */

/**
 * The Template for displaying the User Profile of the Affiliate or Brand Partner
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     1.0.0
 */

/**
 * @var $MlmSoft MlmSoft
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
$inviteCode = get_query_var('referral_code');

$sponsorUser = get_users(array('meta_key' => 'invite_code', 'meta_value' => $inviteCode));
$sponsorData = [];
if (count($sponsorUser) == 0) {
    $sponsorData = $MlmSoft->get_sponsor_data($inviteCode);
    if (!empty($sponsorData)) {
        $sponsorData['rank'] = $MlmSoft->get_user_rank($sponsorData['account_id']);
    }
} else {
    /** @var WP_User $sponsorUser */
    $sponsorUser = $sponsorUser[0];
    $sponsorData = [
        'firstname' => $sponsorUser->first_name,
        'lastname' => $sponsorUser->last_name,
        'email' => $sponsorUser->user_email,
        'phone' => get_user_meta($sponsorUser->ID, 'phone', true),
        'user_url' => $sponsorUser->user_url,
        'about_me' => get_user_meta($sponsorUser->ID, 'description', true),
        'rank' => get_user_meta($sponsorUser->ID, 'mlm_brandpartner_rank', true)
    ];
}

if (!empty($sponsorData)) {

    echo '<div style="text-align: center">Sponsor Info</div>';
    echo '<p>First name: ' . $sponsorData['firstname'] . '</p>';
    echo '<p>Last name: ' . $sponsorData['lastname'] . '</p>';
    echo '<p>Email: ' . $sponsorData['email'] . '</p>';
    echo '<p>Phone: ' . $sponsorData['phone'] . '</p>';
    echo '<p>User url: ' . $sponsorData['user_url'] . '</p>';
    echo '<p>About me: ' . $sponsorData['about_me'] . '</p>';
    echo '<p>Rank: ' . $sponsorData['rank'] . '</p>';
// This function uses currently the current users ID, need to change to use id of userBySponsorId.
// Get current user id
    $user_id = get_current_user_id();

// Get attachment id
    $attachment_id = get_user_meta($user_id, 'image', true);
// True
    if ($attachment_id) {
        // $original_image_url = wp_get_attachment_url($attachment_id);

        // Display Image instead of URL
        ?>
        <?php echo wp_get_attachment_image($attachment_id, $size = 'thumbnail'); ?>

        <?php
    }
} else {
    echo 'User not found';
}
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