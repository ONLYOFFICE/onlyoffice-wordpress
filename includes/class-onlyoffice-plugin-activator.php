<?php
/**
 * Fired during plugin activation
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
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Activator {

	/**
	 * Set defaults on activation.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		if ( empty( get_site_option( 'onlyoffice-plugin-uuid' ) ) ) {
			update_site_option( 'onlyoffice-plugin-uuid', wp_generate_uuid4() );
		}
		if ( empty( get_site_option( 'onlyoffice-plugin-bytes' ) ) ) {
			$ivlen = openssl_cipher_iv_length( 'aes-256-ctr' );
			$iv    = openssl_random_pseudo_bytes( $ivlen );
			update_site_option( 'onlyoffice-plugin-bytes', bin2hex( $iv ) );
		}

		Onlyoffice_Plugin_Document_Manager::init();
	}
}
