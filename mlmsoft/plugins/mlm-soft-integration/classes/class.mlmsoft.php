<?php

class MlmSoft
{


    public $user;
    public $options = array();
    /**
     * @var MlmSoftApi
     */
    protected $apiClient;


    public function __construct()
    {

        $MlmSoftOptions = new MlmSoftOptions;
        $this->options = $MlmSoftOptions->init();
        $this->apiClient = new MlmSoftApi($this->options['mlm_soft_project_url']['value'], $this->options['api_token']['value']);

        if (substr($this->options['mlm_soft_project_url']['value'], -1, 1) == '/') {
            $this->options['mlm_soft_project_url']['value'] = substr($this->options['mlm_soft_project_url']['value'], 0, strlen($this->options['mlm_soft_project_url']['value']) - 1);
        }

        if (substr($this->options['online_office_url']['value'], -1, 1) == '/') {
            $this->options['online_office_url']['value'] = substr($this->options['online_office_url']['value'], 0, strlen($this->options['online_office_url']['value']) - 1);
        }

        /*  Setting up actions... */
        add_action('admin_init', array($this, 'init'));
        add_action('init', array($this, 'init_session'), 1);
        add_action('password_reset', array($this, 'password_reset'), 10, 2);

        if (isset($_REQUEST["standard_login"])) {
            setcookie("standard_login", "Y");
        }

        if (!isset($_REQUEST["standard_login"]) && !isset($_COOKIE["standard_login"])) {
            //error_log('standard_login: N');
            remove_filter('authenticate', 'wp_authenticate_username_password', 20, 3);
            remove_filter('authenticate', 'wp_authenticate_email_password', 20, 3);
            remove_filter('authenticate', 'wp_authenticate_spam_check', 99);
            add_filter('authenticate', array($this, 'auth'), 10, 3);
        } else {
            //error_log('standard_login: Y');
        }

        add_shortcode('mlmsoft_header', array($this, 'mlmsoft_header'));
        add_shortcode('mlmsoft_footer', array($this, 'mlmsoft_footer'));
        add_shortcode('mlmsoft_ref_field', array($this, 'mlmsoft_ref_field'));
        add_shortcode('mlmsoft_ref_fields', array($this, 'mlmsoft_ref_fields'));
        add_shortcode('mlmsoft_invite_code', array($this, 'mlmsoft_invite_code'));

        if ($this->options['pmpro_integration']['value']) {
            add_action('pmpro_added_order', array($this, 'pmpro_added_order'));
            add_action('pmpro_updated_order', array($this, 'pmpro_added_order'));
            add_action('pmpro_checkout_after_email', array($this, 'pmpro_checkout_after_email'));
            add_filter('pmpro_required_user_fields', array($this, 'pmpro_required_user_fields'));
            add_filter('pmpro_new_user', array($this, 'pmpro_new_user'), 10, 2);
        }

        if ($this->options['wc_integration']['value']) {
            add_action('woocommerce_order_status_completed', array($this, 'woocommerce_order_status_completed'));
            add_action('woocommerce_after_checkout_form', array($this, 'woocommerce_after_checkout_form'));
            add_action('woocommerce_checkout_update_order_meta', array($this, 'woocommerce_checkout_update_order_meta'));
            add_filter('woocommerce_checkout_get_value', array($this, 'woocommerce_checkout_get_value'), 10, 2);
            add_filter('woocommerce_checkout_fields', array($this, 'woocommerce_checkout_fields'), 10, 2);
            add_filter('woocommerce_registration_errors', array($this, 'woocommerce_registration_errors'), 10, 3);
            add_filter('woocommerce_new_customer_data', array($this, 'woocommerce_new_customer_data'), 10, 2);
            add_action('woocommerce_created_customer', array($this, 'woocommerce_created_customer'), 10, 3);
            add_action('woocommerce_save_account_details', array($this, 'woocommerce_save_account_details'), 10, 1);
            add_action('woocommerce_checkout_order_created', array($this, 'woocommerce_checkout_order_created'), 10, 1);
        }
    }


