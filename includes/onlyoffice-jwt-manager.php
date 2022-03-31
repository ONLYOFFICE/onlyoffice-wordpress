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

use Firebase\JWT\JWT;

class OOP_JWT_Manager
{
    public static function is_jwt_enabled()
    {
        $options = get_option('onlyoffice_settings');
        return !empty($options[OOP_Settings::docserver_jwt]);
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
