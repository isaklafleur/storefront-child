<?php

/*
Plugin Name: MLMSoft wallet coupons
Description: MLMSoft wallet coupons
Version: 1.0.0
Author: Aleksander MLMSoft.
*/

require_once(plugin_dir_path(__FILE__) . 'core/MLMSoftWalletCoupons_Plugin.php');
require_once(plugin_dir_path(__FILE__) . 'core/MLMSoftWalletCoupons_Options.php');

add_action('plugins_loaded', array('MLMSoftWalletCoupons_Plugin', 'getInstance'));