<?php
/**
 * Set of tools for JWT.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/managers
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

use Firebase\JWT\JWT;

/**
 * Set of tools for JWT.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_JWT_Manager {
	/**
	 * Returns true if jwt enabled.
	 */
	public static function is_jwt_enabled() {
		$options = get_option( 'onlyoffice_settings' );
		return ! empty( $options[ Onlyoffice_Plugin_Settings::DOCSERVER_JWT ] );
	}

	/**
	 * Returns jwt header.
	 */
	public static function get_jwt_header() {
		$options = get_option( 'onlyoffice_settings' );
		$jwt_header = "Authorization";
		if ( ! empty( $options[ Onlyoffice_Plugin_Settings::JWT_HEADER ] ) ) {
			$jwt_header = $options[ Onlyoffice_Plugin_Settings::JWT_HEADER];
		}

		return $jwt_header;
	}

	/**
	 * Converts and signs a PHP object or array into a JWT string.
	 *
	 * @param object|array $payload PHP object or array.
	 * @param string       $secret The secret key.
	 *
	 * @return string
	 */
	public static function jwt_encode( $payload, $secret ) {
		return JWT::encode( $payload, $secret );
	}

	/**
	 * Decodes a JWT string into a PHP object.
	 *
	 * @param string $token The JWT.
	 * @param string $secret The secret key.
	 * @param bool   $for_callback The for callback.
	 *
	 * @return object|string
	 */
	public static function jwt_decode( $token, $secret, $for_callback = false ) {
		if ( ! self::is_jwt_enabled() && ! $for_callback ) {
			return '';
		}

		return JWT::decode( $token, $secret, array( 'HS256' ) );
	}
}
