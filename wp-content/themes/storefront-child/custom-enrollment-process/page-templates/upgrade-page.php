<?php
/**
 * @var $customEnrollmentProcess CE_Process
 * @var $stepNum integer
 */

$customEnrollmentProcess->cart->clearCart();

if (isset($_REQUEST['user-type'])) {
    $customEnrollmentProcess->setStepPayload(1, [
        'type' => $_REQUEST['user-type'],
        'operation' => 'upgrade'
    ]);
    $plugin = CE_ProcessPlugin::getInstance();
    if ($_REQUEST['user-type'] == 'Brand partner') {
        $customEnrollmentProcess->cart->addToCart(CE_ProcessCart::BRAND_PARTNER_OPTION);
    } else {
        $customEnrollmentProcess->cart->addToCart(CE_ProcessCart::AFFILIATE_OPTION);
    }
    $customEnrollmentProcess->redirectToStep(2);
}
get_header('shop'); ?>

<header>
    <h1 style="text-align: center;">Registration</h1>
</header>

<form method="POST">
    <input type="submit" name="user-type" value="Brand partner">
    <input type="submit" name="user-type" value="Affiliate">
</form>
