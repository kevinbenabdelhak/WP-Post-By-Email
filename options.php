<?php 

if (!defined('ABSPATH')) exit;


add_action('admin_menu', function () {
    add_options_page('WP Post by Email', 'WP Post by Email', 'manage_options', 'pbe_settings', 'pbe_render_options_page');
});

function pbe_render_options_page() {
    ?>
    <div class="wrap">
        <h1>WP Post by Email - Réglages</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('pbe_options');
            do_settings_sections('pbe_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', function () {
    register_setting('pbe_options', 'pbe_mail_settings');
    add_settings_section('pbe_section', 'Paramètres Mail', null, 'pbe_settings');

    add_settings_field('pbe_email', 'Adresse mail', function () {
        $opts = get_option('pbe_mail_settings');
        echo "<input type='text' name='pbe_mail_settings[email]' value='" . esc_attr($opts['email'] ?? '') . "' />";
    }, 'pbe_settings', 'pbe_section');

    add_settings_field('pbe_password', 'Mot de passe', function () {
        $opts = get_option('pbe_mail_settings');
        echo "<input type='password' name='pbe_mail_settings[password]' value='" . esc_attr($opts['password'] ?? '') . "' />";
    }, 'pbe_settings', 'pbe_section');

    add_settings_field('pbe_host', 'Serveur IMAP (par ex. : imap.gmail.com)', function () {
        $opts = get_option('pbe_mail_settings');
        echo "<input type='text' name='pbe_mail_settings[host]' value='" . esc_attr($opts['host'] ?? 'imap.gmail.com') . "' />";
    }, 'pbe_settings', 'pbe_section');

    add_settings_field('pbe_port', 'Port (par ex. : 993)', function () {
        $opts = get_option('pbe_mail_settings');
        echo "<input type='number' name='pbe_mail_settings[port]' value='" . esc_attr($opts['port'] ?? '993') . "' />";
    }, 'pbe_settings', 'pbe_section');

    add_settings_field('pbe_allowed_from', "Emails autorisés ('*' pour tous)", function () {
        $opts = get_option('pbe_mail_settings');
        echo "<input type='text' name='pbe_mail_settings[allowed]' value='" . esc_attr($opts['allowed'] ?? '*') . "' />";
        echo '<p class="description">Liste d\'emails autorisés (par virgule) ou * pour tout le monde.</p>';
    }, 'pbe_settings', 'pbe_section');
});
