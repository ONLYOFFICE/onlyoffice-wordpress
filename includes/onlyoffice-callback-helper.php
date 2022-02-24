<?php

class OOP_Callback_Helper
{

    public static function read_body($body) {
        $result["error"] = 0;

        $data = json_decode($body, TRUE);

        if ($data === NULL) {
            $result["error"] = "Callback data is null or empty";
            return $result;
        }

        if (OOP_JWT_Manager::is_jwt_enabled()) {
            $in_header = false;
            $jwt_header = "Authorization";
            $options = get_option('onlyoffice_settings');
            $secret = $options[OOP_Settings::docserver_jwt];

            if (!empty($data["token"])) {
                $token = OOP_JWT_Manager::jwt_decode($data["token"], $secret);
            } elseif (!empty(apache_request_headers()[$jwt_header])) {
                $token = OOP_JWT_Manager::jwt_decode(substr(apache_request_headers()[$jwt_header], strlen("Bearer ")), $secret);
                $in_header = true;
            } else {
                $result["error"] = "Expected JWT";
                return $result;
            }
            if (empty($token)) {
                $result["error"] = "Invalid JWT signature";
                return $result;
            }

            $data = json_decode($token, true);
            if ($in_header) $data = $data["payload"];
        }

        return $data;
    }
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
