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

class OOP_JWT_Manager
{
    public static function is_jwt_enabled()
    {
        $options = get_option('onlyoffice_settings');
        return !empty($options[OOP_Settings::docserver_jwt]);
    }

    public static function jwt_encode($payload, $secret)
    {
        $header = [
            "alg" => "HS256",
            "typ" => "JWT"
        ];
        $enc_header = self::base64_url_encode(json_encode($header));
        $enc_payload = self::base64_url_encode(json_encode($payload));
        $hash = self::base64_url_encode(self::calculate_hash($enc_header, $enc_payload, $secret));

        return "$enc_header.$enc_payload.$hash";
    }

    public static function jwt_decode($token, $secret, $for_callback = false)
    {
        if (!self::is_jwt_enabled() && !$for_callback) return "";

        $split = explode(".", $token);
        if (count($split) != 3) return "";

        $hash = self::base64_url_encode(self::calculate_hash($split[0], $split[1], $secret));

        if (strcmp($hash, $split[2]) != 0) return "";
        return self::base64_url_decode($split[1]);
    }

    public static function calculate_hash($enc_header, $enc_payload, $secret)
    {
        return hash_hmac("sha256", "$enc_header.$enc_payload", $secret, true);
    }

    public static function base64_url_encode($str)
    {
        return str_replace("/", "_", str_replace("+", "-", trim(base64_encode($str), "=")));
    }

    public static function base64_url_decode($payload)
    {
        $b64 = str_replace("_", "/", str_replace("-", "+", $payload));
        switch (strlen($b64) % 4) {
            case 2:
                $b64 = $b64 . "==";
                break;
            case 3:
                $b64 = $b64 . "=";
                break;
        }
        return base64_decode($b64);
    }
}
