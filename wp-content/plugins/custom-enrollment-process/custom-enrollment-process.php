<?php

/*
Plugin Name: Custom enrollment process
Description: Custom enrollment process
Version: 1.0.0
Author: Aleksander MLMSoft.
*/

require_once(plugin_dir_path(__FILE__) . '/core/CE_ProcessPlugin.php');
require_once(plugin_dir_path(__FILE__) . '/core/CE_ProcessOptions.php');
require_once(plugin_dir_path(__FILE__) . '/core/CE_Process.php');
require_once(plugin_dir_path(__FILE__) . '/core/CE_ProcessCart.php');
require_once(plugin_dir_path(__FILE__) . '/core/CE_Data.php');
require_once(plugin_dir_path(__FILE__) . '/core/CE_Database.php');

$customEnrollmentProcess = new CE_Process();

add_action('plugins_loaded', array('CE_ProcessPlugin', 'getInstance'));
register_activation_hook(__FILE__, array('CE_ProcessPlugin', 'plugin_activate'));
register_deactivation_hook(__FILE__, array('CE_ProcessPlugin', 'plugin_deactivate'));

add_filter('http_request_args', 'bal_http_request_args', 100, 1);
function bal_http_request_args($r)
{
    $r['timeout'] = 15;
    return $r;
}

add_action('http_api_curl', 'bal_http_api_curl', 100, 1);
function bal_http_api_curl($handle)
{
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($handle, CURLOPT_TIMEOUT, 15);
}