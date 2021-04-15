<?php
/**
 * @var $customEnrollmentProcess CE_Process
 * @var $stepNum integer
 */

$userTypes = [
    'affiliate' => 'Affiliate',
    'brandpartner' => 'Brand partner'
];

$username = $customEnrollmentProcess->getStepPayload(1)['username'];

if (isset($_REQUEST['user-type'])) {
    $customEnrollmentProcess->setStepPayload(2, [
        'type' => $_REQUEST['user-type']
    ]);
    $plugin = CE_ProcessPlugin::getInstance();
    $customEnrollmentProcess->cart->clearCart();
    if ($_REQUEST['user-type'] == 'Brand partner') {
        $customEnrollmentProcess->cart->addToCart(CE_ProcessCart::BRAND_PARTNER_OPTION);
    } else {
        $customEnrollmentProcess->cart->addToCart(CE_ProcessCart::AFFILIATE_OPTION);
    }
    $customEnrollmentProcess->redirectToStep(CE_Process::PAGE_ENROLLMENT, 3);
}


get_header('shop'); ?>

<header>
    <h1 style="text-align: center;"><?php echo $username . ', select your account type' ?></h1>
</header>

<form method="POST">
    <?php foreach ($userTypes as $userType) { ?>
        <input type="submit" name="user-type" value="<?php echo $userType ?>">
    <?php } ?>
</form>