    /**
     * Init
     */
    public function init()
    {
        $this->user = wp_get_current_user();
    }


    /**
     * Auth function that process get data while login.
     */
    public function auth($user, $username, $password)
    {
        if ($user instanceof WP_User) {
            return $user;
        }

        if (empty($username) || empty($password)) {
            if (is_wp_error($user)) {
                return $user;
            }

            $error = new WP_Error();

            if (empty($username)) {
                $error->add('empty_username', __('<strong>ERROR</strong>: The username field is empty.'));
            }

            if (empty($password)) {
                $error->add('empty_password', __('<strong>ERROR</strong>: The password field is empty.'));
            }

            return $error;
        }

        if (!session_id()) {
            session_start();
        }

        $action = isset($_GET['action']) ? $_GET['action'] : '';


        if (!is_user_logged_in() && $action !== 'logout') {

            if (array_key_exists('redirect_to', $_REQUEST)) {
                $_SESSION['redirect_to'] = esc_url_raw($_REQUEST['redirect_to']);
            }
            $_SESSION['rememberme'] = true;

            $reqRes = new MlmSoftApiResponse($this->apiClient->execGet(
                '/api2/online-office/user/check-auth',
                array(
                    "login" => $username,
                    "type" => 'member',
                    "password" => $password
                )
            ));

            if ($this->verification($reqRes)) {

                $user = $this->set_current_user($reqRes->getPrimaryPayload()->auth);
                wp_set_password($password, $user->ID);

                return $user;
            } else {

                $errors = new WP_Error();
                $errors->add('error!', 'Invalid authorization', 'error');
                return $errors;
            }
        } else {
            $errors = new WP_Error();
            return $errors;
        }
    }


    /**
     * Set current user. Creates user if it doesn't exist, updates user meta fields.
     */
    public function set_current_user($apiAuthResp)
    {

        $users = get_users(array('meta_key' => 'account_id', 'meta_value' => $apiAuthResp->user->account->id));
        $user = isset($users[0]) ? $users[0] : null;

        if (!$user) {
            $userdata = array(
                'user_pass' => 'k9sdfk23asdf9s',
                'user_login' => $apiAuthResp->login,
                'first_name' => $this->getUserProfileField($apiAuthResp->user->profile, 'firstname'),
                'last_name' => $this->getUserProfileField($apiAuthResp->user->profile, 'lastname'),
                'user_email' => $this->getUserProfileField($apiAuthResp->user->profile, 'email'),
                'display_name' => $apiAuthResp->user->account->title,
                'nickname' => $apiAuthResp->user->account->title,
            );

            $user_id = wp_insert_user($userdata);
            $this->set_user_meta($user_id, $apiAuthResp->user->account->id);

            if (!is_wp_error($user_id)) {
                return get_userdata($user_id);
            } else {
                return $user_id;
            }
        } else {
            $userMeta = get_user_meta($user->ID);
            $billingAddress = [];
            $billingAddress[] = empty($userMeta['billing_address_1']) ? '' : $userMeta['billing_address_1'][0];
            $billingAddress[] = empty($userMeta['billing_address_2']) ? '' : $userMeta['billing_address_2'][0];
            $billingAddress = implode(' ', $billingAddress);
            $dataToUpdate = [
                'Mailing_address' => $billingAddress,
                'Postal/ZIP_code' => empty($userMeta['billing_postcode']) ? '' : $userMeta['billing_postcode'][0],
                'countryId' => empty($userMeta['billing_country']) ? '' : $userMeta['billing_country'][0],
                'firstname' => $user->first_name,
                'lastname' => $user->last_name,
                'email' => $user->user_email,
                'phone' => empty($userMeta['phone']) ? '' : $userMeta['phone'][0],
                'birth_date' => empty($userMeta['birthdate']) ? '' : $userMeta['birthdate'][0],
                'billing_city' => empty($userMeta['billing_city']) ? '' : $userMeta['billing_city'][0]
            ];
            $this->updateUserProfile($user->ID, $dataToUpdate);

            $this->set_user_meta($user->ID, $apiAuthResp->user->account->id);

            $this->updateUserRank($user->ID);

            //update_user_meta($user->data->ID, 'invite_code', $openid_fields['invite_code']);
            return $user;
        }
    }


