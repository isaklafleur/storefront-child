<?php


class GeoRedirect_Plugin
{
    const PLUGIN_BASE_NAME = 'geo-redirect/geo-redirect.php';

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * @var GeoRedirect_Options
     */
    public $options;

    /**
     * Returns an instance of this class.
     */
    public static function getInstance()
    {
        if (null == self::$instance) {
            self::$instance = new GeoRedirect_Plugin();
        }

        return self::$instance;
    }

    public function __construct()
    {
        $this->options = new GeoRedirect_Options();
        $this->registerHooks();
    }

    public function registerHooks()
    {
        add_action('init', [$this, 'check_geo_and_redirect'], 10, 0);
    }

    public function check_geo_and_redirect()
    {
        if (is_admin()) {
            return;
        }
        $maxMind_Geolocation = new WC_Integration_MaxMind_Geolocation();
        $locationData = $maxMind_Geolocation->get_geolocation([], $_SERVER['REMOTE_ADDR']);

        if (!$locationData['country']) {
            return;
        }

        $url = $this->options->getURLMatch($locationData['country']);

        if ($url && $url != get_site_url()) {
            $url .= $_SERVER['REQUEST_URI'];
            wp_redirect($url);
        }
    }
}