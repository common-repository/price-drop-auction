<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Salir si se accede directamente

if(!function_exists('rapgk_pricedrop_auction_admin_menu')){
    // Función para registrar la página de configuración
    function rapgk_pricedrop_auction_admin_menu() {
        add_options_page(
            __('Price drop Auction Settings', 'price-drop-auction'),
            __('Price drop Auction', 'price-drop-auction'),
            'manage_options',
            'pricedrop_auction-settings',
            'rapgk_pricedrop_auction_settings_page'
        );
    }
    add_action('admin_menu', 'rapgk_pricedrop_auction_admin_menu');
}

if(!function_exists('rapgk_pricedrop_auction_settings_page')){
    // Función que muestra la página de configuración
    function rapgk_pricedrop_auction_settings_page() {
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('Price Drop Auction Settings', 'price-drop-auction'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('rapgk_settings_group');
                do_settings_sections('rapgk_settings');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }
}

if(!function_exists('rapgk_settings_init')){
    add_action('admin_init', 'rapgk_settings_init');
    function rapgk_settings_init() {
        register_setting('rapgk_settings_group', 'rapgk_post_types', 'sanitize_text_field');
        register_setting('rapgk_settings_group', 'rapgk_time_interval', 'intval');
        register_setting('rapgk_settings_group', 'rapgk_currency_symbol', 'sanitize_text_field');
        register_setting('rapgk_settings_group', 'rapgk_currency_position', 'sanitize_text_field');
        register_setting('rapgk_settings_group', 'rapgk_thousand_separator', 'sanitize_text_field');
        register_setting('rapgk_settings_group', 'rapgk_decimal_separator', 'sanitize_text_field');
        
        add_settings_section(
            'rapgk_section',
            __('Price drop Auctions Post type settings', 'price-drop-auction'),
            null,
            'rapgk_settings'
        );
    
        add_settings_field(
            'rapgk_post_types',
            __('Post Types', 'price-drop-auction'),
            'rapgk_post_types_render',
            'rapgk_settings',
            'rapgk_section'
        );
    
        // Sección para configuración de intervalos de tiempo
        add_settings_section(
            'rapgk_time_section',
            __('Price Drop Auction Time Settings', 'price-drop-auction'),
            'rapgk_time_section_callback',
            'rapgk_settings'
        );
    
        add_settings_field(
            'rapgk_time_interval',
            __('Time Interval (in seconds)', 'price-drop-auction'),
            'rapgk_time_interval_render',
            'rapgk_settings',
            'rapgk_time_section'
        );
    
        // Sección para configuración de moneda
        add_settings_section(
            'rapgk_currency_section',
            __('Price drop Auction Currency Settings', 'price-drop-auction'),
            'rapgk_currency_section_callback',
            'rapgk_settings'
        );
    
        add_settings_field(
            'rapgk_currency_symbol',
            __('Currency Symbol', 'price-drop-auction'),
            'rapgk_currency_symbol_render',
            'rapgk_settings',
            'rapgk_currency_section'
        );
    
        add_settings_field(
            'rapgk_currency_position',
            __('Currency Position', 'price-drop-auction'),
            'rapgk_currency_position_render',
            'rapgk_settings',
            'rapgk_currency_section'
        );
    
        add_settings_field(
            'rapgk_thousand_separator',
            __('Thousand Separator', 'price-drop-auction'),
            'rapgk_thousand_separator_render',
            'rapgk_settings',
            'rapgk_currency_section'
        );
    
        add_settings_field(
            'rapgk_decimal_separator',
            __('Decimal Separator', 'price-drop-auction'),
            'rapgk_decimal_separator_render',
            'rapgk_settings',
            'rapgk_currency_section'
        );
    
        
    }
}

if(!function_exists('rapgk_post_types_render')){
    function rapgk_post_types_render() {
        $selected_post_types = get_option('rapgk_post_types', []);
        $post_types = get_post_types(['public' => true], 'objects');
        foreach ($post_types as $post_type) {
            $checked = in_array($post_type->name, $selected_post_types) ? 'checked' : '';
            echo '<label><input type="checkbox" name="rapgk_post_types[]" value="' . esc_attr($post_type->name) . '" ' . esc_attr($checked) . '> ' . esc_html($post_type->label) . '</label><br>';
        }
    }
}

if(!function_exists('rapgk_time_section_callback')){
    // Callback para la sección de configuración de tiempo
    function rapgk_time_section_callback() {
        echo '<p>' . esc_html(__('Configure the time intervals for price drop auctions.', 'price-drop-auction')) . '</p>';
    }
}

if(!function_exists('rapgk_time_interval_render')){
    function rapgk_time_interval_render() {
        $time_interval = get_option('rapgk_time_interval', 30); // Valor por defecto: 30 segundos
        echo '<input type="number" min="0" name="rapgk_time_interval" value="' . esc_attr($time_interval) . '" />';
    }
}

if(!function_exists('rapgk_currency_section_callback')){
    // Callback para la sección de configuración de formato moneda
    function rapgk_currency_section_callback() {
        echo '<p>' . esc_html(__('Configure the currency format.', 'price-drop-auction')) . '</p>';
    }
}

if(!function_exists('rapgk_currency_symbol_render')){
    function rapgk_currency_symbol_render() {
        $value = get_option('rapgk_currency_symbol', '€');
        echo '<input type="text" name="rapgk_currency_symbol" value="' . esc_attr($value) . '" />';
    }
}

if(!function_exists('rapgk_currency_position_render')){
    function rapgk_currency_position_render() {
        $value = get_option('rapgk_currency_position', 'before');
        $options = [
            'before' => __('Before', 'price-drop-auction'),
            'after' => __('After', 'price-drop-auction')
        ];
        echo '<select name="rapgk_currency_position">';
        foreach ($options as $key => $label) {
            $selected = $value === $key ? 'selected' : '';
            echo '<option value="' . esc_attr($key) . '" ' . esc_attr($selected) . '>' . esc_html($label) . '</option>';
        }
        echo '</select>';
    }
}

if(!function_exists('rapgk_thousand_separator_render')){
    function rapgk_thousand_separator_render() {
        $value = get_option('rapgk_thousand_separator', ',');
        echo '<input type="text" name="rapgk_thousand_separator" value="' . esc_attr($value) . '" />';
    }
}

if(!function_exists('rapgk_decimal_separator_render')){
    function rapgk_decimal_separator_render() {
        $value = get_option('rapgk_decimal_separator', '.');
        echo '<input type="text" name="rapgk_decimal_separator" value="' . esc_attr($value) . '" />';
    }
}