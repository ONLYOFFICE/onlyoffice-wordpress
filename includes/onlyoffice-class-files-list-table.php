<?php

if (!class_exists('WP_List_Table')) {
    require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class OOP_Files_List_Table extends WP_List_Table
{

    function __construct()
    {

        parent::__construct(array(
            'singular' => 'onlyoffice_file',
            'plural' => 'onlyoffice_files',
            'ajax' => false
        ));

        add_action('admin_head', array(&$this, 'admin_header'));
    }

    function admin_header()
    {
        $page = (isset($_GET['page'])) ? esc_attr($_GET['page']) : false;
        if ('onlyoffice-files' != $page)
            return;
        echo '<style type="text/css">';
        echo '.wp-list-table .column-title { width: 40%; }';
        echo '.wp-list-table .column-format { width: 15%; }';
        echo '.wp-list-table .column-date { width: 15%;}';
        echo '</style>';
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
                return $item[$column_name];
            default:
                return print_r($item, true);
        }
    }

    function get_sortable_columns()
    {
        $sortable_columns = array(
            'title' => array('title', false),
            'format' => array('format', false),
            'date' => array('date', false)
        );
        return $sortable_columns;
    }

    function get_columns()
    {
        $columns = array(
            'title' => __('Name'),
            'format' => __('Extension'),
            'date' => __('Date')
        );
        return $columns;
    }

    function usort_reorder($a, $b)
    {
        $orderby = (!empty($_GET['orderby'])) ? $_GET['orderby'] : 'title';
        $order = (!empty($_GET['order'])) ? $_GET['order'] : 'asc';
        $result = strcmp($a[$orderby], $b[$orderby]);
        return ($order === 'asc') ? $result : -$result;
    }

    function prepare_items()
    {
        $attachments = array();
        foreach (get_posts(array('post_type' => 'attachment')) as $attachment) {
            $filename = substr($attachment->guid, strrpos($attachment->guid, '/') + 1);
            if (OOP_Document_Helper::is_editable($filename) || OOP_Document_Helper::is_openable($filename)) {
                array_push($attachments, array(
                        'id' => $attachment->ID,
                        'title' => $filename,
                        'post_date' => $attachment->post_date,
                        'format' => strtoupper(pathinfo($filename, PATHINFO_EXTENSION))
                ));
            }
        }
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();

        $this->_column_headers = array($columns, $hidden, $sortable);
        usort($attachments, array(&$this, 'usort_reorder'));

        $per_page = 15;
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
        $title = $file['title'];
        $link_start = '';
        $link_end = '';

        if (current_user_can('edit_post', $file['id'])) {
            $permalink_structure = get_option('permalink_structure');
            $hidden_id = str_replace('.', ',', OOP_JWT_Manager::jwt_encode(["attachment_id" => $file['id']],
                get_option("onlyoffice-plugin-uuid")));

            $wp_nonce = wp_create_nonce('wp_rest');
            $editor_url = $permalink_structure === '' ? get_option('siteurl') . '/index.php?rest_route=/onlyoffice/editor/' . $hidden_id . '&_wpnonce=' . $wp_nonce
                : get_option('siteurl') . '/wp-json/onlyoffice/editor/' . $hidden_id . '?_wpnonce=' . $wp_nonce;

            $link_start = sprintf(
                '<a href="%s" aria-label="%s">',
                $editor_url,
                esc_attr(sprintf(__('&#8220;%s&#8221;'), $title))
            );
            $link_end = '</a>';
        }

        ?>
        <strong>
            <?php
            echo $link_start;
            echo $title . $link_end;
            ?>
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

        echo $h_time;
    }

}