    protected function set_user_meta($user_id, $account_id)
    {
        update_user_meta($user_id, 'account_id', $account_id);

        $inviteCodeResp = new MlmSoftApiResponse($this->apiClient->execGet(
            '/api2/online-office/account/get-invite-code',
            array('accountId' => $account_id)
        ));
        if ($inviteCodeResp->isPrimarySuccess()) {
            $invite_code = $inviteCodeResp->getPrimaryPayload()->inviteCode;
            if (!empty($invite_code)) {
                update_user_meta($user_id, 'invite_code', $invite_code);
            }
        }
    }


    /**
     * Get referral info
     */
    public function get_referral($refCode)
    {
        $referral = false;

        $reqRes = new MlmSoftApiResponse($this->apiClient->execGet(
            '/api2/online-office/account/search-by-invite',
            array(
                "inviteCode" => $refCode
            )
        ));

        if ($reqRes->isPrimarySuccess()) {
            $data = $reqRes->getPrimaryPayload();
            $referral['id'] = $data->account->id;
            $referral['invite_code'] = $data->inviteCode;
            $referral['data'] = json_encode($data);
            $referral['show_banner'] = isset($_GET['showbanner']);;
        }

        return $referral;
    }

    public function get_sponsor_data($inviteCode)
    {
        $referral = false;

        $reqRes = new MlmSoftApiResponse($this->apiClient->execGet(
            '/api2/online-office/account/search-by-invite',
            array(
                "inviteCode" => $inviteCode
            )
        ));

        if ($reqRes->isPrimarySuccess()) {
            $data = $reqRes->getPrimaryPayload();
            $userProfile = $data->account->users[0];
            $userProfileData = [];
            foreach ($userProfile->profile as $profileField) {
                $userProfileData[$profileField->field->alias] = $profileField->value;
            }
            $userProfileData['account_id'] = $data->account->id;
            $referral = $userProfileData;
        }

        return $referral;
    }

    public function get_property_values($accountId)
    {
        $reqRes = new MlmSoftApiResponse(($this->apiClient->execGet(
            '/api2/online-office/account/get-property-values',
            [
                'accountId' => $accountId
            ]
        )));
        if ($reqRes->isPrimarySuccess()) {
            return $reqRes->getPrimaryPayload()->list;
        }
        return [];
    }

    public function addWalletOperation($userId, $amount, $walletTypeId, $walletOperationTypeId, $comment = '')
    {
        $account_id = get_user_meta($userId, 'account_id', true);

        $params = array(
            'accountId' => $account_id,
            'amount' => $amount,
            'walletTypeId' => $walletTypeId,
            'walletOperationTypeId' => $walletOperationTypeId,
            'comment' => $comment
        );


        $response = new MlmSoftApiResponse($this->apiClient->execPost(
            '/api2/online-office/wallet/add-operation',
            $params
        ));
        return $response->isPrimarySuccess();
    }

    public function format_property_values($properties)
    {
        $result = [];
        foreach ($properties as $property) {
            $result[$property->alias] = [
                'title' => $property->title,
                'value' => $property->value,
                'id' => $property->id
            ];
        }
        return $result;
    }


    /**
     * Rest API verification
     *
     * @param MlmSoftApiResponse $resp
     * @return bool
     */
    public function verification($resp)
    {
        return $resp->isPrimarySuccess() && !empty($resp->getPrimaryPayload()->auth->login);
    }


    /**
     * Simply inits session
     */
    public function init_session()
    {

        $referral = false;
        $needRefresh = false;
        if ((isset($_GET['referral'])) && (!empty($_GET['referral']))) {
            $needRefresh = true;
            $referral = sanitize_text_field($_GET['referral']);
            setcookie('mlmreferral', $referral, time() + 730 * 24 * 60 * 60, "/", "." . $_SERVER['HTTP_HOST']);
        } elseif ((isset($_COOKIE['mlmreferral'])) && (!empty($_COOKIE['mlmreferral']))) {
            $referral = sanitize_text_field($_COOKIE['mlmreferral']);
        }
        if ($referral) {
            if (!session_id()) {
                session_start();
            }

            if ($needRefresh || !isset($_SESSION['referral_data'])) {
                $_SESSION['referral_data'] = $this->get_referral($referral);
            }
        }

        $user = wp_get_current_user();
        if (!in_array('administrator', (array)$user->roles)) {
            add_filter('show_admin_bar', '__return_false');
        }
    }


