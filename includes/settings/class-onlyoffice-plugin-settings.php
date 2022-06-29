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
	 * Init menu.
	 *
	 * @return void
	 */
	public function init_menu() {
		$logo_svg            = file_get_contents( plugin_dir_path( plugin_dir_path( dirname( __FILE__ ) ) ) . '/public/images/logo.svg' );
		$can_manage_settings = current_user_can( 'manage_options' );
		$can_upload_files    = current_user_can( 'upload_files' );

		if ( $can_manage_settings && ! $can_upload_files ) {
			add_menu_page(
				__( 'ONLYOFFICE', 'onlyoffice-plugin' ),
				'ONLYOFFICE',
				'manage_options',
				'onlyoffice-settings',
				array( $this, 'options_page' ),
				'data:image/svg+xml;base64,' . base64_encode( $logo_svg )
			);
		}
		if ( $can_manage_settings && $can_upload_files ) {
			add_submenu_page(
				'onlyoffice-files',
				'ONLYOFFICE',
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
	}

	/**
	 * Input cb
	 *
	 * @param array $args Args.
	 *
	 * @return void
	 */
	public function input_cb( array $args ) {
		$options = get_option( 'onlyoffice_settings' );
		?>
		<input id="<?php echo esc_attr( $args['label_for'] ); ?>" type="text" name="onlyoffice_settings[<?php echo esc_attr( $args['label_for'] ); ?>]" value="<?php echo esc_attr( $options[ $args['label_for'] ] ); ?>">
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
		<p id="<?php echo esc_attr( $args['id'] ); ?>"><?php esc_html_e( 'Configure ONLYOFFICE connector settings', 'onlyoffice-plugin' ); ?></p>
		<?php
	}

	/**
	 * General section callback.
	 *
	 * @global string $settings_updated
	 */
	public function options_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $settings_updated;
		wp_reset_vars( array( 'settings_updated' ) );

		if ( ! empty( $settings_updated ) && sanitize_key( $settings_updated ) === 'true' ) {
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
		</div>
		<?php
	}
}
