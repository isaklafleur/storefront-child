<?php
/*
 * Plugin Name: MLMSoft coupon generator
 * @var $mlmSoftCouponGenerator
 */

require_once(plugin_dir_path(__FILE__) . '/core/MlmSoftCouponGeneratorOptions.php');
require_once(plugin_dir_path(__FILE__) . '/core/MlmSoftCouponGenerator.php');

$mlmSoftCouponGenerator = new MlmSoftCouponGeneratorOptions();

add_action('admin_post_generate', '_handle_form_action'); // If the user is logged in
function _handle_form_action()
{
    $pluginPrefix = MlmSoftCouponGeneratorOptions::PLUGIN_PREFIX;
    $mlmSoftCouponGenerator = new MlmSoftCouponGeneratorOptions();
    $mlmSoft = new MlmSoft();
    if (!empty($_POST[$pluginPrefix . 'users_for_generation'])
        && !empty($_POST[$pluginPrefix . 'amount_to_give'])
        &&!empty($_POST[$pluginPrefix . 'amount_to_deduct']))
    {
        $walletTypeId = $mlmSoftCouponGenerator->options['wallet_type_id']['value'];
        $operationTypeId = $mlmSoftCouponGenerator->options['operation_type_id']['value'];
        $amountToGive = (float)$_POST[$pluginPrefix . 'amount_to_give'];
        $amountToDeduct = -(float)$_POST[$pluginPrefix . 'amount_to_deduct'];
        $userEmails = $_POST[$pluginPrefix . 'users_for_generation'];
        $userEmails = explode(',', $userEmails);
        foreach ($userEmails as $key => $userEmail) {
            $userEmails[$key] = trim($userEmail);
        }
        $couponGenerator = new MlmSoftCouponGenerator();
        foreach ($userEmails as $userEmail) {
            $user = get_user_by_email($userEmail);
            if (!$user) {
                continue;
            }
            $walletOperationResult = $mlmSoft->addWalletOperation($user->ID, $amountToDeduct, $walletTypeId, $operationTypeId, 'Sachet sample');
            if ($walletOperationResult) {
                $couponGenerator->generateCoupon($user->user_email, $amountToGive, 10);
            }
        }
    }
    wp_redirect('/wp-admin/admin.php?page=mlmsoft_coupon_generator_settings');
    //wc_add_notice('awdawfaw');
}