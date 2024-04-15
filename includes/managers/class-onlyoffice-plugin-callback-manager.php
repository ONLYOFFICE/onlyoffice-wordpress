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
 * The set of tools for callback process.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Callback_Manager {

	/**
	 * Method for saving file.
	 *
	 * @param mixed  $body The body from request.
	 * @param string $attachemnt_id The id of the attachment.
	 * @global WP_Filesystem_Base $wp_filesystem WordPress filesystem subclass.
	 * @return int
	 * @since    1.0.0
	 */
	public static function proccess_save( $body, $attachemnt_id ) {
		global $wp_filesystem;

		if ( ! $wp_filesystem ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
		}

		$download_url = $body['url'];
		if ( null === $download_url ) {
			return 1;
		}

		$response = wp_remote_get( $download_url );

		if ( is_wp_error( $response ) ) {
			return 1;
		}

		$new_data = wp_remote_retrieve_body( $response );

		$filepath = get_attached_file( $attachemnt_id );

		$wp_filesystem->put_contents( $filepath, $new_data, FS_CHMOD_FILE );

		$id = wp_update_post(
			array(
				'ID' => $attachemnt_id,
			)
		);

		if ( 0 === $id ) {
			return 1;
		}

		return 0;
	}
}
