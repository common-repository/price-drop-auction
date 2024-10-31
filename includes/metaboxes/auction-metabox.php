<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Salir si se accede directamente

if(!function_exists('rapgk_add_auction_metabox')){
    // Metabox for auction details
    add_action('add_meta_boxes', 'rapgk_add_auction_metabox');
    function rapgk_add_auction_metabox() {
        $post_types = get_option('rapgk_post_types', []);
        foreach ($post_types as $post_type) {
            add_meta_box(
                'rapgk_auction_metabox',
                __('Auction Details', 'price-drop-auction'),
                'rapgk_auction_metabox_callback',
                $post_type,
                'normal',
                'high'
            );
        }
    }
}

if(!function_exists('rapgk_auction_metabox_callback')){
    function rapgk_auction_metabox_callback($post) {
        wp_nonce_field('rapgk_auction_nonce_action', 'rapgk_auction_nonce');
        $auctions = get_post_meta($post->ID, '_rapgk_auctions', true) ?: [];
        echo '<div id="rapgk_auctions_wrapper">
            <table>
                <thead>
                    <tr>
                        <th>'.esc_html(__('Start auction','price-drop-auction')).'</th>
                        <th>'.esc_html(__('End auction','price-drop-auction')).'</th>
                        <th>'.esc_html(__('Date offer','price-drop-auction')).'</th>
                        <th>'.esc_html(__('Real price','price-drop-auction')).'</th>
                        <th>'.esc_html(__('Min price','price-drop-auction')).'</th>
                        <th>'.esc_html(__('Options','price-drop-auction')).'</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($auctions as $index => $auction) {
            rapgk_render_auction_fields($index, $auction);
        }
        echo '</tbody>
        </table>
        </div>';
        echo '<button type="button" id="rapgk_add_auction">' . esc_html(__('Add Auction', 'price-drop-auction')) . '</button>';
    }
}

if(!function_exists('rapgk_render_auction_fields')){
    function rapgk_render_auction_fields($index, $auction = []) {
        $start = $auction['start'] ?? '';
        $end = $auction['end'] ?? '';
        $date = $auction['date'] ?? '';
        $real_price = $auction['real_price'] ?? '';
        $min_price = $auction['min_price'] ?? '';
        $paid = $auction['paid'] ?? 0;
        echo '<tr class="rapgk_auction">
                <td><input type="text" class="datetimepicker" name="rapgk_auctions[' . esc_attr($index) . '][start]" value="' . esc_attr($start) . '" required></td>
                <td><input type="text" class="datetimepicker" name="rapgk_auctions[' . esc_attr($index) . '][end]" value="' . esc_attr($end) . '" required></td>
                <td><input type="text" class="datetimepicker" name="rapgk_auctions[' . esc_attr($index) . '][date]" value="' . esc_attr($date) . '" required></td>
                <td><input type="number" step="0.01" name="rapgk_auctions[' . esc_attr($index) . '][real_price]" value="' . esc_attr($real_price) . '" required></td>
                <td><input type="number" step="0.01" name="rapgk_auctions[' . esc_attr($index) . '][min_price]" value="' . esc_attr($min_price) . '" required></td>
                <td><button type="button" class="rapgk_remove_auction">' . esc_html(__('Remove', 'price-drop-auction')) . '</button></td>
              </tr>';
    }
}

if(!function_exists('rapgk_save_auction_metabox')){
    add_action('save_post', 'rapgk_save_auction_metabox');
    function rapgk_save_auction_metabox($post_id) {
        if (!isset($_POST['rapgk_auction_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['rapgk_auction_nonce'])), 'rapgk_auction_nonce_action')) {
            return;
        }
        if (isset($_POST['rapgk_auctions'])) {
            $rapgk_auctions = sanitize_text_field(wp_unslash($_POST['rapgk_auctions'])); // Unsash the input
            $rapgk_auctions_data = array();
    
            foreach (sanitize_text_field(wp_unslash($_POST['rapgk_auctions'])) as $auction) {
                // Sanitizar cada campo
                $date = sanitize_text_field($auction['date']);
                $start = sanitize_text_field($auction['start']);
                $end = sanitize_text_field($auction['end']);
                $real_price = intval($auction['real_price']);
                $min_price = intval($auction['min_price']);
                $paid = $auction['paid'];
                
                // Crear un arreglo para cada elemento
                $auction_item = array(
                    'date' => $date,
                    'start' => $start,
                    'end' => $end,
                    'real_price' => $real_price,
                    'min_price' => $min_price,
                    'paid' => $paid
                );
    
                // Agregar al arreglo principal
                $rapgk_auctions_data[] = $auction_item;
            }
    
            // Actualizar post meta con los datos preparados
            update_post_meta($post_id, '_rapgk_auctions', $rapgk_auctions_data);
        }
    }
}