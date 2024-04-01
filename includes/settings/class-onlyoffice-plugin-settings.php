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
	const DOCSERVER_URL = 'docserver_url';

	/**
	 * ID setting docserver_jwt.
	 */
	const DOCSERVER_JWT = 'docserver_jwt';

	/**
	 * ID setting jwt_header.
	 */
	const JWT_HEADER = 'jwt_header';

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
			$hook = add_menu_page(
				__( 'ONLYOFFICE Docs', 'onlyoffice-plugin' ),
				'ONLYOFFICE Docs',
				'manage_options',
				'onlyoffice-settings',
				array( $this, 'options_page' ),
				'data:image/svg+xml;base64,' . base64_encode( $logo_svg ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			);
		}
		if ( $can_manage_settings && $can_upload_files ) {
			$hook = add_submenu_page(
				'onlyoffice-files',
				__( 'ONLYOFFICE Docs Settings', 'onlyoffice-plugin' ),
				__( 'Settings', 'onlyoffice-plugin' ),
				'manage_options',
				'onlyoffice-settings',
				array( $this, 'options_page' )
			);
		}

		if ( isset( $hook ) ) {
			add_action( "load-$hook", array( $this, 'update_plugin_settings' ) );
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
			self::DOCSERVER_URL,
			__( 'Document Editing Service address', 'onlyoffice-plugin' ),
			array( $this, 'input_text' ),
			'onlyoffice_settings_group',
			'onlyoffice_settings_general_section',
			array(
				'id'          => self::DOCSERVER_URL,
				'value'       => $this->get_onlyoffice_setting( self::DOCSERVER_URL ),
				'disabled'    => '',
				'description' => '',
			)
		);

		add_settings_field(
			self::DOCSERVER_JWT,
			__( 'Document server JWT secret key', 'onlyoffice-plugin' ),
			array( $this, 'input_text' ),
			'onlyoffice_settings_group',
			'onlyoffice_settings_general_section',
			array(
				'id'          => self::DOCSERVER_JWT,
				'value'       => $this->get_onlyoffice_setting( self::DOCSERVER_JWT ),
				'disabled'    => '',
				'description' => __( 'Secret key (leave blank to disable)', 'onlyoffice-plugin' ),
			)
		);

		add_settings_field(
			self::JWT_HEADER,
			__( 'Authorization header', 'onlyoffice-plugin' ),
			array( $this, 'input_text' ),
			'onlyoffice_settings_group',
			'onlyoffice_settings_general_section',
			array(
				'id'          => self::JWT_HEADER,
				'value'       => $this->get_onlyoffice_setting( self::JWT_HEADER ),
				'disabled'    => '',
				'description' => __( 'Secret key (leave blank to disable)', 'onlyoffice-plugin' ),
			)
		);
	}

	/**
	 * Input text
	 *
	 * @param array $args Args.
	 *
	 * @return void
	 */
	public function input_text( array $args ) {
		?>
		<input id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" value="<?php echo esc_attr( $args['value'] ); ?>" <?php disabled( $args['disabled'] ); ?> type="text" class="regular-text">
		<p class="description"><?php echo esc_attr( $args['description'] ); ?></p>
		<?php
	}

	/**
	 * Input checkbox
	 *
	 * @param array $args Args.
	 *
	 * @return void
	 */
	public function input_checkbox( array $args ) {
		?>
		<input id="<?php echo esc_attr( $args['id'] ); ?>" name="<?php echo esc_attr( $args['id'] ); ?>" <?php checked( $args['checked'] ); ?> <?php disabled( $args['disabled'] ); ?> type="checkbox" value="1">
		<label for="<?php echo esc_attr( $args['id'] ); ?>"><?php echo esc_attr( $args['description'] ); ?></label>
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
		<?php settings_errors(); ?>
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

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form action="admin.php?page=onlyoffice-settings" method="post">
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
	 * Update settings.
	 */
	public function update_plugin_settings() {
		switch ( $this->current_action() ) {
			case 'update':
				check_admin_referer( 'onlyoffice_settings_group-options' );

				if ( isset( $_POST[ self::DOCSERVER_URL ] ) ) {
					$docserver_url = sanitize_text_field( wp_unslash( $_POST[ self::DOCSERVER_URL ] ) );
				}

				if ( isset( $_POST[ self::DOCSERVER_JWT ] ) ) {
					$docserver_jwt = sanitize_text_field( wp_unslash( $_POST[ self::DOCSERVER_JWT ] ) );
				}

				if ( isset( $_POST[ self::JWT_HEADER ] ) ) {
					$jwt_header = sanitize_text_field( wp_unslash( $_POST[ self::JWT_HEADER ] ) );
				}

				$value = array(
					self::DOCSERVER_URL => $docserver_url,
					self::DOCSERVER_JWT => $docserver_jwt,
					self::JWT_HEADER    => $jwt_header,
				);

				update_option( 'onlyoffice_settings', $value );

				add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'success' );
				set_transient( 'settings_errors', get_settings_errors(), 30 );
				wp_safe_redirect( admin_url( 'admin.php?page=onlyoffice-settings&settings-updated=true' ) );

				exit;
		}
	}

	/**
	 * Return ONLYOFFICE Docs plugin Settings
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

	/**
	 * Return current actrion.
	 */
	private function current_action() {
		global $filter_action, $action;
		wp_reset_vars( array( 'filter_action', 'action' ) );

		if ( ! empty( $filter_action ) ) {
			return false;
		}

		if ( ! empty( $action ) && -1 !== $action ) {
			return $action;
		}

		return false;
	}
}
