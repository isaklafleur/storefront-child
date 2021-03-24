<?php
/**
 * @var $customEnrollmentProcess CE_Process
 * @var $stepNum integer
 */

if (isset($_REQUEST['next-step'])) {
    $customEnrollmentProcess->setStepPayload(1, []);
    $customEnrollmentProcess->redirectToStep(2);
}
get_header('shop'); ?>

<header>
    <h1 style="text-align: center;">Registration step 1</h1>
</header>

<form method="POST">
    <input type="submit" name="next-step" value="Next">
</form>
