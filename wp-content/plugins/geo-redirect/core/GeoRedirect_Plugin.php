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
        if (is_admin() || $this->checkNotProcessing($_SERVER['REQUEST_URI'])) {
            return;
        }

        $currentCountryIndex = $this->getCurrentCountryIndex();

        $cookieData = $this->getCookieData();

        $clientCountry = $cookieData['country'];
        $clientLocale = $cookieData['locale'];

        if ($clientCountry && $currentCountryIndex == $clientCountry) {
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'];

        $maxMind_Geolocation = new WC_Integration_MaxMind_Geolocation();
        $locationData = $maxMind_Geolocation->get_geolocation([], $ip);

        if (!$locationData['country']) {
            return;
        }

        $country = $locationData['country'];
        $countryIndex = $this->options->getCountryIndex($country);

        $url = $this->options->getURL($countryIndex);
        $locale = $clientLocale ?: $this->options->getLocale($country);

        setcookie('theuntamed_locale', $locale, time() + (365 * 24 * 60 * 60), '/', '.theuntamed.com');
        setcookie('theuntamed_country', $country, time() + (365 * 24 * 60 * 60), '/', '.theuntamed.com');

        if ($url && $url != get_site_url()) {
            $url .= $_SERVER['REQUEST_URI'];
            wp_redirect($url);
        }
    }

    public function checkNotProcessing($url)
    {
        $urls = explode(',', $this->options->get_option_value(GeoRedirect_Options::NOT_PROCESSING_URLS, ''));
        foreach ($urls as $urlVal) {
            if (strpos($url, trim($urlVal)) !== false) {
                return true;
            }
        }
        return false;
    }

    public function getCurrentCountryIndex()
    {
       return  $this->options->getCountryIndexByURL(get_site_url());
    }

    public function getCookieData()
    {
        $clientCountry = isset($_COOKIE['theuntamed_country']) ? $_COOKIE['theuntamed_country'] : '';
        $clientLocale = isset($_COOKIE['theuntamed_locale']) ? $_COOKIE['theuntamed_locale'] : '';
        return [
            'country' => $clientCountry,
            'locale' => $clientLocale
        ];
    }
}