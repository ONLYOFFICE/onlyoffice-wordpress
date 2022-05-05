<?php
/**
 *
 * (c) Copyright Ascensio System SIA 2022
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 */

/**
 * Plugin Name:       ONLYOFFICE
 * Plugin URI:        https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * Description:       ONLYOFFICE Description
 * Version:           1.0.2
 * Requires at least: 5.7
 * Requires PHP:      7.4
 * Author:            Ascensio System SIA
 * Author URI:        https://www.onlyoffice.com
 * License:           GNU General Public License v2.0
 * License URI:       https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Text Domain:       onlyoffice-plugin
 * Domain Path:       /languages
 */

define( 'ONLYOFFICE_PLUGIN_VERSION', '1.0.2' );

function activate_plugin_name() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/onlyoffice-activator.php';
    Onlyoffice_Plugin_Activator::activate();
}
function deactivate_plugin_name() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/onlyoffice-deactivator.php';
    Onlyoffice_Plugin_Deactivator::deactivate();
}

function uninstall_onlyoffice_wordpress_plugin() {
    delete_option( 'onlyoffice_settings' );
    delete_option( 'onlyoffice-plugin-uuid' );
    delete_option("onlyoffice-plugin-bytes");
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );
register_uninstall_hook(__FILE__, 'uninstall_onlyoffice_wordpress_plugin');

require plugin_dir_path( __FILE__ ) . 'includes/onlyoffice-plugin.php';

function run_plugin_name() {

    $plugin = new Onlyoffice_Plugin();
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
