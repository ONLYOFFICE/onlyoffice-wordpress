<?php
/**
 * Loads a plugin's translated strings.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes
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
 * Loads a plugin's translated strings.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_I18n {

	/**
	 * Loads a plugin's translated strings.
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain(
			'onlyoffice-plugin',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}
}
