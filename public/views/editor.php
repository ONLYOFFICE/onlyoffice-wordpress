<?php

class OOP_Editor
{
    const CALLBACK_STATUS = array(
        0 => 'NotFound',
        1 => 'Editing',
        2 => 'MustSave',
        3 => 'Corrupted',
        4 => 'Closed',
        6 => 'MustForceSave',
        7 => 'CorruptedForceSave'
    );

    function check_attachment_id($req)
    {
        $attachemnt_id = $req->get_params()['id'];
        $post = get_post($attachemnt_id);

        if ($post->post_type != 'attachment') {
            wp_die(__('Post is not an attachment', 'onlyoffice-plugin'));
        }

        return true;
    }

    function editor($req)
    {
        $response = new WP_REST_Response($this->editor_render($req->get_params()));
        $response->header('Content-Type', 'text/html; charset=utf-8');
        return $response;
    }

    function editor_render($params)
    {
        $options = get_option('onlyoffice_settings');
        $attachemnt_id = $params['id'];

        $post = get_post($attachemnt_id);

        $author = get_user_by('id', $post->post_author)->display_name;
        $user = wp_get_current_user();

        $filepath = get_attached_file($attachemnt_id);
        $filetype = strtolower(pathinfo($filepath, PATHINFO_EXTENSION));
        $filename = pathinfo($filepath, PATHINFO_FILENAME) . '.' . $filetype;

        $can_edit = current_user_can('edit_post', $attachemnt_id) && OOP_Document_Helper::is_editable($filename);

        $config = [
            "type" => 'desktop',
            "documentType" => OOP_Document_Helper::get_document_type($filename),
            "document" => [
                "title" => $filename,
                "url" => wp_get_attachment_url($attachemnt_id),
                "fileType" => $filetype,
                "key" => base64_encode($post->post_modified),
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
                "mode" => $can_edit ? 'edit' : 'view',
                "lang" => 'en',
                "callbackUrl" => get_option('siteurl') . '/wp-json/onlyoffice/callback/' . $attachemnt_id, // ToDo: hide attachment id
                "user" => [
                    "id" => $user->ID,
                    "name" => $user->display_name
                ]
            ]
        ];

        // ToDo: JWT
?>
        <!DOCTYPE html>
        <html <?php language_attributes(); ?> class="no-js">

        <head>
            <title><?php echo 'doctitle' . ' ‹ ' . wp_get_document_title(); ?></title>
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui" />
            <meta name="apple-mobile-web-app-capable" content="yes" />
            <meta name="mobile-web-app-capable" content="yes" />
            <link rel="icon" href="/wp-content/plugins/onlyoffice-wordpress/public/images/<?php echo 'cell' ?>.ico" type="image/x-icon" />

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
                    height: 100%;
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

            <script type="text/javascript" src="<?php echo $options[OOP_Settings::docserver_url] . 'web-apps/apps/api/documents/api.js' ?>"></script>
        </head>

        <body <?php body_class(); ?>>
            <div id="iframeEditor"></div>

            <script type="text/javascript">
                var docEditor;

                var connectEditor = function() {
                    var config = <?php echo json_encode($config) ?>;

                    config.width = "100%";
                    config.height = "100%";

                    if ((config.document.fileType === "docxf" || config.document.fileType === "oform") &&
                        DocsAPI.DocEditor.version().split(".")[0] < 7) {
                        innerAlert("<?php __('Please update ONLYOFFICE Docs to version 7.0 to work on fillable forms online.', 'onlyoffice-plugin') ?>");
                        return;
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

    function callback($req)
    {
        $response = new WP_REST_Response();
        $response_json = array(
            'error' => 0
        );

        $attachemnt_id = $req->get_params()['id'];

        $body = json_decode($req->get_body(), true); // ToDo: JWT
        // ToDo: check if null etc

        wp_set_current_user($body["actions"][0]["userid"]);

        $status = OOP_Editor::CALLBACK_STATUS[$body["status"]];

        switch ($status) {
            case "Editing":
                // ToDo: wp_set_post_lock() wp_check_post_lock() ? 
                break;
            case "MustSave":
            case "Corrupted":
                $response_json['error'] = $this->proccess_save($body, $attachemnt_id);
                break;
            case "MustForceSave":
            case "CorruptedForceSave":
                break;
        }

        $response->data = $response_json;

        return $response;
    }

    function proccess_save($body, $attachemnt_id)
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
