<?php

/*
Plugin Name: WP Post By Email
Plugin URI: https://kevin-benabdelhak.fr/plugins/wp-post-by-email/
Description: WP Post by Email est un plugin WordPress qui permet de publier automatiquement des articles en envoyant simplement un e-mail à une adresse configurée. À chaque chargement du site, le plugin se connecte à la boîte mail IMAP spécifiée, récupère tous les nouveaux messages non lus et les publie aussitôt en tant qu’articles WordPress.
Version: 1.0
Author: Kevin BENABDELHAK
*/

if (!defined('ABSPATH')) exit;




if ( !class_exists( 'YahnisElsts\\PluginUpdateChecker\\v5\\PucFactory' ) ) {
    require_once __DIR__ . '/plugin-update-checker/plugin-update-checker.php';
}
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

$monUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/kevinbenabdelhak/WP-Post-By-Email/', 
    __FILE__,
    'wp-post-by-email' 
);

$monUpdateChecker->setBranch('main');





// publication auto (hook init)
add_action('init', function() {
    pbe_check_and_create_posts();
});



include_once plugin_dir_path(__FILE__) . 'options.php';
include_once plugin_dir_path(__FILE__) . 'script/parsing.php';
include_once plugin_dir_path(__FILE__) . 'script/email.php';