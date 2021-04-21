<?php

/**
 * @var $customEnrollmentProcess CE_Process
 * @var $stepNum integer
 */

$customEnrollmentProcess->cart->clearCart();

if (isset($_REQUEST['enrollment-step-1'])) {
    $error = '';
    $email = sanitize_email($_REQUEST['email']);
    $username = sanitize_text_field($_REQUEST['username']);
    if (!is_email($email)) {
        $error = 'Email is not valid';
    } else if (empty($username)) {
        $error = 'Username is empty';
    }
    if ($error) {
        wc_add_notice($error, 'error');
    } else {
        $customEnrollmentProcess->setStepPayload(1, [
            'username' => $_REQUEST['username'],
            'email' => $_REQUEST['email']
        ]);

        $plugin = CE_ProcessPlugin::getInstance();

        if (!$plugin->db->userExists($_REQUEST['email'])) {
            $plugin->db->addUser($_REQUEST['email'], $_REQUEST['username'], 1);
        } else {
            $plugin->db->userUpdate($_REQUEST['email'], $_REQUEST['username'], 1);
        }

        $customEnrollmentProcess->redirectToStep(CE_Process::PAGE_ENROLLMENT, 2);
    }
}


get_header('shop'); ?>

<header>
    <h1 style="text-align: center;"><?php echo 'Registration' ?></h1>
</header>

<form method="POST">
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="enrollment_step_1_username"><?php esc_html_e('Your name', 'woocommerce'); ?>
            <span class="required">*</span>
        </label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="username" id="enrollment_step_1_username" autocomplete="username" value="<?php echo (!empty($_POST['username'])) ? esc_attr(wp_unslash($_POST['username'])) : ''; ?>" />
    </p>
    <p class="woocommerce-form-row woocommerce-form-row--wide form-row form-row-wide">
        <label for="enrollment_step_1_email"><?php esc_html_e('Your email', 'woocommerce'); ?>
            <span class="required">*</span>
        </label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="email" id="enrollment_step_1_email" autocomplete="email" value="<?php echo (!empty($_POST['email'])) ? esc_attr(wp_unslash($_POST['email'])) : ''; ?>" />
    </p>
    <div>
        <input type="submit" name="enrollment-step-1" value="Next">
    </div>
</form>