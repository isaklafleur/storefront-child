<?php


class GeoRedirect_Options extends GeoRedirect_OptionsBase
{
    const PLUGIN_PREFIX = 'geo_redirect_';

    const DEFAULT_SITE_URL = 'default_site_url';
    const NOT_PROCESSING_URLS = 'not_processing_urls';

    const MATCH_OPTIONS_PREFIX = 'match_option_';
    const URL_POSTFIX = '_url';
    const COUNTRIES_POSTFIX = '_country';
    const LOCALE_POSTFIX = '_locale';
    const DELETE_POSTFIX = '_delete';

    public $options = [];
    public $sections = [];

    public function __construct()
    {
        parent::__construct(self::PLUGIN_PREFIX, 'Geo redirect', GeoRedirect_Plugin::PLUGIN_BASE_NAME);

        $this->addSection('section_1', 'Base options');
        $this->addSection('section_2', 'Country to URL matches');

        $this->addOption(self::DEFAULT_SITE_URL, 'Default site URL', self::TYPE_TEXT_FIELD, 'section_1');
        $this->addOption(self::NOT_PROCESSING_URLS, 'Not processing URLs', self::TYPE_TEXT_FIELD, 'section_1');

        $option = new GeoRedirectOptionItem([]);
        $option->id = 'country-url-table';
        $option->section = 'section_2';
        $option->label = 'Match options';
        $option->type = self::TYPE_TABLE;
        $tableData = [
            'head' => ['URL', 'Countries', 'Default locale', ''],
            'optionRows' => []
        ];

        $localeItems = array_merge(array('en_US'), get_available_languages());;
        $localeOptions = [];
        foreach ($localeItems as $item) {
            $localeOptions[$item] = $item;
        }

        $matchOptions = $this->getMatchOptionsFromDb();

        foreach ($matchOptions as $index => $optionData) {
            if (empty($optionData[self::URL_POSTFIX]) && empty($optionData[self::COUNTRIES_POSTFIX])) {
                $base = self::PLUGIN_PREFIX . self::MATCH_OPTIONS_PREFIX . $index;
                $this->deleteOption($base);
                unset($matchOptions[$index]);
            }
        }


        $i = 0;
        foreach ($matchOptions as $index => $optionValue) {
            $tableData['optionRows'][] = [
                new GeoRedirectOptionItem([
                    'id' => self::MATCH_OPTIONS_PREFIX . $index . self::URL_POSTFIX,
                    'type' => self::TYPE_TEXT_FIELD
                ]),
                new GeoRedirectOptionItem([
                    'id' => self::MATCH_OPTIONS_PREFIX . $index . self::COUNTRIES_POSTFIX,
                    'type' => self::TYPE_TEXT_FIELD
                ]),
                new GeoRedirectOptionItem([
                    'id' => self::MATCH_OPTIONS_PREFIX . $index . self::LOCALE_POSTFIX,
                    'type' => self::TYPE_SELECT_FIELD,
                    'payload' => ['selectOptions' => $localeOptions]
                ]),
                new GeoRedirectOptionItem([
                    'id' => self::MATCH_OPTIONS_PREFIX . $index . self::DELETE_POSTFIX,
                    'label' => 'delete',
                    'type' => self::TYPE_CUSTOM_ACTION,
                    'payload' => [
                        'callback' => [$this, 'deleteRow']
                    ]
                ]),
            ];
            $i = $index + 1;
        }

        $tableData['optionRows'][$i] = [
            new GeoRedirectOptionItem([
                'id' => self::MATCH_OPTIONS_PREFIX . $i . self::URL_POSTFIX,
                'type' => self::TYPE_TEXT_FIELD
            ]),
            new GeoRedirectOptionItem([
                'id' => self::MATCH_OPTIONS_PREFIX . $i . self::COUNTRIES_POSTFIX,
                'type' => self::TYPE_TEXT_FIELD
            ]),
            new GeoRedirectOptionItem([
                'id' => self::MATCH_OPTIONS_PREFIX . $i . self::LOCALE_POSTFIX,
                'type' => self::TYPE_SELECT_FIELD,
                'payload' => ['selectOptions' => $localeOptions]
            ])
        ];
        $option->payload['tableData'] = $tableData;

        $this->addOptionRaw($option);

        $this->_init();
    }

    /**
     * @param $option GeoRedirectOptionItem
     */
    public function deleteRow($option)
    {
        $base = str_replace(self::DELETE_POSTFIX, '', $option->id);
        $this->deleteOption($base);
    }

    private function deleteOption($base)
    {
        delete_option($base . self::URL_POSTFIX);
        delete_option($base . self::COUNTRIES_POSTFIX);
        delete_option($base . self::LOCALE_POSTFIX);
        delete_option($base . self::DELETE_POSTFIX);
    }

    public function getURL($country)
    {
        $matchOptions = $this->getMatchOptionsFromDb();
        foreach ($matchOptions as $index => $optionsData) {
            $countryList = explode(',', $optionsData[self::COUNTRIES_POSTFIX]);
            foreach ($countryList as $value) {
                if (trim($value) == $country) {
                    $url = $this->get_option_value(self::MATCH_OPTIONS_PREFIX . $index . self::URL_POSTFIX);
                    if (!$url) {
                        return $this->get_option_value(self::DEFAULT_SITE_URL, get_site_url());
                    }
                    return $url;
                }
            }
        }
        return $this->get_option_value(self::DEFAULT_SITE_URL, get_site_url());
    }

    public function getLocale($country) {
        $matchOptions = $this->getMatchOptionsFromDb();
        foreach ($matchOptions as $index => $optionsData) {
            $countryList = explode(',', $optionsData[self::COUNTRIES_POSTFIX]);
            foreach ($countryList as $value) {
                if (trim($value) == $country) {
                    return $this->get_option_value(self::MATCH_OPTIONS_PREFIX . $index . self::LOCALE_POSTFIX, '');
                }
            }
        }
        return '';
    }

    public function getCountriesByURL($url)
    {
        $matchOptions = $this->getMatchOptionsFromDb();
        foreach ($matchOptions as $index => $optionsData) {
            if ($optionsData[self::URL_POSTFIX] == $url) {
                $countries = explode(',', $optionsData[self::COUNTRIES_POSTFIX]);
                foreach ($countries as &$country) {
                    $country = trim($country);
                }
                return $countries;
            }
        }
        return [];
    }

    private function getMatchOptionsFromDb()
    {
        global $wpdb;

        $options = $wpdb->get_results("SELECT option_name, option_value FROM $wpdb->options WHERE autoload = 'yes' AND option_name LIKE '" . self::PLUGIN_PREFIX . self::MATCH_OPTIONS_PREFIX . '%\'');

        $matchOptions = [];
        foreach ($options as $item) {
            $postfix = explode('_', $item->option_name);
            $index = $postfix[count($postfix) - 2];
            $postfix = '_' . $postfix[count($postfix) - 1];
            $matchOptions[$index][$postfix] = $item->option_value;
        }

        ksort($matchOptions);

        return $matchOptions;
    }
}