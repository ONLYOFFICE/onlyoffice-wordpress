<?php

class OOP_i18n
{

    public function load_plugin_textdomain()
    {

        load_plugin_textdomain(
            'onlyoffice-plugin',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}
