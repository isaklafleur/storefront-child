<?php
/**
 * @var $enrollData CEData
 * @var $customEnrollmentProcess CE_Process
 * @var $stepNum integer
 */

if (isset($_REQUEST['text4'])) {
    $customEnrollmentProcess->setStepPayload(4, [
        'text' => $_REQUEST['text4']
    ]);
    $customEnrollmentProcess->redirectToStep(5);
}

get_header('shop');
?>

<!--<header>
    <h1 style="text-align: center;">Registration 4</h1>
</header>

<form method="POST">
    <input name="text4" type="text"/>
    <input type="submit">
</form>-->
