<?php
/**
 * List Table API: Onlyoffice_Plugin_Files_List_Table class
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

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * Core class used to implement displaying files in a list table for the network admin.
 *
 * @package    Onlyoffice_Plugin
 * @subpackage Onlyoffice_Plugin/includes/files
 * @author     Ascensio System SIA <integration@onlyoffice.com>
 */
class Onlyoffice_Plugin_Files_List_Table extends WP_List_Table {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @see WP_List_Table::__construct() for more information on default arguments.
	 *
	 * @param array $args An associative array of arguments.
	 */
	public function __construct( $args = array() ) {
		parent::__construct(
			array(
				'singular' => 'onlyoffice_file',
				'plural'   => 'onlyoffice_files',
				'screen'   => isset( $args['screen'] ) ? $args['screen'] : null,
			)
		);

		add_action(
			'admin_enqueue_scripts',
			function () {
				wp_enqueue_script(
					ONLYOFFICE_PLUGIN_NAME . '-media-script',
					plugin_dir_url( __FILE__ ) . 'js/onlyoffice-files-list-table.js',
					array( 'jquery', 'clipboard', 'wp-a11y' ),
					ONLYOFFICE_PLUGIN_FILE,
					true
				);

				wp_enqueue_style( 'onlyoffice_files_table', ONLYOFFICE_PLUGIN_URL . 'admin/css/onlyoffice-wordpress-admin.css', array(), ONLYOFFICE_PLUGIN_VERSION );
			}
		);
	}

	/**
	 * Check the current user's permissions.
	 *
	 * @return bool
	 */
	public function ajax_user_can() {
		return current_user_can( 'upload_files' );
	}

	/**
	 * Output 'no files' message.
	 */
	public function no_items() {
		esc_html_e( 'No files found for editing or viewing in ONLYOFFICE Docs editor.', 'onlyoffice-plugin' );
	}
	/**
	 * Handles output for the default column.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Post $item        The current WP_Post object.
	 * @param string  $column_name Current column name.
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
			case 'format':
			case 'date':
			case 'size':
			case 'link':
				return $item[ $column_name ];
			default:
				return $item['title'];
		}
	}

	/**
	 * Get a list of sortable columns for the list table.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of sortable columns.
	 */
	public function get_sortable_columns() {
		return array(
			'title'  => array( 'title', false ),
			'format' => array( 'format', false ),
			'date'   => array( 'date', false ),
			'size'   => array( 'size', false ),
			'link'   => array( 'link', false ),
		);
	}

	/**
	 * Get a list of columns for the list table.
	 *
	 * @since 1.0.0
	 *
	 * @return string[] Array of column titles keyed by their column name.
	 */
	public function get_columns() {
		$columns = array(
			'title'  => __( 'Name' ),
			'format' => __( 'Extension' ),
			'date'   => __( 'Date' ),
			'size'   => __( 'Size' ),
			'link'   => __( 'Link' ),
		);
		return $columns;
	}

	/**
	 * Get a list of sortable columns for the list table.
	 *
	 * @global string $orderby
	 * @global string $order
	 * @param array $a Param a.
	 * @param array $b Param b.
	 * @return array Array of sortable columns.
	 * @since 1.0.0
	 */
	public function usort_reorder( $a, $b ) {
		global $orderby, $order;
		wp_reset_vars( array( 'orderby', 'order' ) );

		$allowed_keys = array( 'title', 'format', 'date', 'size' );

		$order_by = in_array( sanitize_sql_orderby( $orderby ), $allowed_keys, true ) ? sanitize_sql_orderby( $orderby ) : 'title';

		$first  = $a[ $order_by ];
		$second = $b[ $order_by ];
		if ( 'title' === $order_by ) {
			$first  = strtolower( $first );
			$second = strtolower( $second );
		}
		if ( 'size' === $order_by ) {
			$first  = wp_convert_hr_to_bytes( $first );
			$second = wp_convert_hr_to_bytes( $second );
		}
		$result = 'size' === $order_by ? $first <=> $second : strcmp( $first, $second );
		return ( 'desc' === $order ) ? -$result : $result;
	}

