<?php
/**
 * Controller init ONLYOFFICE Editor.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      2.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/controllers
 */

/**
 *
 * (c) Copyright Ascensio System SIA 2023
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
 * Controller init ONLYOFFICE Editor.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/controllers
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Frontend_Controller {

	/**
	 * Register ONLYOFFICE Shortcodes.
	 */
	public function init_shortcodes() {
		add_shortcode( 'onlyoffice', array( $this, 'wp_onlyoffice_shortcode' ) );
	}

	/**
	 * Register the onlyoffice-wordpress-block and its dependencies.
	 */
	public function onlyoffice_custom_block() {
		register_block_type(
			__DIR__ . '/../onlyoffice-wordpress-block',
			array(
				'description'     => __( 'Add ONLYOFFICE editor on page', 'onlyoffice-plugin' ),
				'render_callback' => array( $this, 'onlyoffice_block_render_callback' ),
			),
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations( 'onlyoffice-plugin', 'onlyoffice-plugin' );
		}
	}

	/**
	 * Callback function for rendering the onlyoffice-wordpress-block.
	 *
	 * @param array $block_attributes List of attributes that where included in the block settings.
	 * @return string Resulting HTML code for the table.
	 */
	public function onlyoffice_block_render_callback( array $block_attributes ) {
		if ( '' === $block_attributes['id'] ) {
			return;
		}

		return $this->wp_onlyoffice_shortcode( $block_attributes );
	}

	/**
	 * Handle Shortcode [onlyoffice /].
	 *
	 * @param array $attr List of attributes that where included in the Shortcode.
	 * @return string Resulting HTML code.
	 */
	public function wp_onlyoffice_shortcode( $attr ) {
		static $instance = 0;
		$instance++;

		$defaults_atts = array(
			'id'       => '',
			'fileName' => '',
		);

		$atts = shortcode_atts( $defaults_atts, $attr, 'onlyoffice' );

		add_action(
			'wp_enqueue_scripts',
			function () {
				$api_js_url = Onlyoffice_Plugin_Url_Manager::get_api_js_url();
				wp_enqueue_script( 'onlyoffice_editor_api', $api_js_url, array(), ONLYOFFICE_PLUGIN_VERSION, false );
			}
		);

		$attachment_id = $atts['id'];
		$type          = 'embedded';
		$mode          = 'view';
		$filepath      = get_attached_file( $attachment_id );
		$filename      = wp_basename( $filepath );

		if ( Onlyoffice_Plugin_Document_Manager::is_fillable( $filename ) ) {
			$type      = 'desktop';
			$mode      = 'edit';
			$perm_edit = true;
			$callback  = Onlyoffice_Plugin_Url_Manager::get_callback_url( $attachment_id, true );
		}

		$config = Onlyoffice_Plugin_Config_Manager::get_config( $attachment_id, $type, $mode, $perm_edit, $callback, null );

		$output  = '<div style="height: 650px; maxWidth: inherit, padding: 20px">';
		$output .= '<div id="editorOnlyoffice-' . $instance . '"></div>';
		$output .= '<script type="text/javascript">new DocsAPI.DocEditor("editorOnlyoffice-' . $instance . '", ' . wp_json_encode( $config ) . '); </script>';
		$output .= '</div>';

		return apply_filters( 'wp_onlyoffice_shortcode', $output, $atts );
	}

}
