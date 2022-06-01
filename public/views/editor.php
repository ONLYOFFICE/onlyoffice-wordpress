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

class Onlyoffice_Plugin_Editor
{
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

    const CALLBACK_STATUS = array(
        0 => 'NotFound',
        1 => 'Editing',
        2 => 'MustSave',
        3 => 'Corrupted',
        4 => 'Closed',
        6 => 'MustForceSave',
        7 => 'CorruptedForceSave'
    );

    function get_onlyoffice_editor_url($req) {
        $permalink_structure = get_option('permalink_structure');
        $response = new WP_REST_Response();
        $attachment_id = $req->get_params()['id'];
        $passphrase = get_option("onlyoffice-plugin-uuid");
        $hidden_id = urlencode($this->encode_openssl_data($attachment_id, $passphrase));
        $hidden_id = str_replace('%', ',', $hidden_id);

        $editor_url = $permalink_structure === '' ? get_option('siteurl') . '/index.php?rest_route=/onlyoffice/editor/' . $hidden_id
            : get_option('siteurl') . '/wp-json/onlyoffice/editor/' . $hidden_id;
        $response->set_data(array(
                'url' => $editor_url
        ));
        return $response;
    }

    function check_api_js($url) {
        $response = wp_remote_get($url);
        if (wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }
        return true;
    }

    function add_onlyoffice_api_js() {
        add_action( 'wp_enqueue_scripts', function () {
            $options = get_option('onlyoffice_settings');
            $api_js_url = $options[Onlyoffice_Plugin_Settings::docserver_url] .
                (substr($options[Onlyoffice_Plugin_Settings::docserver_url] , -1) === '/' ? '' : '/') . 'web-apps/apps/api/documents/api.js';
            wp_enqueue_script('onlyoffice_editor_api', $api_js_url, array());
        });
    }

    function encode_openssl_data($data, $passphrase) {
        $iv = hex2bin(get_option("onlyoffice-plugin-bytes"));
        return openssl_encrypt($data, 'aes-256-ctr', $passphrase, $options=0, $iv);
    }

    function decode_openssl_data($data, $passphrase) {
        $iv = hex2bin(get_option("onlyoffice-plugin-bytes"));
        return openssl_decrypt($data, 'aes-256-ctr', $passphrase, $options=0, $iv);
    }

    function check_attachment_id($req)
    {
        $attachemnt_param = urldecode(str_replace(',', '%', $req->get_params()['id']));
        $passphrase = get_option("onlyoffice-plugin-uuid");
        $decoded = $this->decode_openssl_data($attachemnt_param, $passphrase);
        $attachemnt_id = substr($decoded, 0, 1) !== '{' ? intval($decoded) : json_decode($decoded)->attachment_id;
        $post = get_post($attachemnt_id);

        if ($post == null || $post->post_type != 'attachment') {
            wp_die(__('Post is not an attachment', 'onlyoffice-plugin'));
        }

        return true;
    }

    function editor($req)
    {
        $go_back_url = !empty($_SERVER['HTTP_REFERER']) && str_contains(sanitize_url($_SERVER['HTTP_REFERER']), get_option('siteurl'))
        && str_contains(sanitize_url($_SERVER['HTTP_REFERER']), 'onlyoffice-files') ? sanitize_url($_SERVER['HTTP_REFERER']) :
            get_option('siteurl') . '/wp-admin/admin.php?page=onlyoffice-files';
        $opened_from_admin_panel = str_contains($req->get_headers()['referer'][0], 'wp-admin/admin.php');
        $response = new WP_REST_Response($this->editor_render($req->get_params(), $opened_from_admin_panel, $go_back_url));
        $response->header('Content-Type', 'text/html; charset=utf-8');
        return $response;
    }

    function has_edit_capability($attachment_id) {
        $has_edit_cap = false;
        foreach (self::EDIT_CAPS as $capability) {
            $has_edit_cap = $has_edit_cap || current_user_can($capability, $attachment_id);
        }
        return $has_edit_cap;
    }

