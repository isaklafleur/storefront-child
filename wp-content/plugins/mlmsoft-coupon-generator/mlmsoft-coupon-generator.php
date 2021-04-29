<?php
/*
 * Plugin Name: MLMSoft coupon generator
 * @var $mlmSoftCouponGenerator
 */

require_once(plugin_dir_path(__FILE__) . '/core/MlmSoftCouponGeneratorOptions.php');
require_once(plugin_dir_path(__FILE__) . '/core/MlmSoftCouponGenerator.php');

add_action('plugins_loaded', array('MlmSoftCouponGenerator', 'getInstance'));
add_action('admin_post_generate', '_handle_form_action'); // If the user is logged in
function _handle_form_action()
{
    $pluginPrefix = MlmSoftCouponGeneratorOptions::PLUGIN_PREFIX;
    $mlmSoftCouponGenerator = MlmSoftCouponGenerator::getInstance();
    $mlmSoft = new MlmSoft();
    if (!empty($_POST[$pluginPrefix . 'users_for_generation'])
        && !empty($_POST[$pluginPrefix . 'amount_to_give'])
        &&!empty($_POST[$pluginPrefix . 'amount_to_deduct']))
    {
        $walletTypeId = $mlmSoftCouponGenerator->options->get_option_value('wallet_type_id', 0);
        $operationTypeId = $mlmSoftCouponGenerator->options->get_option_value('operation_type_id', 0);
        $amountToGive = (float)$_POST[$pluginPrefix . 'amount_to_give'];
        $amountToDeduct = -(float)$_POST[$pluginPrefix . 'amount_to_deduct'];
        $userEmails = $_POST[$pluginPrefix . 'users_for_generation'];
        $userEmails = explode(',', $userEmails);
        foreach ($userEmails as $key => $userEmail) {
            $userEmails[$key] = trim($userEmail);
        }
        foreach ($userEmails as $userEmail) {
            $user = get_user_by('email', $userEmail);
            if (!$user) {
                continue;
            }
            $walletOperationResult = $mlmSoft->addWalletOperation($user->ID, $amountToDeduct, $walletTypeId, $operationTypeId, 'Sachet sample');
            if ($walletOperationResult) {
                $couponDescription = 'Store Credit can be used to purchase sachet sample products only';
                $mlmSoftCouponGenerator->generateCoupon($user->user_email, $amountToGive, 10, $couponDescription);
            }
        }
    }
    wp_redirect('/wp-admin/admin.php?page=mlmsoft_coupon_generator_settings');
}