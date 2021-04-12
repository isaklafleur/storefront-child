<?php

/*
Plugin Name: Geo redirect
Description: Redirect to another site depending on geo ip
Version: 1.0.0
Author: Aleksander MLMSoft.
*/

require_once(plugin_dir_path(__FILE__) . '/core/GeoRedirect_OptionsBase.php');
require_once(plugin_dir_path(__FILE__) . '/core/GeoRedirectOptionItem.php');
require_once(plugin_dir_path(__FILE__) . '/core/GeoRedirect_Plugin.php');
require_once(plugin_dir_path(__FILE__) . '/core/GeoRedirect_Options.php');

add_action('plugins_loaded', array('GeoRedirect_Plugin', 'getInstance'));