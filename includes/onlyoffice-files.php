<?php

class OOP_Files
{

    public function init_menu()
    {
        $hook = null;
        $logo_svg = file_get_contents(plugin_dir_path(dirname(__FILE__)) . '/public/images/logo.svg');
        $can_manage_settings = current_user_can('manage_options');
        $can_upload_files = current_user_can('upload_files');

        if (!$can_manage_settings && $can_upload_files) {
            $hook = add_menu_page(__('ONLYOFFICE', 'onlyoffice-plugin'), 'ONLYOFFICE', 'upload_files', 'onlyoffice-files',
                array($this, 'files_page'), 'data:image/svg+xml;base64,' . base64_encode($logo_svg));
        } elseif ($can_manage_settings && $can_upload_files) {
            $hook = add_submenu_page('onlyoffice-settings', 'ONLYOFFICE',
                __('Files', 'onlyoffice-plugin'), 'upload_files', 'onlyoffice-files', array($this, 'files_page'));
        }

        add_action("load-$hook", [$this, 'add_files_page']);
    }

    function add_files_page()
    {
        global $OOP_Files_List_Table;
        $OOP_Files_List_Table = new OOP_Files_List_Table();
    }

    public function files_page()
    {
        global $OOP_Files_List_Table;

        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <p><?php _e('Files that can be edited and opened in ONLYOFFICE will be displayed here', 'onlyoffice-plugin'); ?></p>
                <form method="get">
                    <?php
                    $OOP_Files_List_Table->prepare_items();
                    $OOP_Files_List_Table->display();
                    ?>
                </form>
        </div>
        <?php
    }
}
