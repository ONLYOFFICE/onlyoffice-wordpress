<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes
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
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Onlyoffice_Plugin_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->version     = ONLYOFFICE_PLUGIN_VERSION;
		$this->plugin_name = ONLYOFFICE_PLUGIN_NAME;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->init_plugin();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		/**
		 * Load dependencies managed by composer.
		 */
		require_once plugin_dir_path( __DIR__ ) . 'vendor/autoload.php';

		require_once plugin_dir_path( __DIR__ ) . 'admin/class-onlyoffice-plugin-admin.php';
		require_once plugin_dir_path( __DIR__ ) . 'controllers/class-onlyoffice-plugin-frontend-controller.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-onlyoffice-plugin-i18n.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/class-onlyoffice-plugin-loader.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/files/class-onlyoffice-plugin-files.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/files/class-onlyoffice-plugin-files-list-table.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/managers/class-onlyoffice-plugin-callback-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/managers/class-onlyoffice-plugin-config-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/managers/class-onlyoffice-plugin-document-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/managers/class-onlyoffice-plugin-jwt-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/managers/class-onlyoffice-plugin-url-manager.php';
		require_once plugin_dir_path( __DIR__ ) . 'includes/settings/class-onlyoffice-plugin-settings.php';
		require_once plugin_dir_path( __DIR__ ) . 'onlyoffice-tinymce/onlyoffice-tinymce.php';
		require_once plugin_dir_path( __DIR__ ) . 'public/class-onlyoffice-plugin-public.php';

		$this->loader = new Onlyoffice_Plugin_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Onlyoffice_Plugin_I18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {
		$plugin_i18n = new Onlyoffice_Plugin_I18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		$plugin_admin = new Onlyoffice_Plugin_Admin();

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Onlyoffice_Plugin_Public();

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'rest_api_init', $plugin_public, 'register_routes' );
	}

	/**
	 * Init plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function init_plugin() {
		$plugin_files = new Onlyoffice_Plugin_Files();
		$this->loader->add_action( 'admin_menu', $plugin_files, 'init_menu' );

		$plugin_settings = new Onlyoffice_Plugin_Settings();
		$this->loader->add_action( 'admin_menu', $plugin_settings, 'init_menu' );
		$this->loader->add_action( 'network_admin_menu', $plugin_settings, 'init_menu' );
		$this->loader->add_action( 'admin_init', $plugin_settings, 'init' );

		$plugin_frontend_controller = new Onlyoffice_Plugin_Frontend_Controller();
		$this->loader->add_action( 'init', $plugin_frontend_controller, 'init_shortcodes' );
		$this->loader->add_action( 'init', $plugin_frontend_controller, 'onlyoffice_custom_block' );
		$this->loader->add_action( 'wp_footer', $plugin_frontend_controller, 'onlyoffice_error_template', 30 );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Onlyoffice_Plugin_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
