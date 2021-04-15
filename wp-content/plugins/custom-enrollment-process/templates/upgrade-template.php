<?php
/**
 * @var $customEnrollmentProcess CE_Process
 */

$user = wp_get_current_user();
if (!$user->ID) {
    wp_redirect('/my-account');
    exit;
}

$enrollId = get_query_var('enroll_id');
$stepNum = $customEnrollmentProcess->getStepNum($enrollId);

if (!$stepNum) {
    $customEnrollmentProcess->createNewEnrollSession(4);
    $customEnrollmentProcess->setSessionPayload(['upgrade' => true]);
    $customEnrollmentProcess->redirectToStep(CE_Process::PAGE_UPGRADE, 1);
} else {
    $templatePath = plugin_dir_path(__FILE__) . '../pages/upgrade-page.php';

    $templatePathFilter = apply_filters('custom_enrollment_upgrade_template_page', $templatePath, $stepNum);

    if (!empty($templatePathFilter) && !file_exists($templatePathFilter)) {
        wc_add_notice('Template page for upgrade not found', 'error');
    } else {
        $templatePath = $templatePathFilter;
    }

    require_once($templatePath);
}