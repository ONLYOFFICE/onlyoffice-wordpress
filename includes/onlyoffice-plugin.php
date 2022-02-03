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
    }

    private function load_dependencies()
    {

        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/onlyoffice-loader.php';
        $this->loader = new OOP_Loader();
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
