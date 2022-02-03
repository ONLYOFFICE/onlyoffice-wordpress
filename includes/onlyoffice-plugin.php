<?php

class OOPlugin
{

    protected $plugin_name;
    protected $version;

    public function __construct()
    {

        $this->version = ONLYOFFICE_PLUGIN_VERSION;
        $this->plugin_name = 'onlyoffice-plugin';
    }

    public function run()
    {
    }
}
