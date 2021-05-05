<?php


class GeoRedirect_Plugin
{
    const PLUGIN_BASE_NAME = 'geo-redirect/geo-redirect.php';
    const API_ACTION = 'geo-redirect-api';

    /**
     * A reference to an instance of this class.
     */
    private static $instance;

    /**
     * @var GeoRedirect_Options
     */
    public $options;

    /**
     * @var GeoRedirect_Api
     */
    public $api;

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

        $token = $this->options->get_option_value(GeoRedirect_Options::API_TOKEN);
        $this->api = new GeoRedirect_Api(self::API_ACTION, $token, $this->options->get_option_value(GeoRedirect_Options::MASTER_API_URL));
        $this->options->loadRules($this->api);

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

        $currentSiteLocale = wpml_get_current_language();

        $selectedCountry = '';
        if (isset($_REQUEST['country'])) {
            $selectedCountry = $_REQUEST['country'];
        }
        $selectedLanguage = $currentSiteLocale;
        if (isset($_REQUEST['lang'])) {
            $selectedLanguage = $_REQUEST['lang'];
        }

        if ($clientCountry && $currentCountryIndex == $clientCountry && (!strlen($selectedCountry) || $currentCountryIndex == $selectedCountry) &&
            !empty($clientLocale) && $clientLocale == $currentSiteLocale && $selectedLanguage == $currentSiteLocale) {
            return;
        }

        if (strlen($selectedCountry)) {
            $countryIndex = $selectedCountry;
        } else if (strlen($clientCountry)) {
            $countryIndex = $clientCountry;
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
            $maxMind_Geolocation = new WC_Integration_MaxMind_Geolocation();
            $locationData = $maxMind_Geolocation->get_geolocation([], $ip);

            if (!$locationData['country']) {
                return;
            }

            $country = $locationData['country'];

            $countryIndex = $this->options->getCountryIndex($country);
        }

        if ($selectedLanguage) {
            $locale = $selectedLanguage;
        } else {
            $locale = $clientLocale ?: $this->options->getLocale($countryIndex);
        }

        $url = $this->options->getURL($countryIndex);

        $localeItems = wpml_get_active_languages();
        if (!in_array($locale, array_keys($localeItems))) {
            $locale = $currentSiteLocale;
        }

        $cookieDomain = $this->options->get_option_value(GeoRedirect_Options::COOKIE_DOMAIN);

        setcookie('theuntamed_locale', $locale, time() + (365 * 24 * 60 * 60), '/', $cookieDomain);
        setcookie('theuntamed_country', $countryIndex, time() + (365 * 24 * 60 * 60), '/', $cookieDomain);

        $urlCorrect = $url && $url == get_site_url();
        $localeCorrect = $locale == $currentSiteLocale;

        if (!$localeCorrect || !$urlCorrect) {
            $url .= $_SERVER['REQUEST_URI'];
            if (!$localeCorrect) {
                $url = add_query_arg(['lang' => $locale], $url);
            }
            wp_redirect($url);
            exit();
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
        return $this->options->getCountryIndexByURL(get_site_url());
    }

    public function getCookieData()
    {
        $clientCountry = isset($_COOKIE['theuntamed_country']) ? $_COOKIE['theuntamed_country'] : '';
        $clientLocale = isset($_COOKIE['theuntamed_locale']) ? $_COOKIE['theuntamed_locale'] : '';
        return [
            'country' => trim($clientCountry),
            'locale' => trim($clientLocale)
        ];
    }

    public function getRules()
    {
        if (!$this->options->isMaster) {
            return [];
        }
        return $this->options->getListRules();
    }
}