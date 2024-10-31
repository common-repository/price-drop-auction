<?php
/*
Plugin Name: Price Drop Auction
Plugin URI: 
Description: Plugin to manage price drop auctions in WordPress.
Version: 1.0
Author: Gecko Studio
Author URI: https://geckostudio.es
License: GPL v2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin includes Timepicker Addon (https://github.com/trentrichardson/jQuery-Timepicker-Addon)
licensed under MIT License (https://opensource.org/licenses/MIT).
*/

/*
Copyright 2024 Gecko Studio

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Salir si se accede directamente

// Obtener los valores desde la configuración
$time_interval = get_option('rapgk_time_interval', array());

// Define constantes y variables globales basadas en la configuración
define('RAPGK_TIME_INTERVAL', isset($time_interval) ? $time_interval."seconds" : "30seconds");                  // Intervalo de tiempo en minutos por defecto
define('RAPGK_PLUGIN_DIR', plugin_dir_path(__FILE__));

if(!function_exists('rapgk_enqueue_admin_scripts')){
    add_action('admin_enqueue_scripts', 'rapgk_enqueue_admin_scripts');
    function rapgk_enqueue_admin_scripts($hook_suffix) {
        if ('post.php' === $hook_suffix || 'post-new.php' === $hook_suffix) {
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-core');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style('jquery-ui-css', plugins_url('/css/jquery-ui.css',__FILE__),'1.0',true);
            wp_enqueue_style('jquery-ui-timepicker-addon-css', plugins_url('/css/jquery-ui-timepicker-addon.min.css', __FILE__),'1.0',true);
            
            wp_enqueue_script('datetimepicker-init', plugins_url('/js/admin.js', __FILE__), array('jquery', 'jquery-ui-datepicker'), '1.0', true);
            wp_enqueue_script('jquery-ui-timepicker-addon', plugins_url('/js/jquery-ui-timepicker-addon.min.js', __FILE__), array('jquery', 'jquery-ui-datepicker'), '1.0', true);
            
        }
    }
}

if(!function_exists('rapgk_enqueue_front_scripts')){
    add_action( 'wp_enqueue_scripts', 'rapgk_enqueue_front_scripts' );
    function rapgk_enqueue_front_scripts() {
        // Obtén los tipos de contenido seleccionados en la configuración
        global $post;
        
        if ( has_shortcode( $post->post_content, 'rapgk_pricedrop_auction' ) ) {
            wp_enqueue_style('custom-pricedrop-auction', plugins_url('/css/custom.css', __FILE__),array(),'1.0',true);
            wp_enqueue_script('frontend-pricedrop-auction', plugins_url('/js/frontend.js', __FILE__), array('jquery', 'jquery-ui-datepicker'), '1.0', true);
            
            // Localiza la variable ajaxurl para que esté disponible en tu script
            wp_localize_script('frontend-pricedrop-auction', 'wp_ajax', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('get_current_price_nonce')
            ));
            
            // Añadir el atributo defer o async al script
            add_filter('script_loader_tag', function($tag, $handle) {
                if ('frontend-pricedrop-auction' !== $handle) {
                    return $tag;
                }
                // Usar defer
                return str_replace('src', 'defer="defer" src', $tag);
                // O si prefieres async, reemplaza la línea anterior con:
                // return str_replace('src', 'async="async" src', $tag);
            }, 10, 2);
        }
        
    }
}

// Incluir archivos principales del plugin
require_once plugin_dir_path(__FILE__) . 'includes/admin/admin-page.php';
require_once plugin_dir_path(__FILE__) . 'includes/metaboxes/auction-metabox.php';
require_once plugin_dir_path(__FILE__) . 'includes/helpers/shortcode.php';
require_once plugin_dir_path(__FILE__) . 'includes/helpers/ajaxcalls.php';