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
    }
}