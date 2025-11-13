<?php
/**
 * Plugin Name: Courses
 * Description: Lekka wtyczka LMS "Courses" — kursy, lekcje, zapisy, postęp, egzamin i certyfikat. Polski interfejs.
 * Version: 0.3.1
 * Author: Oui42 (generated)
 * Text Domain: courses
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'COURSES_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'COURSES_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once COURSES_PLUGIN_DIR . 'includes/post-types.php';
require_once COURSES_PLUGIN_DIR . 'includes/admin.php';
require_once COURSES_PLUGIN_DIR . 'includes/rest-api.php';
require_once COURSES_PLUGIN_DIR . 'includes/shortcodes.php';
require_once COURSES_PLUGIN_DIR . 'includes/payments.php';
require_once COURSES_PLUGIN_DIR . 'includes/progress.php';
require_once COURSES_PLUGIN_DIR . 'includes/exam.php';
require_once COURSES_PLUGIN_DIR . 'includes/certificates.php';

register_activation_hook( __FILE__, function() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $tables = [];

    $tables['enrollments'] = "CREATE TABLE {
    $wpdb->prefix}courses_enrollments (\n id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n course_id bigint(20) unsigned NOT NULL,\n user_id bigint(20) unsigned DEFAULT NULL,\n email varchar(191) DEFAULT NULL,\n status varchar(50) DEFAULT 'enrolled',\n created_at datetime DEFAULT CURRENT_TIMESTAMP,\n PRIMARY KEY  (id),\n KEY course_id (course_id),\n KEY user_id (user_id)\n) $charset_collate;";

    $tables['progress'] = "CREATE TABLE {
    $wpdb->prefix}courses_progress (\n id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n course_id bigint(20) unsigned NOT NULL,\n lesson_id bigint(20) unsigned NOT NULL,\n user_id bigint(20) unsigned NOT NULL,\n completed_at datetime DEFAULT CURRENT_TIMESTAMP,\n PRIMARY KEY  (id),\n KEY course_lesson (course_id, lesson_id),\n KEY user_id (user_id)\n) $charset_collate;";

    $tables['certificates'] = "CREATE TABLE {
    $wpdb->prefix}courses_certificates (\n id bigint(20) unsigned NOT NULL AUTO_INCREMENT,\n course_id bigint(20) unsigned NOT NULL,\n user_id bigint(20) unsigned NOT NULL,\n name varchar(191) DEFAULT NULL,\n issued_at datetime DEFAULT CURRENT_TIMESTAMP,\n serial varchar(191) DEFAULT NULL,\n PRIMARY KEY  (id),\n KEY course_id (course_id),\n KEY user_id (user_id)\n) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    foreach ( $tables as $sql ) {
        dbDelta( $sql );
    }

    $role = get_role( 'administrator' );
    if ( $role ) {
        $role->add_cap( 'manage_courses' );
    }
});

register_deactivation_hook( __FILE__, function() {
    $role = get_role( 'administrator' );
    if ( $role ) {
        $role->remove_cap( 'manage_courses' );
    }
});

add_action( 'admin_enqueue_scripts', function( $hook ) {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
    if ( ! $screen ) {
        return;
    }
    if ( $screen->post_type === 'course' ) {
        wp_enqueue_script( 'courses-admin-exam-builder', COURSES_PLUGIN_URL . 'assets/admin-exam-builder.js', array( 'jquery' ), '0.1', true );
        wp_enqueue_style( 'courses-admin-exam-builder', COURSES_PLUGIN_URL . 'assets/admin-exam-builder.css', array(), '0.1' );
    }
});