    function editor_render($params, $opened_from_admin_panel, $go_back_url)
    {
        $options = get_option('onlyoffice_settings');
        $api_js_url = $options[Onlyoffice_Plugin_Settings::docserver_url] .
            (substr($options[Onlyoffice_Plugin_Settings::docserver_url] , -1) === '/' ? '' : '/') . 'web-apps/apps/api/documents/api.js';
        ob_start();
        $api_js_status = $this->check_api_js($api_js_url);
        ob_clean();
        if (!$api_js_status) wp_die(__('ONLYOFFICE cannot be reached. Please contact admin', 'onlyoffice-plugin'));

        $passphrase = get_option("onlyoffice-plugin-uuid");
        $attachemnt_id = intval($this->decode_openssl_data(urldecode(str_replace(',', '%', $params['id'])), $passphrase));
        $post = get_post($attachemnt_id);

        $author = get_user_by('id', $post->post_author)->display_name;
        $user = wp_get_current_user();

        $filepath = get_attached_file($attachemnt_id);
        $filetype = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $filename = wp_basename($filepath);

        $has_edit_cap = $this->has_edit_capability($attachemnt_id);

        $can_edit = $has_edit_cap && Onlyoffice_Plugin_Document_Helper::is_editable($filename);

        $permalink_structure = get_option('permalink_structure');
        $hidden_id = str_replace('%', ',', urlencode($this->encode_openssl_data($attachemnt_id, $passphrase)));
        $hidden_data = str_replace('%', ',', urlencode($this->encode_openssl_data(json_encode(["attachment_id" => $attachemnt_id, 'user_id' => $user->ID]), $passphrase)));

        $callback_url = $permalink_structure === '' ? get_option('siteurl') . '/index.php?rest_route=/onlyoffice/callback/' . $hidden_id
            : get_option('siteurl') . '/wp-json/onlyoffice/callback/' . $hidden_id;

        $file_url = $permalink_structure === '' ? get_option('siteurl') . '/index.php?rest_route=/onlyoffice/getfile/' . $hidden_data
            : get_option('siteurl') . '/wp-json/onlyoffice/getfile/' . $hidden_data;

        $lang = $opened_from_admin_panel ? get_user_locale($user->ID) : get_locale();
        $config = [
            "type" => $opened_from_admin_panel ? 'desktop' : 'embedded',
            "documentType" => Onlyoffice_Plugin_Document_Helper::get_document_type($filename),
            "document" => [
                "title" => $filename,
                "url" => $file_url,
                "fileType" => $filetype,
                "key" => base64_encode($post->post_modified) . $attachemnt_id,
                "info" => [
                    "owner" => $author,
                    "uploaded" => $post->post_date
                ],
                "permissions" => [
                    "download" => true,
                    "edit" => $can_edit
                ]
            ],
            "editorConfig" => [
                "mode" => $can_edit && $opened_from_admin_panel ? 'edit' : 'view',
                "lang" => str_contains($lang, '_') ? explode('_', $lang)[0] : $lang,
                "callbackUrl" =>  $callback_url
            ]
        ];

        if ($opened_from_admin_panel) {
            $config['editorConfig']['customization']['goback'] = array(
                    'url' => $go_back_url
            );
            add_action('onlyoffice_wordpress_editor_favicon', function ($doctype) {
                ?>
                    <link rel="shortcut icon" href="<?php echo esc_url(plugins_url('images/' . $doctype . '.ico', dirname(__FILE__) )); ?>" type="image/vnd.microsoft.icon" />
                <?php
            });
            do_action('onlyoffice_wordpress_editor_favicon', $config['documentType']);
        }
        if ($user->ID !== 0) {
            $config['editorConfig']["user"] =  array(
                "id" => (string)$user->ID,
                "name" => $user->display_name
            );
        }

        if (Onlyoffice_Plugin_JWT_Manager::is_jwt_enabled()) {
            $secret = $options[Onlyoffice_Plugin_Settings::docserver_jwt];
            $config["token"] = Onlyoffice_Plugin_JWT_Manager::jwt_encode($config, $secret);
        }
        $this->add_onlyoffice_api_js();

?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?> class="no-js">
        <?php wp_head(); ?>

        <head>
            <title><?php echo esc_html($config['document']['title'] . ' - ONLYOFFICE'); ?></title>
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui" />
            <meta name="apple-mobile-web-app-capable" content="yes" />
            <meta name="mobile-web-app-capable" content="yes" />

            <style>
                html {
                    height: 100%;
                    width: 100%;
                }

                body {
                    background: #fff;
                    color: #333;
                    font-family: Arial, Tahoma, sans-serif;
                    font-size: 12px;
                    font-weight: normal;
                    height: 100%!important;
                    margin: 0;
                    overflow-y: hidden;
                    padding: 0;
                    text-decoration: none;
                }

                form {
                    height: 100%;
                }

                div {
                    margin: 0;
                    padding: 0;
                }
            </style>

        </head>

        <body <?php body_class(); ?>>
            <div id="iframeEditor"></div>

            <script type="text/javascript">
                var docEditor;

                var connectEditor = function() {
                    var config = <?php echo wp_json_encode($config) ?>;

                    config.width = "100%";
                    config.height = "100%";

                    if (/android|avantgo|playbook|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\|plucker|pocket|psp|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i
                        .test(navigator.userAgent)) {
                        config.type='mobile';
                    }

                    docEditor = new DocsAPI.DocEditor("iframeEditor", config);
                };

                if (window.addEventListener) {
                    window.addEventListener("load", connectEditor);
                } else if (window.attachEvent) {
                    window.attachEvent("load", connectEditor);
                }
            </script>
        </body>

        </html>
<?php
    }

