<?php 

/*
Plugin Name: MLM Soft Integration
Description: WP integration with mlm-soft.com cloud platform
Version: 2.1.1
Author: MLM Soft Ltd.
Author URI: https://mlm-soft.com
Text Domain: mlmsoft
License: GPLv2
*/



register_activation_hook( __FILE__, 'mlmsoft_activate' );

function mlmsoft_activate() {
	// some "activation" actions..
}


// Go..
require_once( plugin_dir_path( __FILE__ ).'/classes/class.mlmsoftapi.php');
require_once( plugin_dir_path( __FILE__ ).'/classes/class.mlmsoftapiresponse.php');
require_once( plugin_dir_path( __FILE__ ).'/classes/class.mlmsoftoptions.php');
require_once( plugin_dir_path( __FILE__ ).'/classes/class.mlmsoft.php');

$MlmSoft = new MlmSoft;
