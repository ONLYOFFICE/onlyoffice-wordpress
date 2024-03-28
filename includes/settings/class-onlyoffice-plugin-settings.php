<?php
/**
 * Plugin settings for this site.
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/settings
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
 * Plugin settings for this site.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/settings
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Settings {
	/**
	 * ID setting docserver_url.
	 */
	const DOCSERVER_URL = 'onlyoffice_settings_docserver_url';

	/**
	 * ID setting docserver_jwt.
	 */
	const DOCSERVER_JWT = 'onlyoffice_settings_docserver_jwt';

	/**
	 * ID setting jwt_header.
	 */
	const JWT_HEADER = 'onlyoffice_settings_jwt_header';

	/**
	 * Init menu.
	 *
	 * @return void
	 */
	public function init_menu() {
		$logo_svg            = file_get_contents( plugin_dir_path( plugin_dir_path( __DIR__ ) ) . '/public/images/logo.svg' );
		$can_manage_settings = current_user_can( 'manage_options' );
		$can_upload_files    = current_user_can( 'upload_files' );

		if ( $can_manage_settings && ! $can_upload_files ) {
			add_menu_page(
				__( 'ONLYOFFICE Docs', 'onlyoffice-plugin' ),
				'ONLYOFFICE Docs',
				'manage_options',
				'onlyoffice-settings',
				array( $this, 'options_page' ),
				'data:image/svg+xml;base64,' . base64_encode( $logo_svg ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			);
		}
		if ( $can_manage_settings && $can_upload_files ) {
			add_submenu_page(
				'onlyoffice-files',
				__( 'ONLYOFFICE Docs Settings', 'onlyoffice-plugin' ),
				__( 'Settings', 'onlyoffice-plugin' ),
				'manage_options',
				'onlyoffice-settings',
				array( $this, 'options_page' )
			);
		}
	}

	/**
	 * Init.
	 *
	 * @return void
	 */
	public function init() {
		register_setting( 'onlyoffice_settings_group', 'onlyoffice_settings' );

		add_settings_section(
			'onlyoffice_settings_general_section',
			'',
			array( $this, 'general_section_callback' ),
			'onlyoffice_settings_group'
		);

		add_settings_field(
			'onlyoffice_settings_docserver_url',
			__( 'Document Editing Service address', 'onlyoffice-plugin' ),
			array( $this, 'input_cb' ),
			'onlyoffice_settings_group',
			'onlyoffice_settings_general_section',
			array(
				'label_for' => self::DOCSERVER_URL,
				'desc'      => '',
			)
		);

		add_settings_field(
			'onlyoffice_settings_docserver_jwt',
			__( 'Document server JWT secret key', 'onlyoffice-plugin' ),
			array( $this, 'input_cb' ),
			'onlyoffice_settings_group',
			'onlyoffice_settings_general_section',
			array(
				'label_for' => self::DOCSERVER_JWT,
				'desc'      => __( 'Secret key (leave blank to disable)', 'onlyoffice-plugin' ),
			)
		);

		add_settings_field(
			'onlyoffice_settings_jwt_header',
			__( 'Authorization header', 'onlyoffice-plugin' ),
			array( $this, 'input_cb' ),
			'onlyoffice_settings_group',
			'onlyoffice_settings_general_section',
			array(
				'label_for' => self::JWT_HEADER,
				'desc'      => __( 'Leave blank to use default header', 'onlyoffice-plugin' ),
			)
		);
	}

	/**
	 * Input cb
	 *
	 * @param array $args Args.
	 *
	 * @return void
	 */
	public function input_cb( array $args ) {
		?>
		<input id="<?php echo esc_attr( $args['label_for'] ); ?>" type="text" class="regular-text" name="onlyoffice_settings[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $this->get_onlyoffice_setting( $args['label_for'] ) ); ?>">
		<p class="description">
			<?php echo esc_attr( $args['desc'] ); ?>
		</p>
		<?php
	}

	/**
	 * General section callback.
	 *
	 * @param array $args Args.
	 *
	 * @return void
	 */
	public function general_section_callback( $args ) {
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Configure ONLYOFFICE Docs plugin settings', 'onlyoffice-plugin' ); ?></p>
		<?php
	}

	/**
	 * General section callback.
	 */
	public function options_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		wp_enqueue_style(
			'onlyoffice-settings',
			ONLYOFFICE_PLUGIN_URL . 'admin/css/banner/onlyoffice-cloud-banner.css',
			array(),
			ONLYOFFICE_PLUGIN_VERSION
		);

		if ( ! empty( $_GET['settings-updated'] ) && sanitize_key( $_GET['settings-updated'] ) === 'true' ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_settings_error( 'onlyoffice_settings_messages', 'onlyoffice_message', __( 'Settings Saved', 'onlyoffice-plugin' ), 'updated' ); // ToDo: can also check if settings are valid e.g. make connection to docServer.
		}

		settings_errors( 'onlyoffice_settings_messages' );
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="options.php" method="post">
				<?php
				settings_fields( 'onlyoffice_settings_group' );
				do_settings_sections( 'onlyoffice_settings_group' );
				submit_button( __( 'Save Settings', 'onlyoffice-plugin' ) );
				?>
			</form>
			<div id="onlyoffice-cloud-banner">
				<div class="onlyoffice-cloud-banner-info">
					<img src="<?php echo esc_url( ONLYOFFICE_PLUGIN_URL . 'admin/images/banner/logo.svg' ); ?>">
					<div class="info">
						<h3><?php esc_html_e( 'ONLYOFFICE Docs Cloud', 'onlyoffice-plugin' ); ?></h3>
						<p><?php esc_html_e( 'Easily launch the editors in the cloud without downloading and installation', 'onlyoffice-plugin' ); ?></p>
					</div>
				</div>
				<div class="onlyoffice-cloud-banner-buttons">
					<a class="onlyoffice-cloud-banner-button"  href="https://www.onlyoffice.com/docs-registration.aspx?referer=wordpress" target="_blank"><?php esc_html_e( 'Get Now', 'onlyoffice-plugin' ); ?></a>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Return ONLYOFFICE  Setting
	 *
	 * @param string $key Setting key.
	 * @param string $def Default value.
	 */
	public static function get_onlyoffice_setting( $key, $def = '' ) {
		$options = get_option( 'onlyoffice_settings' );
		if ( ! empty( $options ) && array_key_exists( $key, $options ) ) {
			return $options[ $key ];
		}

		return $def;
	}
}
