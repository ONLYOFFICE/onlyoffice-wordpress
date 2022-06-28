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
 * Set of tools for working with documents.
 *
 * This class defines all tools code for working with documents.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/managers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Document_Manager {

	const DOC_SERV_VIEWD   = array( '.pdf', '.djvu', '.xps', '.oxps' );
	const DOC_SERV_EDITED  = array( '.docx', '.xlsx', '.pptx' );
	const DOC_SERV_CONVERT = array( '.docm', '.doc', '.dotx', '.dotm', '.dot', '.odt', '.fodt', '.ott', '.xlsm', '.xls', '.xltx', '.xltm', '.xlt', '.ods', '.fods', '.ots', '.pptm', '.ppt', '.ppsx', '.ppsm', '.pps', '.potx', '.potm', '.pot', '.odp', '.fodp', '.otp', '.rtf', '.mht', '.html', '.htm', '.xml', '.epub', '.fb2' );

	const EXTS_CELL = array(
		'.xls',
		'.xlsx',
		'.xlsm',
		'.xlt',
		'.xltx',
		'.xltm',
		'.ods',
		'.fods',
		'.ots',
		'.csv',
	);

	const EXTS_SLIDE = array(
		'.pps',
		'.ppsx',
		'.ppsm',
		'.ppt',
		'.pptx',
		'.pptm',
		'.pot',
		'.potx',
		'.potm',
		'.odp',
		'.fodp',
		'.otp',
	);

	const EXTS_WORD = array(
		'.doc',
		'.docx',
		'.docm',
		'.dot',
		'.dotx',
		'.dotm',
		'.odt',
		'.fodt',
		'.ott',
		'.rtf',
		'.txt',
		'.html',
		'.htm',
		'.mht',
		'.xml',
		'.pdf',
		'.djvu',
		'.fb2',
		'.epub',
		'.xps',
		'.oxps',
	);

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
		$ext = strtolower( '.' . pathinfo( $filename, PATHINFO_EXTENSION ) );

		if ( in_array( $ext, self::EXTS_WORD, true ) ) {
			return 'word';
		}
		if ( in_array( $ext, self::EXTS_CELL, true ) ) {
			return 'cell';
		}
		if ( in_array( $ext, self::EXTS_SLIDE, true ) ) {
			return 'slide';
		}
		return 'word';
	}

	/**
	 * Returns true if the format is supported for editing, otherwise false.
	 *
	 * @param string $filename The file name.
	 *
	 * @return bool
	 */
	public static function is_editable( $filename ) {
		$ext = strtolower( '.' . pathinfo( $filename, PATHINFO_EXTENSION ) );
		return in_array( $ext, self::DOC_SERV_EDITED, true );
	}

	/**
	 * Returns true if the format is supported for opening, otherwise false.
	 *
	 * @param string $filename The file name.
	 *
	 * @return bool
	 */
	public static function is_openable( $filename ) {
		$ext = strtolower( '.' . pathinfo( $filename, PATHINFO_EXTENSION ) );
		return in_array( $ext, array_merge( self::EXTS_WORD, self::EXTS_SLIDE, self::EXTS_CELL ), true );
	}

	/**
	 * Returns all supported formats.
	 */
	public static function all_formats() {
		return array_merge( self::EXTS_WORD, self::EXTS_SLIDE, self::EXTS_CELL );
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

}