    function get_file($req) {
        $param = urldecode(str_replace(',', '%', $req->get_params()['id']));
        $decoded = json_decode($this->decode_openssl_data($param, get_option("onlyoffice-plugin-uuid")));

        $attachment_id = $decoded->attachment_id;
        $user_id = $decoded->user_id;

        if ($user_id !== 0) {
            $user = get_user_by('id', $user_id);
            if ($user_id !== null && $user) {
                wp_set_current_user($user_id, $user->user_login);
                wp_set_auth_cookie($user_id);
                do_action('wp_login', $user->user_login);
            } else {
                wp_die("No user information", '', array('response' => 403));
            }

            $has_read_capability = current_user_can('read');
            if (!$has_read_capability) wp_die('No read capability', '', array('response' => 403));
        }
        if (Onlyoffice_Plugin_JWT_Manager::is_jwt_enabled()) {
            $jwt_header = "Authorization";
            if (!empty(apache_request_headers()[$jwt_header])) {
                $options = get_option('onlyoffice_settings');
                $secret = $options[Onlyoffice_Plugin_Settings::docserver_jwt];
                $token = Onlyoffice_Plugin_JWT_Manager::jwt_decode(substr(apache_request_headers()[$jwt_header], strlen("Bearer ")), $secret);
                if (empty($token)) {
                    wp_die("Invalid JWT signature", '', array('response' => 403));
                }
            }
        }

        if (ob_get_level()) {
            ob_end_clean();
        }

        $filepath = get_attached_file($attachment_id);

        @header('Content-Length: ' . filesize($filepath));
        @header('Content-Disposition: attachment; filename*=UTF-8\'\'' . urldecode(basename($filepath)));
        @header('Content-Type: ' . mime_content_type($filepath));

        if ($fd = fopen($filepath, 'rb')) {
            while (!feof($fd)) {
                print fread($fd, 1024);
            }
            fclose($fd);
        }
        exit;
    }

    function callback($req)
    {
        require_once ABSPATH . 'wp-admin/includes/post.php';

        $response = new WP_REST_Response();
        $response_json = array(
            'error' => 0
        );

        $param = urldecode(str_replace(',', '%', $req->get_params()['id']));
        $attachemnt_id = intval($this->decode_openssl_data($param, get_option("onlyoffice-plugin-uuid")));
        $body = Onlyoffice_Plugin_Callback_Helper::read_body($req->get_body());
        if (!empty($body["error"])){
            $response_json["message"] = $body["error"];
            $response->data = $response_json;
            return $response;
        }

        wp_set_current_user($body["actions"][0]["userid"]);

        $status = Onlyoffice_Plugin_Editor::CALLBACK_STATUS[$body["status"]];

        $user_id = null;
        if (!empty($body['users'])) {
            $users = $body['users'];
            if (count($users) > 0) {
                $user_id = $users[0];
            }
        }

        if ($user_id === null && !empty($body['actions'])) {
            $actions = $body['actions'];
            if (count($actions) > 0) {
                $user_id = $actions[0]['userid'];
            }
        }

        $user = get_user_by( 'id', $user_id );
        if ($user_id !== null && $user) {
            wp_set_current_user( $user_id, $user->user_login );
            wp_set_auth_cookie( $user_id );
            do_action( 'wp_login', $user->user_login );
        } else {
            wp_die("No user information", '', array('response' => 403));
        }

        switch ($status) {
            case "Editing":
                break;
            case "MustSave":
                $can_edit = $this->has_edit_capability($attachemnt_id);
                if (!$can_edit) wp_die("No edit capability", '', array('response' => 403));

                $locked = wp_check_post_lock($attachemnt_id);
                if (!$locked) wp_set_post_lock($attachemnt_id);

                $response_json['error'] = Onlyoffice_Plugin_Callback_Helper::proccess_save($body, $attachemnt_id);
                break;
            case "Corrupted":
            case "Closed":
            case "NotFound":
                delete_post_meta($attachemnt_id, '_edit_lock');
                break;
            case "MustForceSave":
            case "CorruptedForceSave":
                break;
        }

        $response->data = $response_json;

        return $response;
    }
}
