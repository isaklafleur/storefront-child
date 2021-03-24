<?php
/**
 * @var $customEnrollmentProcess CE_Process
 * @var $stepNum integer
 * @var $isUpgrade boolean
 */

$customEnrollmentProcess->cart->clearCart();

$userTypes = [
    'affiliate' => 'Affiliate',
    'brandpartner' => 'Brand partner'
];

if ($isUpgrade) {
    $user = wp_get_current_user();
    $roles = $user->roles;
    if (in_array('affiliate', $roles)) {
        unset($userTypes['affiliate']);
    }
    if (in_array('brandpartner', $roles)) {
        $userTypes = [];
    }
}

if (isset($_REQUEST['user-type'])) {
    $customEnrollmentProcess->setStepPayload(1, [
        'type' => $_REQUEST['user-type']
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
    <h1 style="text-align: center;"><?php echo($isUpgrade ? 'Upgrade' : 'Registration') ?></h1>
</header>

<?php if (empty($userTypes)) { ?>
    You have maximum level. No upgrades available
<?php } else { ?>
    <form method="POST">
        <?php foreach ($userTypes as $userType) { ?>
            <input type="submit" name="user-type" value="<?php echo $userType ?>">
        <?php } ?>
    </form>
<?php } ?>