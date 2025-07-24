<?php
/**
 * The callback route.
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
 * The callback route.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public/views
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Callback {

	const CALLBACK_STATUS = array(
		0 => 'NotFound',
		1 => 'Editing',
		2 => 'MustSave',
		3 => 'Corrupted',
		4 => 'Closed',
		6 => 'MustForceSave',
		7 => 'CorruptedForceSave',
	);

	/**
	 * Callback
	 *
	 * @param array $req Request.
	 *
	 * @return WP_REST_Response
	 */
	public function callback( $req ) {
		require_once ABSPATH . 'wp-admin/includes/post.php';

		$body = json_decode( $req->get_body(), true );

		if ( null === $body ) {
			wp_send_json(
				array(
					'error'   => 1,
					'message' => 'The request body is missing.',
				),
				400
			);
		}

		if ( Onlyoffice_Plugin_JWT_Manager::is_jwt_enabled() ) {
			$token   = $body['token'];
			$in_body = true;

			if ( empty( $token ) ) {
				$jwt_header           = Onlyoffice_Plugin_JWT_Manager::get_jwt_header();
				$authorization_header = apache_request_headers()[ $jwt_header ];
				$token                = null !== $authorization_header ? substr( $authorization_header, strlen( 'Bearer ' ) ) : $authorization_header;
				$in_body              = false;
			}

			if ( empty( $token ) ) {
				wp_send_json(
					array(
						'error'   => 1,
						'message' => 'The request token is missing.',
					),
					401
				);
			}

			$secret = Onlyoffice_Plugin_Settings::get_onlyoffice_setting( Onlyoffice_Plugin_Settings::DOCSERVER_JWT );

			try {
				$body_from_token = Onlyoffice_Plugin_JWT_Manager::jwt_decode( $token, $secret );
				$body            = json_decode( wp_json_encode( $body_from_token ), true );

				if ( ! $in_body ) {
					$body = $body['payload'];
				}
			} catch ( Exception $e ) {
				wp_send_json(
					array(
						'error'   => 1,
						'message' => 'Invalid request token.',
					),
					401
				);
			}
		}

		$token_value = Onlyoffice_Plugin_Url_Manager::decode_url_token( $req->get_query_params()['token'] );

		if ( false === $token_value ) {
			wp_die( 'Invalid link token!', '', array( 'response' => 401 ) );
		}

		if ( 'callback' !== $token_value->action ) {
			wp_die( 'Forbidden!', '', array( 'response' => 403 ) );
		}

		$attachment_id = $token_value->attachment_id;
		$user_id       = $token_value->user_id;

		$user = get_user_by( 'id', $user_id );
		if ( null !== $user_id && $user ) {
			wp_set_current_user( $user_id, $user->user_login );
		} else {
			wp_die( 'No user information', '', array( 'response' => 403 ) );
		}

		$status = self::CALLBACK_STATUS [ $body['status'] ];

		$response      = new WP_REST_Response();
		$response_json = array(
			'error' => 0,
		);

		switch ( $status ) {
			case 'Editing':
				break;
			case 'MustSave':
				$can_edit = Onlyoffice_Plugin_Document_Manager::can_user_edit_attachment( $attachment_id );
				if ( ! $can_edit ) {
					wp_die( 'No edit capability', '', array( 'response' => 403 ) );
				}

				$locked = wp_check_post_lock( $attachment_id );
				if ( ! $locked ) {
					wp_set_post_lock( $attachment_id );
				}

				$response_json['error'] = Onlyoffice_Plugin_Callback_Manager::proccess_save( $body, $attachment_id );
				break;
			case 'Corrupted':
			case 'Closed':
			case 'NotFound':
				delete_post_meta( $attachment_id, '_edit_lock' );
				break;
			case 'MustForceSave':
			case 'CorruptedForceSave':
				break;
		}

		$response->data = $response_json;

		return $response;
	}

	/**
	 * Callback for public forms.
	 *
	 * @return WP_REST_Response
	 */
	public function callback_public_forms() {
		$response = new WP_REST_Response();

		$response->data = array(
			'error' => 0,
		);

		return $response;
	}
}
