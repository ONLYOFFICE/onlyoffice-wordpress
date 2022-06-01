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

class Onlyoffice_Plugin
{

    protected $loader;
    protected $plugin_name;
    protected $version;
    protected $settings;

    public function __construct()
    {

        $this->version = ONLYOFFICE_PLUGIN_VERSION;
        $this->plugin_name = 'onlyoffice-plugin';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
        $this->init_plugin();
    }

    private function load_dependencies()
    {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/onlyoffice-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/onlyoffice-public.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-settings.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-document-helper.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-callback-helper.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-jwt-manager.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-files.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-class-files-list-table.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'onlyoffice-tinymce/onlyoffice-tinymce.php';
        require_once plugin_dir_path(dirname(__FILE__)) . '3rdparty/JWT.php';
        require_once plugin_dir_path(dirname(__FILE__)) . '3rdparty/BeforeValidException.php';
        require_once plugin_dir_path(dirname(__FILE__)) . '3rdparty/ExpiredException.php';
        require_once plugin_dir_path(dirname(__FILE__)) . '3rdparty/SignatureInvalidException.php';

        $this->loader = new Onlyoffice_Plugin_Loader();
    }

    private function set_locale()
    {

        $plugin_i18n = new Onlyoffice_Plugin_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks()
    {

        $plugin_admin = new Onlyoffice_Plugin_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    private function define_public_hooks()
    {

        $plugin_public = new Onlyoffice_Plugin_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        $this->loader->add_action('rest_api_init', $plugin_public, 'register_routes');
    }

    private function init_plugin()
    {
        $plugin_files = new Onlyoffice_Plugin_Files();
        $this->loader->add_action('admin_menu', $plugin_files, 'init_menu');

        $plugin_settings = new Onlyoffice_Plugin_Settings();
        $this->loader->add_action('admin_menu', $plugin_settings, 'init_menu');
        $this->loader->add_action('admin_init', $plugin_settings, 'init');
    }

    public function run()
    {
        $this->loader->run();
    }

    public function get_plugin_name()
    {
        return $this->plugin_name;
    }

    public function get_loader()
    {
        return $this->loader;
    }

    public function get_version()
    {
        return $this->version;
    }
}
