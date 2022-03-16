<?php

defined('ABSPATH') || exit;

function onlyoffice_wordpress_block()
{
    register_block_type(__DIR__);

    if (function_exists('wp_set_script_translations')) {
        wp_set_script_translations('onlyoffice-wordpress', 'onlyoffice-plugin');
    }
}

add_action('init', 'onlyoffice_wordpress_block');