    public function password_reset($user, $new_pass)
    {
        $account_id = get_user_meta($user->ID, 'account_id', true);
        if (!$account_id || empty($user->user_login)) return;

        $reqRes = new MlmSoftApiResponse($this->apiClient->execGet(
            '/api2/online-office/user/exists',
            array(
                "login" => $user->user_login,
                "type" => 'member',
            )
        ));
        if (!$reqRes->isPrimarySuccess() || empty($reqRes->getPrimaryPayload()->auth->user->id)) {
            return;
        }
        $account_user_id = $reqRes->getPrimaryPayload()->auth->user->id;
        $this->apiClient->execPost(
            '/api2/online-office/user/set-password',
            array(
                "userId" => $account_user_id,
                "type" => 'member',
                "newPassword" => $new_pass
            )
        );
    }

    /**
     * Shortcode. Gets referral account field
     */
    public function mlmsoft_ref_field($atts)
    {
        if (empty($atts) || empty($atts['field'])) return;
        else return $this->mlmsoft_referral($atts['field']);
    }

    /**
     * Shortcode. Print enclosed text only if referral set
     */
    public function mlmsoft_ref_fields($atts, $content = null)
    {
        if (!$this->mlmsoft_referral('title')) return;
        else return do_shortcode($content);
    }

    /**
     * Shortcode. Print invite_code
     */
    public function mlmsoft_invite_code()
    {
        if (is_user_logged_in()) {
            $user = wp_get_current_user();
            return get_user_meta($user->ID, 'invite_code', true);
        }
    }

    /**
     * Shortcode. Gets header
     */
    public function mlmsoft_header($atts)
    {

        if (!is_user_logged_in()) {

            if ($this->options['automatic_affiliate_header']['value'] && isset($_SESSION['referral_data']) && $_SESSION['referral_data']['show_banner']) {
                $fullName = $this->mlmsoft_referral('fullName');
                $invite_code = $this->mlmsoft_referral('invite_code');

                if ($fullName) {
?>
                    <script>
                        function myFunction() {
                            var x = document.getElementById("referralbanner");
                            if (x.style.display === "none") {
                                x.style.display = "block";
                            } else {
                                x.style.display = "none";
                            }
                        }
                    </script>
                    <div id="referralbanner" style="background: #000000;">
                        <a href="/profile/<?php echo $invite_code ?>">You are shopping with <?php echo $fullName; ?></a>
                        <span style="margin-right:10px; float:right" onclick="myFunction()"><a href="#">Hide</a></span>


                    </div>


                <?php
                }
            }
        } else {

            if ($this->options['automatic_authorized_user_header']['value']) {
                $user = wp_get_current_user();
                ?>
                <div style="background: #000000; opacity: 0.5; width: 100%;  color:#ffffff; position: fixed; top:0px; z-index: 50; padding:20px;">
                    <?php echo $user->data->display_name; ?>
                    <span style="float:right"><a style="color:#ffffff;" href="/wp-login.php?action=logout">Logout</a></span>
                    <span style="margin-right:10px; float:right"><a style="color:#ffffff;" href="<?php echo $this->options['online_office_url']['value']; ?>">Online office</a></span>
                </div>
                <?php
            }
        }
    }


