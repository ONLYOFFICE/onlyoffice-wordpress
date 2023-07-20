<?php
/**
 * The editor route.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public/views
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
 * The editor route.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public/views
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Editor {

	/**
	 * Check availability api.js.
	 *
	 * @param string $url      The array of request data. All arguments are optional and may be empty.
	 *
	 * @return WP_REST_Response
	 */
	public function check_api_js( $url ) {
		$response = wp_remote_get( $url );
		if ( wp_remote_retrieve_response_code( $response ) !== 200 ) {
			return false;
		}
		return true;
	}
	/**
	 * Add scripts api.js on front end.
	 */
	public function add_onlyoffice_api_js() {
		add_action(
			'wp_enqueue_scripts',
			function () {
				$api_js_url = Onlyoffice_Plugin_Url_Manager::get_api_js_url();
				wp_enqueue_script( 'onlyoffice_editor_api', $api_js_url, array(), ONLYOFFICE_PLUGIN_VERSION, false );
			}
		);
	}

	/**
	 * Editor.
	 *
	 * @param array $req The request.
	 * @return bool
	 */
	public function editor( $req ) {
		$go_back_url = ! empty( $_SERVER['HTTP_REFERER'] ) && str_contains( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), get_option( 'siteurl' ) )
		&& str_contains( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), 'onlyoffice-files' ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) :
			get_option( 'siteurl' ) . '/wp-admin/admin.php?page=onlyoffice-files';
		$response    = new WP_REST_Response( $this->editor_render( $req->get_params(), $go_back_url ) );
		$response->header( 'Content-Type', 'text/html; charset=utf-8' );
		return $response;
	}

	/**
	 * Editor render
	 *
	 * @param array  $params The parameters.
	 * @param string $go_back_url Go back url.
	 */
	public function editor_render( $params, $go_back_url ) {
		$api_js_url = Onlyoffice_Plugin_Url_Manager::get_api_js_url();

		ob_start();
		$api_js_status = $this->check_api_js( $api_js_url );
		ob_clean();

		if ( ! $api_js_status ) {
			wp_die( esc_attr_e( 'ONLYOFFICE cannot be reached. Please contact admin', 'onlyoffice-plugin' ) );
		}

		$attachment_id = intval( Onlyoffice_Plugin_Url_Manager::decode_openssl_data( $params['id'] ) );
		$filepath      = get_attached_file( $attachment_id );
		$filename      = wp_basename( $filepath );
		$type          = 'desktop';
		$mode          = 'view';
		$has_edit_cap  = Onlyoffice_Plugin_Document_Manager::has_edit_capability( $attachment_id );
		$edit_perm     = $has_edit_cap && ( Onlyoffice_Plugin_Document_Manager::is_editable( $filename ) || Onlyoffice_Plugin_Document_Manager::is_fillable( $filename ) );

		if ( $edit_perm ) {
			$mode         = 'edit';
			$callback_url = Onlyoffice_Plugin_Url_Manager::get_callback_url( $attachment_id, false );
		}

		$config = Onlyoffice_Plugin_Config_Manager::get_config( $attachment_id, $type, $mode, $edit_perm, $callback_url, $go_back_url, false );

		$this->add_onlyoffice_api_js();

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?> class="no-js">
		<?php wp_head(); ?>

		<head>
			<title><?php echo esc_html( $config['document']['title'] . ' - ONLYOFFICE' ); ?></title>
			<meta http-equiv="X-UA-Compatible" content="IE=edge">
			<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui" />
			<meta name="apple-mobile-web-app-capable" content="yes" />
			<meta name="mobile-web-app-capable" content="yes" />

			<style>
				html {
					height: 100%;
					width: 100%;
				}

				body {
					background: #fff;
					color: #333;
					font-family: Arial, Tahoma, sans-serif;
					font-size: 12px;
					font-weight: normal;
					height: 100%!important;
					margin: 0;
					overflow-y: hidden;
					padding: 0;
					text-decoration: none;
				}

				form {
					height: 100%;
				}

				div {
					margin: 0;
					padding: 0;
				}
			</style>

		</head>

		<body <?php body_class(); ?>>
			<div id="iframeEditor"></div>

			<script type="text/javascript">
				var docEditor;

				var connectEditor = function() {
					var config = <?php echo wp_json_encode( $config ); ?>;

					config.width = "100%";
					config.height = "100%";

					if (/android|avantgo|playbook|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\|plucker|pocket|psp|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i
						.test(navigator.userAgent)) {
						config.type='mobile';
					}

					docEditor = new DocsAPI.DocEditor("iframeEditor", config);
				};

				if (window.addEventListener) {
					window.addEventListener("load", connectEditor);
				} else if (window.attachEvent) {
					window.attachEvent("load", connectEditor);
				}
			</script>
		</body>

		</html>
		<?php
	}

}
