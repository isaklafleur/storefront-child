<?php


class MLMSoftSSO_Plugin
{
    const PLUGIN_BASE_NAME = 'mlm-soft-sso/mlm-soft-sso.php';

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * @var MLMSoftSSO_Options
     */
    public $options;

    /**
     * Returns an instance of this class.
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new MLMSoftSSO_Plugin();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->options = new MLMSoftSSO_Options();
        $this->registerHooks();
    }

    public function registerHooks()
    {
        add_action('wp_head', [$this, 'add_xdomain_cookie_js']);
        add_action('storefront_before_site', [$this, 'add_sso_auth']);

        add_filter('mlmsoft_auth_success', array($this, 'sso_login'), 10, 2);
    }

    public function add_xdomain_cookie_js()
    {
        $ssoUrl = $this->options->get_option_value(MLMSoftSSO_Options::MLMSOFT_SSO_URL_KEY, '');
        if (!$ssoUrl) {
            return;
        }
        ?>
        <script src="<?php echo $ssoUrl ?>/xdomain_cookie.min.js"></script>
        <?php
    }

    public function add_sso_auth()
    {
        $ssoUrl = $this->options->get_option_value(MLMSoftSSO_Options::MLMSOFT_SSO_URL_KEY, '');
        if (!$ssoUrl) {
            return;
        }
        ?>
        <script id="mlm-soft-sso-auth">
            const xdomain = window.xDomainCookie("<?php echo $ssoUrl ?>", "my.namespace", true, undefined, true);
            <?php if (isset($_SESSION['sso_data'])): ?>
            xdomain.set('tkns', JSON.stringify({
                "ut": "<?php print($_SESSION['sso_data']['access_token']) ?>",
                "urt": "<?php print($_SESSION['sso_data']['refresh_token']) ?>"
            }));
            <?php else: ?>
            xdomain.set('tkns', false);
            <?php endif ?>
        </script>
        <?php
    }

    /**
     * @param WP_User $user
     * @param $reqRes
     * @param $password
     * @return WP_Error|null
     */
    public function sso_login($user, $password)
    {
        $proxyUrl = $this->options->get_option_value(MLMSoftSSO_Options::MLMSOFT_PROXY_URL_KEY, '');
        if (!$proxyUrl) {
            return null;
        }

        if (!session_id()) {
            session_start();
        }

        $url = trim($proxyUrl, '/') . '/auth/login';
        $params = array(
            'grant_type' => 'password',
            'client_id' => get_user_meta($user->ID, 'account_id', true),
            'client_secret' => ' ',
            'username' => $user->user_login,
            'password' => $password
        );
        $myCurl = curl_init();
        curl_setopt_array($myCurl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params)
        ));
        $response = curl_exec($myCurl);
        curl_close($myCurl);
        $array = json_decode($response, true);
        if (isset($array['oauth2']['error'])) {
            return new WP_Error('oauth_error', 'OAuth Error: ' . $array['oauth2']['message']);
        } else {
            $_SESSION['sso_data'] = $array;
            return null;
        }
    }
}