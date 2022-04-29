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

class Onlyoffice_Plugin_Admin
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
    {
        wp_enqueue_script($this->plugin_name . '-media-script', plugin_dir_url(__FILE__) . 'js/onlyoffice-admin-media.js', array('jquery'),
            $this->version, true);

        wp_localize_script($this->plugin_name . '-media-script', 'oo_media', array(
            'getEditorUrl' => get_option('permalink_structure') === '' ? get_option('siteurl') . '/index.php?rest_route=/onlyoffice/editorurl/'
                : get_option('siteurl') . '/wp-json/onlyoffice/editorurl/',
            'formats' => Onlyoffice_Plugin_Document_Helper::all_formats()
        ));
    }
}
