<?php

/**
 * Plugin Name:       ONLYOFFICE Wordpress plugin
 * Plugin URI:        https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * Description:       ONLYOFFICE Description
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Ascensio System SIA
 * Author URI:        https://www.onlyoffice.com
 * License:           Apache License 2.0
 * License URI:       https://www.apache.org/licenses/LICENSE-2.0
 * Text Domain:       onlyoffce-plugin
 * Domain Path:       /languages
 */

define( 'ONLYOFFICE_PLUGIN_VERSION', '1.0.0' );

function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/onlyoffice-activator.php';
	OOP_Activator::activate();
}
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/onlyoffice-deactivator.php';
	OOP_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

require plugin_dir_path( __FILE__ ) . 'includes/onlyoffice-plugin.php';

function run_plugin_name() {

	$plugin = new OOPlugin();
	$plugin->run();

}
run_plugin_name();