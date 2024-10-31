<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Salir si se accede directamente

if(!functions_exists('rapgk_get_current_price')){
    add_action('wp_ajax_nopriv_get_current_price', 'rapgk_get_current_price');
    add_action('wp_ajax_get_current_price', 'rapgk_get_current_price');
    function rapgk_get_current_price() {
        check_ajax_referer('get_current_price_nonce', 'nonce');
        
        // Verificar si el post_id está presente y es válido
        if (!isset($_POST['post_id']) || intval($_POST['post_id']) <= 0) {
            // Responder con error si no se proporciona un post_id válido
            wp_send_json_error(array('message' => __('Invalid post ID.', 'price-drop-auction')));
            wp_die();
        }
        
        $post_id = intval($_POST['post_id']);
        $auctions = get_post_meta($post_id, '_rapgk_auctions', true) ?: [];
        $response = array('success' => false);
    
        $currency_symbol = get_option('rapgk_currency_symbol', '€');
        $currency_position = get_option('rapgk_currency_position', 'before');
        $thousand_separator = get_option('rapgk_thousand_separator', ',');
        $decimal_separator = get_option('rapgk_decimal_separator', '.');
    
        if (!empty($auctions)) {
            foreach ($auctions as $auction) {
                $start = $auction['start'];
                $end = $auction['end'];
    
                $current_time = current_time('timestamp');
                if (strtotime($start) <= $current_time && $current_time <= strtotime($end)) {
                    $real_price = floatval($auction['real_price']);
                    $min_price = floatval($auction['min_price']);
    
                    /* calculamos el precio total a bajar */
                    $priceToLow = $real_price - $min_price;
    
                    $date1 = new DateTime($start);
                    $date2 = new DateTime($end);
    
                    // Calcular la diferencia
                    $intervalo = $date1->diff($date2);
    
                    // Convertir el intervalo a segundos
                    $segundos = ($intervalo->days * 24 * 60 * 60) +
                                ($intervalo->h * 60 * 60) +
                                ($intervalo->i * 60) +
                                $intervalo->s;
                    
                    $totalIntervals = $segundos / RAPGK_TIME_INTERVAL;
    
                    /* obtenemos el precio que tiene que bajar cada intervalo */
                    $priceInterval = $priceToLow / $totalIntervals;
    
                    /* obtenemos los intervalos que hemos pasado */
                    $start_time_window = strtotime($start);
                    $time_since_start = ($current_time - $start_time_window);
    
                    $intervals = floor($time_since_start / RAPGK_TIME_INTERVAL);
    
                    // Calcular el precio actual
                    $current_price = $real_price - ($priceInterval*$intervals);
    
                    // Formatear los precios
                    if($currency_position == 'after'){
                        $formatted_current_price = number_format($current_price, 2, $decimal_separator, $thousand_separator).$currency_symbol;
                        $pay_now_price_formatted = number_format($current_price/2, 2, $decimal_separator, $thousand_separator).$currency_symbol;
                        $pay_before_price_formatted = number_format($current_price-($current_price/2), 2, $decimal_separator, $thousand_separator).$currency_symbol;
                    }else if($currency_position == 'before'){
                        $formatted_current_price = $currency_symbol.number_format($current_price, 2, $decimal_separator, $thousand_separator);
                        $pay_now_price_formatted = $currency_symbol.number_format($current_price/2, 2, $decimal_separator, $thousand_separator);
                        $pay_before_price_formatted = $currency_symbol.number_format($current_price-($current_price/2), 2, $decimal_separator, $thousand_separator);
                    }
    
                    $response['success'] = true;
                    $response['current_price_formatted'] = $formatted_current_price;
                    $response['pay_now_price_formatted'] = $pay_now_price_formatted;
                    $response['pay_before_price_formatted'] = $pay_before_price_formatted;
                    $response['current_price'] = number_format($current_price/2, 2, '.', '');
                    break;
                }
            }
        }
    
        echo wp_json_encode($response);
        wp_die();
    }
}
