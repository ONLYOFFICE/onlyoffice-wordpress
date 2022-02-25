<?php

class OOP_Settings
{
    const docserver_url = 'onlyoffice_settings_docserver_url';
    const docserver_jwt = 'onlyoffice_settings_docserver_jwt';

    public function init_menu()
    {
        $logo_svg = file_get_contents(plugin_dir_path(dirname(__FILE__)) . '/public/images/logo.svg');
        add_menu_page(__('ONLYOFFICE', 'onlyoffice-plugin'), 'ONLYOFFICE', 'manage_options', 'onlyoffice-settings',
            array($this, 'options_page'), 'data:image/svg+xml;base64,' . base64_encode($logo_svg));

        add_submenu_page('onlyoffice-settings', __('ONLYOFFICE', 'onlyoffice-plugin'),
            __('Settings', 'onlyoffice-plugin'), 'manage_options', 'onlyoffice-settings');

        add_submenu_page('onlyoffice-settings', 'ONLYOFFICE',
            __('Files', 'onlyoffice-plugin'), 'manage_options', 'onlyoffice-files', array($this, 'files_page'));
    }

    public function init()
    {
        register_setting('onlyoffice_settings_group', 'onlyoffice_settings');

        add_settings_section(
            'onlyoffice_settings_general_section',
            '',
            array($this, 'general_section_callback'),
            'onlyoffice_settings_group'
        );

        add_settings_field(
            'onlyoffice_settings_docserver_url',
            __('Document Editing Service address', 'onlyoffice-plugin'),
            array($this, 'input_cb'),
            'onlyoffice_settings_group',
            'onlyoffice_settings_general_section',
            array(
                'label_for'         => OOP_Settings::docserver_url,
                'desc' => ''
            )
        );

        add_settings_field(
            'onlyoffice_settings_docserver_jwt',
            __('Document server JWT secret key', 'onlyoffice-plugin'),
            array($this, 'input_cb'),
            'onlyoffice_settings_group',
            'onlyoffice_settings_general_section',
            array(
                'label_for'         => OOP_Settings::docserver_jwt,
                'desc' => 'Secret key (leave blank to disable)'
            )
        );
    }

    public function input_cb($args) {
        $options = get_option('onlyoffice_settings');
    ?>
        <input id="<?php echo esc_attr($args['label_for']) ?>" type="text" name="onlyoffice_settings[<?php echo esc_attr($args['label_for']); ?>]" value="<?php echo $options[$args['label_for']]; ?>">
        <p class="description">
            <?php esc_html_e($args['desc'], 'onlyoffice-plugin'); ?>
        </p>
    <?php
    }

    public function general_section_callback($args)
    {
    ?>
        <p id="<?php echo esc_attr($args['id']); ?>"><?php esc_html_e('Configure ONLYOFFICE connector settings', 'onlyoffice-plugin'); ?></p>
    <?php
    }

    public function options_page()
    {

        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error('onlyoffice_settings_messages', 'onlyoffice_message', __('Settings Saved', 'onlyoffice-plugin'), 'updated'); // ToDo: can also check if settings are valid e.g. make connection to docServer
        }

        settings_errors('onlyoffice_settings_messages');
    ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('onlyoffice_settings_group');
                do_settings_sections('onlyoffice_settings_group');
                submit_button(__('Save Settings', 'onlyoffice-plugin'));
                ?>
            </form>
        </div>
<?php
    }

    public function files_page(){

        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
            <div class="wrap">
                <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            </div>
        <?php
    }
}
