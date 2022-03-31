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

class OOP_Public
{


    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles()
    {}


    public function enqueue_scripts()
    {}

    public function register_routes()
    {
        require_once plugin_dir_path( __FILE__ ) . 'views/editor.php';

        $editor = new OOP_Editor();

        register_rest_route('onlyoffice', '/editor/(?P<id>[^\/\n\r]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($editor, 'editor'),
            'permission_callback' => array($editor, 'check_attachment_id'),
        ));

        register_rest_route('onlyoffice', '/callback/(?P<id>[^\/\n\r]+)', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($editor, 'callback'),
            'permission_callback' => array($editor, 'check_attachment_id'),
        ));

        register_rest_route('onlyoffice', '/getfile/(?P<id>[^\/\n\r]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($editor, 'get_file'),
            'permission_callback' => array($editor, 'check_attachment_id'),
        ));

        register_rest_route('onlyoffice', '/editorurl/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($editor, 'get_onlyoffice_editor_url')
        ));
    }
}
