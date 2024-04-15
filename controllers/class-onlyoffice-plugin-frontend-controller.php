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
				'description'     => __( 'Add ONLYOFFICE Docs editor on page', 'onlyoffice-plugin' ),
				'render_callback' => array( $this, 'onlyoffice_block_render_callback' ),
			),
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				'onlyoffice-wordpress-onlyoffice-editor-script',
				'onlyoffice-plugin',
				plugin_dir_path( ONLYOFFICE_PLUGIN_FILE ) . 'languages/'
			);
		}
	}

	/**
	 * Callback function for rendering the onlyoffice-wordpress-block.
	 *
	 * @param array $block_attributes List of attributes that where included in the block settings.
	 * @return string Resulting HTML code for the table.
	 */
	public function onlyoffice_block_render_callback( array $block_attributes ) {
		if ( ! array_key_exists( 'id', $block_attributes ) || '' === $block_attributes['id'] ) {
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
		++$instance;

		$defaults_atts = array(
			'id'             => '',
			'fileName'       => '',
			'documentView'   => 'embedded',
			'inNewTab'       => true,
			'align'          => '',
			'width'          => '100%',
			'height'         => '500px',
			'showOpenButton' => 'true',
			'openButtonText' => __( 'Open in ONLYOFFICE', 'onlyoffice-plugin' ),
		);

		$atts = shortcode_atts( $defaults_atts, $attr, 'onlyoffice' );

		if ( empty( $atts['width'] ) ) {
			$atts['width'] = $defaults_atts['width'];
		}

		if ( empty( $atts['height'] ) ) {
			$atts['height'] = $defaults_atts['height'];
		}

		wp_enqueue_style(
			'wp-block-onlyoffice-wordpress-styles',
			ONLYOFFICE_PLUGIN_URL . 'onlyoffice-wordpress-block/src/styles.css',
			array(),
			ONLYOFFICE_PLUGIN_VERSION
		);

		if ( 'link' === $atts['documentView'] ) {
			return $this->render_link( $atts, $instance );
		}

		return $this->render_embedded( $atts, $instance );
	}

	/**
	 * ONLYOFFICE Embedded Template.
	 *
	 * @param array $atts List of attributes that where included in the Shortcode.
	 * @param int   $instance Element number on page.
	 * @return string Link Template.
	 */
	private function render_embedded( $atts, $instance ) {
		add_action(
			'wp_enqueue_scripts',
			function () {
				$api_js_url = Onlyoffice_Plugin_Url_Manager::get_api_js_url();
				wp_enqueue_script( 'onlyoffice_editor_api', $api_js_url, array(), ONLYOFFICE_PLUGIN_VERSION, false );
				wp_enqueue_script(
					'wp-block-onlyoffice-wordpress-js',
					ONLYOFFICE_PLUGIN_URL . 'controllers/js/frontend-controller.js',
					array( 'wp-util', 'wp-i18n' ),
					ONLYOFFICE_PLUGIN_VERSION,
					false
				);
				wp_enqueue_style(
					'wp-block-onlyoffice-wordpress-css',
					ONLYOFFICE_PLUGIN_URL . 'controllers/css/frontend-controller.css',
					array(),
					ONLYOFFICE_PLUGIN_VERSION
				);

				if ( function_exists( 'wp_set_script_translations' ) ) {
					wp_set_script_translations(
						'wp-block-onlyoffice-wordpress-js',
						'onlyoffice-plugin',
						plugin_dir_path( ONLYOFFICE_PLUGIN_FILE ) . 'languages/'
					);
				}
			}
		);

		$attachment_id = $atts['id'];
		$type          = 'embedded';
		$mode          = 'view';
		$perm_edit     = null;
		$callback      = null;
		$filepath      = get_attached_file( $attachment_id );
		$filename      = wp_basename( $filepath );

		if ( Onlyoffice_Plugin_Document_Manager::is_fillable( $filename ) ) {
			$type      = 'desktop';
			$mode      = 'edit';
			$perm_edit = true;
			$callback  = Onlyoffice_Plugin_Url_Manager::get_callback_url( $attachment_id, true );
		}

		$config = Onlyoffice_Plugin_Config_Manager::get_config( $attachment_id, $type, $mode, $perm_edit, $callback, null, true );

		$align = ! empty( $atts['align'] ) ? 'align' . $atts['align'] : '';
		$size  = ! empty( $atts['width'] ) && ! ( 'full' === $atts['align'] ) ? 'width: ' . $atts['width'] . ';' : '';
		$size .= ! empty( $atts['height'] ) ? 'height: ' . $atts['height'] . ';' : '';

		$output  = '<div class="wp-block-onlyoffice-wordpress-onlyoffice ' . $align . ' size-full" style="' . $size . '">';
		$output .= '<div id="editorOnlyoffice-' . $instance . '" data-config="' . htmlspecialchars( wp_json_encode( $config ), ENT_QUOTES, 'UTF-8' ) . '" data-instance="' . $instance . '" class="wp-block-onlyoffice-wordpress-onlyoffice__target" ></div>';
		$output .= '</div>';

		return apply_filters( 'wp_onlyoffice_shortcode', $output, $atts );
	}

	/**
	 * ONLYOFFICE Link Template.
	 *
	 * @param array $atts List of attributes that where included in the Shortcode.
	 * @param int   $instance Element number on page.
	 * @return string Link Template.
	 */
	private function render_link( $atts, $instance ) {
		$target = true === $atts['inNewTab'] ? 'target="_blank"' : '';
		$align  = ! empty( $atts['align'] ) ? 'align' . $atts['align'] : '';

		$output  = '<div class="wp-block-onlyoffice-wordpress-onlyoffice ' . $align . '">';
		$output .= '<a id="linkToOnlyofficeEditor-' . $instance . '" href="' . Onlyoffice_Plugin_Url_Manager::get_editor_url( $atts['id'] ) . '" ' . $target . '>' . $atts['fileName'] . '</a>';

		if ( $atts['showOpenButton'] ) {
			$output .= '<a href="' . Onlyoffice_Plugin_Url_Manager::get_editor_url( $atts['id'] ) . '" ' . $target . ' class="wp-block-onlyoffice-wordpress__button wp-element-button">' . $atts['openButtonText'] . '</a>';
		}

		$output .= '</div>';

		return apply_filters( 'wp_onlyoffice_shortcode', $output, $atts );
	}

	/**
	 *  ONLYOFFICE error template.
	 *
	 * @return void
	 */
	public function onlyoffice_error_template() {
		?>
		<script type="text/html" id="tmpl-onlyoffice-error">
			<div class="onlyoffice-error" >
				<div class="onlyoffice-error-body">
					<div class="onlyoffice-error-table js">
						<div>
							<img src="<?php echo esc_url( ONLYOFFICE_PLUGIN_URL . 'controllers/images/onlyoffice.svg' ); ?>" style="width: 50%" />
						</div>
						<div style="padding: 16px;">
							<img src="<?php echo esc_url( ONLYOFFICE_PLUGIN_URL . 'controllers/images/error.svg' ); ?>" style="width: 100%"/>
						</div>
						<div>{{{data.email}}}</div>
					</div>
				</div>
			</div>
		</script>
		<?php
	}
}