    /**
     * Shortcode. Gets footer
     */
    public function mlmsoft_footer($atts)
    {

        if (!is_user_logged_in()) {

            if ($this->options['automatic_affiliate_header']['value']) {

                $fullName = $this->mlmsoft_referral('fullName');
                $phone = $this->mlmsoft_referral('phone');
                $email = $this->mlmsoft_referral('email');

                if ($fullName) {
                ?>
                    <div style="background: #000000; opacity: 0.5; width: 100%;  color:#ffffff; position: fixed; bottom:0px; z-index: 50; padding:20px;">
                        Hi! I'm <?php echo $fullName; ?>, your shopping consultant. I'm happy to help, you can reach me
                        by: <a style="text-decoration:underline; color:#ffffff; font-weight:bold; " href=""><?php echo $phone; ?></a> or <a style="text-decoration:underline; color:#ffffff; font-weight:bold;" href="mailto:<?php echo $email; ?>"><?php echo $email; ?></a>
                    </div>
            <?php
                }
            }
        }
    }


    /**
     * Mlmsoft get ref data
     */
    public function mlmsoft_referral($field_name)
    {
        if (!session_id()) {
            session_start();
        }

        if ((isset($_SESSION['referral_data'])) && ($_SESSION['referral_data'])) {

            if ($field_name == 'invite_code' || $field_name == 'id') return $_SESSION['referral_data'][$field_name];

            $data = json_decode($_SESSION['referral_data']['data']);
            if (isset($data->account->$field_name)) return $data->account->$field_name;
            if (isset($data->account->users[0]->$field_name)) return $data->account->users[0]->$field_name;
            return $this->getUserProfileField($data->account->users[0]->profile, $field_name);
        }

        return false;
    }

    public function pmpro_added_order($morder)
    {
        if ($morder->status != 'success') return;

        $account_id = get_user_meta($morder->user_id, 'account_id', true);
        $prop_alias = $this->options['sale_property_alias']['value'];

        $params = array(
            'accountId' => $account_id,
            'pointsAmount' => $morder->total,
            'orderId' => (string)$morder->id,
            'volumePropertyAlias' => $prop_alias
        );

        $this->apiClient->execPost(
            '/api2/online-office/account/volume-change',
            $params
        );
    }


    public function pmpro_checkout_after_email()
    {
        $refId = $this->mlmsoft_referral('id');
        if ($refId) {
            ?>
            <input type="hidden" id="mlmsoftsponsorid" name="mlmsoftsponsorid" value="<?php echo $refId; ?>">
        <?php
        } else {
        ?>
            <div>
                <label for="mlmsoftsponsorid"><?php _e('Sponsor ID', 'mlmsoft'); ?></label>
                <input id="mlmsoftsponsorid" name="mlmsoftsponsorid" type="text" class="input pmpro_required" size="30" value="<?php echo isset($_REQUEST['mlmsoftsponsorid']) ? esc_attr($_REQUEST['mlmsoftsponsorid']) : '' ?>" />
            </div>
        <?php
        } ?>
        <script>
            jQuery('#username').parent().remove();
            jQuery('#bemail').parent().insertBefore(jQuery('#password').parent());
            jQuery('#bconfirmemail').parent().insertBefore(jQuery('#password').parent());
        </script>
        <?php
    }


    public function pmpro_required_user_fields($pmpro_required_user_fields)
    {
        unset($pmpro_required_user_fields['username']);
        $pmpro_required_user_fields['mlmsoftsponsorid'] = sanitize_text_field($_REQUEST['mlmsoftsponsorid']);
        return $pmpro_required_user_fields;
    }


