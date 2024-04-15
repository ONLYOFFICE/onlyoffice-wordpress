<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public
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
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/public
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Public {

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
	}

	/**
	 * Register the routes.
	 *
	 * @since    1.0.0
	 */
	public function register_routes() {
		require_once plugin_dir_path( __FILE__ ) . 'views/class-onlyoffice-plugin-callback.php';
		require_once plugin_dir_path( __FILE__ ) . 'views/class-onlyoffice-plugin-download.php';

		$callback = new Onlyoffice_Plugin_Callback();
		$download = new Onlyoffice_Plugin_Download();

		// "oo."-prefix is needed to keep the connector working in conjunction with the plugin "Force Lowercase URLs"
		// (https://wordpress.org/plugins/force-lowercase-urls/)

		register_rest_route(
			'onlyoffice',
			'/oo.callback/(?P<id>[^\/\n\r]+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $callback, 'callback' ),
				'permission_callback' => array( $this, 'check_attachment_id' ),
			)
		);

		register_rest_route(
			'onlyoffice',
			'/oo.callback-public-forms/(?P<id>[^\/\n\r]+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $callback, 'callback_public_forms' ),
				'permission_callback' => array( $this, 'check_attachment_id' ),
			)
		);

		register_rest_route(
			'onlyoffice',
			'/oo.getfile/(?P<id>[^\/\n\r]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $download, 'get_file' ),
				'permission_callback' => array( $this, 'check_attachment_id' ),
			)
		);
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
			wp_die( esc_attr_e( 'Post is not an attachment', 'onlyoffice-plugin' ) );
		}

		return true;
	}
}
