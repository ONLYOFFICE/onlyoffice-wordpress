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
			if ( $format['name'] === $ext ) {
				return $format['type'];
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
			if ( $format['name'] === $ext ) {
				return in_array( 'edit', $format['actions'], true );
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
			if ( $format['name'] === $ext ) {
				return in_array( 'fill', $format['actions'], true );
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
			if ( $format['name'] === $ext ) {
				return in_array( 'view', $format['actions'], true );
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
			if ( in_array( 'view', $format['actions'], true ) ) {
				array_push( $extensions, $format['name'] );
			}
		}

		return $extensions;
	}

	/**
	 * Returns true if user can edit attachment.
	 *
	 * @param string $attachment_id The request.
	 * @return bool
	 */
	public static function has_edit_capability( $attachment_id ) {
		$has_edit_cap = false;
		foreach ( self::EDIT_CAPS as $capability ) {
			$has_edit_cap = $has_edit_cap || current_user_can( $capability, $attachment_id );
		}
		return $has_edit_cap;
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
	 * Return option onlyoffice-formats.
	 */
	public static function get_onlyoffice_formats() {
		$formats = get_option( 'onlyoffice-formats' );
		if ( ! empty( $formats ) ) {
			return $formats;
		}

		return array();
	}

}
