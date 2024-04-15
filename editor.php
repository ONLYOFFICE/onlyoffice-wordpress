<?php
/**
 * ONLYOFFICE Docs Editor Page.
 *
 * @package Onlyoffice_Plugin
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

if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) ) {
	$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
	require_once $parse_uri[0] . 'wp-load.php';
} else {
	require_once '../../../wp-load.php';
}

// phpcs:disable WordPress.Security.NonceVerification

if ( ! isset( $_GET['attachment_id'] ) ) {
	status_header( 404 );
	nocache_headers();
	include get_query_template( '404' );
	die();
}

$attachment_id = sanitize_text_field( wp_unslash( $_GET['attachment_id'] ) );
$attachment    = get_post( $attachment_id );

// phpcs:enable WordPress.Security.NonceVerification

if ( ! $attachment || 'attachment' !== $attachment->post_type ) {
	status_header( 404 );
	nocache_headers();
	include get_query_template( '404' );
	die();
}

if ( ! is_user_logged_in() ) {
	if ( ! Onlyoffice_Plugin_Document_Manager::can_anonymous_user_view_attachment( $attachment_id ) ) {
		$redirect_to = '';

		if ( isset( $_SERVER['REQUEST_URI'] ) ) {
			$redirect_to = esc_url( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
		}

		$login_url = wp_login_url( $redirect_to );
		wp_safe_redirect( $login_url );
		exit;
	}
} elseif ( ! Onlyoffice_Plugin_Document_Manager::can_user_view_attachment( $attachment_id ) ) {
	wp_die(
		esc_attr_e( 'Sorry, you are not allowed to view this item.' ),
		403
	);
}

$filepath           = get_attached_file( $attachment_id );
$filename           = wp_basename( $filepath );
$editor_config_mode = 'view';
$has_edit_cap       = Onlyoffice_Plugin_Document_Manager::can_user_edit_attachment( $attachment_id );
$edit_perm          = $has_edit_cap && ( Onlyoffice_Plugin_Document_Manager::is_editable( $filename ) || Onlyoffice_Plugin_Document_Manager::is_fillable( $filename ) );
$callback_url       = null;
$go_back_url        = null;

if ( is_user_logged_in() ) {
	$go_back_url = ! empty( $_SERVER['HTTP_REFERER'] )
			&& str_contains( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), get_option( 'siteurl' ) )
			&& str_contains( esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) ), 'onlyoffice-files' )
			? esc_url_raw( wp_unslash( $_SERVER['HTTP_REFERER'] ) )
			: get_option( 'siteurl' ) . '/wp-admin/admin.php?page=onlyoffice-files';
}

if ( $edit_perm ) {
	$editor_config_mode = 'edit';
	$callback_url       = Onlyoffice_Plugin_Url_Manager::get_callback_url( $attachment_id, false );
}

$config = Onlyoffice_Plugin_Config_Manager::get_config( $attachment_id, 'desktop', $editor_config_mode, $edit_perm, $callback_url, $go_back_url, false );
?>

<!DOCTYPE html>
<html>
<head>
	<title><?php echo esc_html( $config['document']['title'] . ' - ONLYOFFICE' ); ?></title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1, user-scalable=no, minimal-ui" />
	<meta name="apple-mobile-web-app-capable" content="yes" />
	<meta name="mobile-web-app-capable" content="yes" />
	<?php // phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
	<script src="<?php echo esc_url( Onlyoffice_Plugin_Url_Manager::get_api_js_url() ); ?>"></script>
	<?php // phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript ?>
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

<body>
	<div id="iframeEditor"></div>

	<script type="text/javascript">
		var docEditor;

		var connectEditor = function() {
			if (typeof DocsAPI === "undefined") {
				alert("<?php esc_attr_e( 'ONLYOFFICE Docs cannot be reached. Please contact admin.', 'onlyoffice-plugin' ); ?>");
				return;
			}

			var config = <?php echo wp_json_encode( $config ); ?>;

			config.width = "100%";
			config.height = "100%";

			if (/android|avantgo|playbook|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od|ad)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\\|plucker|pocket|psp|symbian|treo|up\\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i
				.test(navigator.userAgent)) {
				config.type = "mobile";
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
