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
	 * ID setting allowed_owerwrite_network_settings.
	 */
	const ALLOWED_OWERWRITE_NETWORK_SETTINGS = 'onlyoffice_settings_allowed_owerwrite_network_settings';

	/**
	 * ID setting inherit_network_settings.
	 */
	const INHERIT_NETWORK_SETTINGS = 'onlyoffice_settings_inherit_network_settings';

	/**
	 * Init menu.
	 *
	 * @return void
	 */
	public function init_menu() {
		$logo_svg            = file_get_contents( plugin_dir_path( plugin_dir_path( __DIR__ ) ) . '/public/images/logo.svg' );
		$can_manage_settings = current_user_can( 'manage_options' );
		$can_upload_files    = current_user_can( 'upload_files' );
		$can_manage_network  = current_user_can( 'manage_network' );

		if ( $can_manage_settings ) {
			if ( $can_upload_files ) {
				$hook = add_submenu_page(
					'onlyoffice-files',
					__( 'ONLYOFFICE Docs Settings', 'onlyoffice-plugin' ),
					__( 'Settings', 'onlyoffice-plugin' ),
					'manage_options',
					'onlyoffice-settings',
					array( $this, 'options_page' )
				);
			} else {
				$hook = add_menu_page(
					__( 'ONLYOFFICE Docs Settings', 'onlyoffice-plugin' ),
					'ONLYOFFICE Docs',
					'manage_options',
					'onlyoffice-settings',
					array( $this, 'options_page' ),
					'data:image/svg+xml;base64,' . base64_encode( $logo_svg ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
				);
			}
		}

		if ( $can_manage_network && is_network_admin() ) {
			$hook = add_menu_page(
				__( 'ONLYOFFICE Docs Settings', 'onlyoffice-plugin' ),
				'ONLYOFFICE Docs',
				'manage_network',
				'onlyoffice-settings',
				array( $this, 'options_page' ),
				'data:image/svg+xml;base64,' . base64_encode( $logo_svg ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
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

		if ( is_multisite() ) {
			if ( ! is_network_admin() ) {
				add_settings_field(
					self::INHERIT_NETWORK_SETTINGS,
					__( 'Connection Settings', 'onlyoffice-plugin' ),
					array( $this, 'input_checkbox' ),
					'onlyoffice_settings_group',
					'onlyoffice_settings_general_section',
					array(
						'id'          => self::INHERIT_NETWORK_SETTINGS,
						'checked'     => self::inherit_network_settings(),
						'disabled'    => ! $this->allowed_owerwrite_network_settings(),
						'description' => __( 'Inherit Network Settings', 'onlyoffice-plugin' ),
					),
				);
			}
		}

		add_settings_field(
			self::DOCSERVER_URL,
			__( 'Document Editing Service address', 'onlyoffice-plugin' ),
			array( $this, 'input_text' ),
			'onlyoffice_settings_group',
			'onlyoffice_settings_general_section',
			array(
				'id'          => self::DOCSERVER_URL,
				'value'       => $this->get_onlyoffice_current_value_setting( self::DOCSERVER_URL ),
				'disabled'    => is_multisite() && ! is_network_admin() && ( ! $this->allowed_owerwrite_network_settings() || self::inherit_network_settings() ),
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
				'value'       => $this->get_onlyoffice_current_value_setting( self::DOCSERVER_JWT ),
				'disabled'    => is_multisite() && ! is_network_admin() && ( ! $this->allowed_owerwrite_network_settings() || self::inherit_network_settings() ),
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
				'value'       => $this->get_onlyoffice_current_value_setting( self::JWT_HEADER ),
				'disabled'    => is_multisite() && ! is_network_admin() && ( ! $this->allowed_owerwrite_network_settings() || self::inherit_network_settings() ),
				'description' => __( 'Leave blank to use default header', 'onlyoffice-plugin' ),
			)
		);

		if ( is_multisite() ) {
			if ( is_network_admin() ) {
				add_settings_field(
					self::ALLOWED_OWERWRITE_NETWORK_SETTINGS,
					'',
					array( $this, 'input_checkbox' ),
					'onlyoffice_settings_group',
					'onlyoffice_settings_general_section',
					array(
						'id'          => self::ALLOWED_OWERWRITE_NETWORK_SETTINGS,
						'checked'     => self::allowed_owerwrite_network_settings(),
						'disabled'    => '',
						'description' => __( 'Allow site administrators to configure plugin for their sites', 'onlyoffice-plugin' ),
					)
				);
			}
		}
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
		settings_errors();
		?>
		<p id="<?php echo esc_attr( $args['id'] ); ?>">
		<?php
		if ( is_multisite() ) {
			if ( is_network_admin() ) {
				esc_html_e( 'Configure ONLYOFFICE Docs plugin settings for the whole network.', 'onlyoffice-plugin' );
			} else {
				printf(
					wp_kses(
						/* translators: %s: Title WP Site. */
						__( 'Configure ONLYOFFICE Docs plugin settings for %s site.', 'onlyoffice-plugin' ),
						array(
							'strong' => array(
								'class' => array(),
							),
						),
					),
					'<a href="' . esc_attr( get_bloginfo( 'url' ) ) . '">' . esc_attr( get_bloginfo() ) . '</a>'
				);

				if ( ! $this->allowed_owerwrite_network_settings() ) {
					?>
					<div class="onlyoffice-settings-notice">
						<p>
							<?php esc_html_e( 'Blocked from changing settings by the Network administrator', 'onlyoffice-plugin' ); ?>
						</p>
					</div>
					<?php
				}
			}
		} else {
			?>
			<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Configure ONLYOFFICE Docs plugin settings.', 'onlyoffice-plugin' ); ?></p>
			<?php
		}
		?>
		</p>
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
			ONLYOFFICE_PLUGIN_NAME . '-settings',
			ONLYOFFICE_PLUGIN_URL . 'admin/css/settings.css',
			array(),
			ONLYOFFICE_PLUGIN_VERSION
		);

		wp_enqueue_style(
			ONLYOFFICE_PLUGIN_NAME . '-cloud-banner',
			ONLYOFFICE_PLUGIN_URL . 'admin/css/banner/onlyoffice-cloud-banner.css',
			array(),
			ONLYOFFICE_PLUGIN_VERSION
		);

		wp_enqueue_script(
			ONLYOFFICE_PLUGIN_NAME . '-settings',
			ONLYOFFICE_PLUGIN_URL . 'admin/js/settings.js',
			array( 'jquery' ),
			ONLYOFFICE_PLUGIN_VERSION,
			true
		);

		if ( function_exists( 'wp_set_script_translations' ) ) {
			wp_set_script_translations(
				ONLYOFFICE_PLUGIN_NAME . '-settings',
				'onlyoffice-plugin',
				plugin_dir_path( ONLYOFFICE_PLUGIN_FILE ) . 'languages/'
			);
		}

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<form id="onlyoffice-settings-form" action="admin.php?page=onlyoffice-settings" method="post">
				<?php
				settings_fields( 'onlyoffice_settings_group' );
				do_settings_sections( 'onlyoffice_settings_group' );

				$submit_button_args = array(
					'id' => 'onlyoffice-settings-submit-button',
				);
				if ( is_multisite() && ! is_network_admin() && ! $this->allowed_owerwrite_network_settings() ) {
					$submit_button_args = array(
						'disabled' => '',
					);
				}

				submit_button(
					__( 'Save Settings', 'onlyoffice-plugin' ),
					'primary',
					'',
					true,
					$submit_button_args
				);
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
		$this->wp_print_onlyoffice_setting_confirm_dialog();
	}

	/**
	 * Update settings.
	 */
	public function update_plugin_settings() {
		switch ( $this->current_action() ) {
			case 'update':
				check_admin_referer( 'onlyoffice_settings_group-options' );

				if ( is_multisite() && ! is_network_admin() && ! $this->allowed_owerwrite_network_settings() ) {
					wp_die( 'Blocked from changing settings by the Network administrator!', '', array( 'response' => 403 ) );
				}

				$value = array();

				if ( isset( $_POST[ self::DOCSERVER_URL ] ) ) {
					$value[ self::DOCSERVER_URL ] = sanitize_text_field( wp_unslash( $_POST[ self::DOCSERVER_URL ] ) );
				}

				if ( isset( $_POST[ self::DOCSERVER_JWT ] ) ) {
					$value[ self::DOCSERVER_JWT ] = sanitize_text_field( wp_unslash( $_POST[ self::DOCSERVER_JWT ] ) );
				}

				if ( isset( $_POST[ self::JWT_HEADER ] ) ) {
					$value[ self::JWT_HEADER ] = sanitize_text_field( wp_unslash( $_POST[ self::JWT_HEADER ] ) );
				}

				if ( is_multisite() ) {
					if ( is_network_admin() ) {
						if ( isset( $_POST[ self::ALLOWED_OWERWRITE_NETWORK_SETTINGS ] ) ) {
							$value[ self::ALLOWED_OWERWRITE_NETWORK_SETTINGS ] = sanitize_text_field( wp_unslash( $_POST[ self::ALLOWED_OWERWRITE_NETWORK_SETTINGS ] ) );
						} else {
							$value[ self::ALLOWED_OWERWRITE_NETWORK_SETTINGS ] = 0;
						}

						$current_options = get_site_option( 'onlyoffice_settings', array() );
						$new_options     = array_merge( $current_options, $value );

						update_site_option( 'onlyoffice_settings', $new_options );
					} else {
						if ( isset( $_POST[ self::INHERIT_NETWORK_SETTINGS ] ) ) {
							$value[ self::INHERIT_NETWORK_SETTINGS ] = sanitize_text_field( wp_unslash( $_POST[ self::INHERIT_NETWORK_SETTINGS ] ) );

							if ( $value[ self::INHERIT_NETWORK_SETTINGS ] ) {
								$value = array(
									self::INHERIT_NETWORK_SETTINGS => $value[ self::INHERIT_NETWORK_SETTINGS ],
								);
							}
						} else {
							$value[ self::INHERIT_NETWORK_SETTINGS ] = 0;
						}

						$current_options = get_option( 'onlyoffice_settings', array() );
						$new_options     = array_merge( $current_options, $value );

						update_option( 'onlyoffice_settings', $new_options );
					}
				} else {
					$current_options = get_site_option( 'onlyoffice_settings', array() );
					$new_options     = array_merge( $current_options, $value );

					update_option( 'onlyoffice_settings', $new_options );
				}

				add_settings_error( 'general', 'settings_updated', __( 'Settings saved.' ), 'success' );
				set_transient( 'settings_errors', get_settings_errors(), 30 );

				if ( is_network_admin() ) {
					wp_safe_redirect( network_admin_url( 'admin.php?page=onlyoffice-settings&settings-updated=true' ) );
				} else {
					wp_safe_redirect( admin_url( 'admin.php?page=onlyoffice-settings&settings-updated=true' ) );
				}

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
		if ( is_multisite() ) {
			if ( ! self::allowed_owerwrite_network_settings() || self::inherit_network_settings() ) {
				$options = get_site_option( 'onlyoffice_settings' );
			} else {
				$options = get_option( 'onlyoffice_settings' );
			}
		} else {
			$options = get_option( 'onlyoffice_settings' );
		}

		if ( ! empty( $options ) && array_key_exists( $key, $options ) ) {
			return $options[ $key ];
		}

		return $def;
	}

	/**
	 * Return ONLYOFFICE Docs plugin settings value
	 *
	 * @param string $key Setting key.
	 * @param string $def Default value.
	 */
	private function get_onlyoffice_current_value_setting( $key, $def = '' ) {
		if ( is_multisite() ) {
			if ( is_network_admin() ) {
				$options = get_site_option( 'onlyoffice_settings' );
			} else {
				$options = get_option( 'onlyoffice_settings' );
			}
		} else {
			$options = get_option( 'onlyoffice_settings' );
		}

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

	/**
	 * Return setting allowed_owerwrite_network_settings.
	 */
	private static function allowed_owerwrite_network_settings() {
		$options = get_site_option( 'onlyoffice_settings' );
		if ( ! empty( $options ) && array_key_exists( self::ALLOWED_OWERWRITE_NETWORK_SETTINGS, $options ) ) {
			return $options[ self::ALLOWED_OWERWRITE_NETWORK_SETTINGS ];
		}

		return false;
	}

	/**
	 * Return setting inherit_network_settings.
	 */
	private static function inherit_network_settings() {
		$options = get_option( 'onlyoffice_settings' );
		if ( ! empty( $options ) && array_key_exists( self::INHERIT_NETWORK_SETTINGS, $options ) ) {
			return $options[ self::INHERIT_NETWORK_SETTINGS ];
		}

		return true;
	}

	/**
	 * Return confirm update settings dialog.
	 */
	private function wp_print_onlyoffice_setting_confirm_dialog() {
		?>
		<div id="onlyoffice-setting-confirm-dialog" class="notification-dialog-wrap hidden">
			<div class="notification-dialog-background"></div>
			<div class="notification-dialog" role="dialog" tabindex="0">
				<div class="onlyoffice-setting-confirm-dialog-content">
					<h1><?php esc_html_e( 'Settings update', 'onlyoffice-plugin' ); ?></h1>
					<div class="onlyoffice-setting-confirm-message"></div>
					<p>
						<a class="button onlyoffice-setting-confirm-dialog-cancel"><?php esc_html_e( 'Cancel' ); ?></a>
						<button type="button" class="onlyoffice-setting-confirm-dialog-ok button button-primary"><?php esc_html_e( 'OK' ); ?></button>
					</p>
				</div>
			</div>
		</div>
		<?php
	}
}
