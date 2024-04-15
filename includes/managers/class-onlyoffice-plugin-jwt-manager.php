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

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

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
		return ! empty( Onlyoffice_Plugin_Settings::get_onlyoffice_setting( Onlyoffice_Plugin_Settings::DOCSERVER_JWT ) );
	}

	/**
	 * Returns jwt header.
	 */
	public static function get_jwt_header() {
		$jwt_header = Onlyoffice_Plugin_Settings::get_onlyoffice_setting( Onlyoffice_Plugin_Settings::JWT_HEADER );
		if ( ! empty( $jwt_header ) ) {
			return $jwt_header;
		}

		return 'Authorization';
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
		return JWT::encode( $payload, $secret, 'HS256' );
	}

	/**
	 * Decodes a JWT string into a PHP object.
	 *
	 * @param string $token The JWT.
	 * @param string $secret The secret key.
	 *
	 * @return object|string
	 */
	public static function jwt_decode( $token, $secret ) {
		$jwt_reflection_class = new ReflectionClass( '\Firebase\JWT\JWT' );
		$methods              = $jwt_reflection_class->getMethods();

		$filtered_methods = array_filter(
			$methods,
			function ( $method ) {
				return strpos( $method->getName(), 'getKey' ) === 0;
			}
		);

		if ( empty( $filtered_methods ) ) {
			$allowed_algs = array( 'HS256' );
			return JWT::decode( $token, $secret, $allowed_algs );
		} else {
			return JWT::decode( $token, new Key( $secret, 'HS256' ) );
		}
	}
}
