<?php
add_action('admin_enqueue_scripts', 'woo_tiny_admin_enqueue_scripts');
add_action('wp_enqueue_scripts', 'woo_tiny_client_enqueue_scripts');

function woo_tiny_admin_enqueue_scripts(){
    wp_enqueue_style('export-pdf', WOO_TINY_URL . 'assets/css/export-pdf.css');

    wp_enqueue_script('jspdf', WOO_TINY_URL . 'assets/modules/jspdf/jspdf.min.js');
    wp_enqueue_script('export-pdf', WOO_TINY_URL . 'assets/js/export-pdf.js', ['jquery', 'jspdf']);
}

function woo_tiny_client_enqueue_scripts(){

}