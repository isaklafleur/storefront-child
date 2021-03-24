<?php
/*
Plugin Name: Role based discounts
Description: Role based discounts
Version: 1.0.0
Author: Aleksander MLMSoft.
*/

require_once(plugin_dir_path(__FILE__) . '/core/RBD_Plugin.php');
require_once(plugin_dir_path(__FILE__) . '/core/RBD_Options.php');

add_action('plugins_loaded', array('RBD_Plugin', 'getInstance'));