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

class Onlyoffice_Plugin_Public
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

        $editor = new Onlyoffice_Plugin_Editor();

        // "oo."-prefix is needed to keep the connector working in conjunction with the plugin "Force Lowercase URLs"
        // (https://wordpress.org/plugins/force-lowercase-urls/)

        register_rest_route('onlyoffice', '/oo.editor/(?P<id>[^\/\n\r]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($editor, 'editor'),
            'permission_callback' => array($editor, 'check_attachment_id'),
        ));

        register_rest_route('onlyoffice', '/oo.callback/(?P<id>[^\/\n\r]+)', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($editor, 'callback'),
            'permission_callback' => array($editor, 'check_attachment_id'),
        ));

        register_rest_route('onlyoffice', '/oo.getfile/(?P<id>[^\/\n\r]+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($editor, 'get_file'),
            'permission_callback' => array($editor, 'check_attachment_id'),
        ));

        register_rest_route('onlyoffice', '/oo.editorurl/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($editor, 'get_onlyoffice_editor_url')
        ));
    }
}
