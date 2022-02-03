<?php

class OOPlugin
{

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct()
    {

        $this->version = ONLYOFFICE_PLUGIN_VERSION;
        $this->plugin_name = 'onlyoffice-plugin';

        $this->load_dependencies();
        $this->set_locale();
    }

    private function load_dependencies()
    {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-loader.php';
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-i18n.php';
        $this->loader = new OOP_Loader();
    }

    private function set_locale()
    {

        $plugin_i18n = new OOP_i18n();

        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

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
