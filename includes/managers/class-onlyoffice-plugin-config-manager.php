<?php
/**
 * Manager for creating config for ONLYOFFICE Editor.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      2.0.0
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
 * Manager for creating config for ONLYOFFICE Editor.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Config_Manager {

	/**
	 * Return the config for ONLYOFFICE Editor.
	 *
	 * @param string  $attachment_id The attachment ID.
	 * @param string  $type The type of editor (desktop, mobile, embbeded).
	 * @param string  $mode The mode of editor (view or edit).
	 * @param boolean $perm_edit The permission for editing.
	 * @param string  $callback_url The callback url.
	 * @param string  $go_back_url The go back URL.
	 * @param boolean $unic_key The flag generate unic key.
	 * @return array
	 */
	public static function get_config( $attachment_id, $type, $mode, $perm_edit, $callback_url, $go_back_url, $unic_key ) {
		$post = get_post( $attachment_id );

		if ( ! $post ) {
			return null;
		}

		$user      = wp_get_current_user();
		$author    = get_user_by( 'id', $post->post_author )->display_name;
		$filepath  = get_attached_file( $attachment_id );
		$filename  = wp_basename( $filepath );
		$filetype  = strtolower( pathinfo( $filepath, PATHINFO_EXTENSION ) );
		$file_url  = Onlyoffice_Plugin_Url_Manager::get_download_url( $attachment_id );
		$lang      = get_user_locale( $user->ID );
		$perm_fill = false;

		if ( Onlyoffice_Plugin_Document_Manager::is_fillable( $filename ) ) {
			$perm_fill = true;
			$perm_edit = false;
		}

		$config = array(
			'type'         => $type,
			'documentType' => Onlyoffice_Plugin_Document_Manager::get_document_type( $filename ),
			'document'     => array(
				'title'       => $filename,
				'url'         => $file_url,
				'fileType'    => $filetype,
				'key'         => base64_encode( $post->post_modified ) . $attachment_id, // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				'info'        => array(
					'owner'    => $author,
					'uploaded' => $post->post_date,
				),
				'permissions' => array(
					'download'  => true,
					'edit'      => $perm_edit,
					'fillForms' => $perm_fill,
				),
			),
			'editorConfig' => array(
				'mode'        => $mode,
				'lang'        => str_contains( $lang, '_' ) ? explode( '_', $lang )[0] : $lang,
				'callbackUrl' => $callback_url,
			),
		);

		if ( $unic_key ) {
			$config['document']['key'] .= '_' . wp_generate_uuid4();
		}

		if ( $go_back_url ) {
			$config['editorConfig']['customization']['goback'] = array(
				'url' => $go_back_url,
			);
		}

		if ( 0 !== $user->ID ) {
			$config['editorConfig']['user'] = array(
				'id'   => (string) $user->ID,
				'name' => $user->display_name,
			);
		}

		if ( Onlyoffice_Plugin_JWT_Manager::is_jwt_enabled() ) {
			$secret          = Onlyoffice_Plugin_Settings::get_onlyoffice_setting( Onlyoffice_Plugin_Settings::DOCSERVER_JWT );
			$config['token'] = Onlyoffice_Plugin_JWT_Manager::jwt_encode( $config, $secret );
		}

		return $config;
	}
}
