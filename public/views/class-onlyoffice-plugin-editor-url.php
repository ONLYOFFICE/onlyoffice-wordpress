<?php
/**
 * The editor url route.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public/views
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

/**
 * The editor url route.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public/views
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Editor_Url {

	/**
	 * Return editor url.
	 *
	 * @param array $req      The array of request data. All arguments are optional and may be empty.
	 * @return WP_REST_Response
	 */
	public function get_onlyoffice_editor_url( $req ) {
		$attachment_id = $req->get_params()['id'];

		$response = new WP_REST_Response();
		$response->set_data(
			array(
				'url' => Onlyoffice_Plugin_Url_Manager::get_editor_url( $attachment_id ),
			)
		);

		return $response;
	}
}
