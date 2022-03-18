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

class OOP_Admin
{

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    public function enqueue_styles()
    {

        //wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/onlyoffice-admin.css', array(), $this->version, 'all');
    }

    public function enqueue_scripts()
    {
        wp_enqueue_script($this->plugin_name . '-media-script', plugin_dir_url(__FILE__) . 'js/onlyoffice-admin-media.js', array('jquery'),
            $this->version, true);

        $wp_nonce = wp_create_nonce('wp_rest');
        wp_localize_script($this->plugin_name . '-media-script', 'oo_media', array(
            'nonce' => $wp_nonce,
            'getEditorUrl' => get_option('permalink_structure') === '' ? get_option('siteurl') . '/index.php?rest_route=/onlyoffice/editorurl/'
                : get_option('siteurl') . '/wp-json/onlyoffice/editorurl/'
        ));
    }
}