    public function pmpro_new_user($user_id, $new_user_array)
    {

        $reqRes = new MlmSoftApiResponse($this->apiClient->execGet(
            '/api2/online-office/user/exists',
            array(
                "login" => $new_user_array['user_email'],
                "type" => 'member',
            )
        ));
        if (!$reqRes->isPrimarySuccess()) {
            $error = new WP_Error();
            $error->add('error!', 'Cannot check if user exists.', 'error');
            return $error;
        }
        if ($reqRes->getPrimaryPayload()->exist) {
            $error = new WP_Error();
            $error->add('error!', 'User with this email already exists.', 'error');
            return $error;
        }

        $reqRes = new MlmSoftApiResponse($this->apiClient->execPost(
            '/api2/online-office/user/create',
            array(
                "login" => $new_user_array['user_email'],
                "type" => 'member',
                "password" => $new_user_array['user_pass'],
                "sponsorId" => sanitize_text_field(stripslashes($_REQUEST['mlmsoftsponsorid'])),
                "confirmationUrl" => $this->options['online_office_url']['value'] . '?code_confirm={{code}}',
                "profile" => array(
                    "firstname" => $new_user_array['first_name'],
                    "lastname" => $new_user_array['last_name'],
                    "email" => $new_user_array['user_email'],
                    "phone" => sanitize_text_field(stripslashes($_REQUEST['bphone'])),
                    "countryId" => sanitize_text_field(stripslashes($_REQUEST['bcountry']))
                )
            )
        ));
        if (!$reqRes->isPrimarySuccess()) {
            $error = new WP_Error();
            $error->add('error!', $reqRes->getErrorCode() . ' ' . $reqRes->getErrorMessage(), 'error');
            return $error;
        }

        $new_user_array['user_login'] = $new_user_array['user_email'];
        $user_id = wp_insert_user($new_user_array);
        if (is_wp_error($user_id)) {
            return $user_id;
        }

        $update_user_array = array(
            'ID' => $user_id,
            'display_name' => $reqRes->getPrimaryPayload()->auth->user->account->title,
            'nickname' => $reqRes->getPrimaryPayload()->auth->user->account->title
        );
        wp_update_user($update_user_array);
        $this->set_user_meta($user_id, $reqRes->getPrimaryPayload()->auth->user->account->id);

        return $user_id;
    }


    protected function getUserProfileField($profileFields, $alias)
    {
        foreach ($profileFields as $data) {
            if ($data->field->alias == $alias) {
                return $data->value;
            }
        }
    }


    public function woocommerce_order_status_completed($orderId)
    {
        $order = wc_get_order($orderId);
        $userId = $order->get_user_id();
        if ($userId) {
            $account_id = get_user_meta($userId, 'account_id', true);
        } else {
            $account_id = get_post_meta($orderId, 'Referral account ID', true);
        }
        if (!$account_id) return;

        if (empty($this->options['product_volume_attr']['value'])) {
            $pointsAmount = $order->get_total();
        } else {
            $pointsAmount = 0;
        }

        if (!empty($this->options['product_volume_attr']['value']) || !empty($this->options['rank_property_alias']['value'])) {
            $orderItems = $order->get_items();
            $skuRankValues = $this->getSkuRankValues();
            foreach ($orderItems as $item_id => $item) {
                $product = $item->get_product();
                if (!empty($this->options['product_volume_attr']['value'])) {
                    $quantity = $item->get_quantity();
                    $value = $product->get_attribute($this->options['product_volume_attr']['value']);
                    if (!$value && $value !== '0') {
                        $value = $product->get_meta($this->options['product_volume_attr']['value']);
                    }
                    $pointsAmount += $quantity * $value;
                }
                if (!empty($this->options['rank_property_alias']['value']) && !empty($skuRankValues)) {
                    $sku = $product->get_sku();
                    if (isset($skuRankValues[$sku])) {
                        $this->apiClient->execPost(
                            '/api2/online-office/account/volume-change',
                            array(
                                'accountId' => $account_id,
                                'pointsAmount' => $skuRankValues[$sku],
                                'orderId' => (string)$orderId,
                                'volumePropertyAlias' => $this->options['rank_property_alias']['value']
                            )
                        );
                    }
                }
            }
        }

        $salePropAlias = $this->options['sale_property_alias']['value'];

        $params = array(
            'accountId' => $account_id,
            'pointsAmount' => $pointsAmount,
            'orderId' => (string)$orderId,
            'volumePropertyAlias' => $salePropAlias
        );

        $this->apiClient->execPost(
            '/api2/online-office/account/volume-change',
            $params
        );
    }

    private function getSkuRankValues()
    {
        $skuRankValues = array();
        if (!empty($this->options['rank_values']['value'])) {
            $rankValues = explode("\n", $this->options['rank_values']['value']);
            foreach ($rankValues as $rankValue) {
                $rankValue = explode("|", trim($rankValue));
                $skuRankValues[trim($rankValue[0])] = trim($rankValue[1]);
            }
        }
        return $skuRankValues;
    }

