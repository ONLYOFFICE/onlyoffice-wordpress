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

use Firebase\JWT\JWT;

class Onlyoffice_Plugin_JWT_Manager
{
    public static function is_jwt_enabled()
    {
        $options = get_option('onlyoffice_settings');
        return !empty($options[Onlyoffice_Plugin_Settings::docserver_jwt]);
    }

    public static function jwt_encode($payload, $secret)
    {
        return JWT::encode($payload, $secret);
    }

    public static function jwt_decode($token, $secret, $for_callback = false)
    {
        if (!self::is_jwt_enabled() && !$for_callback) return "";

        return JWT::decode($token, $secret, array('HS256'));
    }
}
