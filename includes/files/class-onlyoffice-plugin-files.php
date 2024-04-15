<?php
/**
 * Page ONLYOFFICE files.
 *
 * @link       https://github.com/ONLYOFFICE/onlyoffice-wordpress
 * @since      1.0.0
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/files
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
 * Page ONLYOFFICE files.
 *
 * This class defines code necessary displaying a page with files supported by ONLYOFFICE.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/files
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Files {
	/**
	 * Init menu.
	 *
	 * @return void
	 */
	public function init_menu() {
		$hook             = null;
		$logo_svg         = file_get_contents( plugin_dir_path( plugin_dir_path( __DIR__ ) ) . '/public/images/logo.svg' );
		$can_upload_files = current_user_can( 'upload_files' );

		if ( $can_upload_files ) {
			add_menu_page(
				__( 'ONLYOFFICE Docs', 'onlyoffice-plugin' ),
				'ONLYOFFICE Docs',
				'upload_files',
				'onlyoffice-files',
				array( $this, 'files_page' ),
				'data:image/svg+xml;base64,' . base64_encode( $logo_svg ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
			);

			$hook = add_submenu_page(
				'onlyoffice-files',
				'ONLYOFFICE Docs',
				__( 'Files', 'onlyoffice-plugin' ),
				'upload_files',
				'onlyoffice-files',
				array( $this, 'files_page' )
			);
		}

		add_action( "load-$hook", array( $this, 'add_files_page' ) );
	}

	/**
	 * Add files page.
	 *
	 * @return void
	 */
	public function add_files_page() {
		global $onlyoffice_plugin_files_list_table;
		$onlyoffice_plugin_files_list_table = new Onlyoffice_Plugin_Files_List_Table();

		global $_wp_http_referer;
		wp_reset_vars( array( '_wp_http_referer' ) );

		if ( ! empty( $_wp_http_referer ) && isset( $_SERVER['REQUEST_URI'] ) ) {
			wp_safe_redirect( remove_query_arg( array( '_wp_http_referer', '_wpnonce' ), esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ) );
			exit;
		}
	}

	/**
	 *  Files page.
	 *
	 * @global string $_wp_http_referer
	 * @return void
	 */
	public function files_page() {
		global $onlyoffice_plugin_files_list_table;
		$onlyoffice_plugin_files_list_table->prepare_items();

		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Files that can be edited and opened in ONLYOFFICE Docs editor are displayed here.', 'onlyoffice-plugin' ); ?></p>
			<form method="get">
				<?php $onlyoffice_plugin_files_list_table->search_box( __( 'Search' ), 'onlyoffice_file' ); ?>
				<?php $onlyoffice_plugin_files_list_table->display(); ?>
			</form>
		</div>
		<?php
	}
}
