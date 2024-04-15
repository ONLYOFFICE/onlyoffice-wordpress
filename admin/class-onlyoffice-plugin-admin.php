<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/admin
 */

/**
 *
 * (c) Copyright Ascensio System SIA 2024
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
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/admin
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
final class Onlyoffice_Plugin_Admin {

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			ONLYOFFICE_PLUGIN_NAME . '-formats-utils',
			ONLYOFFICE_PLUGIN_URL . 'assets-onlyoffice/js/formatsUtils.js',
			array(),
			ONLYOFFICE_PLUGIN_VERSION,
			true
		);

		wp_localize_script(
			ONLYOFFICE_PLUGIN_NAME . '-formats-utils',
			'ONLYOFFICE',
			array(
				'formats'   => Onlyoffice_Plugin_Document_Manager::get_onlyoffice_formats(),
				'mimeTypes' => get_allowed_mime_types(),
			)
		);
	}
}
