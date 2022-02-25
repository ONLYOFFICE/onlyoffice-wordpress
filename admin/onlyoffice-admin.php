<?php

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

        wp_enqueue_script($this->plugin_name.'-media-script', plugin_dir_url(__FILE__) . 'js/onlyoffice-admin-media.js', array('jquery'), $this->version, true);
        wp_localize_script($this->plugin_name.'-media-script', 'oo_media', array(
            'nonce' => wp_create_nonce('wp_rest'),
            'permalinkStructure' => get_option('permalink_structure'),
            'editable' => OOP_Document_Helper::DOC_SERV_EDITED,
            'openable' => array_merge(OOP_Document_Helper::EXTS_CELL, OOP_Document_Helper::EXTS_SLIDE, OOP_Document_Helper::EXTS_WORD),
            'localization' => [
                'edit' => __('Edit in ONLYOFFICE', 'onlyoffice-plugin'),
                'open' => __('Open in ONLYOFFICE', 'onlyoffice-plugin')
            ]
        ));
    }
}
