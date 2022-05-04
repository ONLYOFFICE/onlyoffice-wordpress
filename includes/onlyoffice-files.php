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

class Onlyoffice_Plugin_Files
{

    public function init_menu()
    {
        $hook = null;
        $logo_svg = file_get_contents(plugin_dir_path(dirname(__FILE__)) . '/public/images/logo.svg');
        $can_upload_files = current_user_can('upload_files');

        if ($can_upload_files) {
            add_menu_page(__('ONLYOFFICE', 'onlyoffice-plugin'), 'ONLYOFFICE', 'upload_files', 'onlyoffice-files',
                array($this, 'files_page'), 'data:image/svg+xml;base64,' . base64_encode($logo_svg));

            $hook = add_submenu_page('onlyoffice-files', 'ONLYOFFICE',
                __('Files', 'onlyoffice-plugin'), 'upload_files', 'onlyoffice-files', array($this, 'files_page'));
        }

        if (!empty($_REQUEST['_wp_http_referer']) && str_contains(sanitize_url($_REQUEST['_wp_http_referer']), 'onlyoffice-files')) {
            $redirect_url = remove_query_arg(array('_wp_http_referer', '_wpnonce'), sanitize_url(wp_unslash($_SERVER['REQUEST_URI'])));
            $redirect_url = str_replace('/wp-admin/admin.php?', sanitize_url($_REQUEST['_wp_http_referer']) . '&', $redirect_url);
            wp_redirect($redirect_url);
            exit;
        }

        add_action("load-$hook", [$this, 'add_files_page']);
    }

    function add_files_page()
    {
        global $Onlyoffice_Plugin_Files_List_Table;
        $Onlyoffice_Plugin_Files_List_Table = new Onlyoffice_Plugin_Files_List_Table();
    }

    public function files_page()
    {
        global $Onlyoffice_Plugin_Files_List_Table;
        $Onlyoffice_Plugin_Files_List_Table->prepare_items();

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p><?php _e('Files that can be edited and opened in ONLYOFFICE will be displayed here', 'onlyoffice-plugin'); ?></p>
            <form method="get">
                <?php $Onlyoffice_Plugin_Files_List_Table->search_box(__('Search'), 'onlyoffice_file'); ?>
                <?php
                $Onlyoffice_Plugin_Files_List_Table->display();
                ?>
            </form>
        </div>
        <?php
    }
}
