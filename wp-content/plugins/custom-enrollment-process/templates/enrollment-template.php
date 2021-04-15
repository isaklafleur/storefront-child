<?php
/**
 * @var $customEnrollmentProcess CE_Process
 */


$enrollId = get_query_var('enroll_id');
$stepNum = $customEnrollmentProcess->getStepNum($enrollId);
$isUpgrade = false;

if ($stepNum) {
    $sessionPayload = $customEnrollmentProcess->getSessionPayload();
    $isUpgrade = isset($sessionPayload['upgrade']) && $sessionPayload['upgrade'];
}

$user = wp_get_current_user();
if ($user->ID && !$isUpgrade) {
    $customEnrollmentProcess->clearEnrollmentSession();
    wp_redirect('/' . CE_Process::PAGE_UPGRADE);
    exit;
}

if ($enrollId == 'affiliate' || $enrollId == 'brandpartner') {
    $customEnrollmentProcess->createNewEnrollSession(4);
    $customEnrollmentProcess->setStepPayload(1, [
        'type' => $enrollId == 'affiliate' ? 'Affiliate' : $enrollId
    ]);
    $plugin = CE_ProcessPlugin::getInstance();
    if ($enrollId == 'brandpartner') {
        $customEnrollmentProcess->cart->addToCart(CE_ProcessCart::BRAND_PARTNER_OPTION);
    } else {
        $customEnrollmentProcess->cart->addToCart(CE_ProcessCart::AFFILIATE_OPTION);
    }
    $customEnrollmentProcess->redirectToStep(CE_Process::PAGE_ENROLLMENT,2);
}

if (!$stepNum) {
    $customEnrollmentProcess->createNewEnrollSession(4);
    $customEnrollmentProcess->redirectToStep(CE_Process::PAGE_ENROLLMENT,1);
} else {
    $templatePath = plugin_dir_path(__FILE__) . '../pages/enrollment-page-' . $stepNum . '.php';

    $templatePathFilter = apply_filters('custom_enrollment_process_template_page', $templatePath, $stepNum);

    if (!empty($templatePathFilter) && !file_exists($templatePathFilter)) {
        wc_add_notice('Template page for custom enrollment process not found', 'error');
    } else {
        $templatePath = $templatePathFilter;
    }

    require_once($templatePath);
}