<?php
/**
 *
 * (c) Copyright Ascensio System SIA 2022
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 */

class OOP_Document_Helper
{
    const DOC_SERV_VIEWD = array(".pdf", ".djvu", ".xps", ".oxps");
    const DOC_SERV_EDITED = array(".docx", ".xlsx", ".pptx",);
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

        if (in_array($ext, OOP_Document_Helper::EXTS_WORD)) return "word";
        if (in_array($ext, OOP_Document_Helper::EXTS_CELL)) return "cell";
        if (in_array($ext, OOP_Document_Helper::EXTS_SLIDE)) return "slide";
        return "word";
    }

    public static function is_editable($filename) {
        $ext = strtolower('.' . pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, OOP_Document_Helper::DOC_SERV_EDITED);
    }

    public static function is_openable($filename) {
        $ext = strtolower('.' . pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, array_merge(self::EXTS_WORD, self::EXTS_SLIDE, self::EXTS_CELL));
    }

    public static function all_formats() {
        return array_merge(self::EXTS_WORD, self::EXTS_SLIDE, self::EXTS_CELL);
    }
}
