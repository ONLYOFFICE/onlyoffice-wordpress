<?php
/**
 * The file download route.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public/views
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
 * The file download route.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public/views
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Download {

	/**
	 * Get file.
	 *
	 * @param array $req The request.
	 *
	 * @return void
	 */
	public function get_file( $req ) {
		$decoded = json_decode( Onlyoffice_Plugin_Url_Manager::decode_openssl_data( $req->get_params()['id'] ) );

		$attachment_id = $decoded->attachment_id;
		$user_id       = $decoded->user_id;

		if ( 0 !== $user_id ) {
			$user = get_user_by( 'id', $user_id );
			if ( ( null !== $user_id ) && $user ) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
				do_action( 'wp_login', $user->user_login );
			} else {
				wp_die( 'No user information', '', array( 'response' => 403 ) );
			}

			$has_read_capability = current_user_can( 'read' );
			if ( ! $has_read_capability ) {
				wp_die( 'No read capability', '', array( 'response' => 403 ) );
			}
		}
		if ( Onlyoffice_Plugin_JWT_Manager::is_jwt_enabled() ) {
			$jwt_header = 'Authorization';
			if ( ! empty( apache_request_headers()[ $jwt_header ] ) ) {
				$options = get_option( 'onlyoffice_settings' );
				$secret  = $options[ Onlyoffice_Plugin_Settings::DOCSERVER_JWT ];
				$token   = Onlyoffice_Plugin_JWT_Manager::jwt_decode( substr( apache_request_headers()[ $jwt_header ], strlen( 'Bearer ' ) ), $secret );
				if ( empty( $token ) ) {
					wp_die( 'Invalid JWT signature', '', array( 'response' => 403 ) );
				}
			}
		}

		if ( ob_get_level() ) {
			ob_end_clean();
		}

		$filepath = get_attached_file( $attachment_id );

		header( 'Content-Length: ' . filesize( $filepath ) );
		header( 'Content-Disposition: attachment; filename*=UTF-8\'\'' . urldecode( basename( $filepath ) ) );
		header( 'Content-Type: ' . mime_content_type( $filepath ) );

		flush();
		readfile( $filepath );
		unlink( $filepath );
		exit;
	}

}