    public function woocommerce_checkout_update_order_meta($orderId)
    {
        $order = wc_get_order($orderId);
        if (!$order->get_user_id()) {
            update_post_meta($orderId, 'Referral account ID', $this->mlmsoft_referral('account_id'));
        }
    }

    public function woocommerce_after_checkout_form()
    {
        $refId = $this->mlmsoft_referral('id');
        if ($refId) {
        ?>
            <script>
                jQuery('#mlmsoftsponsorid_field').hide();
            </script>
<?php
        }
    }


    public function woocommerce_checkout_fields($fields)
    {
        $fields['account']['mlmsoftsponsorid'] = array(
            'type' => 'number',
            'label' => __('Sponsor ID', 'mlmsoft'),
            'required' => true,
            'placeholder' => esc_attr__('Sponsor ID', 'mlmsoft'),
        );
        return $fields;
    }

    public function woocommerce_checkout_get_value($null, $input)
    {
        if ($input == 'mlmsoftsponsorid') {
            $refId = $this->mlmsoft_referral('id');
            if ($refId) {
                return $refId;
            }
        }
    }

    public function woocommerce_registration_errors($errors, $username, $email)
    {
        $reqRes = new MlmSoftApiResponse($this->apiClient->execGet(
            '/api2/online-office/user/exists',
            array(
                "login" => $email,
                "type" => 'member',
            )
        ));
        if (!$reqRes->isPrimarySuccess()) {
            $errors->add('error!', 'Cannot check if user exists.', 'error');
            return $errors;
        }
        if ($reqRes->getPrimaryPayload()->exist) {
            $errors->add('error!', 'User with this email already exists.', 'error');
            return $errors;
        }

        $reqRes = new MlmSoftApiResponse($this->apiClient->execGet(
            '/api2/online-office/account/search',
            array(
                "accountId" => sanitize_text_field(stripslashes($_REQUEST['mlmsoftsponsorid'])),
            )
        ));
        if (!$reqRes->isPrimarySuccess()) {
            $errors->add('error!', 'Sponsor with this id doesn\'t exists.', 'error');
            return $errors;
        }

        return $errors;
    }

    public function woocommerce_new_customer_data($new_user_array)
    {
        $new_user_array['user_login'] = $new_user_array['user_email'];
        return $new_user_array;
    }

    public function woocommerce_created_customer($user_id, $new_user_array, $password_generated)
    {
        $reqRes = new MlmSoftApiResponse($this->apiClient->execPost(
            '/api2/online-office/user/create',
            array(
                "login" => $new_user_array['user_email'],
                "type" => 'member',
                "password" => $new_user_array['user_pass'],
                "sponsorId" => sanitize_text_field(stripslashes($_REQUEST['mlmsoftsponsorid'])),
                "confirmationUrl" => $this->options['online_office_url']['value'] . '?code_confirm={{code}}',
                "profile" => array(
                    "firstname" => sanitize_text_field(stripslashes($_REQUEST['billing_first_name'])),
                    "lastname" => sanitize_text_field(stripslashes($_REQUEST['billing_last_name'])),
                    "email" => $new_user_array['user_email'],
                    "phone" => sanitize_text_field(stripslashes($_REQUEST['billing_phone'])),
                    "countryId" => sanitize_text_field(stripslashes($_REQUEST['billing_country']))
                )
            )
        ));
        if ($reqRes->isPrimarySuccess()) {
            $this->set_user_meta($user_id, $reqRes->getPrimaryPayload()->auth->user->account->id);
        }
    }

    private function getMlmSoftUserId($wpUserId)
    {
        $accountId = get_user_meta($wpUserId, 'account_id');
        if (empty($accountId)) {
            return 0;
        }
        $accountId = (int)$accountId[0];
        $reqRes = new MlmSoftApiResponse($this->apiClient->execGet(
            '/api2/online-office/account/get-info',
            array(
                'ids' => [$accountId],
            )
        ));
        if (!$reqRes->isPrimarySuccess()) {
            return 0;
        }
        $accountInfo = $reqRes->getPrimaryPayload();
        return $accountInfo->list->$accountId->{'s.'}->user_id;
    }