	/**
	 * Prepare the file list for display.
	 *
	 * @since 1.0.0
	 * @global string $s
	 */
	public function prepare_items() {
		global $s;
		wp_reset_vars( array( 's' ) );

		$attachments = array();
		foreach ( get_posts(
			array(
				'post_type'      => 'attachment',
				'posts_per_page' => -1,
			)
		) as $attachment ) {
			$attached_file = get_attached_file( $attachment->ID );
			$filename      = pathinfo( $attached_file, PATHINFO_BASENAME );
			if ( ( '' !== $s ) && ! str_contains( strtolower( $filename ), strtolower( $s ) ) ) {
				continue;
			}
			if ( ( Onlyoffice_Plugin_Document_Manager::is_editable( $filename )
						|| Onlyoffice_Plugin_Document_Manager::is_viewable( $filename ) )
					&& Onlyoffice_Plugin_Document_Manager::can_user_view_attachment( $attachment->ID ) ) {
				array_push(
					$attachments,
					array(
						'id'     => $attachment->ID,
						'title'  => pathinfo( $attached_file, PATHINFO_FILENAME ),
						'format' => strtoupper( pathinfo( $attached_file, PATHINFO_EXTENSION ) ),
						'date'   => $attachment->post_modified,
						'size'   => size_format( filesize( $attached_file ) ),
						'link'   => Onlyoffice_Plugin_Url_Manager::get_editor_url( $attachment->ID ),
					)
				);
			}
		}
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		usort( $attachments, array( &$this, 'usort_reorder' ) );

		$per_page     = 20;
		$current_page = $this->get_pagenum();

		$found_data = array_slice( $attachments, ( ( $current_page - 1 ) * $per_page ), $per_page );

		$this->set_pagination_args(
			array(
				'total_items' => count( $attachments ),
				'per_page'    => $per_page,
			)
		);
		$this->items = $found_data;
	}

	/**
	 * Gets the name of the default primary column.
	 *
	 * @since 1.0.0
	 *
	 * @return string Name of the default primary column, in this case, 'title'.
	 */
	protected function get_default_primary_column_name() {
		return 'title';
	}

	/**
	 * Handles the title column output.
	 *
	 * @since 1.0.0
	 * @param array $item The item.
	 */
	public function column_title( $item ) {
		$attached = get_attached_file( $item['id'] );
		$title    = wp_basename( $attached );

		$editor_url = Onlyoffice_Plugin_Url_Manager::get_editor_url( $item['id'] );

		?>
		<strong>
			<a target="_blank" href="<?php echo esc_url( $editor_url ); ?>" >
				<?php
				echo esc_html( $title );
				?>
			</a>
		</strong>
		<?php
	}

	/**
	 * Handles the post date column output.
	 *
	 * @since 1.0.0
	 * @param array $item The item.
	 */
	public function column_date( $item ) {
		$file = get_post( $item['id'] );

		$last_user = get_userdata( get_post_meta( $item['id'], '_edit_last', true ) );
		if ( $last_user ) {
			/* translators: 1: Name of most recent post author, 2: Post edited date, 3: Post edited time. */
			$h_time = sprintf( __( 'Last edited by %1$s on %2$s at %3$s' ), esc_html( $last_user->display_name ), mysql2date( __( 'F j, Y' ), $file->post_modified ), mysql2date( __( 'g:i a' ), $file->post_modified ) );
		} else {
			/* translators: 1: Post edited date, 2: Post edited time. */
			$h_time = sprintf( __( 'Last edited on %1$s at %2$s' ), mysql2date( __( 'F j, Y' ), $file->post_modified ), mysql2date( __( 'g:i a' ), $file->post_modified ) );
		}

		echo esc_html( $h_time );
	}

	/**
	 * Handles the post link column output.
	 *
	 * @since 1.0.0
	 * @param array $item The item.
	 */
	public function column_link( $item ) {
		$file = get_post( $item['id'] );
		?>
		<span class="copy-to-clipboard-container">
			<button type="button" data-clipboard-text="<?php echo esc_url( Onlyoffice_Plugin_Url_Manager::get_editor_url( $item['id'] ) ); ?>" class="onlyoffice-editor-link button-link has-icon" title="<?php esc_attr_e( 'Copy URL' ); ?>" aria-label="<?php esc_attr_e( 'Link' ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false"><path d="M15.6 7.2H14v1.5h1.6c2 0 3.7 1.7 3.7 3.7s-1.7 3.7-3.7 3.7H14v1.5h1.6c2.8 0 5.2-2.3 5.2-5.2 0-2.9-2.3-5.2-5.2-5.2zM4.7 12.4c0-2 1.7-3.7 3.7-3.7H10V7.2H8.4c-2.9 0-5.2 2.3-5.2 5.2 0 2.9 2.3 5.2 5.2 5.2H10v-1.5H8.4c-2 0-3.7-1.7-3.7-3.7zm4.6.9h5.3v-1.5H9.3v1.5z"></path></svg>
			</button>
			<span class="success hidden" aria-hidden="true"><?php esc_html_e( 'Copied!' ); ?>
		</span>
		<?php
	}

	/**
	 * Displays the search box.
	 *
	 * @since 1.0.2
	 *
	 * @param string $text     The 'submit' button label.
	 * @param string $input_id ID attribute value for the search input field.
	 * @global string $s
	 * @global string $page
	 */
	public function search_box( $text, $input_id ) {
		global $s, $page;
		wp_reset_vars( array( 's', 'page' ) );

		if ( empty( $s ) && ! $this->has_items() ) {
			return;
		}

		if ( ! empty( $page ) ) {
			echo '<input type="hidden" name="page" value="' . esc_attr( $page ) . '" />';
		}

		parent::search_box( $text, $input_id );
	}
}
