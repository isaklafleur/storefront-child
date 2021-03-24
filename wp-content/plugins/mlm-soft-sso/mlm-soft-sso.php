<?php

/*
Plugin Name: MLMSoft SSO
Description: MLMSoft SSO integration
Version: 1.0.0
Author: Aleksander MLMSoft.
*/

require_once(plugin_dir_path(__FILE__) . '/core/MLMSoftSSO_Plugin.php');
require_once(plugin_dir_path(__FILE__) . '/core/MLMSoftSSO_Options.php');

add_action('plugins_loaded', array('MLMSoftSSO_Plugin', 'getInstance'));