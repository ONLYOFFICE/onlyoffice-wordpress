<?php
/**
 *
 * (c) Copyright Ascensio System SIA 2022
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

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
 * Text Domain:       onlyoffice-plugin
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

function onlyoffice_custom_block() {
    register_block_type( __DIR__ . '/onlyoffice-wordpress-block', array(
        'description' => __('Add ONLYOFFICE editor on page', 'onlyoffice-plugin')
    ));

    if (function_exists('wp_set_script_translations')) {
        wp_set_script_translations('onlyoffice-plugin', 'onlyoffice-plugin');
    }
}
add_action( 'init', 'onlyoffice_custom_block' );