    private function updateUserProfile($wpUserId, $profile, $userId = -1)
    {
        if ($userId <= 0) {
            $userId = $this->getMlmSoftUserId($wpUserId);
        }
        if (!$userId) {
            return false;
        }
        $reqRes = new MlmSoftApiResponse($this->apiClient->execPost(
            '/api2/online-office/user/profile-update',
            [
                'userId' => $userId,
                'profile' => $profile
            ]
        ));
        return $reqRes->isPrimarySuccess();
    }

    private function updateUserRank($userId)
    {
        $accountId = get_user_meta($userId, 'account_id', true);
        $rank = $this->get_user_rank($accountId);

        if ($rank) {
            update_user_meta($userId, 'mlm_brandpartner_rank', $rank);
        }
    }

    public function get_user_rank($accountId)
    {
        $propertyValues = $this->get_property_values($accountId);
        if (!empty($propertyValues)) {
            $propertyValues = $this->format_property_values($propertyValues);
            if (isset($propertyValues[$this->options['rank_property_alias']['value']])) {
                return $propertyValues[$this->options['rank_property_alias']['value']]['value'];
            }
        }
        return null;
    }

    public function woocommerce_save_account_details($wpUserId)
    {
        $userId = $this->getMlmSoftUserId($wpUserId);
        if (!$userId) {
            return;
        }
        $userMeta = get_user_meta($wpUserId);
        $billingAddress = [];
        $billingAddress[] = empty($userMeta['billing_address_1']) ? '' : $userMeta['billing_address_1'][0];
        $billingAddress[] = empty($userMeta['billing_address_2']) ? '' : $userMeta['billing_address_2'][0];
        $billingAddress = implode(' ', $billingAddress);
        $dataToUpdate = [
            'Mailing_address' => $billingAddress,
            'Postal/ZIP_code' => empty($userMeta['billing_postcode']) ? '' : $userMeta['billing_postcode'][0],
            'countryId' => empty($userMeta['billing_country']) ? '' : $userMeta['billing_country'][0],
            'firstname' => sanitize_text_field(stripslashes($_REQUEST['account_first_name'])),
            'lastname' => sanitize_text_field(stripslashes($_REQUEST['account_last_name'])),
            'email' => sanitize_text_field(stripslashes($_REQUEST['account_email'])),
            'phone' => sanitize_text_field(stripslashes($_REQUEST['phone'])),
            'birth_date' => sanitize_text_field(stripslashes($_REQUEST['birthdate'])),
            'billing_city' => empty($userMeta['billing_city']) ? '' : $userMeta['billing_city'][0]
        ];
        if (!$this->updateUserProfile($wpUserId, $dataToUpdate, $userId)) {
            wc_add_notice('Error updating user profile', 'error');
            return;
        }
        if (!empty($_REQUEST['password_1'])) {
            $reqRes = new MlmSoftApiResponse($this->apiClient->execPost(
                '/api2/online-office/user/set-password',
                [
                    'userId' => $userId,
                    'newPassword' => $_REQUEST['password_1'],
                    'type' => 'member'
                ]
            ));
            if (!$reqRes->isPrimarySuccess()) {
                wc_add_notice('Error updating user password', 'error');
            }
        }
    }

    public function woocommerce_checkout_order_created($order)
    {
        $billingData = $order->data['billing'];
        $dataToUpdate = [
            'Mailing_address' => $billingData['address_1'] . ' ' . $billingData['address_2'],
            'Postal/ZIP_code' => $billingData['postcode'],
            'countryId' => $billingData['country'],
            'firstname' => $billingData['first_name'],
            'lastname' => $billingData['last_name'],
            'email' => $billingData['email'],
            'phone' => $billingData['phone'],
            'billing_city' => $billingData['city']
        ];
        $this->updateUserProfile($order->data['customer_id'], $dataToUpdate);
    }
}
