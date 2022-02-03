<?php

class OOP_Public
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

        //wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/onlyoffice-public.css', array(), $this->version, 'all');
    }


    public function enqueue_scripts()
    {

        //wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/onlyoffice-public.js', array('jquery'), $this->version, false);
    }

    public function register_routes()
    {
        require_once plugin_dir_path( __FILE__ ) . 'views/editor.php';

        $editor = new OOP_Editor();

        register_rest_route('onlyoffice', '/editor/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array($editor, 'editor'),
            'permission_callback' => array($editor, 'check_attachment_id'),
        ));

        register_rest_route('onlyoffice', '/callback/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array($editor, 'callback'),
            'permission_callback' => array($editor, 'check_attachment_id'),
        ));
    } 
}
