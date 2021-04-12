<?php


class GeoRedirect_Options extends GeoRedirect_OptionsBase
{
    const PLUGIN_PREFIX = 'geo_redirect_';

    const DEFAULT_SITE_URL = 'default_site_url';
    const COUNTRY_URL_MATH_COUNTRY = 'country_url_match_country';
    const COUNTRY_URL_MATH_URL = 'country_url_match_url';

    public $options = [];
    public $sections = [];

    public function __construct()
    {
        parent::__construct(self::PLUGIN_PREFIX, 'Geo redirect', GeoRedirect_Plugin::PLUGIN_BASE_NAME);

        $this->addSection('section_1', 'Base options');
        $this->addSection('section_2', 'Country to URL matches');

        $this->addOption(self::DEFAULT_SITE_URL, 'Default site URL', self::TYPE_TEXT_FIELD, 'section_1');

        $i = 0;
        for (;get_option(self::PLUGIN_PREFIX . self::COUNTRY_URL_MATH_COUNTRY . '_' . $i); $i++) {
            $this->addOption(self::COUNTRY_URL_MATH_COUNTRY . '_' . $i, 'Countries ' . ($i + 1), self::TYPE_TEXT_FIELD, 'section_2', null, null, 'Enter the countries, separated by commas.');
            $this->addOption(self::COUNTRY_URL_MATH_URL . '_' . $i, 'URL ' . ($i + 1), self::TYPE_TEXT_FIELD, 'section_2');
        }

        $this->addOption(self::COUNTRY_URL_MATH_COUNTRY . '_' . $i, 'Countries ' . ($i + 1), self::TYPE_TEXT_FIELD, 'section_2',null, null, 'Enter the countries, separated by commas.');
        $this->addOption(self::COUNTRY_URL_MATH_URL . '_' . $i, 'URL ' . ($i + 1), self::TYPE_TEXT_FIELD, 'section_2');

        $this->_init();
    }
}