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

		$response      = new WP_REST_Response();
		$response_json = array(
			'error' => 0,
		);

		$attachemnt_id = intval( Onlyoffice_Plugin_Url_Manager::decode_openssl_data( $req->get_params()['id'] ) );
		$body          = Onlyoffice_Plugin_Callback_Manager::read_body( $req->get_body() );
		if ( ! empty( $body['error'] ) ) {
			$response_json['message'] = $body['error'];
			$response->data           = $response_json;
			return $response;
		}

		wp_set_current_user( $body['actions'][0]['userid'] );

		$status = self::CALLBACK_STATUS[ $body['status'] ];

		$user_id = null;
		if ( ! empty( $body['users'] ) ) {
			$users = $body['users'];
			if ( count( $users ) > 0 ) {
				$user_id = $users[0];
			}
		}

		if ( null === $user_id && ! empty( $body['actions'] ) ) {
			$actions = $body['actions'];
			if ( count( $actions ) > 0 ) {
				$user_id = $actions[0]['userid'];
			}
		}

		$user = get_user_by( 'id', $user_id );
		if ( null !== $user_id && $user ) {
			wp_set_current_user( $user_id, $user->user_login );
			wp_set_auth_cookie( $user_id );
			do_action( 'wp_login', $user->user_login, $user );
		} else {
			wp_die( 'No user information', '', array( 'response' => 403 ) );
		}

		switch ( $status ) {
			case 'Editing':
				break;
			case 'MustSave':
				$can_edit = Onlyoffice_Plugin_Document_Manager::has_edit_capability( $attachemnt_id );
				if ( ! $can_edit ) {
					wp_die( 'No edit capability', '', array( 'response' => 403 ) );
				}

				$locked = wp_check_post_lock( $attachemnt_id );
				if ( ! $locked ) {
					wp_set_post_lock( $attachemnt_id );
				}

				$response_json['error'] = Onlyoffice_Plugin_Callback_Manager::proccess_save( $body, $attachemnt_id );
				break;
			case 'Corrupted':
			case 'Closed':
			case 'NotFound':
				delete_post_meta( $attachemnt_id, '_edit_lock' );
				break;
			case 'MustForceSave':
			case 'CorruptedForceSave':
				break;
		}

		$response->data = $response_json;

		return $response;
	}
}
