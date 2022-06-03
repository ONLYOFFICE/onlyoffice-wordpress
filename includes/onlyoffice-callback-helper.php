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

class Onlyoffice_Plugin_Callback_Helper
{
    public static function proccess_save($body, $attachemnt_id)
    {
        $download_url = $body["url"];
        if ($download_url === null) {
            return 1;
        }

        $new_data = file_get_contents($download_url);
        if ($new_data === null) return 1;

        $filepath = get_attached_file($attachemnt_id);
        file_put_contents($filepath, $new_data, LOCK_EX);
        $id = wp_update_post(array('id' => $attachemnt_id, 'file' => 'file'));

        if ($id === 0) return 1;

        return 0;
    }
}
