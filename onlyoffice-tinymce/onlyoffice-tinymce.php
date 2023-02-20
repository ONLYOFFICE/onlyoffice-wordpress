<?php
/**
 * ONLYOFFICE scripts and styles for tinymce.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/onlyoffice-tinymce
 */

/**
 * The fanction add ONLYOFFICE button
 *
 * @param array $buttons Buttons.
 */
function mce_onlyoffice_button( $buttons ) {
	array_push( $buttons, 'onlyoffice-tinymce' );
	return $buttons;
}

/** The filter add ONLYOFFICE button*/
add_filter( 'mce_buttons', 'mce_onlyoffice_button' );

/**
 * The function add ONLYOFFICE scripts
 *
 * @param array $plugin_array Array plugins.
 */
function mce_onlyoffice_js( $plugin_array ) {
	$plugin_array['onlyoffice-tinymce'] = plugins_url( '/onlyoffice-tinymce.js', __FILE__ );
	return $plugin_array;
}
/** The filter add ONLYOFFICE scripts*/
add_filter( 'mce_external_plugins', 'mce_onlyoffice_js' );

/** The function add ONLYOFFICE styles*/
function mce_onlyoffice_css() {
	wp_enqueue_style( 'onlyoffice-tinymce', plugins_url( '/onlyoffice-tinymce.css', __FILE__ ), array(), ONLYOFFICE_PLUGIN_VERSION );
}

/** The action add ONLYOFFICE styles*/
add_action( 'admin_enqueue_scripts', 'mce_onlyoffice_css' );

/** The action add ONLYOFFICE styles*/
add_action( 'wp_enqueue_scripts', 'mce_onlyoffice_css' );
