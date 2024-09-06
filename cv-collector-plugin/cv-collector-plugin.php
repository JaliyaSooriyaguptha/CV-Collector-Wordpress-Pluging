<?php

/**
 * Plugin Name: CV Collector Plugin
 * Description: A plugin to collect and manage CVs.
 * Version: 1.1
 * Author: Sithumini
 * License: GPL2
 */

// Prevent direct access
defined('ABSPATH') or die('No script kiddies please!');

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/cv-collector-functions.php';

// Register activation hook
register_activation_hook(__FILE__, 'cv_collector_install');

// Enqueue scripts and styles
function cv_collector_enqueue_scripts() {
    wp_enqueue_style('cv-collector-style', plugins_url('/css/style.css', __FILE__));
    wp_enqueue_script('cv-collector-js', plugins_url('/js/cv-collector.js', __FILE__), array('jquery'), null, true);
    wp_localize_script('cv-collector-js', 'cv_collector_params', array('ajax_url' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'cv_collector_enqueue_scripts');

// Add admin menu
function cv_collector_admin_menu() {
    add_menu_page(
        'CV Collector Settings',
        'CV Collector',
        'manage_options',
        'cv-collector-settings',
        'cv_collector_settings_page',
        'dashicons-clipboard',
        20
    );
}

add_action('admin_menu', 'cv_collector_admin_menu');

// Display settings page content
function cv_collector_settings_page() {
    ?>
    <div class="wrap">
        <h1>CV Collector Plugin Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('cv_collector_options_group');
            do_settings_sections('cv_collector_settings');
            submit_button();
            ?>
        </form>
        <h2>Usage Instructions</h2>
        <p>To display the CV submission form, use the following shortcode:</p>
        <pre>[cv_collector_form]</pre>
        <p>To display the CV database with a filterable table, use the following shortcode:</p>
        <pre>[cv_collector_display]</pre>
        <p>Simply copy and paste these shortcodes into any page or post where you want the form or database to appear.</p>
    </div>
    <?php
}

// Register and define the settings
function cv_collector_register_settings() {
    register_setting('cv_collector_options_group', 'cv_collector_options');

    add_settings_section(
        'cv_collector_main_section',
        'Main Settings',
        'cv_collector_section_text',
        'cv_collector_settings'
    );

    add_settings_field(
        'cv_collector_field_shortcode_form',
        'Form Shortcode',
        'cv_collector_field_shortcode_form_callback',
        'cv_collector_settings',
        'cv_collector_main_section'
    );

    add_settings_field(
        'cv_collector_field_shortcode_display',
        'Display Shortcode',
        'cv_collector_field_shortcode_display_callback',
        'cv_collector_settings',
        'cv_collector_main_section'
    );
}

add_action('admin_init', 'cv_collector_register_settings');

function cv_collector_section_text() {
    echo '<p>Settings for the CV Collector Plugin.</p>';
}

function cv_collector_field_shortcode_form_callback() {
    echo '<input type="text" value="[cv_collector_form]" readonly="readonly" style="width: 100%; font-size: 16px;">';
}

function cv_collector_field_shortcode_display_callback() {
    echo '<input type="text" value="[cv_collector_display]" readonly="readonly" style="width: 100%; font-size: 16px;">';
}
