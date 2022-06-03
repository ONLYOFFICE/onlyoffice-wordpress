<?php
/**
 *
 * (c) Copyright Ascensio System SIA 2022
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
 *
 */

function mce_onlyoffice_button($buttons) {
	array_push($buttons, 'onlyoffice-tinymce');
	return $buttons;
}
add_filter('mce_buttons', 'mce_onlyoffice_button');

function mce_onlyoffice_js($plugin_array) {
	$plugin_array["onlyoffice-tinymce"] = plugins_url("/onlyoffice-tinymce.js", __FILE__);
	return $plugin_array;
}
add_filter("mce_external_plugins", "mce_onlyoffice_js");

function mce_onlyoffice_css() {
	wp_enqueue_style("onlyoffice-tinymce", plugins_url("/onlyoffice-tinymce.css", __FILE__));
}
add_action("admin_enqueue_scripts", "mce_onlyoffice_css");
add_action("wp_enqueue_scripts", "mce_onlyoffice_css");
