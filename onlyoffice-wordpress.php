<?php
/**
 * The plugin bootstrap file.
 *
 * @package           Onlyoffice_Plugin
 *
 * Plugin Name:       ONLYOFFICE
 * Plugin URI:        https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * Description:       Add ONLYOFFICE editor on page
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

/**
 *
 * (c) Copyright Ascensio System SIA 2023
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
 */

/**
 * Currently plugin version.
 */
define( 'ONLYOFFICE_PLUGIN_VERSION', '1.0.2' );


/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-onlyoffice-plugin-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-onlyoffice-plugin-activator.php';
	Onlyoffice_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-onlyoffice-plugin-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-onlyoffice-plugin-deactivator.php';
	Onlyoffice_Plugin_Deactivator::deactivate();
}
/**
 * Uninstall hook.
 */
function uninstall_onlyoffice_wordpress_plugin() {
	delete_option( 'onlyoffice_settings' );
	delete_option( 'onlyoffice-plugin-uuid' );
	delete_option( 'onlyoffice-plugin-bytes' );
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );
register_uninstall_hook( __FILE__, 'uninstall_onlyoffice_wordpress_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-onlyoffice-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_plugin_name() {

	$plugin = new Onlyoffice_Plugin();
	$plugin->run();

}
run_plugin_name();

