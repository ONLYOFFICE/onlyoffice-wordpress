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
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of the plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

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
		require_once plugin_dir_path( __FILE__ ) . 'views/class-onlyoffice-plugin-editor.php';

		$editor = new Onlyoffice_Plugin_Editor();

		register_rest_route(
			'onlyoffice',
			'/editor/(?P<id>[^\/\n\r]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $editor, 'editor' ),
				'permission_callback' => array( $editor, 'check_attachment_id' ),
			)
		);

		register_rest_route(
			'onlyoffice',
			'/callback/(?P<id>[^\/\n\r]+)',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $editor, 'callback' ),
				'permission_callback' => array( $editor, 'check_attachment_id' ),
			)
		);

		register_rest_route(
			'onlyoffice',
			'/getfile/(?P<id>[^\/\n\r]+)',
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $editor, 'get_file' ),
				'permission_callback' => array( $editor, 'check_attachment_id' ),
			)
		);

		register_rest_route(
			'onlyoffice',
			'/editorurl/(?P<id>\d+)',
			array(
				'methods'  => WP_REST_Server::READABLE,
				'callback' => array( $editor, 'get_onlyoffice_editor_url' ),
			)
		);
	}
}
