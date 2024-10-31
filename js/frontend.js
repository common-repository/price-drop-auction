jQuery(document).ready(function($) {

    const counterElement = $('.counter');

    if(counterElement.length){
        const changePrice = parseInt(counterElement.attr('data-change-price'),10);
        const dateEnd = new Date(counterElement.attr('data-date-end')).getTime();

        function updateCounter() {

            const now = new Date().getTime();
            const distance = dateEnd - now;

            if (distance < 0) {
                location.reload();
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const hoursTens = Math.floor(hours / 10);
            const hoursUnits = hours % 10;
            const minutesTens = Math.floor(minutes / 10);
            const minutesUnits = minutes % 10;
            const secondsTens = Math.floor(seconds / 10);
            const secondsUnits = seconds % 10;

            $('.counter-digit.hours.tens').text(hoursTens);
            $('.counter-digit.hours.units').text(hoursUnits);
            $('.counter-digit.minutes.tens').text(minutesTens);
            $('.counter-digit.minutes.units').text(minutesUnits);
            $('.counter-digit.seconds.tens').text(secondsTens);
            $('.counter-digit.seconds.units').text(secondsUnits);
        }

        function updatePrice() {
            $.ajax({
                url: wp_ajax.ajaxurl, // `ajaxurl` is a global variable in WordPress for admin-ajax.php
                type: 'POST',
                data: {
                    action: 'get_current_price',
                    post_id: counterElement.closest('.offer').find('.stripe-checkout-button').data('id'),
                    nonce:wp_ajax.nonce
                },
                success: function(response) {
                    const data = JSON.parse(response);
                    if (data.success) {
                        $('.real-now span').text(data.current_price_formatted);
                    }
                }
            });
        }

        function checkPriceUpdate() {
            const now = new Date();
            const seconds = now.getSeconds();

            if (seconds % changePrice === 0) {
                updatePrice();
            }
        }

        setInterval(updateCounter, 1000);
        setInterval(checkPriceUpdate, 1000);
        
    }

});