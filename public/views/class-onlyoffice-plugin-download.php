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
	 * @return void|WP_Error
	 */
	public function get_file( $req ) {
		global $wp_filesystem;

		if ( Onlyoffice_Plugin_JWT_Manager::is_jwt_enabled() ) {
			$jwt_header           = Onlyoffice_Plugin_JWT_Manager::get_jwt_header();
			$authorization_header = apache_request_headers()[ $jwt_header ];

			$token = null !== $authorization_header ? substr( $authorization_header, strlen( 'Bearer ' ) ) : $authorization_header;

			if ( empty( $token ) ) {
				wp_die( 'The request token is missing.', '', array( 'response' => 401 ) );
			}

			$secret = Onlyoffice_Plugin_Settings::get_onlyoffice_setting( Onlyoffice_Plugin_Settings::DOCSERVER_JWT );

			try {
				Onlyoffice_Plugin_JWT_Manager::jwt_decode( $token, $secret );
			} catch ( Exception $e ) {
				wp_die( 'Invalid JWT signature', '', array( 'response' => 401 ) );
			}
		}

		$decoded = json_decode( Onlyoffice_Plugin_Url_Manager::decode_openssl_data( $req->get_params()['id'] ) );

		$attachment_id = $decoded->attachment_id;
		$user_id       = $decoded->user_id;

		if ( 0 !== $user_id ) {
			$user = get_user_by( 'id', $user_id );
			if ( ( null !== $user_id ) && $user ) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
				do_action( 'wp_login', $user->user_login, $user );
			} else {
				wp_die( 'No user information', '', array( 'response' => 403 ) );
			}

			$has_read_capability = current_user_can( 'read' );
			if ( ! $has_read_capability ) {
				wp_die( 'No read capability', '', array( 'response' => 403 ) );
			}
		}

		if ( ob_get_level() ) {
			ob_end_clean();
		}

		if ( ! function_exists( 'WP_Filesystem' ) ) {
			include ABSPATH . '/wp-admin/includes/file.php';
		}

		if ( ! WP_Filesystem() ) {
			return new WP_Error( 'filesystem_error', 'Unable to initialize the filesystem.' );
		}

		$file_path = get_attached_file( $attachment_id );

		if ( $wp_filesystem->is_file( $file_path ) ) {
			header( 'Content-Length: ' . filesize( $file_path ) );
			header( 'Content-Disposition: attachment; filename*=UTF-8\'\'' . urldecode( basename( $file_path ) ) );
			header( 'Content-Type: ' . Onlyoffice_Plugin_Document_Manager::get_mime_type( $file_path ) );

			$file_content = $wp_filesystem->get_contents( $file_path );
			echo $file_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

			exit;
		} else {
				return new WP_Error( 'file_not_found', 'File not found.' );
		}
	}
}
