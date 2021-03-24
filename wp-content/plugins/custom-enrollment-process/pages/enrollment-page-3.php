<?php
/**
 * @var $customEnrollmentProcess CE_Process
 * @var $stepNum integer
 */

if (isset($_REQUEST['final-step'])) {
    $customEnrollmentProcess->setStepPayload(3, []);
    $customEnrollmentProcess->clearEnrollmentSession();
    wp_redirect('/my-account');
}
get_header('shop'); ?>

<header>
    <h1 style="text-align: center;">Registration step 3</h1>
</header>

<form method="POST">
    <input type="submit" name="final-step" value="OK">
</form>
