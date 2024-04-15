<?php
/**
 * Set of tools for working with documents.
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
 * Set of tools for working with documents.
 *
 * This class defines all tools code for working with documents.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Document_Manager {
	const EDIT_CAPS = array(
		'edit_others_pages',
		'edit_others_posts',
		'edit_pages',
		'edit_posts',
		'edit_private_pages',
		'edit_private_posts',
		'edit_published_pages',
		'edit_published_posts',
	);

	/**
	 * Init Onlyoffice_Plugin_Document_Manager.
	 */
	public static function init() {
		$path_to_formats_json = plugin_dir_path( ONLYOFFICE_PLUGIN_FILE ) . '/public/assets/document-formats/onlyoffice-docs-formats.json';

		if ( file_exists( $path_to_formats_json ) === true ) {
			$formats = wp_json_file_decode( $path_to_formats_json );
			update_site_option( 'onlyoffice-formats', $formats );
			update_site_option( 'onlyoffice-formats-version', ONLYOFFICE_PLUGIN_VERSION );
		}
	}

	/**
	 * Returns the type of the document (word, cell, slide).
	 *
	 * @param string $filename The file name.
	 *
	 * @return string
	 */
	public static function get_document_type( $filename ) {
		$formats = self::get_onlyoffice_formats();
		$ext     = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		foreach ( $formats as $format ) {
			if ( $format->name === $ext ) {
				return $format->type;
			}
		}

		return null;
	}

	/**
	 * Returns true if the format is supported for editing, otherwise false.
	 *
	 * @param string $filename The file name.
	 *
	 * @return bool
	 */
	public static function is_editable( $filename ) {
		$formats = self::get_onlyoffice_formats();
		$ext     = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		foreach ( $formats as $format ) {
			if ( $format->name === $ext ) {
				return in_array( 'edit', $format->actions, true );
			}
		}

		return false;
	}

	/**
	 * Returns true if the format is supported for filling.
	 *
	 * @param string $filename The file name.
	 *
	 * @return bool
	 */
	public static function is_fillable( $filename ) {
		$formats = self::get_onlyoffice_formats();
		$ext     = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		foreach ( $formats as $format ) {
			if ( $format->name === $ext ) {
				return in_array( 'fill', $format->actions, true );
			}
		}

		return false;
	}

	/**
	 * Returns true if the format is supported for opening, otherwise false.
	 *
	 * @param string $filename The file name.
	 *
	 * @return bool
	 */
	public static function is_viewable( $filename ) {
		$formats = self::get_onlyoffice_formats();
		$ext     = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );

		foreach ( $formats as $format ) {
			if ( $format->name === $ext ) {
				return in_array( 'view', $format->actions, true );
			}
		}

		return false;
	}

	/**
	 * Returns all supported on view extensions.
	 */
	public static function get_viewable_extensions() {
		$formats    = self::get_onlyoffice_formats();
		$extensions = array();

		foreach ( $formats as $format ) {
			if ( in_array( 'view', $format->actions, true ) ) {
				array_push( $extensions, $format->name );
			}
		}

		return $extensions;
	}

	/**
	 * Returns true if user can view attachment.
	 *
	 * @param string $attachment_id The attachment id.
	 * @return bool
	 */
	public static function can_user_view_attachment( $attachment_id ) {
		$attachment = get_post( $attachment_id );

		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			return false;
		}

		return current_user_can( 'read_post', $attachment_id );
	}

	/**
	 * Returns true if anonymous user can view attachment.
	 *
	 * @param string $attachment_id The attachment id.
	 * @return bool
	 */
	public static function can_anonymous_user_view_attachment( $attachment_id ) {
		$attachment = get_post( $attachment_id );

		if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
			return false;
		}

		$is_public = get_post_meta( $attachment_id, '_wp_attachment_metadata', true );

		if ( ! $is_public ) {
			return false;
		}

		$parent_post_id = $attachment->post_parent;
		$parent_post    = get_post( $parent_post_id );

		if ( ! $parent_post ) {
			return false;
		}

		if ( 'publish' !== $parent_post->post_status ) {
			return false;
		}

		return true;
	}

	/**
	 * Returns true if user can edit attachment.
	 *
	 * @param string $attachment_id The attachment id.
	 * @return bool
	 */
	public static function can_user_edit_attachment( $attachment_id ) {
		$attachment = get_post( $attachment_id );

		if ( ! $attachment ) {
			return false;
		}

		return current_user_can( 'edit_post', $attachment_id );
	}

	/**
	 * Return mime type by file name.
	 *
	 * @param string $filename The file name.
	 * @return string
	 */
	public static function get_mime_type( $filename ) {
		$mime = wp_check_filetype( $filename );

		if ( false === $mime['type'] && function_exists( 'mime_content_type' ) ) {
			$mime['type'] = mime_content_type( $filename );
		}

		return false === $mime['type'] ? 'application/octet-stream' : $mime['type'];
	}

	/**
	 * Return supported ONLYOFFICE formats.
	 */
	public static function get_onlyoffice_formats() {
		$onlyoffice_formats_version = get_site_option( 'onlyoffice-formats-version' );
		if ( empty( $onlyoffice_formats_version ) || ONLYOFFICE_PLUGIN_VERSION !== $onlyoffice_formats_version ) {
			self::init();
		}

		$formats = get_site_option( 'onlyoffice-formats' );
		if ( ! empty( $formats ) ) {
			return $formats;
		}

		return array();
	}
}
