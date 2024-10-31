<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Salir si se accede directamente

if(!function_exists('rapgk_pricedrop_auction_shortcode')){
    // Shortcode to display pricedrop auction prices
    add_shortcode('rapgk_pricedrop_auction', 'rapgk_pricedrop_auction_shortcode');
    function rapgk_pricedrop_auction_shortcode($atts) {
        global $post;
        $auctions = get_post_meta($post->ID, '_rapgk_auctions', true) ?: [];

        $currency_symbol = get_option('rapgk_currency_symbol', '€');
        $currency_position = get_option('rapgk_currency_position', 'before');
        $thousand_separator = get_option('rapgk_thousand_separator', ',');
        $decimal_separator = get_option('rapgk_decimal_separator', '.');

        ob_start();
        if (!empty($auctions)) {

            $empty = true;
            foreach ($auctions as $auction) {
                $start = $auction['start'];
                $end = $auction['end'];
                $date = $auction['date'];

                $current_time = current_time('timestamp');
                
                if(strtotime($start) <= $current_time && $current_time <= strtotime($end)){
                    $empty = false;

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
                    $formatted_real_price = number_format($real_price, 2, $decimal_separator, $thousand_separator);
                    $formatted_current_price = number_format($current_price, 2, $decimal_separator, $thousand_separator);

                    if($currency_position == 'after'){
                        $formatted_real_price = number_format($real_price, 2, $decimal_separator, $thousand_separator).$currency_symbol;
                        $formatted_current_price = number_format($current_price, 2, $decimal_separator, $thousand_separator).$currency_symbol;
                        $pay_now_price_formatted = number_format($current_price/2, 2, $decimal_separator, $thousand_separator).$currency_symbol;
                        $pay_before_price_formatted = number_format($current_price-($current_price/2), 2, $decimal_separator, $thousand_separator).$currency_symbol;
                    }else if($currency_position == 'before'){
                        $formatted_real_price = $currency_symbol.number_format($real_price, 2, $decimal_separator, $thousand_separator);
                        $formatted_current_price = $currency_symbol.number_format($current_price, 2, $decimal_separator, $thousand_separator);
                        $pay_now_price_formatted = $currency_symbol.number_format($current_price/2, 2, $decimal_separator, $thousand_separator);
                        $pay_before_price_formatted = $currency_symbol.number_format($current_price-($current_price/2), 2, $decimal_separator, $thousand_separator);
                    }

                    echo '<div class="offer"><p class="title-pricedrop">'.esc_html(__('Price drop auction for','price-drop-auction')).' '.esc_html(gmdate('d-m-Y',strtotime($date)))."</p>";
                    echo '<div class="real-price">'.esc_html(__('Initial price','price-drop-auction')).'<span>'.esc_html($formatted_real_price).'</span></div><div class="real-now">'.esc_html(__('Price now','price-drop-auction')).'<span>'.esc_html($formatted_current_price).'</span></div>';
                    echo '<div class="counter" data-change-price="'.esc_attr(str_replace("seconds","",RAPGK_TIME_INTERVAL)).'" data-date-end="' . esc_attr($end) . '">';
                    echo '<div class="counter-digit hours tens">0</div><div class="counter-digit hours units">0</div>';
                    echo '<div class="counter-separator">:</div>';
                    echo '<div class="counter-digit minutes tens">0</div><div class="counter-digit minutes units">0</div>';
                    echo '<div class="counter-separator">:</div>';
                    echo '<div class="counter-digit seconds tens">0</div><div class="counter-digit seconds units">0</div>';
                    echo '</div>';
                    echo '</div>';
                }elseif($current_time < strtotime($start)){
                    $empty = false;
                    echo '<div class="offer"><div><span>'.esc_html(__('Price drop auction for','price-drop-auction')).' '.esc_html(gmdate('d-m-Y',strtotime($date))).'</span><span>'.esc_html(__('Coming soon','price-drop-auction'))."</span></div></div>";
                }
            }

            if($empty){
                esc_html_e('No auctions for now','price-drop-auction');
            }
        }
        ?>
        
        <?php
        $html = ob_get_contents();
        ob_get_clean();

        return $html;
    }
}

if(!function_exists('rapgk_format_price')){
    function rapgk_format_price($price, $currency_symbol, $currency_position) {
        if ($currency_position === 'before') {
            return $currency_symbol . ' ' . $price;
        } else {
            return $price . ' ' . $currency_symbol;
        }
    }
}