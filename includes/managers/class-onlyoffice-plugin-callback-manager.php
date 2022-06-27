<?php
/**
 * The set of tools for callback process.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/managers
 */

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
 */

/**
 * The set of tools for callback process.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Callback_Manager {

	/**
	 * Method for read body from request.
	 *
	 * @param mixed $body The body from request.
	 * @return array|mixed
	 * @since    1.0.0
	 */
	public static function read_body( $body ) {
		$result['error'] = 0;

		$data = json_decode( $body, true );

		if ( null === $data ) {
			$result['error'] = 'Callback data is null or empty';
			return $result;
		}

		if ( Onlyoffice_Plugin_JWT_Manager::is_jwt_enabled() ) {
			$in_header  = false;
			$jwt_header = 'Authorization';
			$options    = get_option( 'onlyoffice_settings' );
			$secret     = $options[ Onlyoffice_Plugin_Settings::DOCSERVER_JWT ];

			if ( ! empty( $data['token'] ) ) {
				$token = Onlyoffice_Plugin_JWT_Manager::jwt_decode( $data['token'], $secret );
			} elseif ( ! empty( apache_request_headers()[ $jwt_header ] ) ) {
				$token     = Onlyoffice_Plugin_JWT_Manager::jwt_decode( substr( apache_request_headers()[ $jwt_header ], strlen( 'Bearer ' ) ), $secret );
				$in_header = true;
			} else {
				$result['error'] = 'Expected JWT';
				return $result;
			}
			if ( empty( $token ) ) {
				$result['error'] = 'Invalid JWT signature';
				return $result;
			}

			$data = json_decode( wp_json_encode( $token ), true );
			if ( $in_header ) {
				$data = $data['payload'];
			}
		}

		return $data;
	}

	/**
	 * Method for saving file.
	 *
	 * @param mixed  $body The body from request.
	 * @param string $attachemnt_id The id of the attachment.
	 * @return int
	 * @since    1.0.0
	 */
	public static function proccess_save( $body, $attachemnt_id ) {
		$download_url = $body['url'];
		if ( null === $download_url ) {
			return 1;
		}

		$new_data = wp_remote_get( $download_url );
		if ( null === $new_data ) {
			return 1;
		}

		$filepath = get_attached_file( $attachemnt_id );
		WP_Filesystem( $filepath, $new_data, LOCK_EX );
		$id = wp_update_post(
			array(
				'id'   => $attachemnt_id,
				'file' => 'file',
			)
		);

		if ( 0 === $id ) {
			return 1;
		}

		return 0;
	}
}
