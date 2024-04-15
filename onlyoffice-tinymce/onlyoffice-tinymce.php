<?php
/**
 * ONLYOFFICE scripts and styles for tinymce.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/onlyoffice-tinymce
 */

/**
 *
 * (c) Copyright Ascensio System SIA 2024
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
