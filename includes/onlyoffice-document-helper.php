<?php
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
 *
 */

class Onlyoffice_Plugin_Document_Helper
{
    const DOC_SERV_VIEWD = array(".pdf", ".djvu", ".xps", ".oxps");
    const DOC_SERV_EDITED = array(".docx", ".xlsx", ".pptx");
    const DOC_SERV_CONVERT = array(".docm", ".doc", ".dotx", ".dotm", ".dot", ".odt", ".fodt", ".ott", ".xlsm", ".xls", ".xltx", ".xltm", ".xlt", ".ods", ".fods", ".ots", ".pptm", ".ppt", ".ppsx", ".ppsm", ".pps", ".potx", ".potm", ".pot", ".odp", ".fodp", ".otp", ".rtf", ".mht", ".html", ".htm", ".xml", ".epub", ".fb2");

    const EXTS_CELL = array(
        ".xls", ".xlsx", ".xlsm",
        ".xlt", ".xltx", ".xltm",
        ".ods", ".fods", ".ots", ".csv"
    );

    const EXTS_SLIDE = array(
        ".pps", ".ppsx", ".ppsm",
        ".ppt", ".pptx", ".pptm",
        ".pot", ".potx", ".potm",
        ".odp", ".fodp", ".otp"
    );

    const EXTS_WORD = array(
        ".doc", ".docx", ".docm",
        ".dot", ".dotx", ".dotm",
        ".odt", ".fodt", ".ott", ".rtf", ".txt",
        ".html", ".htm", ".mht", ".xml",
        ".pdf", ".djvu", ".fb2", ".epub", ".xps", ".oxps"
    );

    public static function get_document_type($filename) {
        $ext = strtolower('.' . pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($ext, Onlyoffice_Plugin_Document_Helper::EXTS_WORD)) return "word";
        if (in_array($ext, Onlyoffice_Plugin_Document_Helper::EXTS_CELL)) return "cell";
        if (in_array($ext, Onlyoffice_Plugin_Document_Helper::EXTS_SLIDE)) return "slide";
        return "word";
    }

    public static function is_editable($filename) {
        $ext = strtolower('.' . pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, Onlyoffice_Plugin_Document_Helper::DOC_SERV_EDITED);
    }

    public static function is_openable($filename) {
        $ext = strtolower('.' . pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, array_merge(self::EXTS_WORD, self::EXTS_SLIDE, self::EXTS_CELL));
    }

    public static function all_formats() {
        return array_merge(self::EXTS_WORD, self::EXTS_SLIDE, self::EXTS_CELL);
    }

    public static function get_mime_type($filename){
        $mime = wp_check_filetype($filename);

        if ($mime['type'] === false && function_exists('mime_content_type')) {
            $mime['type'] = mime_content_type($filename);
        }

       return $mime['type'] === false ? 'application/octet-stream' : $mime['type'];
    }
}
