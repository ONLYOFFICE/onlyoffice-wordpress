<?php
/**
 * The editor routes.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public/views
 */

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
 */

/**
 * The editor routes.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public/views
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Editor {

	const EDIT_CAPS = array(
		'edit_others_pages',
		'edit_others_posts',
		'edit_pages',
		'edit_posts',
		'edit_private_pages',
		'edit_private_posts',
		'edit_published_pages',
		'edit_published_posts',
	);

	const CALLBACK_STATUS = array(
		0 => 'NotFound',
		1 => 'Editing',
		2 => 'MustSave',
		3 => 'Corrupted',
		4 => 'Closed',
		6 => 'MustForceSave',
		7 => 'CorruptedForceSave',
	);

	/**
	 * Return editor url.
	 *
	 * @param array $req      The array of request data. All arguments are optional and may be empty.
	 * @return WP_REST_Response
	 */
	public function get_onlyoffice_editor_url( $req ) {
		$attachment_id = $req->get_params()['id'];

		$response = new WP_REST_Response();
		$response->set_data(
			array(
				'url' => Onlyoffice_Plugin_Url_Manager::get_editor_url( $attachment_id ),
			)
		);

		return $response;
	}

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
				$options    = get_option( 'onlyoffice_settings' );
				$api_js_url = Onlyoffice_Plugin_Url_Manager::get_api_js_url();
				wp_enqueue_script( 'onlyoffice_editor_api', $api_js_url, array(), ONLYOFFICE_PLUGIN_VERSION, false );
			}
		);
	}

	/**
	 * Decode openssl data.
	 *
	 * @param string $data The data.
	 * @param string $passphrase The password.
	 * @return false|string
	 */
	public function decode_openssl_data( $data, $passphrase ) {
		$iv = hex2bin( get_option( 'onlyoffice-plugin-bytes' ) );
		return openssl_decrypt( $data, 'aes-256-ctr', $passphrase, $options = 0, $iv );
	}

	/**
	 * Check valid attachment id.
	 *
	 * @param array $req The request.
	 * @return bool
	 */
	public function check_attachment_id( $req ) {
		$decoded       = Onlyoffice_Plugin_Url_Manager::decode_openssl_data( $req->get_params()['id'] );
		$attachemnt_id = str_starts_with( $decoded, '{' ) ? json_decode( $decoded )->attachment_id : intval( $decoded );
		$post          = get_post( $attachemnt_id );

		if ( null === $post || 'attachment' !== $post->post_type ) {
			wp_die( __( 'Post is not an attachment', 'onlyoffice-plugin' ) );
		}

		return true;
	}

	/**
	 * Editor.
	 *
	 * @param array $req The request.
	 * @return bool
	 */
	public function editor( $req ) {
		$go_back_url             = ! empty( $_SERVER['HTTP_REFERER'] ) && str_contains( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), get_option( 'siteurl' ) )
		&& str_contains( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), 'onlyoffice-files' ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ) :
			get_option( 'siteurl' ) . '/wp-admin/admin.php?page=onlyoffice-files';
		$opened_from_admin_panel = str_contains( $req->get_headers()['referer'][0], 'wp-admin/admin.php' );
		$response                = new WP_REST_Response( $this->editor_render( $req->get_params(), $opened_from_admin_panel, $go_back_url ) );
		$response->header( 'Content-Type', 'text/html; charset=utf-8' );
		return $response;
	}

	/**
	 * Returns true if user can edit attachment.
	 *
	 * @param string $attachment_id The request.
	 * @return bool
	 */
	public function has_edit_capability( $attachment_id ) {
		$has_edit_cap = false;
		foreach ( self::EDIT_CAPS as $capability ) {
			$has_edit_cap = $has_edit_cap || current_user_can( $capability, $attachment_id );
		}
		return $has_edit_cap;
	}

	/**
	 * Editor render
	 *
	 * @param array  $params The parameters.
	 * @param bool   $opened_from_admin_panel Opened from admin panel.
	 * @param string $go_back_url Go back url.
	 */
	public function editor_render( $params, $opened_from_admin_panel, $go_back_url ) {
		$api_js_url = Onlyoffice_Plugin_Url_Manager::get_api_js_url();

		ob_start();
		$api_js_status = $this->check_api_js( $api_js_url );
		ob_clean();

		if ( ! $api_js_status ) {
			wp_die( __( 'ONLYOFFICE cannot be reached. Please contact admin', 'onlyoffice-plugin' ) );
		}

		$attachemnt_id = intval( Onlyoffice_Plugin_Url_Manager::decode_openssl_data( $params['id'] ) );
		$post          = get_post( $attachemnt_id );

		$author = get_user_by( 'id', $post->post_author )->display_name;
		$user   = wp_get_current_user();

		$filepath = get_attached_file( $attachemnt_id );
		$filetype = strtolower( pathinfo( $filepath, PATHINFO_EXTENSION ) );
		$filename = wp_basename( $filepath );

		$has_edit_cap = $this->has_edit_capability( $attachemnt_id );

		$can_edit = $has_edit_cap && Onlyoffice_Plugin_Document_Manager::is_editable( $filename );

		$callback_url = Onlyoffice_Plugin_Url_Manager::get_callback_url( $attachemnt_id );
		$file_url     = Onlyoffice_Plugin_Url_Manager::get_download_url( $attachemnt_id );

		$lang   = $opened_from_admin_panel ? get_user_locale( $user->ID ) : get_locale();
		$config = array(
			'type'         => $opened_from_admin_panel ? 'desktop' : 'embedded',
			'documentType' => Onlyoffice_Plugin_Document_Manager::get_document_type( $filename ),
			'document'     => array(
				'title'       => $filename,
				'url'         => $file_url,
				'fileType'    => $filetype,
				'key'         => base64_encode( $post->post_modified ) . $attachemnt_id,
				'info'        => array(
					'owner'    => $author,
					'uploaded' => $post->post_date,
				),
				'permissions' => array(
					'download' => true,
					'edit'     => $can_edit,
				),
			),
			'editorConfig' => array(
				'mode'        => $can_edit && $opened_from_admin_panel ? 'edit' : 'view',
				'lang'        => str_contains( $lang, '_' ) ? explode( '_', $lang )[0] : $lang,
				'callbackUrl' => $callback_url,
			),
		);

		if ( $opened_from_admin_panel ) {
			$config['editorConfig']['customization']['goback'] = array(
				'url' => $go_back_url,
			);
			add_action(
				'onlyoffice_wordpress_editor_favicon',
				function ( $doctype ) {
					?>
					<link rel="shortcut icon" href="<?php echo esc_url( plugins_url( 'images/' . $doctype . '.ico', dirname( __FILE__ ) ) ); ?>" type="image/vnd.microsoft.icon" />
					<?php
				}
			);
			do_action( 'onlyoffice_wordpress_editor_favicon', $config['documentType'] );
		}
		if ( 0 !== $user->ID ) {
			$config['editorConfig']['user'] = array(
				'id'   => (string) $user->ID,
				'name' => $user->display_name,
			);
		}

		if ( Onlyoffice_Plugin_JWT_Manager::is_jwt_enabled() ) {
			$options         = get_option( 'onlyoffice_settings' );
			$secret          = $options[ Onlyoffice_Plugin_Settings::DOCSERVER_JWT ];
			$config['token'] = Onlyoffice_Plugin_JWT_Manager::jwt_encode( $config, $secret );
		}
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

	/**
	 * Get file.
	 *
	 * @param array $req The request.
	 *
	 * @return void
	 */
	public function get_file( $req ) {
		$decoded = json_decode( Onlyoffice_Plugin_Url_Manager::decode_openssl_data( $req->get_params()['id'] ) );

		$attachment_id = $decoded->attachment_id;
		$user_id       = $decoded->user_id;

		if ( 0 !== $user_id ) {
			$user = get_user_by( 'id', $user_id );
			if ( ( null !== $user_id ) && $user ) {
				wp_set_current_user( $user_id, $user->user_login );
				wp_set_auth_cookie( $user_id );
				do_action( 'wp_login', $user->user_login );
			} else {
				wp_die( 'No user information', '', array( 'response' => 403 ) );
			}

			$has_read_capability = current_user_can( 'read' );
			if ( ! $has_read_capability ) {
				wp_die( 'No read capability', '', array( 'response' => 403 ) );
			}
		}
		if ( Onlyoffice_Plugin_JWT_Manager::is_jwt_enabled() ) {
			$jwt_header = 'Authorization';
			if ( ! empty( apache_request_headers()[ $jwt_header ] ) ) {
				$options = get_option( 'onlyoffice_settings' );
				$secret  = $options[ Onlyoffice_Plugin_Settings::DOCSERVER_JWT ];
				$token   = Onlyoffice_Plugin_JWT_Manager::jwt_decode( substr( apache_request_headers()[ $jwt_header ], strlen( 'Bearer ' ) ), $secret );
				if ( empty( $token ) ) {
					wp_die( 'Invalid JWT signature', '', array( 'response' => 403 ) );
				}
			}
		}

		if ( ob_get_level() ) {
			ob_end_clean();
		}

		$filepath = get_attached_file( $attachment_id );

		@header( 'Content-Length: ' . filesize( $filepath ) );
		@header( 'Content-Disposition: attachment; filename*=UTF-8\'\'' . urldecode( basename( $filepath ) ) );
		@header( 'Content-Type: ' . mime_content_type( $filepath ) );

		if ( $fd = fopen( $filepath, 'rb' ) ) {
			while ( ! feof( $fd ) ) {
				print fread( $fd, 1024 );
			}
			fclose( $fd );
		}
		exit;
	}

	/**
	 * Callback
	 *
	 * @param array $req Request.
	 *
	 * @return WP_REST_Response
	 */
	public function callback( $req ) {
		require_once ABSPATH . 'wp-admin/includes/post.php';

		$response      = new WP_REST_Response();
		$response_json = array(
			'error' => 0,
		);

		$attachemnt_id = intval( Onlyoffice_Plugin_Url_Manager::decode_openssl_data( $req->get_params()['id'] ) );
		$body          = Onlyoffice_Plugin_Callback_Manager::read_body( $req->get_body() );
		if ( ! empty( $body['error'] ) ) {
			$response_json['message'] = $body['error'];
			$response->data           = $response_json;
			return $response;
		}

		wp_set_current_user( $body['actions'][0]['userid'] );

		$status = self::CALLBACK_STATUS[ $body['status'] ];

		$user_id = null;
		if ( ! empty( $body['users'] ) ) {
			$users = $body['users'];
			if ( count( $users ) > 0 ) {
				$user_id = $users[0];
			}
		}

		if ( null === $user_id && ! empty( $body['actions'] ) ) {
			$actions = $body['actions'];
			if ( count( $actions ) > 0 ) {
				$user_id = $actions[0]['userid'];
			}
		}

		$user = get_user_by( 'id', $user_id );
		if ( null !== $user_id && $user ) {
			wp_set_current_user( $user_id, $user->user_login );
			wp_set_auth_cookie( $user_id );
			do_action( 'wp_login', $user->user_login );
		} else {
			wp_die( 'No user information', '', array( 'response' => 403 ) );
		}

		switch ( $status ) {
			case 'Editing':
				break;
			case 'MustSave':
				$can_edit = $this->has_edit_capability( $attachemnt_id );
				if ( ! $can_edit ) {
					wp_die( 'No edit capability', '', array( 'response' => 403 ) );
				}

				$locked = wp_check_post_lock( $attachemnt_id );
				if ( ! $locked ) {
					wp_set_post_lock( $attachemnt_id );
				}

				$response_json['error'] = Onlyoffice_Plugin_Callback_Manager::proccess_save( $body, $attachemnt_id );
				break;
			case 'Corrupted':
			case 'Closed':
			case 'NotFound':
				delete_post_meta( $attachemnt_id, '_edit_lock' );
				break;
			case 'MustForceSave':
			case 'CorruptedForceSave':
				break;
		}

		$response->data = $response_json;

		return $response;
	}
}
