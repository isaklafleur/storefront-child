<?php
/**
 * @var $enrollData CEData
 * @var $customEnrollmentProcess CE_Process
 * @var $stepNum integer
 */

$customEnrollmentProcess->clearEnrollmentSession();

get_header('shop'); ?>

<header>
    <h1 style="text-align: center;">Registration final</h1>
</header>

<form method="POST">
    <input type="submit" value="OK">
</form>
<?php
echo '<pre>';
echo print_r($customEnrollmentProcess->enrollmentData->getData());
echo '</pre>';
echo 'Registration success и все такое';
