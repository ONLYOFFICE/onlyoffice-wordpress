<?php
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
 *
 */

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Onlyoffice_Plugin_Files_List_Table extends WP_List_Table
{

    function __construct($args = array()){
        parent::__construct(array(
            'singular' => 'onlyoffice_file',
            'plural' => 'onlyoffice_files',
            'screen' => isset( $args['screen'] ) ? $args['screen'] : null,
        ));

        add_action( 'admin_enqueue_scripts', function ($hook) {
            wp_enqueue_style('onlyoffice_files_table', plugins_url('admin/css/onlyoffice-wordpress-admin.css', dirname(__FILE__)));
        });
    }

    public function ajax_user_can() {
        return current_user_can( 'upload_files' );
    }

    function no_items()
    {
        _e('No files found for editing or viewing in ONLYOFFICE.', 'onlyoffice-plugin');
    }

    function column_default($item, $column_name)
    {
        switch ($column_name) {
            case 'title':
            case 'format':
            case 'date':
            case 'size':
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    function get_sortable_columns()
    {
        return array(
            'title' => array('title', false),
            'format' => array('format', false),
            'date' => array('date', false),
            'size' => array('size', false)
        );
    }

    function get_columns()
    {
        $columns = array(
            'title' => __('Name'),
            'format' => __('Extension'),
            'date' => __('Date'),
            'size' => __('Size')
        );
        return $columns;
    }

    function usort_reorder($a, $b)
    {
        $allowed_keys = ['title', 'format', 'date', 'size'];
        $orderby = (!empty($_GET['orderby'])) && in_array(sanitize_sql_orderby($_GET['orderby']), $allowed_keys)? sanitize_sql_orderby($_GET['orderby']) : 'title';
        $order = (!empty($_GET['order'])) && in_array(sanitize_key($_GET['order']), ['asc', 'desc']) ? sanitize_key($_GET['order']) : 'asc';
        $first = $a[$orderby];
        $second = $b[$orderby];
        if ($orderby === 'title') {
            $first = strtolower($first);
            $second = strtolower($second);
        }
        if ($orderby === 'size') {
            $first = wp_convert_hr_to_bytes($first);
            $second = wp_convert_hr_to_bytes($second);
        }
        $result = $orderby === 'size' ? $first <=> $second : strcmp($first, $second);
        return ($order === 'asc') ? $result : -$result;
    }

    function prepare_items()
    {
        $post_search = isset($_REQUEST['s']) ? wp_unslash(trim(sanitize_text_field($_REQUEST['s']))) : '';
        $attachments = array();
        foreach (get_posts(array('post_type' => 'attachment', 'posts_per_page' => -1)) as $attachment) {
            $attached_file = get_attached_file($attachment->ID);
            $filename = pathinfo($attached_file, PATHINFO_BASENAME);
            if ($post_search !== '' && !str_contains(strtolower($filename), strtolower($post_search))) continue;
            if (Onlyoffice_Plugin_Document_Helper::is_editable($filename) || Onlyoffice_Plugin_Document_Helper::is_openable($filename)) {
                array_push($attachments, array(
                        'id' => $attachment->ID,
                        'title' => pathinfo($attached_file, PATHINFO_FILENAME),
                        'date' => $attachment->post_date,
                        'format' => strtoupper(pathinfo($attached_file, PATHINFO_EXTENSION)),
                        'size' => size_format(filesize($attached_file))
                ));
            }
        }
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);
        usort($attachments, array(&$this, 'usort_reorder'));

        $per_page = 20;
        $current_page = $this->get_pagenum();

        $found_data = array_slice($attachments, (($current_page - 1) * $per_page), $per_page);

        $this->set_pagination_args(array(
            'total_items' => count($attachments),
            'per_page' => $per_page
        ));
        $this->items = $found_data;
    }

    protected function get_default_primary_column_name()
    {
        return 'title';
    }

    public function column_title($file)
    {
        $attached = get_attached_file($file['id']);
        $title = wp_basename($attached);

        $permalink_structure = get_option('permalink_structure');
        $iv = hex2bin(get_option("onlyoffice-plugin-bytes"));
        $hidden_id = urlencode(openssl_encrypt($file['id'], 'aes-256-ctr', get_option("onlyoffice-plugin-uuid"), $options=0, $iv));
        $hidden_id = str_replace('%', ',', $hidden_id);

        $wp_nonce = wp_create_nonce('wp_rest');
        $editor_url = $permalink_structure === '' ? get_option('siteurl') . '/index.php?rest_route=/onlyoffice/oo.editor/' . $hidden_id . '&_wpnonce=' . $wp_nonce
            : get_option('siteurl') . '/wp-json/onlyoffice/oo.editor/' . $hidden_id . '?_wpnonce=' . $wp_nonce;

        ?>
        <strong>
            <a href="<?php echo esc_url($editor_url); ?>" aria-label="<?php echo esc_attr(sprintf(__('&#8220;%s&#8221;'), $title)); ?>">
                <?php
                echo esc_html($title);
                ?>
            </a>
        </strong>
        <?php
    }

    public function column_date($attachment)
    {
        $file = get_post($attachment['id']);
        if ('0000-00-00 00:00:00' === $file->post_date) {
            $h_time = __('Unpublished');
        } else {
            $time = get_post_timestamp($file);
            $time_diff = time() - $time;

            if ($time && $time_diff > 0 && $time_diff < DAY_IN_SECONDS) {
                $h_time = sprintf(__('%s ago'), human_time_diff($time));
            } else {
                $h_time = get_the_time(__('Y/m/d'), $file);
            }
        }

        echo esc_html($h_time);
    }

}
