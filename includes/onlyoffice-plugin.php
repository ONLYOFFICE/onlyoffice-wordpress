<?php

class OOPlugin
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
        $this->init_settings();
    }

    private function load_dependencies()
    {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-i18n.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/onlyoffice-admin.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'public/onlyoffice-public.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-settings.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-document-helper.php';

        $this->loader = new OOP_Loader();
    }

    private function set_locale()
    {

        $plugin_i18n = new OOP_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    private function define_admin_hooks()
    {

        $plugin_admin = new OOP_Admin($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
    }

    private function define_public_hooks()
    {

        $plugin_public = new OOP_Public($this->get_plugin_name(), $this->get_version());

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        $this->loader->add_action('rest_api_init', $plugin_public, 'register_routes');
    }

    private function init_settings()
    {

        $plugin_settings = new OOP_Settings($this->get_plugin_name(), $this->get_version());

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
